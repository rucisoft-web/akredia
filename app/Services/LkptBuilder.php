<?php

namespace App\Services;

use App\Enums\AcademicRank;
use App\Models\AccreditationProject;
use App\Models\Achievement;
use App\Models\EmployerSatisfaction;
use App\Models\FinancialAudit;
use App\Models\Institution;
use App\Models\IpRight;
use App\Models\Lecturer;
use App\Models\ProgramYearStat;
use App\Models\Publication;
use App\Models\Recognition;
use App\Models\StudentCohort;
use App\Models\StudyProgram;
use App\Models\Survey;
use App\Models\TracerStudy;
use App\Enums\PublicationType;
use App\Enums\Stakeholder;

/**
 * Tabel LKPT siap salin ke SAPTO, disusun dari Data Kinerja untuk TS proyek.
 *
 * @phpstan-type Table array{key:string, title:string, note:string, header: array<int,string>, rows: array<int, array<int, mixed>>}
 */
class LkptBuilder
{
    /** @return array<int, array> */
    public function tables(Institution $institution, ?AccreditationProject $project): array
    {
        $ts = $project?->tsYear() ?? (int) now()->year - 1;
        $years = [$ts - 2, $ts - 1, $ts];
        $programs = StudyProgram::query()->where('is_active', true)->with('currentAccreditation')->orderBy('level')->orderBy('name')->get();

        return [
            $this->identity($institution, $programs),
            $this->lecturers($programs),
            $this->students($programs, $years),
            $this->cohorts($programs, $ts),
            $this->achievements($years),
            $this->tracer($ts),
            $this->employer($ts),
            $this->publications($years),
            $this->ipRights($years),
            $this->recognitions($years),
            $this->satisfaction($years),
            $this->audits($years),
        ];
    }

    protected function identity(Institution $i, $programs): array
    {
        return ['key' => 'identitas', 'title' => 'Identitas Perguruan Tinggi dan Akreditasi Program Studi', 'note' => 'Halaman identitas LKPT dan Tabel akreditasi program studi (indikator 4).',
            'header' => ['No', 'Program Studi', 'Jenjang', 'Kode PD Dikti', 'Lembaga', 'Peringkat', 'Nomor SK', 'Berlaku Sampai'],
            'rows' => $programs->values()->map(fn ($p, $n) => [$n + 1, $p->name, $p->level, $p->code_pddikti, $p->currentAccreditation?->agency?->getLabel(), $p->currentAccreditation?->rank?->getLabel() ?? 'Belum terakreditasi', $p->currentAccreditation?->sk_number, $p->currentAccreditation?->valid_until?->format('d/m/Y')])->all(),
            'meta' => ['Nama PT' => $i->name, 'Kode PT' => $i->code, 'Badan penyelenggara' => $i->legal_entity, 'SK pendirian' => trim($i->sk_number.' '.$i->sk_date?->format('d/m/Y')), 'Tahun pertama menerima mahasiswa' => $i->first_intake_year]];
    }

    protected function lecturers($programs): array
    {
        $l = Lecturer::query()->where('is_active', true)->get();
        $rows = $programs->values()->map(function ($p, $n) use ($l) {
            $tetap = $l->where('study_program_id', $p->id)->where('employment', 'tetap');

            return [$n + 1, $p->level.' '.$p->name, $tetap->count(), $tetap->where('highest_degree', 'S3')->count(),
                $tetap->filter(fn ($x) => $x->academic_rank === AcademicRank::Gb)->count(), $tetap->filter(fn ($x) => $x->academic_rank === AcademicRank::Lk)->count(),
                $tetap->filter(fn ($x) => $x->academic_rank === AcademicRank::L)->count(), $tetap->filter(fn ($x) => $x->academic_rank === AcademicRank::Aa)->count(),
                $tetap->where('is_certified', true)->count(), $l->where('study_program_id', $p->id)->where('employment', 'tidak_tetap')->count()];
        })->all();

        return ['key' => 'dosen', 'title' => 'Dosen Tetap dan Tidak Tetap per Program Studi', 'note' => 'Dasar RMDT, PDTT, persentase doktor, dan DTJA (indikator 6, 7, 11).',
            'header' => ['No', 'Program Studi', 'Dosen Tetap', 'Doktor', 'GB', 'LK', 'L', 'AA', 'Bersertifikat', 'Dosen Tidak Tetap'], 'rows' => $rows];
    }

    protected function students($programs, array $years): array
    {
        $stats = ProgramYearStat::query()->whereIn('year', $years)->get();
        $rows = [];

        foreach ($programs->values() as $n => $p) {
            $row = [$n + 1, $p->level.' '.$p->name];

            foreach ($years as $y) {
                $s = $stats->where('study_program_id', $p->id)->firstWhere('year', $y);
                array_push($row, $s?->new_students ?? 0, $s?->active_students ?? 0, $s?->graduates ?? 0);
            }

            $rows[] = $row;
        }

        $header = ['No', 'Program Studi'];

        foreach (['TS-2', 'TS-1', 'TS'] as $t) {
            array_push($header, "Maba {$t}", "Aktif {$t}", "Lulusan {$t}");
        }

        return ['key' => 'mahasiswa', 'title' => 'Tren Mahasiswa Baru, Mahasiswa Aktif, dan Lulusan (Tabel 2c)', 'note' => 'Dasar PPM dan RPL (indikator 12A, 15A).', 'header' => $header, 'rows' => $rows];
    }

    protected function cohorts($programs, int $ts): array
    {
        $c = StudentCohort::query()->whereBetween('intake_year', [$ts - 6, $ts])->get();
        $rows = [];

        foreach ($programs->values() as $p) {
            foreach ($c->where('study_program_id', $p->id)->sortBy('intake_year') as $row) {
                $rows[] = [$p->level.' '.$p->name, $row->intake_year, $row->intake_count, $row->graduated_on_time, $row->graduated_within_double, $row->still_active, $row->dropped_out,
                    $row->intake_count ? round($row->graduated_on_time / $row->intake_count * 100, 1) : null];
            }
        }

        return ['key' => 'kohort', 'title' => 'Kohort Kelulusan (II-1.C)', 'note' => 'Dasar PKMTK dan PK2MTK (indikator 12A).',
            'header' => ['Program Studi', 'Tahun Masuk', 'Jumlah Masuk', 'Lulus Tepat Waktu', 'Lulus ≤ 2× Masa Tempuh', 'Masih Aktif', 'Keluar/DO', '% Tepat Waktu'], 'rows' => $rows];
    }

    protected function achievements(array $years): array
    {
        $a = Achievement::query()->whereIn('year', $years)->get();
        $levels = ['provinsi' => 'Provinsi', 'nasional' => 'Nasional', 'internasional' => 'Internasional'];
        $rows = [];

        foreach ($levels as $key => $label) {
            $row = [$label];

            foreach ($years as $y) {
                $row[] = $a->where('year', $y)->filter(fn ($x) => $x->level?->value === $key)->whereIn('rank', ['1', '2', '3'])->sum('student_count');
            }

            $rows[] = $row;
        }

        return ['key' => 'prestasi', 'title' => 'Prestasi Mahasiswa Juara 1–3', 'note' => 'Dasar PMAP (indikator 15A).', 'header' => ['Tingkat', 'TS-2', 'TS-1', 'TS'], 'rows' => $rows];
    }

    protected function tracer(int $ts): array
    {
        $rows = TracerStudy::query()->whereBetween('graduation_year', [$ts - 3, $ts - 1])->orderBy('graduation_year')->get()
            ->map(fn ($t) => [$t->graduation_year, $t->graduates, $t->respondents, $t->employed, $t->further_study, $t->entrepreneur, $t->within_one_year, $t->avg_wait_months, $t->global_work, $t->certified])->all();

        return ['key' => 'tracer', 'title' => 'Tracer Study dan Masa Tunggu Lulusan (II-1.D.3)', 'note' => 'Dasar PLLK (indikator 14A, 15A).',
            'header' => ['Tahun Lulus', 'Lulusan', 'Responden', 'Bekerja', 'Studi Lanjut', 'Wirausaha', 'Terserap ≤ 1 Tahun', 'Rata-rata Tunggu (bln)', 'Kerja Global', 'Bersertifikat'], 'rows' => $rows];
    }

    protected function employer(int $ts): array
    {
        $year = EmployerSatisfaction::query()->where('year', '<=', $ts)->max('year');
        $rows = EmployerSatisfaction::query()->where('year', $year)->get()->map(function ($e) {
            $t = max(1, $e->total());

            return [EmployerSatisfaction::ASPECTS[$e->aspect] ?? $e->aspect, round($e->sb / $t * 100, 1), round($e->b / $t * 100, 1), round($e->c / $t * 100, 1), round($e->kb / $t * 100, 1), $e->follow_up];
        })->all();

        return ['key' => 'pengguna', 'title' => 'Kepuasan Pengguna Lulusan (7 aspek, II-1.D.2)'.($year ? ' · '.$year : ''), 'note' => 'Indikator 14B.',
            'header' => ['Aspek', 'Sangat Baik (%)', 'Baik (%)', 'Cukup (%)', 'Kurang (%)', 'Tindak Lanjut'], 'rows' => $rows];
    }

    protected function publications(array $years): array
    {
        $p = Publication::query()->whereIn('year', $years)->get();
        $rows = [];

        foreach (PublicationType::cases() as $n => $type) {
            $row = [$n + 1, $type->getLabel()];

            foreach ($years as $y) {
                $row[] = $p->where('year', $y)->filter(fn ($x) => $x->type === $type)->count();
            }

            $row[] = array_sum(array_slice($row, 2));
            $rows[] = $row;
        }

        return ['key' => 'publikasi', 'title' => 'Luaran Publikasi Dosen Tetap (II-2.C.2)', 'note' => 'Indikator 18C.', 'header' => ['No', 'Jenis Publikasi', 'TS-2', 'TS-1', 'TS', 'Jumlah'], 'rows' => $rows];
    }

    protected function ipRights(array $years): array
    {
        $rows = IpRight::query()->whereIn('year', $years)->with('lecturer')->orderBy('year')->get()
            ->map(fn ($i) => [$i->year, $i->title, $i->ip_type?->getLabel() ?? $i->ip_type, $i->number, $i->lecturer?->name, $i->is_adopted ? 'Ya' : 'Tidak', $i->adopter])->all();

        return ['key' => 'hki', 'title' => 'Karya Dosen yang Diadopsi dan HKI (Tabel 22a/22b)', 'note' => 'Indikator 19, 23B.', 'header' => ['Tahun', 'Judul', 'Jenis', 'Nomor', 'Dosen', 'Diadopsi', 'Pengadopsi'], 'rows' => $rows];
    }

    protected function recognitions(array $years): array
    {
        $rows = Recognition::query()->whereIn('year', $years)->with('lecturer')->orderBy('year')->get()
            ->map(fn ($r) => [$r->year, $r->lecturer?->name, $r->title, \App\Support\Labels::RECOGNITIONS[$r->kind] ?? $r->kind, $r->level?->getLabel() ?? $r->level])->all();

        return ['key' => 'rekognisi', 'title' => 'Rekognisi Dosen Tetap (Tabel 21)', 'note' => 'Indikator 23A.', 'header' => ['Tahun', 'Dosen', 'Rekognisi', 'Jenis', 'Tingkat'], 'rows' => $rows];
    }

    protected function satisfaction(array $years): array
    {
        $s = Survey::query()->whereIn('year', $years)->get();
        $rows = [];

        foreach (Stakeholder::cases() as $st) {
            $row = [$st->getLabel()];

            foreach ($years as $y) {
                $x = $s->first(fn ($v) => $v->stakeholder === $st && $v->year === $y);
                array_push($row, $x?->respondents ?? 0, $x ? $x->satisfaction() : null);
            }

            $rows[] = $row;
        }

        return ['key' => 'kepuasan', 'title' => 'Kepuasan Pemangku Kepentingan terhadap Tata Pamong (Tabel 24)', 'note' => 'Indikator 34.',
            'header' => ['Kelompok', 'Responden TS-2', 'SB+B TS-2 (%)', 'Responden TS-1', 'SB+B TS-1 (%)', 'Responden TS', 'SB+B TS (%)'], 'rows' => $rows];
    }

    protected function audits(array $years): array
    {
        $rows = FinancialAudit::query()->whereIn('year', $years)->orderBy('year')->get()
            ->map(fn ($a) => [$a->year, $a->auditor, \App\Models\FinancialAudit::OPINIONS[$a->opinion] ?? $a->opinion, $a->report_date?->format('d/m/Y')])->all();

        return ['key' => 'audit', 'title' => 'Audit Keuangan Eksternal (Tabel 25)', 'note' => 'Indikator 35.', 'header' => ['Tahun', 'Kantor Akuntan Publik', 'Opini', 'Tanggal Laporan'], 'rows' => $rows];
    }
}
