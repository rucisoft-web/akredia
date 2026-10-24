<?php

namespace App\Services;

use App\Enums\PublicationType;
use App\Models\Lecturer;
use App\Models\ProgramYearStat;
use App\Models\Publication;
use App\Models\StudentCohort;
use App\Models\StudyProgram;
use App\Support\Spreadsheet;
use Illuminate\Support\Facades\DB;

/** Impor Data Kinerja dari XLSX/CSV (ekspor SIAKAD, SISTER, atau PD Dikti) dengan templat kolom tetap. */
class DataImporter
{
    public const TYPES = [
        'dosen' => ['Dosen', ['nidn', 'nama', 'kode_prodi', 'status', 'pendidikan', 'jabatan', 'serdos', 'aktif'],
            'status: tetap/tidak_tetap · pendidikan: S1/S2/S3 · jabatan: gb/lk/l/aa/none · serdos & aktif: 1/0. Baris dicocokkan dengan NIDN.'],
        'statistik' => ['Statistik mahasiswa per prodi per tahun', ['kode_prodi', 'tahun', 'pendaftar', 'mahasiswa_baru', 'mahasiswa_aktif', 'lulusan', 'luar_prodi_10sks', 'rerata_masa_studi_bulan', 'rerata_ipk'],
            'Satu baris per prodi per tahun; data yang sudah ada ditimpa.'],
        'kohort' => ['Kohort kelulusan', ['kode_prodi', 'tahun_masuk', 'jumlah_masuk', 'lulus_tepat_waktu', 'lulus_dua_kali_masa', 'masih_aktif', 'keluar'],
            'Satu baris per prodi per tahun masuk.'],
        'publikasi' => ['Publikasi dosen', ['nidn', 'tahun', 'judul', 'jenis', 'kategori', 'media', 'sitasi', 'url'],
            'jenis: '.'jurnal_intl_bereputasi/jurnal_intl/jurnal_nas_terakreditasi/jurnal_nas/seminar_intl/seminar_nas/media_massa · kategori: penelitian/pkm.'],
        'pddikti' => ['Rekap PD Dikti untuk rekonsiliasi', ['kode', 'prodi', 'nilai_pddikti'],
            'kode: kode metrik (RMDT, DTJA, ...) atau variabel (dosen_tetap, mhs_aktif_utama, ...) · prodi: kode PD Dikti, kosongkan untuk tingkat institusi.'],
    ];

    /** @return array{created:int, updated:int, errors: array<int,string>} */
    public function import(string $type, string $path, ?string $extension = null): array
    {
        $rows = Spreadsheet::read($path, $extension);
        $programs = StudyProgram::query()->get()->keyBy('code_pddikti');
        $result = ['created' => 0, 'updated' => 0, 'errors' => []];
        $count = function ($model) use (&$result) {
            $model->wasRecentlyCreated ? $result['created']++ : $result['updated']++;
        };

        DB::transaction(function () use ($type, $rows, $programs, &$result, $count) {
            foreach ($rows as $i => $r) {
                $line = $i + 2;
                $program = isset($r['kode_prodi']) ? $programs->get(trim((string) $r['kode_prodi'])) : null;

                try {
                    switch ($type) {
                        case 'dosen':
                            if (blank($r['nama'] ?? null)) {
                                throw new \InvalidArgumentException('nama kosong');
                            }

                            $count(Lecturer::updateOrCreate(
                                filled($r['nidn'] ?? null) ? ['nidn' => (string) $r['nidn']] : ['name' => $r['nama']],
                                ['name' => $r['nama'], 'study_program_id' => $program?->id, 'employment' => ($r['status'] ?? 'tetap') === 'tidak_tetap' ? 'tidak_tetap' : 'tetap',
                                    'highest_degree' => strtoupper((string) ($r['pendidikan'] ?? 'S2')), 'academic_rank' => strtolower((string) ($r['jabatan'] ?? 'none')) ?: 'none',
                                    'is_certified' => (bool) ($r['serdos'] ?? false), 'is_active' => ($r['aktif'] ?? 1) !== '0' && ($r['aktif'] ?? 1) !== 0],
                            ));
                            break;
                        case 'statistik':
                            $this->needProgram($program, $r);
                            $count(ProgramYearStat::updateOrCreate(['study_program_id' => $program->id, 'year' => (int) $r['tahun']], [
                                'applicants' => (int) ($r['pendaftar'] ?? 0), 'new_students' => (int) ($r['mahasiswa_baru'] ?? 0), 'active_students' => (int) ($r['mahasiswa_aktif'] ?? 0),
                                'graduates' => (int) ($r['lulusan'] ?? 0), 'students_outside_10sks' => (int) ($r['luar_prodi_10sks'] ?? 0),
                                'avg_study_months' => $r['rerata_masa_studi_bulan'] ?: null, 'avg_gpa' => $r['rerata_ipk'] ?: null,
                            ]));
                            break;
                        case 'kohort':
                            $this->needProgram($program, $r);
                            $count(StudentCohort::updateOrCreate(['study_program_id' => $program->id, 'intake_year' => (int) $r['tahun_masuk']], [
                                'intake_count' => (int) $r['jumlah_masuk'], 'graduated_on_time' => (int) ($r['lulus_tepat_waktu'] ?? 0), 'graduated_within_double' => (int) ($r['lulus_dua_kali_masa'] ?? 0),
                                'still_active' => (int) ($r['masih_aktif'] ?? 0), 'dropped_out' => (int) ($r['keluar'] ?? 0),
                            ]));
                            break;
                        case 'publikasi':
                            $jenis = PublicationType::tryFrom((string) ($r['jenis'] ?? '')) ?? throw new \InvalidArgumentException('jenis tidak dikenal');
                            $lecturer = filled($r['nidn'] ?? null) ? Lecturer::query()->where('nidn', (string) $r['nidn'])->first() : null;
                            $count(Publication::updateOrCreate(['year' => (int) $r['tahun'], 'title' => mb_substr((string) $r['judul'], 0, 500)], [
                                'lecturer_id' => $lecturer?->id, 'type' => $jenis, 'category' => ($r['kategori'] ?? 'penelitian') === 'pkm' ? 'pkm' : 'penelitian',
                                'venue' => $r['media'] ?? null, 'citations' => (int) ($r['sitasi'] ?? 0), 'url' => $r['url'] ?? null,
                            ]));
                            break;
                    }
                } catch (\Throwable $e) {
                    $result['errors'][$line] = $e->getMessage();
                }
            }
        });

        return $result;
    }

    protected function needProgram(?StudyProgram $program, array $r): void
    {
        if (! $program) {
            throw new \InvalidArgumentException('kode_prodi '.($r['kode_prodi'] ?? '').' tidak dikenal');
        }
    }

    public function template(string $type, string $path): void
    {
        [$label, $columns] = self::TYPES[$type];
        $example = match ($type) {
            'dosen' => ['0412345678', 'Dr. Contoh Dosen, M.T.', '55201', 'tetap', 'S3', 'lk', 1, 1],
            'statistik' => ['55201', now()->year - 1, 820, 210, 860, 190, 120, 50, 3.41],
            'kohort' => ['55201', now()->year - 5, 200, 120, 180, 6, 14],
            'publikasi' => ['0412345678', now()->year - 1, 'Judul artikel', 'jurnal_nas_terakreditasi', 'penelitian', 'Jurnal Contoh (SINTA 2)', 3, 'https://doi.org/...'],
            'pddikti' => ['dosen_tetap', '', 412],
        };
        Spreadsheet::write($path, [$label => [$columns, [$example]]]);
    }
}
