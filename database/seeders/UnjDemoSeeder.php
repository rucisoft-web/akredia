<?php

namespace Database\Seeders;

use App\Enums\DocumentStatus;
use App\Enums\Track;
use App\Models\AcademicPeriod;
use App\Models\AccreditationProject;
use App\Models\Document;
use App\Models\EvidenceLink;
use App\Models\ProgramAccreditation;
use App\Models\StudyProgram;
use App\Models\Unit;
use App\Models\WorkItem;
use App\Services\EvidenceService;
use Illuminate\Support\Carbon;

/**
 * Data demo Universitas Negeri Jakarta (PTN Badan Hukum) yang menyiapkan reakreditasi institusi
 * jalur Unggul dengan IAPT 4.1.
 *
 * Diambil dari unj.ac.id (Oktober 2026): identitas, status PTN-BH (PP 31/2024), pimpinan universitas,
 * visi-misi, fakultas, riwayat akreditasi institusi, daftar prodi terakreditasi internasional,
 * dan pemeringkatan. Selebihnya (nama dosen dan staf, angka mahasiswa, publikasi, survei, temuan,
 * tugas, risiko, narasi) adalah data ilustrasi untuk demonstrasi, bukan data resmi UNJ.
 */
class UnjDemoSeeder extends DemoSeeder
{
    public const SLUG = 'unj';

    protected string $emailDomain = 'unj.akredia.test';

    protected string $short = 'UNJ';

    protected function institutionAttributes(): array
    {
        return [
            'name' => 'Universitas Negeri Jakarta',
            'slug' => static::SLUG,
            'code' => '001037',
            'type' => 'ptnbh',
            'legal_entity' => 'PTN Badan Hukum (PP Nomor 31 Tahun 2024)',
            'sk_number' => 'Keppres Nomor 093 Tahun 1999',
            'sk_date' => '1999-08-04',
            'first_intake_year' => 1964,
            'city' => 'Jakarta Timur',
            'address' => 'Jl. Rawamangun Muka, Rawamangun, Pulo Gadung, Jakarta Timur 13220',
            'website' => 'https://unj.ac.id',
            'vision' => 'Universitas berkelas dunia yang unggul dalam bidang kependidikan, sains, teknologi, dan humaniora.',
            'mission_focus' => 'Universitas kependidikan (LPTK) berkelas dunia: pendidikan guru dan tenaga kependidikan bereputasi internasional, riset kependidikan dan sains terapan, serta pengabdian yang berdampak lokal hingga global.',
            'last_accreditation_rank' => 'Unggul',
            'last_accreditation_sk' => '45/SK/BAN-PT/Akred/PT/II/2021',
            'last_accreditation_until' => '2027-02-02',
            'is_demo' => true,
        ];
    }

    // ------------------------------------------------------------------ organisasi

    protected function seedOrganisation(): void
    {
        $rektorat = Unit::create(['code' => 'REK', 'name' => 'Rektorat', 'type' => 'rektorat', 'head_name' => 'Prof. Dr. Komarudin, M.Si.']);

        $list = [
            'lpm' => ['SPM', 'Satuan Penjaminan Mutu', 'lembaga', 'Dr. Rahmawati Puspitasari, M.Pd.'],
            'lppm' => ['LPPM', 'Lembaga Penelitian dan Pengabdian kepada Masyarakat', 'lembaga', 'Prof. Dr. Hendra Gunawan, M.Si.'],
            'spi' => ['SPI', 'Satuan Pengawas Internal', 'lembaga', 'Dr. Agus Riyanto, S.E., M.Ak.'],
            'akademik' => ['DAK', 'Direktorat Akademik', 'biro', 'Dr. Siti Rohmah, M.Pd.'],
            'kemahasiswaan' => ['DITMAWANI', 'Direktorat Kemahasiswaan dan Alumni', 'biro', 'Dr. Yudi Prasetyo, M.Pd.'],
            'keuangan' => ['DKP', 'Direktorat Keuangan dan Perencanaan', 'biro', 'Dr. Ratna Komalasari, S.E., M.Si., Ak.'],
            'sdm' => ['DSDPBJ', 'Direktorat Sumber Daya dan PBJ', 'biro', 'Dr. Bambang Wicaksono, M.M.'],
            'sarpras' => ['SARPRAS', 'Subdirektorat Sarana dan Prasarana', 'biro', 'Ir. Teguh Santoso, M.T.'],
            'perencanaan' => ['DITISIP', 'Direktorat Inovasi, Sistem Informasi, dan Pemeringkatan', 'biro', 'Dr. Fitri Handayani, M.Kom.'],
            'kerjasama' => ['DKSB', 'Direktorat Kerja Sama dan Bisnis', 'biro', 'Dr. Arif Nugraha, M.B.A.'],
            'sekretariat' => ['SU', 'Sekretariat Universitas', 'biro', 'Prof. Dr. Suyono, M.Si.'],
            'tik' => ['PUSTIKOM', 'Pusat Teknologi Informasi dan Komunikasi', 'upt', 'Dr. Muhammad Rizal, M.Kom.'],
            'perpustakaan' => ['UPT-PUS', 'UPT Perpustakaan', 'upt', 'Dra. Endah Wulandari, M.Hum.'],
            'satgas' => ['SATGAS', 'Satgas Pencegahan dan Penanganan Kekerasan', 'satgas', 'Dr. Lina Marlina, M.Psi., Psikolog'],
        ];

        foreach ($list as $key => [$code, $name, $type, $head]) {
            $this->units[$key] = Unit::create(['parent_id' => $rektorat->id, 'code' => $code, 'name' => $name, 'type' => $type, 'head_name' => $head]);
        }

        $faculties = [
            'fip' => ['FIP', 'Fakultas Ilmu Pendidikan', 'Dr. Dewi Anggraeni, M.Pd.'],
            'fbs' => ['FBS', 'Fakultas Bahasa dan Seni', 'Dr. Ahmad Fauzi, M.Hum.'],
            'fmipa' => ['FMIPA', 'Fakultas Matematika dan Ilmu Pengetahuan Alam', 'Prof. Dr. Irwan Setiadi, M.Si.'],
            'fish' => ['FISH', 'Fakultas Ilmu Sosial dan Hukum', 'Dr. Nurul Hidayah, M.Si.'],
            'ft' => ['FT', 'Fakultas Teknik', 'Dr. Ir. Bayu Pratama, M.T.'],
            'fikk' => ['FIKK', 'Fakultas Ilmu Keolahragaan dan Kesehatan', 'Dr. Galih Permana, M.Or.'],
            'feb' => ['FEB', 'Fakultas Ekonomi dan Bisnis', 'Prof. Dr. Wulan Sari, M.M.'],
            'fpsi' => ['FPSI', 'Fakultas Psikologi', 'Dr. Maya Kartika, M.Psi., Psikolog'],
            'sps' => ['SPS', 'Sekolah Pascasarjana', 'Prof. Dr. Herlambang Saputra, M.Pd.'],
        ];

        foreach ($faculties as $key => [$code, $name, $head]) {
            $this->units[$key] = Unit::create(['parent_id' => $rektorat->id, 'code' => $code, 'name' => $name, 'type' => 'fakultas', 'head_name' => $head]);
        }

        $this->units['rektorat'] = $rektorat;

        // Akreditasi internasional dari spm.unj.ac.id: [lembaga, mulai, berakhir].
        $intl = [
            'bio' => ['ASIIN', 2022, 2028], 'pbio' => ['ASIIN', 2022, 2028], 'ptb' => ['ASIIN', 2022, 2028], 'kim' => ['ASIIN', 2022, 2028],
            'pkim' => ['ASIIN', 2022, 2028], 'pte' => ['ASIIN', 2022, 2028], 'ptel' => ['ASIIN', 2022, 2028], 'ptik' => ['ASIIN', 2022, 2028],
            'ptm' => ['ASIIN', 2022, 2028], 'mat' => ['ASIIN', 2024, 2029], 'fis' => ['ASIIN', 2024, 2029], 'pmat' => ['ASIIN', 2024, 2029],
            'pfis' => ['ASIIN', 2024, 2029], 'pjkr' => ['AQAS', 2023, 2028], 'plb' => ['FIBAA', 2024, 2029], 'psi' => ['FIBAA', 2024, 2029],
            'pm' => ['FIBAA', 2024, 2029], 'sosio' => ['FIBAA', 2024, 2029], 'pai' => ['FIBAA', 2024, 2029], 'paud' => ['FIBAA', 2024, 2029],
            'psos' => ['FIBAA', 2024, 2029], 'pgsd' => ['FIBAA', 2024, 2029], 'pbpr' => ['FIBAA', 2024, 2029], 'akt' => ['FIBAA', 2024, 2029],
            'sasing' => ['FIBAA', 2024, 2029], 'pbing' => ['FIBAA', 2024, 2029], 'pbar' => ['FIBAA', 2024, 2029], 'pbis' => ['FIBAA', 2024, 2029],
            'pips' => ['FIBAA', 2024, 2029], 'mnj' => ['FIBAA', 2024, 2029], 'pbsi' => ['FIBAA', 2024, 2029], 'pekon' => ['FIBAA', 2024, 2029],
            'kom' => ['FIBAA', 2024, 2029], 'pap' => ['FIBAA', 2024, 2029],
            's2pbio' => ['ASIIN', 2022, 2028], 's2pkim' => ['ASIIN', 2022, 2028], 's2pmat' => ['ASIIN', 2022, 2028], 's2pfis' => ['ASIIN', 2022, 2028],
            's2pep' => ['AQAS', 2023, 2027], 's2paud' => ['AQAS', 2023, 2027], 's2ml' => ['AQAS', 2023, 2028], 's2lt' => ['AQAS', 2023, 2028],
            's2pd' => ['AQAS', 2023, 2027], 's2pbi' => ['AQAS', 2023, 2027], 's2tp' => ['AQAS', 2023, 2027], 's2pj' => ['AQAS', 2023, 2028],
            's2psej' => ['AQAS', 2023, 2028], 's2pl' => ['AQAS', 2023, 2028], 's2mp' => ['AQAS', 2023, 2028], 's2mnj' => ['FIBAA', 2024, 2029],
            's3pep' => ['AQAS', 2023, 2027], 's3im' => ['AQAS', 2023, 2028], 's3mp' => ['AQAS', 2023, 2028], 's3pd' => ['AQAS', 2023, 2027],
            's3pl' => ['AQAS', 2023, 2028], 's3pj' => ['AQAS', 2023, 2028], 's3tp' => ['AQAS', 2023, 2027], 's3lt' => ['AQAS', 2023, 2028],
            's3paud' => ['AQAS', 2023, 2027],
        ];

        // [kunci, fakultas, kode prodi, nama, jenjang, dibuka, lembaga, peringkat, berlaku s.d., dosen tetap, mahasiswa aktif TS]
        $programs = [
            ['bk', 'fip', '86201', 'Bimbingan dan Konseling', 'S1', 1964, 'lam_dik', 'unggul', '2029-03-14', 24, 960],
            ['tp', 'fip', '86203', 'Teknologi Pendidikan', 'S1', 1975, 'lam_dik', 'unggul', '2028-11-02', 18, 640],
            ['pgsd', 'fip', '86206', 'Pendidikan Guru Sekolah Dasar', 'S1', 1993, 'lam_dik', 'unggul', '2029-08-21', 32, 1480],
            ['paud', 'fip', '86207', 'Pendidikan Guru Pendidikan Anak Usia Dini', 'S1', 1993, 'lam_dik', 'unggul', '2029-05-10', 24, 920],
            ['plb', 'fip', '86208', 'Pendidikan Khusus', 'S1', 1966, 'lam_dik', 'unggul', '2028-09-18', 18, 600],
            ['pm', 'fip', '86209', 'Pendidikan Masyarakat', 'S1', 1966, 'lam_dik', 'unggul', '2028-07-26', 15, 520],
            ['mp', 'fip', '86211', 'Manajemen Pendidikan', 'S1', 1975, 'lam_dik', 'baik_sekali', '2027-06-30', 14, 450],
            ['ppg', 'fip', '86904', 'Pendidikan Profesi Guru', 'Profesi', 2018, 'lam_dik', 'unggul', '2029-12-12', 20, 2350],
            ['pbsi', 'fbs', '88201', 'Pendidikan Bahasa dan Sastra Indonesia', 'S1', 1964, 'lam_dik', 'unggul', '2029-02-17', 26, 1040],
            ['sasind', 'fbs', '79201', 'Sastra Indonesia', 'S1', 2004, 'banpt', 'baik_sekali', '2028-05-08', 12, 420],
            ['pbing', 'fbs', '88203', 'Pendidikan Bahasa Inggris', 'S1', 1964, 'lam_dik', 'unggul', '2029-04-23', 28, 1120],
            ['sasing', 'fbs', '79202', 'Sastra Inggris', 'S1', 2004, 'banpt', 'unggul', '2028-12-19', 14, 540],
            ['pbar', 'fbs', '88204', 'Pendidikan Bahasa Arab', 'S1', 1964, 'lam_dik', 'unggul', '2028-10-15', 16, 560],
            ['pbpr', 'fbs', '88205', 'Pendidikan Bahasa Prancis', 'S1', 1964, 'lam_dik', 'unggul', '2028-08-04', 12, 380],
            ['pbjm', 'fbs', '88206', 'Pendidikan Bahasa Jerman', 'S1', 1964, 'lam_dik', 'baik_sekali', '2028-03-27', 10, 340],
            ['pbjp', 'fbs', '88207', 'Pendidikan Bahasa Jepang', 'S1', 1993, 'lam_dik', 'baik_sekali', '2027-11-20', 11, 360],
            ['pbmd', 'fbs', '88208', 'Pendidikan Bahasa Mandarin', 'S1', 2016, 'lam_dik', 'baik', '2027-04-11', 8, 290],
            ['psr', 'fbs', '88210', 'Pendidikan Seni Rupa', 'S1', 1975, 'lam_dik', 'baik_sekali', '2028-06-02', 12, 420],
            ['pmus', 'fbs', '88211', 'Pendidikan Musik', 'S1', 1975, 'lam_dik', 'baik_sekali', '2028-01-29', 11, 380],
            ['ptari', 'fbs', '88212', 'Pendidikan Tari', 'S1', 1975, 'lam_dik', 'baik_sekali', '2027-09-14', 10, 320],
            ['pmat', 'fmipa', '84202', 'Pendidikan Matematika', 'S1', 1964, 'lam_dik', 'unggul', '2029-06-05', 22, 820],
            ['mat', 'fmipa', '44201', 'Matematika', 'S1', 1999, 'lam_sama', 'unggul', '2029-01-23', 16, 540],
            ['pfis', 'fmipa', '84203', 'Pendidikan Fisika', 'S1', 1964, 'lam_dik', 'unggul', '2028-11-30', 18, 640],
            ['fis', 'fmipa', '45201', 'Fisika', 'S1', 1999, 'lam_sama', 'unggul', '2028-10-09', 14, 430],
            ['pkim', 'fmipa', '84204', 'Pendidikan Kimia', 'S1', 1964, 'lam_dik', 'unggul', '2028-12-14', 18, 640],
            ['kim', 'fmipa', '47201', 'Kimia', 'S1', 1999, 'lam_sama', 'unggul', '2028-09-01', 14, 440],
            ['pbio', 'fmipa', '84205', 'Pendidikan Biologi', 'S1', 1964, 'lam_dik', 'unggul', '2029-03-03', 20, 720],
            ['bio', 'fmipa', '46201', 'Biologi', 'S1', 1999, 'lam_sama', 'unggul', '2028-08-17', 15, 460],
            ['stat', 'fmipa', '49201', 'Statistika', 'S1', 2015, 'lam_sama', 'baik_sekali', '2028-02-12', 10, 380],
            ['ilkom', 'fmipa', '55201', 'Ilmu Komputer', 'S1', 2008, 'lam_infokom', 'baik_sekali', '2027-12-06', 14, 640],
            ['psej', 'fish', '87201', 'Pendidikan Sejarah', 'S1', 1964, 'lam_dik', 'unggul', '2028-07-12', 16, 560],
            ['pgeo', 'fish', '87203', 'Pendidikan Geografi', 'S1', 1964, 'lam_dik', 'unggul', '2028-05-29', 16, 560],
            ['ppkn', 'fish', '87205', 'Pendidikan Pancasila dan Kewarganegaraan', 'S1', 1964, 'lam_dik', 'unggul', '2029-01-08', 16, 600],
            ['pips', 'fish', '87204', 'Pendidikan Ilmu Pengetahuan Sosial', 'S1', 2008, 'lam_dik', 'unggul', '2028-09-24', 14, 520],
            ['psos', 'fish', '87206', 'Pendidikan Sosiologi', 'S1', 2008, 'lam_dik', 'unggul', '2028-04-16', 14, 500],
            ['sosio', 'fish', '69201', 'Sosiologi', 'S1', 2004, 'banpt', 'unggul', '2028-11-11', 12, 430],
            ['kom', 'fish', '70201', 'Ilmu Komunikasi', 'S1', 2010, 'banpt', 'unggul', '2029-02-25', 16, 720],
            ['pai', 'fish', '86231', 'Pendidikan Agama Islam', 'S1', 1993, 'lam_dik', 'unggul', '2028-06-21', 12, 420],
            ['upw', 'fish', '93305', 'Usaha Perjalanan Wisata', 'D4', 2017, 'banpt', 'baik_sekali', '2027-08-09', 10, 410],
            ['humas', 'fish', '70302', 'Hubungan Masyarakat dan Komunikasi Digital', 'D4', 2017, 'banpt', 'baik_sekali', '2027-10-27', 10, 430],
            ['pte', 'ft', '83207', 'Pendidikan Teknik Elektro', 'S1', 1964, 'lam_dik', 'unggul', '2028-12-01', 16, 520],
            ['ptel', 'ft', '83208', 'Pendidikan Teknik Elektronika', 'S1', 1999, 'lam_dik', 'unggul', '2028-10-20', 14, 480],
            ['ptik', 'ft', '83209', 'Pendidikan Teknik Informatika dan Komputer', 'S1', 2008, 'lam_dik', 'unggul', '2029-03-17', 16, 660],
            ['ptm', 'ft', '83203', 'Pendidikan Teknik Mesin', 'S1', 1964, 'lam_dik', 'unggul', '2028-09-09', 16, 520],
            ['ptb', 'ft', '83202', 'Pendidikan Teknik Bangunan', 'S1', 1964, 'lam_dik', 'unggul', '2028-08-28', 16, 520],
            ['pkk', 'ft', '83204', 'Pendidikan Kesejahteraan Keluarga', 'S1', 1964, 'lam_dik', 'baik_sekali', '2028-01-15', 14, 480],
            ['ptbus', 'ft', '83211', 'Pendidikan Tata Busana', 'S1', 2008, 'lam_dik', 'baik_sekali', '2027-12-22', 12, 420],
            ['ptbog', 'ft', '83212', 'Pendidikan Tata Boga', 'S1', 2008, 'lam_dik', 'baik_sekali', '2028-04-04', 12, 440],
            ['ptrias', 'ft', '83213', 'Pendidikan Tata Rias', 'S1', 2008, 'lam_dik', 'baik_sekali', '2027-07-19', 10, 380],
            ['rkk', 'ft', '26209', 'Rekayasa Keselamatan Kebakaran', 'S1', 2016, 'lam_teknik', 'baik', '2027-03-08', 10, 390],
            ['pjkr', 'fikk', '85201', 'Pendidikan Jasmani, Kesehatan dan Rekreasi', 'S1', 1964, 'lam_dik', 'unggul', '2029-05-27', 24, 1000],
            ['pko', 'fikk', '85202', 'Pendidikan Kepelatihan Olahraga', 'S1', 1964, 'lam_dik', 'unggul', '2028-11-18', 18, 760],
            ['ikor', 'fikk', '85203', 'Ilmu Keolahragaan', 'S1', 2004, 'banpt', 'unggul', '2028-07-03', 14, 520],
            ['orrek', 'fikk', '85204', 'Olahraga Rekreasi', 'S1', 2016, 'banpt', 'baik_sekali', '2028-02-28', 10, 360],
            ['gizi', 'fikk', '13211', 'Gizi', 'S1', 2019, 'lam_ptkes', 'baik', '2026-12-30', 10, 380],
            ['pekon', 'feb', '87202', 'Pendidikan Ekonomi', 'S1', 1964, 'lam_dik', 'unggul', '2029-01-30', 18, 680],
            ['pbis', 'feb', '87207', 'Pendidikan Bisnis', 'S1', 2008, 'lam_dik', 'unggul', '2028-10-02', 14, 540],
            ['pap', 'feb', '87208', 'Pendidikan Administrasi Perkantoran', 'S1', 2008, 'lam_dik', 'unggul', '2028-09-16', 14, 540],
            ['pakt', 'feb', '87209', 'Pendidikan Akuntansi', 'S1', 2008, 'lam_dik', 'unggul', '2028-08-11', 14, 520],
            ['mnj', 'feb', '61201', 'Manajemen', 'S1', 1999, 'lam_emba', 'unggul', '2029-07-24', 28, 1420],
            ['akt', 'feb', '62201', 'Akuntansi', 'S1', 1999, 'lam_emba', 'unggul', '2029-06-18', 24, 1120],
            ['bisdig', 'feb', '61209', 'Bisnis Digital', 'S1', 2021, 'lam_emba', 'baik', '2027-02-26', 10, 540],
            ['aksp', 'feb', '62301', 'Akuntansi Sektor Publik', 'D4', 2017, 'lam_emba', 'baik_sekali', '2027-11-05', 10, 400],
            ['mpem', 'feb', '61306', 'Manajemen Pemasaran', 'D4', 2017, 'lam_emba', 'baik_sekali', '2028-03-13', 10, 420],
            ['psi', 'fpsi', '73201', 'Psikologi', 'S1', 2004, 'banpt', 'unggul', '2029-04-09', 26, 1100],
            ['s2psi', 'fpsi', '73101', 'Psikologi', 'S2', 2016, 'banpt', 'baik_sekali', '2027-09-30', 6, 110],
            ['s2pep', 'sps', '86102', 'Penelitian dan Evaluasi Pendidikan', 'S2', 1980, 'lam_dik', 'unggul', '2028-06-14', 8, 150],
            ['s2mp', 'sps', '86111', 'Manajemen Pendidikan', 'S2', 1980, 'lam_dik', 'unggul', '2028-09-07', 9, 180],
            ['s2tp', 'sps', '86122', 'Teknologi Pendidikan', 'S2', 1985, 'lam_dik', 'unggul', '2028-05-19', 8, 160],
            ['s2pd', 'sps', '86106', 'Pendidikan Dasar', 'S2', 2002, 'lam_dik', 'unggul', '2028-03-22', 8, 170],
            ['s2paud', 'sps', '86107', 'Pendidikan Anak Usia Dini', 'S2', 2004, 'lam_dik', 'unggul', '2028-02-07', 7, 130],
            ['s2pbi', 'sps', '88101', 'Pendidikan Bahasa Indonesia', 'S2', 2004, 'lam_dik', 'unggul', '2028-01-24', 7, 120],
            ['s2lt', 'sps', '79101', 'Linguistik Terapan', 'S2', 1994, 'banpt', 'unggul', '2028-10-31', 8, 140],
            ['s2pj', 'sps', '85101', 'Pendidikan Jasmani', 'S2', 2002, 'lam_dik', 'unggul', '2028-07-08', 7, 130],
            ['s2psej', 'sps', '87101', 'Pendidikan Sejarah', 'S2', 2008, 'lam_dik', 'unggul', '2028-04-30', 6, 90],
            ['s2pl', 'sps', '86123', 'Pendidikan Lingkungan', 'S2', 1997, 'lam_dik', 'unggul', '2028-06-26', 7, 110],
            ['s2ml', 'sps', '95101', 'Manajemen Lingkungan', 'S2', 2002, 'banpt', 'unggul', '2028-08-20', 7, 100],
            ['s2pbio', 'fmipa', '84105', 'Pendidikan Biologi', 'S2', 2010, 'lam_dik', 'unggul', '2028-11-25', 6, 90],
            ['s2pkim', 'fmipa', '84104', 'Pendidikan Kimia', 'S2', 2010, 'lam_dik', 'unggul', '2028-12-09', 6, 85],
            ['s2pmat', 'fmipa', '84102', 'Pendidikan Matematika', 'S2', 2008, 'lam_dik', 'unggul', '2029-01-14', 6, 95],
            ['s2pfis', 'fmipa', '84103', 'Pendidikan Fisika', 'S2', 2010, 'lam_dik', 'unggul', '2028-10-28', 6, 80],
            ['s2mnj', 'feb', '61101', 'Manajemen', 'S2', 2008, 'lam_emba', 'unggul', '2029-05-05', 10, 220],
            ['s3pep', 'sps', '86002', 'Penelitian dan Evaluasi Pendidikan', 'S3', 1985, 'lam_dik', 'unggul', '2028-06-14', 6, 90],
            ['s3mp', 'sps', '86011', 'Manajemen Pendidikan', 'S3', 1985, 'lam_dik', 'unggul', '2028-09-07', 8, 140],
            ['s3tp', 'sps', '86022', 'Teknologi Pendidikan', 'S3', 2008, 'lam_dik', 'unggul', '2028-05-19', 6, 80],
            ['s3pd', 'sps', '86006', 'Pendidikan Dasar', 'S3', 2010, 'lam_dik', 'unggul', '2028-03-22', 6, 85],
            ['s3paud', 'sps', '86007', 'Pendidikan Anak Usia Dini', 'S3', 2012, 'lam_dik', 'unggul', '2028-02-07', 6, 70],
            ['s3lt', 'sps', '79001', 'Linguistik Terapan', 'S3', 2006, 'banpt', 'unggul', '2028-10-31', 6, 70],
            ['s3pj', 'sps', '85001', 'Pendidikan Jasmani', 'S3', 2012, 'lam_dik', 'unggul', '2028-07-08', 6, 65],
            ['s3pl', 'sps', '86023', 'Pendidikan Lingkungan', 'S3', 2006, 'lam_dik', 'unggul', '2028-06-26', 6, 75],
            ['s3im', 'feb', '61001', 'Ilmu Manajemen', 'S3', 2012, 'lam_emba', 'unggul', '2029-05-05', 6, 90],
        ];

        foreach ($programs as [$key, $fac, $code, $name, $level, $opened, $agency, $rank, $until, $lecturers, $students]) {
            $program = StudyProgram::create(['unit_id' => $this->units[$fac]->id, 'code_pddikti' => $code, 'name' => $name, 'level' => $level, 'opened_year' => $opened]);
            $program->setAttribute('demo_lecturers', $lecturers);
            $program->setAttribute('demo_students', $students);
            $this->programs[$key] = $program;

            $validUntil = Carbon::parse($until);
            ProgramAccreditation::create([
                'study_program_id' => $program->id, 'agency' => $agency, 'rank' => $rank,
                'sk_number' => sprintf('%04d/SK/%s/Akred/%s/%s', mt_rand(1000, 9999), strtoupper(str_replace('_', '-', $agency)), $level, $validUntil->copy()->subYears(5)->format('m/Y')),
                'sk_date' => $validUntil->copy()->subYears(5), 'valid_until' => $validUntil, 'is_current' => true,
            ]);

            if ($international = $intl[$key] ?? null) {
                [$body, $from, $to] = $international;
                ProgramAccreditation::create([
                    'study_program_id' => $program->id, 'agency' => 'intl', 'rank' => 'internasional', 'sk_number' => "{$body} {$from}-{$to}",
                    'sk_date' => "{$from}-09-01", 'valid_until' => "{$to}-08-31", 'is_current' => false, 'is_international' => true,
                ]);
            }
        }

        foreach ([[2023, '2023/2024'], [2024, '2024/2025'], [2025, '2025/2026'], [2026, '2026/2027']] as [$y, $name]) {
            AcademicPeriod::create(['name' => $name, 'year_start' => $y, 'starts_at' => "{$y}-08-15", 'ends_at' => ($y + 1).'-08-14']);
        }
    }

    protected function people(): array
    {
        return [
            ['admin', 'admin.pt', 'Dr. Muhammad Rizal, M.Kom.', 'admin', 'tik', 'Kepala Pustikom'],
            ['rektor', 'rektor', 'Prof. Dr. Komarudin, M.Si.', 'pimpinan', 'rektorat', 'Rektor'],
            ['wr1', 'wr1', 'Prof. Dr. Ifan Iskandar, M.Hum.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Akademik, Kemahasiswaan, dan Alumni'],
            ['wr2', 'wr2', 'Prof. Dr. Ari Saptono, M.Pd.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Keuangan dan Sumber Daya'],
            ['wr3', 'wr3', 'Prof. Dr. Fahrurrozi, M.Pd.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Riset, Inovasi, dan Sistem Informasi'],
            ['wr4', 'wr4', 'Dr. Andy Hadiyanto, M.A.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Kerja Sama dan Bisnis'],
            ['manajer', 'manajer', 'Dr. Rahmawati Puspitasari, M.Pd.', 'manajer', 'lpm', 'Kepala SPM / Ketua Tim Reakreditasi Institusi'],
            ['reviewer', 'reviewer', 'Prof. Dr. Sri Wahyuni, M.Pd.', 'reviewer', 'lpm', 'Reviewer LED'],
            ['reviewer2', 'reviewer2', 'Dr. Agus Setiawan, M.T.', 'reviewer', 'ft', 'Reviewer LED'],
            ['asesor', 'asesor', 'Prof. Dr. Made Wirawan, M.Si.', 'asesor', 'lpm', 'Asesor internal (asesor BAN-PT)'],
            ['pic.lpm', 'pic.lpm', 'Nur Aisyah, M.Pd.', 'pic', 'lpm', 'Koordinator Akreditasi SPM'],
            ['pic.akademik', 'pic.akademik', 'Dr. Siti Rohmah, M.Pd.', 'pic', 'akademik', 'Direktur Akademik'],
            ['pic.sdm', 'pic.sdm', 'Dr. Bambang Wicaksono, M.M.', 'pic', 'sdm', 'Direktur Sumber Daya dan PBJ'],
            ['pic.keuangan', 'pic.keuangan', 'Dr. Ratna Komalasari, S.E., M.Si., Ak.', 'pic', 'keuangan', 'Direktur Keuangan dan Perencanaan'],
            ['pic.kemahasiswaan', 'pic.kemahasiswaan', 'Dr. Yudi Prasetyo, M.Pd.', 'pic', 'kemahasiswaan', 'Direktur Kemahasiswaan dan Alumni'],
            ['pic.sarpras', 'pic.sarpras', 'Ir. Teguh Santoso, M.T.', 'pic', 'sarpras', 'Kepala Subdirektorat Sarana dan Prasarana'],
            ['pic.perencanaan', 'pic.perencanaan', 'Dr. Fitri Handayani, M.Kom.', 'pic', 'perencanaan', 'Direktur Inovasi, Sistem Informasi, dan Pemeringkatan'],
            ['pic.kerjasama', 'pic.kerjasama', 'Dr. Arif Nugraha, M.B.A.', 'pic', 'kerjasama', 'Direktur Kerja Sama dan Bisnis'],
            ['pic.sekretariat', 'pic.sekretariat', 'Rudi Hermawan, S.H., M.H.', 'pic', 'sekretariat', 'Kepala Biro Hukum dan Tata Laksana'],
            ['pic.spi', 'pic.spi', 'Dr. Agus Riyanto, S.E., M.Ak.', 'pic', 'spi', 'Kepala SPI'],
            ['pic.lppm', 'pic.lppm', 'Prof. Dr. Hendra Gunawan, M.Si.', 'pic', 'lppm', 'Ketua LPPM'],
            ['pic.tik', 'pic.tik', 'Fikri Ramadhan, S.Kom., M.T.I.', 'pic', 'tik', 'Koordinator Pangkalan Data dan PDDikti'],
            ['pic.satgas', 'pic.satgas', 'Dr. Lina Marlina, M.Psi., Psikolog', 'pic', 'satgas', 'Ketua Satgas PPK'],
            ['data.feb', 'data.feb', 'Yoga Pratama, S.E., M.Ak.', 'pemilik_data', 'feb', 'Operator Data FEB'],
            ['pengamat', 'pengamat', 'Ketua Majelis Wali Amanat', 'pengamat', 'rektorat', 'Majelis Wali Amanat'],
        ];
    }

    protected function performanceProfile(): array
    {
        return [
            'nidn_start' => 1010000001,
            'doctor_pct' => 47,
            'professor_pct' => 22,
            'events' => ['Pekan Ilmiah Mahasiswa Nasional (PIMNAS)', 'Kompetisi Debat Bahasa Indonesia', 'National University Debating Championship', 'Olimpiade Nasional MIPA (ON MIPA-PT)', 'Lomba Inovasi Pembelajaran Digital', 'Kompetisi Mahasiswa Informatika (Gemastik)', 'Pekan Olahraga Mahasiswa Nasional', 'SEA Games / ASEAN University Games', 'Festival Seni Mahasiswa Nasional (Peksiminas)', 'Kompetisi Robotik Indonesia', 'Lomba Microteaching Nasional', 'Kontes Busana dan Tata Rias', 'Kejuaraan Pencak Silat Internasional', 'Lomba Karya Tulis Pendidikan Inklusif'],
            'achievement_base' => 64,
            'topics' => ['pembelajaran berdiferensiasi', 'kurikulum merdeka', 'asesmen literasi dan numerasi', 'pendidikan inklusif', 'guru penggerak', 'literasi digital siswa', 'pembelajaran STEM', 'media pembelajaran berbasis AR', 'kecerdasan buatan dalam pendidikan', 'evaluasi program pendidikan', 'kesehatan mental remaja', 'olahraga prestasi', 'gizi atlet', 'bahasa Indonesia untuk penutur asing', 'pendidikan karakter Pancasila', 'sejarah lokal Jakarta', 'ekonomi kreatif Jakarta', 'pariwisata perkotaan', 'keselamatan kebakaran gedung', 'energi terbarukan', 'IoT untuk layanan kesehatan', 'mitigasi banjir Jakarta', 'kimia hijau', 'biodiversitas perkotaan'],
            'places' => ['DKI Jakarta', 'Indonesia', 'sekolah dasar di Jakarta Timur', 'Asia Tenggara', 'LPTK negeri', 'SMK di Jabodetabek'],
            'publication_types' => ['jurnal_intl_bereputasi' => 24, 'jurnal_intl' => 10, 'jurnal_nas_terakreditasi' => 36, 'jurnal_nas' => 10, 'seminar_intl' => 12, 'seminar_nas' => 5, 'media_massa' => 3],
            'publication_base' => 560,
            'publication_growth' => 95,
            'venues' => ['Jurnal Internasional Bereputasi (Scopus Q1-Q4)', 'Jurnal Nasional Terakreditasi SINTA'],
            'ip_base' => 38,
            'ip_adopter' => 'Dinas Pendidikan Provinsi DKI Jakarta',
            'recognitions' => ['Asesor BAN-PT / LAMDIK', 'Mitra bestari jurnal internasional bereputasi', 'Narasumber Kemendikdasmen', 'Visiting professor universitas mitra luar negeri', 'Tim ahli kurikulum nasional', 'Penghargaan dosen berprestasi nasional'],
            'recognition_base' => 30,
            'grant_base' => 72,
            'grant_scale' => 1.6,
            'auditors' => [2021 => 'KAP Tanudiredja, Wibisana & Rekan', 2022 => 'KAP Tanudiredja, Wibisana & Rekan', 2023 => 'KAP Purwantono, Sungkoro & Surja', 2024 => 'KAP Purwantono, Sungkoro & Surja', 2025 => 'KAP Purwantono, Sungkoro & Surja'],
            'survey_population' => ['mahasiswa' => 38500, 'dosen' => 1260, 'tendik' => 930, 'mitra' => 310, 'lulusan' => 7900, 'pengguna' => 680],
            'survey_open_population' => 39200,
            'tracer_graduates' => 7100,
            'tracer_growth' => 160,
            'employer_respondents' => 290,
            'employer_follow_up' => 'English Proficiency Test wajib bagi mahasiswa tingkat akhir dan perluasan English Program melalui Pusat Bahasa.',
        ];
    }

    /** Pemetaan PIC mengikuti struktur organisasi UNJ sebagai PTN Badan Hukum. */
    protected function unitForIndicator(int $n): string
    {
        return match ($n) {
            25 => 'spi',
            39 => 'kerjasama',
            14 => 'kemahasiswaan',
            default => parent::unitForIndicator($n),
        };
    }

    // ------------------------------------------------------------------ proyek akreditasi

    protected function projectPlan(): array
    {
        return [
            'variant' => '3a',
            'name' => 'Reakreditasi Institusi 2027 · Jalur Unggul',
            'track' => Track::Excellent,
            'target_date' => '2026-12-15',
            'notes' => 'SK akreditasi institusi peringkat Unggul berlaku sampai 2 Februari 2027. Target unggah LED dan LKPT ke SAPTO 15 Desember 2026 agar asesmen kecukupan dan lapangan selesai sebelum SK berakhir.',
            'locked_weeks' => 12,
            'eligibility_note' => 'Menunggu sinkronisasi pelaporan PDDikti semester gasal 2026/2027 untuk 2 prodi baru.',
            // Jalur Terakreditasi praktis terpenuhi; tantangan ada di jalur Unggul.
            'not_met' => ['15C', '23B'],
            // 11B (proporsi lektor kepala dan guru besar) adalah syarat perlu jalur Unggul.
            'not_met_excellent' => ['11B', '12B', '19B', '27B', '30A', '38B'],
            'unassessed' => ['37A'],
            'internal_disagree' => ['19B'],
            'internal_note' => 'Bukti pemanfaatan hasil riset oleh mitra belum mencakup tiga tahun terakhir; dinilai belum memenuhi.',
            'due_start' => '2026-10-20',
        ];
    }

    protected function seedEvidenceAndLed(AccreditationProject $project): void
    {
        parent::seedEvidenceAndLed($project);

        // Bukti unggulan yang bersumber dari laman resmi UNJ.
        $highlights = [
            [4, 'sk', 'SK BAN-PT Akreditasi Institusi Peringkat Unggul Universitas Negeri Jakarta', '45/SK/BAN-PT/Akred/PT/II/2021', '2021-02-02', '2027-02-02', 'https://unj.ac.id/akreditasi-unj/'],
            [4, 'data', 'Rekap 59 program studi terakreditasi internasional (ASIIN 17, AQAS 21, FIBAA 21)', null, '2026-09-01', null, 'https://spm.unj.ac.id/?page_id=16696'],
            [24, 'peraturan', 'PP Nomor 31 Tahun 2024 tentang Perguruan Tinggi Negeri Badan Hukum Universitas Negeri Jakarta', 'PP 31/2024', '2024-08-14', null, 'https://peraturan.bpk.go.id/Details/296643'],
            [36, 'kebijakan', 'Visi, misi, tujuan, nilai dasar, dan budaya kerja Universitas Negeri Jakarta', null, '2025-01-10', null, 'https://unj.ac.id/visi-dan-misi/'],
            [39, 'lainnya', 'QS World University Rankings by Subject 2026: Education peringkat 351-400 dunia (4 nasional)', null, '2026-03-25', null, 'https://unj.ac.id/'],
            [39, 'lainnya', 'THE World University Rankings by Subject 2026: Social Sciences (2 nasional) dan Education (7 nasional)', null, '2026-01-21', null, 'https://unj.ac.id/'],
            [39, 'lainnya', 'QS Asia University Rankings 2026 peringkat 951-1000 dan QS Southeast Asia peringkat 153', null, '2025-11-04', null, 'https://unj.ac.id/'],
            [9, 'sertifikat', 'Sertifikat akreditasi A Perpustakaan UNJ dari Perpustakaan Nasional RI', null, '2026-09-15', '2031-09-15', 'https://lib.unj.ac.id/'],
        ];
        $evidence = app(EvidenceService::class);
        $uploader = $this->users['pic.lpm'];

        foreach ($highlights as [$no, $type, $title, $number, $date, $until, $url]) {
            $unit = $this->unitForIndicator($no);
            $doc = Document::create([
                'owner_unit_id' => $this->units[$unit]->id, 'title' => $title, 'doc_type' => $type, 'number' => $number,
                'doc_date' => $date, 'valid_until' => $until, 'approved_by' => in_array($type, ['sk', 'peraturan', 'kebijakan'], true) ? 'Pejabat berwenang' : null,
                'confidentiality' => 'publik', 'is_public' => true, 'status' => DocumentStatus::Draft, 'uploaded_by' => $uploader->id,
                'description' => 'Bukti unggulan dari laman resmi UNJ untuk indikator '.$no.'.',
            ]);
            $evidence->addVersion($doc, null, $url, $uploader);
            $doc->forceFill(['status' => DocumentStatus::Valid, 'validated_by' => $this->users['manajer']->id, 'validated_at' => now()->subDays(mt_rand(2, 20))])->save();

            $item = WorkItem::with('subIndicator.descriptors.keywords')->where('accreditation_project_id', $project->id)
                ->whereHas('subIndicator.indicator', fn ($query) => $query->where('number', $no))->first();
            $keyword = $item?->subIndicator->descriptorFor(Track::Excellent)?->keywords->first() ?? $item?->subIndicator->descriptorFor(Track::Accredited)?->keywords->first();

            if ($keyword) {
                EvidenceLink::firstOrCreate(['document_id' => $doc->id, 'work_item_id' => $item->id, 'keyword_id' => $keyword->id], ['relevance_note' => 'Bukti unggulan: '.$keyword->text]);
            }
        }
    }

    protected function reconciliationNotes(): array
    {
        return [
            'MHS_AKTIF' => 'Selisih karena 1.284 mahasiswa PPG dan cuti akademik belum diperbarui statusnya di PDDikti.',
            'LULUSAN_TS' => 'Lulusan wisuda sesi kelima dan keenam (Oktober 2026) belum seluruhnya dilaporkan.',
        ];
    }

    protected function governanceProfile(): array
    {
        $base = parent::governanceProfile();

        return [
            'decisions' => [
                ['Penetapan Tim Reakreditasi Institusi 2027', '-96 days', 'rapat_pimpinan', 'Rektor menetapkan Tim Reakreditasi Institusi yang diketuai Kepala SPM dengan anggota direktur, ketua lembaga, dan wakil dekan bidang akademik.'],
                ['Penetapan jalur akreditasi: Unggul', '-84 days', 'senat', 'Senat Akademik Universitas menyetujui pengajuan jalur Unggul berdasarkan simulasi skor IAPT 4.1 dan status 59 prodi terakreditasi internasional.'],
                ['Percepatan guru besar dan lektor kepala', '-70 days', 'rapat_pimpinan', 'Direktorat Sumber Daya membuka klinik percepatan jabatan akademik; target 40 lektor kepala dan 18 guru besar baru sampai Juni 2027 untuk memenuhi syarat perlu 11B.'],
                ['Dukungan anggaran reakreditasi dari MWA', '-63 days', 'mwa', 'Majelis Wali Amanat menyetujui alokasi Rp 1,2 miliar untuk penyusunan LED, LKPT, dan simulasi asesmen lapangan.'],
                ['Klinik penulisan LED setiap Kamis', '-49 days', 'taskforce', 'Klinik penulisan dengan pendampingan reviewer dan asesor internal untuk kriteria 2 dan 4.'],
                ['Sinkronisasi data PDDikti sebelum 30 November', '-28 days', 'rapat_pimpinan', 'Pustikom dan Direktorat Akademik memutakhirkan status mahasiswa PPG, cuti, dan lulusan wisuda Oktober.'],
                ['Tindak lanjut hasil AMI 2026', '-18 days', 'rtm', 'RTM menetapkan 8 tindakan koreksi dan 4 tindakan pencegahan, termasuk penguatan bukti hilirisasi riset.'],
                ['Simulasi asesmen lapangan dengan asesor eksternal', '-6 days', 'taskforce', 'Simulasi kedua dijadwalkan pekan pertama Desember 2026 di Gedung Rektorat Kampus A Rawamangun.'],
            ],
            'decision_owners' => ['pic.sdm', 'pic.keuangan', 'pic.lpm', 'pic.tik', 'pic.lpm', 'pic.lpm'],
            'tasks' => [
                ['Lengkapi notulen RTM fakultas 2023-2025', 'lpm'], ['Unggah SK Gugus Penjaminan Mutu seluruh fakultas', 'lpm'], ['Selaraskan dokumen SPMI dengan statuta PTN-BH', 'lpm'],
                ['Rekap prestasi mahasiswa internasional TS', 'kemahasiswaan'], ['Analisis tren mahasiswa baru per jalur seleksi', 'akademik'], ['Laporan evaluasi kurikulum OBE seluruh prodi', 'akademik'],
                ['Validasi jabatan akademik dosen di SISTER', 'sdm'], ['Perbarui katalog layanan Pustikom', 'tik'], ['Unggah laporan tracer study 2025', 'kemahasiswaan'],
                ['Peta jalan riset kependidikan 2025-2029', 'lppm'], ['Kumpulkan bukti hilirisasi riset ke Dinas Pendidikan', 'lppm'], ['Laporan PkM sekolah mitra di Jakarta Timur', 'lppm'],
                ['Perbarui SOP Satgas PPK sesuai Permendikbudristek 55/2024', 'satgas'], ['Rekap penerima KIP Kuliah dan beasiswa UNJ', 'kemahasiswaan'], ['Susun RKAT 2027 PTN-BH', 'keuangan'],
                ['Laporan keuangan audited 2025 dan opini KAP', 'keuangan'], ['Dokumentasi benchmarking dengan universitas mitra luar negeri', 'kerjasama'], ['Rekap kerja sama luar negeri aktif dan implementasinya', 'kerjasama'],
                ['Laporan capaian pemeringkatan QS dan THE 2026', 'perencanaan'], ['Lengkapi dokumen K3 dan aksesibilitas disabilitas Kampus A-E', 'sarpras'], ['Laporan evaluasi pembelajaran semester genap', 'akademik'],
                ['Sisipkan pendidikan antikorupsi pada RPS', 'akademik'], ['Rekap sertifikasi kompetensi lulusan (LSP P1 UNJ)', 'akademik'], ['Validasi survei kepuasan tendik 2026', 'lpm'],
                ['Perbaiki tautan bukti yang rusak', 'tik'], ['Monev hibah riset internal 2026', 'lppm'], ['Laporan capaian diferensiasi misi LPTK', 'perencanaan'],
                ['Laporan SPI atas pengendalian internal 2025', 'spi'], ['Laporan layanan mahasiswa dan konseling', 'kemahasiswaan'], ['Perbarui pedoman PMB inklusif dan afirmasi', 'kemahasiswaan'],
            ],
            'risks' => [
                ['Syarat perlu Unggul 11B: proporsi lektor kepala dan guru besar di bawah ambang', 4, 5, 'pic.sdm', 'Klinik percepatan jabatan akademik dan pendampingan publikasi Q1-Q2.'],
                ['SK akreditasi institusi berakhir 2 Februari 2027', 3, 5, 'manajer', 'Unggah ke SAPTO paling lambat 15 Desember 2026; rapat mingguan pimpinan.'],
                ['Selisih data mahasiswa aktif dengan PDDikti melebihi toleransi', 4, 4, 'pic.akademik', 'Pemutakhiran status mahasiswa PPG dan cuti sebelum 30 November.'],
                ['Prodi Gizi dan Bisnis Digital: akreditasi berakhir dalam 6 bulan', 3, 4, 'pic.akademik', 'Pengajuan reakreditasi ke LAM-PTKes dan LAMEMBA paling lambat November 2026.'],
                ['Bukti pemanfaatan hasil riset oleh mitra belum lengkap', 3, 3, 'pic.lppm', 'Kumpulkan surat pemanfaatan dari Dinas Pendidikan dan sekolah mitra.'],
                ['Respons survei mahasiswa di bawah 30%', 2, 3, 'pic.lpm', 'Integrasi survei pada portal SIAKAD sebelum pengisian KRS.'],
                ['Bukti kerja sama internasional kedaluwarsa', 2, 4, 'pic.kerjasama', 'Perpanjangan MoU dan dokumentasi implementasi kerja sama.'],
                ['Kehilangan data bukti karena server tunggal', 1, 5, 'pic.tik', 'Cadangan harian otomatis ke pusat data sekunder Pustikom.'],
                ['Pergantian pejabat struktural pascarestrukturisasi PTN-BH', 2, 3, 'manajer', 'Dokumentasi peran dan pendamping cadangan setiap PIC.'],
                ['Kemampuan bahasa asing lulusan dinilai rendah oleh pengguna', 3, 2, 'pic.akademik', 'English Proficiency Test wajib dan English Program Pusat Bahasa.'],
            ],
            'standards' => $base['standards'],
            'standard_statement' => ' Universitas Negeri Jakarta melampaui SN Dikti sesuai visi universitas berkelas dunia di bidang kependidikan, sains, teknologi, dan humaniora.',
            'audits' => [
                ['name' => 'AMI Siklus 2025', 'cycle_year' => 2025, 'audit_date' => '2025-11-10', 'lead_auditor' => 'Prof. Dr. Made Wirawan, M.Si.', 'state' => 'selesai', 'summary' => '86 prodi dan 18 unit diaudit; 2 temuan mayor, 14 minor, 9 observasi.'],
                ['name' => 'AMI Siklus 2026', 'cycle_year' => 2026, 'audit_date' => now()->subDays(24), 'lead_auditor' => 'Prof. Dr. Made Wirawan, M.Si.', 'state' => 'pelaporan', 'summary' => 'Fokus pada kesiapan reakreditasi jalur Unggul, hilirisasi riset, dan tata kelola PTN-BH.'],
            ],
            'findings' => [
                [0, 'mayor', 'Bukti RTM tingkat fakultas belum lengkap untuk siklus 2023 pada dua fakultas.', 2, 'lpm', 'closed', 'ami'],
                [0, 'minor', 'RPS belum memuat sisipan pendidikan antikorupsi pada 6 prodi.', 13, 'akademik', 'closed', 'ami'],
                [0, 'minor', 'Data jabatan akademik di SISTER belum selaras dengan data kepegawaian.', 11, 'sdm', 'verification', 'ami'],
                [0, 'mayor', 'Peta jalan PkM belum memuat target dampak terukur.', 20, 'lppm', 'closed', 'ami'],
                [1, 'mayor', 'Bukti pemanfaatan hasil riset oleh mitra belum terdokumentasi tiga tahun.', 19, 'lppm', 'capa', 'ami'],
                [1, 'minor', 'Survei kepuasan mitra belum mencakup mitra luar negeri.', 34, 'lpm', 'capa', 'ami'],
                [1, 'minor', 'Pedoman PMB belum memuat ketentuan afirmasi penyandang disabilitas secara rinci.', 31, 'kemahasiswaan', 'open', 'ami'],
                [1, 'observasi', 'Beberapa tautan bukti di laman unit tidak dapat diakses.', 26, 'tik', 'open', 'ami'],
                [1, 'minor', 'Rencana strategis keuangan belum menurunkan target pendapatan non-UKT PTN-BH.', 27, 'keuangan', 'capa', 'ami'],
                [null, 'minor', 'Analisis akar masalah kelulusan tepat waktu S2/S3 masih deskriptif.', 12, 'akademik', 'open', 'simulasi'],
                [null, 'observasi', 'Narasumber mahasiswa belum memahami kanal pelaporan Satgas PPK.', 28, 'satgas', 'capa', 'simulasi'],
                [null, 'peluang', 'Keunggulan 59 prodi terakreditasi internasional dapat dijadikan narasi utama kriteria 4.', 39, 'kerjasama', 'open', 'simulasi'],
                [null, 'minor', 'Kepuasan mahasiswa terhadap kecepatan layanan administrasi di bawah 75%.', 34, 'kemahasiswaan', 'verification', 'survei'],
                [null, 'mayor', 'Proporsi lektor kepala dan guru besar belum memenuhi ambang jalur Unggul.', 11, 'sdm', 'capa', 'simulasi'],
            ],
            'reviews' => [
                ['meeting_date' => '2025-12-16', 'title' => 'RTM Universitas Semester Gasal 2025/2026', 'chair' => 'Rektor', 'minutes' => 'Membahas hasil AMI 2025, survei kepuasan, dan capaian IKU PTN-BH.', 'outcomes' => 'Penetapan 6 tindakan koreksi; revisi standar penelitian; klinik jabatan akademik.'],
                ['meeting_date' => '2026-06-22', 'title' => 'RTM Universitas Semester Genap 2025/2026', 'chair' => 'Rektor', 'minutes' => 'Membahas kesiapan reakreditasi institusi dan simulasi skor IAPT 4.1.', 'outcomes' => 'Pemilihan jalur Unggul; pembentukan klinik LED; sinkronisasi data PDDikti.'],
                ['meeting_date' => now()->subDays(18)->toDateString(), 'title' => 'RTM Khusus Kesiapan Reakreditasi Institusi', 'chair' => 'Wakil Rektor Bidang Akademik, Kemahasiswaan, dan Alumni', 'minutes' => 'Membahas hasil AMI 2026 dan syarat perlu Unggul 11B.', 'outcomes' => 'Rencana percepatan jabatan akademik; target AKI 88 sebelum unggah SAPTO.'],
            ],
        ];
    }

    protected function siteVisitProfile(): array
    {
        $base = parent::siteVisitProfile();

        return [
            'mock' => [
                'title' => 'Simulasi Asesmen Lapangan I', 'starts_on' => now()->subDays(40), 'ends_on' => now()->subDays(39),
                'mode' => 'luring', 'assessors' => 'Prof. Dr. Made Wirawan, M.Si.; Prof. Dr. Ir. Kusnadi, M.Eng. (asesor eksternal)', 'state' => 'selesai',
                'document_room' => 'Ruang Sidang Senat, Gedung Rektorat Kampus A Rawamangun', 'minutes' => 'Simulasi berjalan baik; 6 rekomendasi perbaikan untuk jalur Unggul.',
            ],
            'recommendations' => [
                [11, 'Sajikan peta jalan kenaikan lektor kepala dan guru besar per fakultas dengan target terukur.', 'task'],
                [19, 'Lengkapi bukti pemanfaatan hasil riset oleh Dinas Pendidikan dan sekolah mitra.', 'task'],
                [12, 'Tajamkan analisis kelulusan tepat waktu program magister dan doktor.', null],
                [28, 'Tunjukkan bukti sosialisasi Satgas PPK pada PKKMB dan kanal pelaporan daring.', 'capa'],
                [34, 'Perluas responden survei mitra hingga mitra luar negeri.', null],
                [39, 'Jadikan 59 prodi terakreditasi internasional dan capaian QS/THE sebagai bukti keunggulan.', null],
            ],
            'visit' => [
                'title' => 'Asesmen Lapangan BAN-PT', 'starts_on' => '2027-01-18', 'ends_on' => '2027-01-19',
                'mode' => 'luring', 'assessors' => 'Menunggu penugasan BAN-PT', 'rehearsal_on' => '2027-01-11', 'state' => 'rencana',
                'document_room' => 'Ruang Sidang Senat, Gedung Rektorat Kampus A Rawamangun',
            ],
            'visit_date' => '2027-01-18',
            'sessions' => [
                ['08:00', '09:00', 'pimpinan', 'Pembukaan dan presentasi Rektor', 'Ruang Senat', 'Rektor'],
                ['09:00', '10:30', 'pimpinan', 'Wawancara pimpinan, MWA, dan Senat Akademik', 'Ruang Senat', 'Kepala SPM'],
                ['10:30', '11:30', 'dosen', 'Wawancara dosen', 'Ruang Rapat FMIPA', 'Dekan FMIPA'],
                ['11:30', '12:30', 'tendik', 'Wawancara tenaga kependidikan', 'Ruang Rapat Direktorat Akademik', 'Direktur Akademik'],
                ['13:30', '14:30', 'mahasiswa', 'Wawancara mahasiswa', 'Aula Gedung Dewi Sartika', 'Direktur Kemahasiswaan'],
                ['14:30', '15:30', 'lulusan', 'Wawancara lulusan dan pengguna lulusan', 'Ruang Alumni', 'Direktur Kemahasiswaan'],
                ['15:30', '17:00', 'observasi', 'Observasi sarana, laboratorium, dan Labschool', 'Kampus A Rawamangun', 'Kepala Subdirektorat Sarpras'],
            ],
            'interviewees' => [
                ['pimpinan', 'Prof. Dr. Komarudin, M.Si.', 'Rektor', true], ['pimpinan', 'Ketua Majelis Wali Amanat', 'MWA', true],
                ['pimpinan', 'Prof. Dr. Ifan Iskandar, M.Hum.', 'Wakil Rektor Bidang Akademik', true], ['dosen', 'Prof. Dr. Irwan Setiadi, M.Si.', 'Dekan FMIPA', true],
                ['dosen', 'Sinta Rahayu, M.Pd.', 'Dosen PGSD', false], ['dosen', 'Dr. Bayu Setiawan, M.T.', 'Dosen Pendidikan Teknik Mesin', true],
                ['tendik', 'Nurul Hakim, S.E.', 'Staf Direktorat Keuangan', true], ['tendik', 'Galih Saputra, A.Md.', 'Pranata laboratorium Kimia', false],
                ['mahasiswa', 'Rizki Maulana', 'Ketua BEM UNJ', true], ['mahasiswa', 'Putri Anggraini', 'Mahasiswa Pendidikan Bahasa Inggris 2023', false],
                ['lulusan', 'Taufik Hidayat, S.Pd.', 'Alumni PGSD, guru SD negeri', true], ['pengguna', 'Kepala Sekolah SMA Negeri mitra', 'Pengguna lulusan', false],
            ],
            'questions' => array_map(fn ($q) => [$q[0], $q[1], str_replace('UNM', 'UNJ', $q[2]), match ($q[0]) {
                36 => 'Visi universitas berkelas dunia di bidang kependidikan, sains, teknologi, dan humaniora; IKU PTN-BH.',
                39 => '59 prodi terakreditasi internasional; QS by Subject Education 351-400; THE Social Sciences 2 nasional.',
                35 => 'Opini WTP 2021-2025 dari KAP; laporan keuangan PTN-BH.',
                default => $q[3],
            }], $base['questions']),
        ];
    }
}
