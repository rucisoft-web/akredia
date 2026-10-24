<?php

namespace Database\Seeders;

use App\Enums\DocumentStatus;
use App\Enums\LedState;
use App\Enums\ProjectState;
use App\Enums\TaskStatus;
use App\Enums\Track;
use App\Enums\WorkItemState;
use App\Models\AcademicPeriod;
use App\Models\AccreditationProject;
use App\Models\Achievement;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Capa;
use App\Models\Decision;
use App\Models\Document;
use App\Models\EmployerSatisfaction;
use App\Models\EvidenceLink;
use App\Models\FinancialAudit;
use App\Models\Finding;
use App\Models\GapItem;
use App\Models\Indicator;
use App\Models\Institution;
use App\Models\InstrumentVariant;
use App\Models\InternalAudit;
use App\Models\IpRight;
use App\Models\Lecturer;
use App\Models\LedComment;
use App\Models\LedSection;
use App\Models\LinkCheck;
use App\Models\ManagementReview;
use App\Models\Membership;
use App\Models\MetricValue;
use App\Models\ProgramAccreditation;
use App\Models\ProgramYearStat;
use App\Models\Publication;
use App\Models\QualityStandard;
use App\Models\ReadinessSnapshot;
use App\Models\Recognition;
use App\Models\Reconciliation;
use App\Models\ResearchGrant;
use App\Models\Risk;
use App\Models\SiteVisit;
use App\Models\StudentCohort;
use App\Models\StudyProgram;
use App\Models\Survey;
use App\Models\Task;
use App\Models\TracerStudy;
use App\Models\Unit;
use App\Models\User;
use App\Models\VisitInterviewee;
use App\Models\VisitQuestion;
use App\Models\VisitRecommendation;
use App\Models\VisitSession;
use App\Models\WorkItem;
use App\Services\EvidenceService;
use App\Services\LedService;
use App\Services\MetricEngine;
use App\Services\ProjectWorkflow;
use App\Services\ReadinessCalculator;
use App\Support\CurrentInstitution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Data contoh: Universitas Nusantara Mandiri (fiktif), PTS akademik yang sedang menyiapkan
 * akreditasi institusi 2027. Semua nama orang, nomor, dan angka adalah rekaan.
 */
class DemoSeeder extends Seeder
{
    public const SLUG = 'unm';

    protected Institution $pt;

    /** @var array<string, Unit> */
    protected array $units = [];

    /** @var array<string, User> */
    protected array $users = [];

    /** @var array<string, StudyProgram> */
    protected array $programs = [];

    protected int $ts = 2025;

    /** Domain email akun demo; tiap institusi contoh memakai subdomain sendiri. */
    protected string $emailDomain = 'akredia.test';

    /** Singkatan institusi untuk nomor SK dan teks contoh. */
    protected string $short = 'UNM';

    /** Sebutan pimpinan tertinggi (Rektor, Direktur, Ketua). */
    protected string $leaderTitle = 'Rektor';

    /** Akun tamu bersama ikut menjadi anggota institusi ini (pilihan "Masuk sebagai Tamu" di halaman login). */
    protected bool $guestAccess = false;

    public function run(): void
    {
        mt_srand(2027);
        AuditLog::$enabled = false;

        if ($old = Institution::where('slug', static::SLUG)->first()) {
            $old->delete();
        }

        $this->pt = Institution::create($this->institutionAttributes());

        app(CurrentInstitution::class)->set($this->pt);

        $this->seedOrganisation();
        $this->seedUsers();
        $this->seedPerformanceData();
        $project = $this->seedProject();
        $this->seedEvidenceAndLed($project);
        $this->seedMetrics($project);
        $this->seedGovernance($project);
        $this->seedSiteVisits($project);
        $this->seedSnapshots($project);
        $this->seedAuditTrail();

        AuditLog::$enabled = true;
        app(CurrentInstitution::class)->set(null);

        $this->command?->info("Data contoh {$this->pt->name} dimuat. Kata sandi akun demo: ".config('akredia.demo.password'));
    }

    /** @return array<string, mixed> */
    protected function institutionAttributes(): array
    {
        return [
            'name' => 'Universitas Nusantara Mandiri',
            'slug' => static::SLUG,
            'code' => '041099',
            'type' => 'pts_akademik',
            'legal_entity' => 'Yayasan Pendidikan Nusantara Mandiri',
            'sk_number' => '112/KPT/I/2003',
            'sk_date' => '2003-07-14',
            'first_intake_year' => 2003,
            'city' => 'Bandung',
            'address' => 'Jl. Cendekia Raya No. 27, Bandung 40135',
            'website' => 'https://www.unm-demo.ac.id',
            'email' => 'info@unm-demo.ac.id',
            'vision' => 'Menjadi universitas kewirausahaan berbasis teknologi yang unggul di Asia Tenggara pada 2035.',
            'mission_focus' => 'Pendidikan berbasis kewirausahaan dan teknologi terapan; penelitian terapan untuk UMKM dan industri kreatif; pengabdian berbasis pemberdayaan desa digital.',
            'last_accreditation_rank' => 'Baik Sekali',
            'last_accreditation_sk' => '1543/SK/BAN-PT/Ak.KP/PT/VIII/2022',
            'last_accreditation_until' => '2027-08-15',
            'is_demo' => true,
        ];
    }

    // ------------------------------------------------------------------ organisasi

    protected function seedOrganisation(): void
    {
        $rektorat = Unit::create(['code' => 'REK', 'name' => 'Rektorat', 'type' => 'rektorat', 'head_name' => 'Prof. Dr. Ir. Hendra Wiratmaja, M.T.']);

        $list = [
            'lpm' => ['LPM', 'Lembaga Penjaminan Mutu', 'lembaga', 'Dr. Bambang Sutrisno, M.M.'],
            'lppm' => ['LPPM', 'Lembaga Penelitian dan Pengabdian kepada Masyarakat', 'lembaga', 'Dr. Ratna Dewi Anggraeni, M.Si.'],
            'akademik' => ['BAA', 'Biro Akademik', 'biro', 'Drs. Yusuf Hidayat, M.Pd.'],
            'sdm' => ['BSDM', 'Biro Sumber Daya Manusia', 'biro', 'Dra. Endang Purwati, M.M.'],
            'keuangan' => ['BKU', 'Biro Keuangan', 'biro', 'Hartono Gunawan, S.E., Ak., M.Ak.'],
            'kemahasiswaan' => ['BKM', 'Biro Kemahasiswaan dan Alumni', 'biro', 'Dian Permatasari, S.Psi., M.Psi.'],
            'sarpras' => ['BUS', 'Biro Umum dan Sarana Prasarana', 'biro', 'Ir. Joko Santoso, M.T.'],
            'perencanaan' => ['BPK', 'Biro Perencanaan dan Kerja Sama', 'biro', 'Dr. Wulan Sari, M.B.A.'],
            'sekretariat' => ['SEK', 'Sekretariat Universitas', 'biro', 'Rudi Hermawan, S.H., M.H.'],
            'tik' => ['UPT-TIK', 'UPT Teknologi Informasi dan Pangkalan Data', 'upt', 'Rina Kartikasari, M.Kom.'],
            'perpustakaan' => ['UPT-PUS', 'UPT Perpustakaan', 'upt', 'Siti Aminah, S.IP., M.Hum.'],
            'satgas' => ['SATGAS', 'Satgas Pencegahan dan Penanganan Kekerasan', 'satgas', 'Dr. Maria Ulfa, M.Psi.'],
        ];

        foreach ($list as $key => [$code, $name, $type, $head]) {
            $this->units[$key] = Unit::create(['parent_id' => $rektorat->id, 'code' => $code, 'name' => $name, 'type' => $type, 'head_name' => $head]);
        }

        $faculties = [
            'feb' => ['FEB', 'Fakultas Ekonomi dan Bisnis', 'Dr. Andi Firmansyah, S.E., M.Si.'],
            'ft' => ['FT', 'Fakultas Teknik', 'Dr. Ir. Teguh Prakoso, M.T.'],
            'fik' => ['FIK', 'Fakultas Ilmu Komputer', 'Dr. Fajar Nugroho, M.Kom.'],
            'fkip' => ['FKIP', 'Fakultas Keguruan dan Ilmu Pendidikan', 'Dr. Sri Mulyani, M.Pd.'],
            'fh' => ['FH', 'Fakultas Hukum', 'Dr. Arief Rahman, S.H., M.H.'],
            'fikes' => ['FIKES', 'Fakultas Ilmu Kesehatan', 'Ns. Lina Marlina, S.Kep., M.Kep.'],
        ];

        foreach ($faculties as $key => [$code, $name, $head]) {
            $this->units[$key] = Unit::create(['parent_id' => $rektorat->id, 'code' => $code, 'name' => $name, 'type' => 'fakultas', 'head_name' => $head]);
        }

        $this->units['rektorat'] = $rektorat;

        // [kunci, fakultas, kode PDDikti, nama, jenjang, dibuka, lembaga, peringkat, berlaku s.d., dosen tetap, mahasiswa aktif TS]
        $programs = [
            ['mnj', 'feb', '61201', 'Manajemen', 'S1', 2003, 'lam_emba', 'unggul', '2029-11-20', 34, 1480],
            ['akt', 'feb', '62201', 'Akuntansi', 'S1', 2003, 'lam_emba', 'baik_sekali', '2028-06-12', 24, 980],
            ['ekp', 'feb', '60201', 'Ekonomi Pembangunan', 'S1', 2008, 'lam_emba', 'baik', '2027-09-30', 12, 320],
            ['mm', 'feb', '61101', 'Manajemen', 'S2', 2012, 'lam_emba', 'baik_sekali', '2028-03-04', 8, 140],
            ['d3akt', 'feb', '62401', 'Akuntansi', 'D3', 2004, 'lam_emba', 'baik', '2027-12-18', 7, 210],
            ['sipil', 'ft', '22201', 'Teknik Sipil', 'S1', 2004, 'lam_teknik', 'baik_sekali', '2028-08-01', 18, 610],
            ['ti', 'ft', '26201', 'Teknik Industri', 'S1', 2004, 'lam_teknik', 'baik_sekali', '2029-01-15', 16, 560],
            ['elektro', 'ft', '20201', 'Teknik Elektro', 'S1', 2006, 'lam_teknik', 'baik', '2027-02-20', 12, 380],
            ['arsi', 'ft', '23201', 'Arsitektur', 'S1', 2010, 'lam_teknik', 'baik', '2028-10-09', 9, 290],
            ['if', 'fik', '55201', 'Informatika', 'S1', 2005, 'lam_infokom', 'unggul', '2030-02-28', 30, 1420],
            ['si', 'fik', '57201', 'Sistem Informasi', 'S1', 2005, 'lam_infokom', 'baik_sekali', '2028-12-05', 22, 920],
            ['d3mi', 'fik', '57401', 'Manajemen Informatika', 'D3', 2005, 'lam_infokom', 'baik', '2027-05-22', 7, 230],
            ['pbi', 'fkip', '88203', 'Pendidikan Bahasa Inggris', 'S1', 2009, 'lam_dik', 'baik_sekali', '2028-04-17', 14, 460],
            ['pgsd', 'fkip', '86206', 'Pendidikan Guru Sekolah Dasar', 'S1', 2011, 'lam_dik', 'baik_sekali', '2029-07-08', 20, 780],
            ['pmat', 'fkip', '84202', 'Pendidikan Matematika', 'S1', 2011, 'lam_dik', 'baik', '2027-11-11', 10, 300],
            ['hukum', 'fh', '74201', 'Ilmu Hukum', 'S1', 2003, 'lam_sama', 'unggul', '2029-09-19', 26, 1120],
            ['mh', 'fh', '74101', 'Ilmu Hukum', 'S2', 2014, 'lam_sama', 'baik_sekali', '2028-07-25', 7, 120],
            ['kep', 'fikes', '14201', 'Keperawatan', 'S1', 2015, 'lam_ptkes', 'baik_sekali', '2029-03-30', 21, 540],
        ];

        foreach ($programs as [$key, $fac, $code, $name, $level, $opened, $agency, $rank, $until, $lecturers, $students]) {
            $program = StudyProgram::create(['unit_id' => $this->units[$fac]->id, 'code_pddikti' => $code, 'name' => $name, 'level' => $level, 'opened_year' => $opened]);
            $program->setAttribute('demo_lecturers', $lecturers);
            $program->setAttribute('demo_students', $students);
            $this->programs[$key] = $program;

            $validUntil = Carbon::parse($until);
            ProgramAccreditation::create([
                'study_program_id' => $program->id, 'agency' => $agency, 'rank' => $rank,
                'sk_number' => sprintf('%03d/SK/%s/Akred/S/%s', mt_rand(100, 999), strtoupper(str_replace('_', '-', $agency)), $validUntil->copy()->subYears(5)->format('m/Y')),
                'sk_date' => $validUntil->copy()->subYears(5), 'valid_until' => $validUntil, 'is_current' => true,
            ]);
        }

        foreach ([[2023, '2023/2024'], [2024, '2024/2025'], [2025, '2025/2026'], [2026, '2026/2027']] as [$y, $name]) {
            AcademicPeriod::create(['name' => $name, 'year_start' => $y, 'starts_at' => "{$y}-09-01", 'ends_at' => ($y + 1).'-08-31']);
        }
    }

    protected function seedUsers(): void
    {
        $password = config('akredia.demo.password', 'Demo#2026akredia');

        foreach ($this->people() as [$key, $local, $name, $role, $unit, $title]) {
            $user = User::updateOrCreate(['email' => "{$local}@{$this->emailDomain}"], [
                'name' => $name, 'password' => $password, 'is_active' => true,
                'last_login_at' => now()->subHours(mt_rand(1, 240)),
            ]);
            Membership::create(['user_id' => $user->id, 'unit_id' => $this->units[$unit]->id, 'role' => $role, 'position_title' => $title]);
            $this->users[$key] = $user;
        }

        if ($this->guestAccess) {
            $guest = User::updateOrCreate(['email' => User::DEMO_GUEST_EMAIL], ['name' => 'Tamu Demo', 'password' => Str::random(40), 'is_active' => true]);
            Membership::create(['user_id' => $guest->id, 'unit_id' => $this->units['rektorat']->id, 'role' => 'pengamat', 'position_title' => 'Tamu (hanya lihat)']);
        }

        // Administrator platform juga dapat masuk ke PT contoh sebagai admin.
        if ($email = config('akredia.admin.email')) {
            if ($admin = User::where('email', $email)->first()) {
                Membership::create(['user_id' => $admin->id, 'unit_id' => $this->units['lpm']->id, 'role' => 'admin', 'position_title' => 'Administrator platform']);
            }
        }
    }

    /** @return list<array{string, string, string, string, string, string}> [kunci, email, nama, peran, unit, jabatan] */
    protected function people(): array
    {
        return [
            ['admin', 'admin.pt', 'Rina Kartikasari, M.Kom.', 'admin', 'tik', 'Kepala UPT TIK dan Pangkalan Data'],
            ['rektor', 'rektor', 'Prof. Dr. Ir. Hendra Wiratmaja, M.T.', 'pimpinan', 'rektorat', 'Rektor'],
            ['wr1', 'wr1', 'Dr. Lestari Handayani, M.Pd.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Akademik'],
            ['manajer', 'manajer', 'Dr. Bambang Sutrisno, M.M.', 'manajer', 'lpm', 'Kepala LPM / Ketua Tim Akreditasi'],
            ['reviewer', 'reviewer', 'Dr. Siti Nurhaliza, M.Si.', 'reviewer', 'lpm', 'Reviewer LED'],
            ['reviewer2', 'reviewer2', 'Dr. Agus Prasetyo, M.T.', 'reviewer', 'ft', 'Reviewer LED'],
            ['asesor', 'asesor', 'Prof. Dr. Made Suardana, M.Si.', 'asesor', 'lpm', 'Asesor internal'],
            ['pic.lpm', 'pic.lpm', 'Nur Aisyah, S.T., M.T.', 'pic', 'lpm', 'Sekretaris LPM'],
            ['pic.akademik', 'pic.akademik', 'Drs. Yusuf Hidayat, M.Pd.', 'pic', 'akademik', 'Kepala Biro Akademik'],
            ['pic.sdm', 'pic.sdm', 'Dra. Endang Purwati, M.M.', 'pic', 'sdm', 'Kepala Biro SDM'],
            ['pic.keuangan', 'pic.keuangan', 'Hartono Gunawan, S.E., Ak., M.Ak.', 'pic', 'keuangan', 'Kepala Biro Keuangan'],
            ['pic.kemahasiswaan', 'pic.kemahasiswaan', 'Dian Permatasari, S.Psi., M.Psi.', 'pic', 'kemahasiswaan', 'Kepala Biro Kemahasiswaan'],
            ['pic.sarpras', 'pic.sarpras', 'Ir. Joko Santoso, M.T.', 'pic', 'sarpras', 'Kepala Biro Umum'],
            ['pic.perencanaan', 'pic.perencanaan', 'Dr. Wulan Sari, M.B.A.', 'pic', 'perencanaan', 'Kepala Biro Perencanaan'],
            ['pic.sekretariat', 'pic.sekretariat', 'Rudi Hermawan, S.H., M.H.', 'pic', 'sekretariat', 'Sekretaris Universitas'],
            ['pic.lppm', 'pic.lppm', 'Dr. Ratna Dewi Anggraeni, M.Si.', 'pic', 'lppm', 'Kepala LPPM'],
            ['pic.tik', 'pic.tik', 'Fikri Ramadhan, S.Kom., M.T.I.', 'pic', 'tik', 'Koordinator Sistem Informasi'],
            ['pic.satgas', 'pic.satgas', 'Dr. Maria Ulfa, M.Psi.', 'pic', 'satgas', 'Ketua Satgas PPK'],
            ['data.feb', 'data.feb', 'Yoga Pratama, S.E., M.Ak.', 'pemilik_data', 'feb', 'Operator Data FEB'],
            ['pengamat', 'pengamat', 'H. Darmawan Kusuma, S.E., M.M.', 'pengamat', 'rektorat', 'Ketua Yayasan'],
        ];
    }

    // ------------------------------------------------------------------ data kinerja

    /**
     * Parameter data kinerja yang berbeda antar-institusi contoh.
     *
     * @return array<string, mixed>
     */
    protected function performanceProfile(): array
    {
        return [
            'nidn_start' => 410000001,
            'doctor_pct' => 26,
            'professor_pct' => 14,
            'events' => ['Kompetisi Debat Bahasa Inggris', 'Lomba Karya Tulis Ilmiah', 'Gemastik', 'Kompetisi Bisnis Plan', 'PKM Nasional', 'Hackathon Smart City', 'Pekan Olahraga Mahasiswa', 'Festival Film Pendek', 'Lomba Peradilan Semu', 'Kompetisi Robotik', 'Olimpiade Akuntansi', 'Kontes Desain Arsitektur', 'Kejuaraan Pencak Silat', 'Lomba Inovasi Keperawatan'],
            'achievement_base' => 18,
            'topics' => ['UMKM digital', 'rantai pasok halal', 'pembelajaran daring', 'keamanan siber', 'beton ramah lingkungan', 'fintech syariah', 'kecerdasan buatan untuk pertanian', 'tata kelola desa digital', 'literasi keuangan', 'perilaku konsumen generasi Z', 'energi surya atap', 'kesehatan mental mahasiswa', 'hukum perlindungan data pribadi', 'pariwisata berkelanjutan', 'kurikulum merdeka', 'deteksi dini stunting', 'Internet of Things', 'analitik big data', 'ekonomi sirkular', 'manajemen bencana'],
            'places' => ['Jawa Barat', 'Indonesia', 'kota Bandung', 'Asia Tenggara', 'perguruan tinggi swasta'],
            'publication_types' => ['jurnal_intl_bereputasi' => 14, 'jurnal_intl' => 10, 'jurnal_nas_terakreditasi' => 36, 'jurnal_nas' => 18, 'seminar_intl' => 10, 'seminar_nas' => 8, 'media_massa' => 4],
            'publication_base' => 140,
            'publication_growth' => 28,
            'venues' => ['International Journal of Applied Sciences', 'Jurnal Ilmiah Nusantara'],
            'ip_base' => 6,
            'ip_adopter' => 'CV Mitra Usaha Bersama',
            'recognitions' => ['Narasumber seminar nasional', 'Mitra bestari jurnal internasional', 'Juri kompetisi inovasi', 'Visiting lecturer Universiti Malaya', 'Staf ahli DPRD Provinsi', 'Penghargaan dosen berprestasi'],
            'recognition_base' => 7,
            'grant_base' => 14,
            'grant_scale' => 1,
            'auditors' => [2021 => 'KAP Rahardjo, Wibisono & Rekan', 2022 => 'KAP Rahardjo, Wibisono & Rekan', 2023 => 'KAP Santoso Halim & Rekan', 2024 => 'KAP Santoso Halim & Rekan', 2025 => 'KAP Santoso Halim & Rekan'],
            'survey_population' => ['mahasiswa' => 9800, 'dosen' => 330, 'tendik' => 260, 'mitra' => 85, 'lulusan' => 2100, 'pengguna' => 240],
            'survey_open_population' => 10100,
            'tracer_graduates' => 1850,
            'tracer_growth' => 70,
            'employer_respondents' => 96,
            'employer_follow_up' => 'Program English Proficiency wajib dan sertifikasi TOEFL ITP mulai 2025.',
        ];
    }

    protected function seedPerformanceData(): void
    {
        $p = $this->performanceProfile();
        $first = ['Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fitri', 'Gilang', 'Hana', 'Indra', 'Joko', 'Kartika', 'Lukman', 'Maya', 'Nanda', 'Oki', 'Putri', 'Rahmat', 'Sari', 'Taufik', 'Umi', 'Vina', 'Wahyu', 'Yanti', 'Zainal', 'Agung', 'Bayu', 'Dian', 'Erni', 'Farid', 'Galih', 'Hesti', 'Irfan', 'Kurnia', 'Lia', 'Mega', 'Nurul', 'Prasetyo', 'Rizki', 'Sinta', 'Tri', 'Wawan', 'Yudi', 'Anisa', 'Bagus', 'Desi', 'Ferry', 'Gita', 'Heru', 'Intan', 'Rudi'];
        $last = ['Saputra', 'Wijaya', 'Kusuma', 'Pratama', 'Hidayat', 'Nugroho', 'Lestari', 'Santoso', 'Purnomo', 'Rahayu', 'Setiawan', 'Gunawan', 'Siregar', 'Nasution', 'Simanjuntak', 'Wibowo', 'Hakim', 'Susanto', 'Permana', 'Kurniawan', 'Halim', 'Utami', 'Firmansyah', 'Ramadhan', 'Maulana', 'Suryadi', 'Handoko', 'Anggraini', 'Fauzi', 'Sembiring'];
        $nidn = $p['nidn_start'];
        $lecturers = collect();

        foreach ($this->programs as $key => $program) {
            $count = $program->demo_lecturers;

            for ($i = 0; $i < $count + max(2, intdiv($count, 3)); $i++) {
                $permanent = $i < $count;
                $roll = mt_rand(1, 100);
                $degree = $permanent ? ($roll <= $p['doctor_pct'] ? 'S3' : 'S2') : (mt_rand(1, 100) <= 10 ? 'S3' : 'S2');
                $rankRoll = mt_rand(1, 100);
                $rank = ! $permanent ? 'none' : match (true) {
                    $rankRoll <= ($degree === 'S3' ? $p['professor_pct'] : 1) => 'gb',
                    $rankRoll <= ($degree === 'S3' ? 60 : 17) => 'lk',
                    $rankRoll <= 60 => 'l',
                    $rankRoll <= 87 => 'aa',
                    default => 'none',
                };
                $name = $first[array_rand($first)].' '.$last[array_rand($last)];
                $name = ($rank === 'gb' ? 'Prof. ' : '').($degree === 'S3' ? 'Dr. ' : '').$name.($degree === 'S3' ? ', Ph.D.' : ', M.'.(['Si.', 'T.', 'Kom.', 'M.', 'Pd.', 'H.'][mt_rand(0, 5)]));

                $lecturers->push(Lecturer::create([
                    'study_program_id' => $program->id, 'nidn' => (string) ($permanent ? '0' : '8').$nidn++, 'name' => $name,
                    'employment' => $permanent ? 'tetap' : 'tidak_tetap', 'highest_degree' => $degree, 'academic_rank' => $rank,
                    'is_certified' => $permanent && $rank !== 'none' && mt_rand(1, 100) <= 78,
                ]));
            }
        }

        $permanentIds = $lecturers->where('employment', 'tetap')->pluck('id')->all();

        foreach ($this->programs as $key => $program) {
            $active = $program->demo_students;
            $level = $program->level;
            $span = match ($level) { 'D3' => 3, 'S2' => 2, default => 4 };

            for ($y = 2018; $y <= $this->ts; $y++) {
                $growth = 1 + ($y - $this->ts) * 0.035;
                $act = (int) round($active * $growth * (0.97 + mt_rand(0, 6) / 100));
                $new = (int) round($act / $span * (1.02 + mt_rand(0, 8) / 100));
                ProgramYearStat::create([
                    'study_program_id' => $program->id, 'year' => $y,
                    'applicants' => (int) round($new * (2.4 + mt_rand(0, 15) / 10)),
                    'new_students' => $new, 'active_students' => $act,
                    'graduates' => (int) round($act / $span * (0.84 + mt_rand(0, 8) / 100)),
                    'students_outside_10sks' => (int) round($act * ($y >= 2021 ? 0.06 + ($y - 2021) * 0.02 : 0.01)),
                    'avg_study_months' => match ($level) { 'D3' => 37 + mt_rand(0, 4), 'S2' => 26 + mt_rand(0, 4), default => 50 + mt_rand(0, 4) },
                    'avg_gpa' => round(3.2 + mt_rand(0, 30) / 100, 2),
                ]);
            }

            for ($y = 2015; $y <= $this->ts; $y++) {
                $intake = (int) round($active / $span * (0.85 + ($y - 2015) * 0.02));
                $age = $this->ts - $y;
                $onTime = $age >= $span - 1 ? (int) round($intake * (0.50 + mt_rand(0, 14) / 100)) : 0;
                $double = $age >= 2 * $span - 2 ? (int) round($intake * (0.76 + mt_rand(0, 8) / 100)) : ($age >= $span - 1 ? $onTime + (int) round($intake * 0.12) : 0);
                $dropped = (int) round($intake * ($age >= 2 ? 0.07 + mt_rand(0, 5) / 100 : 0.02));
                StudentCohort::create([
                    'study_program_id' => $program->id, 'intake_year' => $y, 'intake_count' => $intake,
                    'graduated_on_time' => $onTime, 'graduated_within_double' => $double,
                    'still_active' => max(0, $intake - $double - $dropped), 'dropped_out' => $dropped,
                ]);
            }
        }

        $programIds = collect($this->programs)->pluck('id')->all();
        $events = $p['events'];

        for ($y = 2021; $y <= $this->ts; $y++) {
            for ($i = 0; $i < $p['achievement_base'] + ($y - 2021) * 4; $i++) {
                $levelRoll = mt_rand(1, 100);
                Achievement::create([
                    'study_program_id' => $programIds[array_rand($programIds)], 'year' => $y,
                    'title' => $events[array_rand($events)].' '.$y,
                    'level' => $levelRoll <= 12 ? 'internasional' : ($levelRoll <= 55 ? 'nasional' : ($levelRoll <= 85 ? 'provinsi' : 'lokal')),
                    'rank' => (string) [1, 2, 3, 'lainnya'][mt_rand(0, 3)],
                    'kind' => mt_rand(1, 100) <= 65 ? 'akademik' : 'nonakademik',
                    'student_count' => mt_rand(1, 4),
                ]);
            }
        }

        $topics = $p['topics'];
        $prefix = ['Analisis', 'Model', 'Pengembangan', 'Evaluasi', 'Rancang bangun', 'Pengaruh', 'Strategi', 'Kajian'];
        $types = $p['publication_types'];

        for ($y = 2021; $y <= $this->ts; $y++) {
            $n = $p['publication_base'] + ($y - 2021) * $p['publication_growth'];

            for ($i = 0; $i < $n; $i++) {
                $type = $this->weighted($types);
                Publication::create([
                    'lecturer_id' => $permanentIds[array_rand($permanentIds)], 'year' => $y,
                    'title' => $prefix[array_rand($prefix)].' '.$topics[array_rand($topics)].' di '.$p['places'][array_rand($p['places'])],
                    'type' => $type, 'category' => mt_rand(1, 100) <= 78 ? 'penelitian' : 'pkm',
                    'venue' => $p['venues'][str_contains($type, 'intl') ? 0 : 1],
                    'citations' => str_contains($type, 'jurnal') ? mt_rand(0, ($this->ts - $y + 1) * 9) : 0,
                ]);
            }

            for ($i = 0; $i < $p['ip_base'] + ($y - 2021) * 2; $i++) {
                IpRight::create([
                    'lecturer_id' => $permanentIds[array_rand($permanentIds)], 'year' => $y,
                    'title' => ['Aplikasi', 'Modul', 'Sistem', 'Alat', 'Buku ajar'][mt_rand(0, 4)].' '.$topics[array_rand($topics)],
                    'ip_type' => ['hak_cipta', 'hak_cipta', 'hak_cipta', 'paten_sederhana', 'paten', 'merek', 'desain_industri'][mt_rand(0, 6)],
                    'number' => 'EC'.$y.sprintf('%07d', mt_rand(1, 9999999)), 'is_adopted' => mt_rand(1, 100) <= 30,
                    'adopter' => mt_rand(1, 100) <= 30 ? $p['ip_adopter'] : null,
                ]);
            }

            for ($i = 0; $i < $p['recognition_base'] + ($y - 2021) * 2; $i++) {
                Recognition::create([
                    'lecturer_id' => $permanentIds[array_rand($permanentIds)], 'year' => $y,
                    'title' => $p['recognitions'][array_rand($p['recognitions'])],
                    'kind' => array_keys(\App\Support\Labels::RECOGNITIONS)[mt_rand(0, 5)],
                    'level' => ['nasional', 'internasional', 'provinsi'][mt_rand(0, 2)],
                ]);
            }

            for ($i = 0; $i < $p['grant_base'] + ($y - 2021) * 3; $i++) {
                $source = $this->weighted(['internal' => 45, 'nasional' => 30, 'industri' => 15, 'internasional' => 5, 'mandiri' => 5]);
                ResearchGrant::create([
                    'year' => $y, 'category' => mt_rand(1, 100) <= 70 ? 'penelitian' : 'pkm', 'source' => $source,
                    'title' => ucfirst($prefix[array_rand($prefix)]).' '.$topics[array_rand($topics)],
                    'amount' => match ($source) { 'internal' => mt_rand(10, 30), 'nasional' => mt_rand(40, 180), 'industri' => mt_rand(30, 120), 'internasional' => mt_rand(150, 400), default => mt_rand(5, 15) } * 1_000_000 * $p['grant_scale'],
                ]);
            }
        }

        foreach ($p['auditors'] as $y => $kap) {
            FinancialAudit::create(['year' => $y, 'auditor' => $kap, 'opinion' => 'WTP', 'report_date' => ($y + 1).'-04-'.mt_rand(10, 28)]);
        }

        // Survei kepuasan 6 kelompok x 3 tahun: tiap responden menjawab 6 butir.
        $population = $p['survey_population'];
        $rate = ['mahasiswa' => 0.32, 'dosen' => 0.62, 'tendik' => 0.58, 'mitra' => 0.45, 'lulusan' => 0.28, 'pengguna' => 0.4];
        $labels = ['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'tendik' => 'Tenaga Kependidikan', 'mitra' => 'Mitra Kerja Sama', 'lulusan' => 'Lulusan', 'pengguna' => 'Pengguna Lulusan'];

        foreach ([2023, 2024, 2025] as $y) {
            foreach ($population as $group => $pop) {
                $resp = (int) round($pop * $rate[$group] * (0.9 + ($y - 2023) * 0.08));
                $answers = $resp * count(Survey::QUESTIONS);
                $good = 0.70 + ($y - 2023) * 0.045 + ($group === 'mahasiswa' ? -0.03 : 0.02);
                $sb = (int) round($answers * $good * 0.42);
                $b = (int) round($answers * $good) - $sb;
                $c = (int) round(($answers - $sb - $b) * 0.75);
                Survey::create([
                    'year' => $y, 'stakeholder' => $group, 'title' => "Survei Kepuasan {$labels[$group]} terhadap Tata Pamong {$y}",
                    'population' => $pop, 'respondents' => $resp, 'sb' => $sb, 'b' => $b, 'c' => $c, 'kb' => $answers - $sb - $b - $c,
                    'follow_up' => $y < 2025 ? 'Hasil dibahas pada RTM dan ditindaklanjuti dengan perbaikan layanan unit terkait.' : null,
                ]);
            }
        }

        Survey::create([
            'year' => 2026, 'stakeholder' => 'mahasiswa', 'title' => 'Survei Kepuasan Mahasiswa terhadap Tata Pamong 2026',
            'population' => $p['survey_open_population'], 'respondents' => 0, 'is_open' => true,
        ]);

        foreach ([2020, 2021, 2022, 2023, 2024] as $y) {
            $grad = $p['tracer_graduates'] + ($y - 2020) * $p['tracer_growth'];
            $resp = (int) round($grad * (0.52 + ($y - 2020) * 0.03));
            $employed = (int) round($resp * (0.68 + ($y - 2020) * 0.015));
            TracerStudy::create([
                'graduation_year' => $y, 'graduates' => $grad, 'respondents' => $resp, 'employed' => $employed,
                'further_study' => (int) round($resp * 0.06), 'entrepreneur' => (int) round($resp * (0.09 + ($y - 2020) * 0.01)),
                'within_one_year' => (int) round($employed * 0.82), 'avg_wait_months' => round(4.6 - ($y - 2020) * 0.3, 1),
                'global_work' => (int) round($resp * 0.015), 'certified' => (int) round($resp * (0.18 + ($y - 2020) * 0.03)),
            ]);
        }

        $aspectBase = ['etika' => 0.92, 'keahlian' => 0.84, 'bahasa_asing' => 0.61, 'ti' => 0.88, 'komunikasi' => 0.8, 'kerja_sama' => 0.89, 'pengembangan_diri' => 0.83];

        foreach ([2023, 2024, 2025] as $y) {
            foreach ($aspectBase as $aspect => $base) {
                $n = $p['employer_respondents'] + ($y - 2023) * 12;
                $good = min(0.97, $base + ($y - 2023) * 0.025);
                $sb = (int) round($n * $good * 0.45);
                $b = (int) round($n * $good) - $sb;
                $c = (int) round(($n - $sb - $b) * 0.8);
                EmployerSatisfaction::create([
                    'year' => $y, 'aspect' => $aspect, 'sb' => $sb, 'b' => $b, 'c' => $c, 'kb' => $n - $sb - $b - $c,
                    'follow_up' => $aspect === 'bahasa_asing' ? $p['employer_follow_up'] : null,
                ]);
            }
        }
    }

    // ------------------------------------------------------------------ proyek akreditasi

    /** Unit penanggung jawab per nomor indikator. */
    protected function unitForIndicator(int $n): string
    {
        return match (true) {
            $n <= 3 || $n === 34 => 'lpm',
            $n === 4 || in_array($n, [5, 10, 12, 13, 14, 15, 30], true) => 'akademik',
            in_array($n, [6, 7, 11], true) => 'sdm',
            $n === 8 => 'sarpras',
            in_array($n, [9, 26], true) => 'tik',
            $n >= 16 && $n <= 23 => 'lppm',
            in_array($n, [24, 25, 29], true) => 'sekretariat',
            in_array($n, [27, 33, 35], true) => 'keuangan',
            $n === 28 => 'satgas',
            in_array($n, [31, 32], true) => 'kemahasiswaan',
            default => 'perencanaan',
        };
    }

    /**
     * Skenario proyek akreditasi contoh.
     *
     * @return array<string, mixed>
     */
    protected function projectPlan(): array
    {
        return [
            'variant' => '3c',
            'name' => 'Akreditasi Institusi 2027',
            'track' => Track::Accredited,
            'target_date' => '2027-03-31',
            'notes' => 'Target pengajuan Maret 2027, lima bulan sebelum SK akreditasi institusi berakhir (15 Agustus 2027).',
            'locked_weeks' => 14,
            'eligibility_note' => 'Menunggu pembaruan data pelaporan PDDikti semester gasal 2025/2026.',
            // Sub-indikator yang dinilai tidak memenuhi (2A adalah syarat perlu) dan yang belum dinilai.
            'not_met' => ['2A', '12B', '15C', '19B', '23B', '27B', '30A', '38B'],
            // null: penilaian jalur Unggul diacak (sekitar 58% terpenuhi).
            'not_met_excellent' => null,
            'unassessed' => ['18C', '37A', '39A'],
            'internal_disagree' => ['13A'],
            'internal_note' => 'Bukti pelaksanaan PAK belum mencakup seluruh prodi; dinilai belum memenuhi.',
            'due_start' => '2026-11-15',
        ];
    }

    protected function seedProject(): AccreditationProject
    {
        $plan = $this->projectPlan();
        $variant = InstrumentVariant::where('code', $plan['variant'])->firstOrFail();
        $ts = AcademicPeriod::where('year_start', $this->ts)->first();

        $project = AccreditationProject::create([
            'instrument_variant_id' => $variant->id, 'ts_period_id' => $ts->id,
            'name' => $plan['name'], 'track' => $plan['track'], 'target_date' => $plan['target_date'],
            'state' => ProjectState::Drafting, 'track_locked_at' => now()->subWeeks($plan['locked_weeks']), 'track_locked_by' => $this->users['rektor']->id,
            'word_limit' => config('akredia.word_limit', 1500),
            'notes' => $plan['notes'],
        ]);
        $project->forceFill(['created_at' => now()->subWeeks(16)])->saveQuietly();

        app(ProjectWorkflow::class)->generate($project);

        foreach ($project->eligibilityItems as $i => $item) {
            $item->update(['is_met' => $i !== 3, 'note' => $i === 3 ? $plan['eligibility_note'] : 'Terverifikasi tim akreditasi.']);
        }

        $notMet = $plan['not_met'];
        $notMetExcellent = $plan['not_met_excellent'];
        $unassessed = $plan['unassessed'];
        $internalDisagree = $plan['internal_disagree'];
        $reviewers = [$this->users['reviewer'], $this->users['reviewer2']];
        $asesor = $this->users['asesor'];

        $items = WorkItem::with('subIndicator.indicator')->where('accreditation_project_id', $project->id)->get();

        foreach ($items as $idx => $item) {
            $code = $item->subIndicator->code;
            $n = $item->subIndicator->indicator->number;
            $unit = $this->unitForIndicator($n);
            $pic = $this->users['pic.'.$unit] ?? $this->users['pic.perencanaan'];
            $reviewer = $reviewers[$idx % 2];

            $item->update([
                'unit_id' => $this->units[$unit]->id, 'pic_id' => $pic->id, 'reviewer_id' => $reviewer->id,
                'second_pic_id' => $n >= 16 && $n <= 23 ? $this->users['pic.akademik']->id : null,
                'due_date' => Carbon::parse($plan['due_start'])->addDays(($n % 8) * 7),
                'current_condition' => 'Kondisi saat ini telah dipetakan dari dokumen SPMI dan data PDDikti TS-2 s.d. TS.',
            ]);

            if (in_array($code, $unassessed, true)) {
                continue;
            }

            $met = ! in_array($code, $notMet, true);
            Assessment::create([
                'work_item_id' => $item->id, 'assessor_id' => $pic->id, 'kind' => 'self', 'track' => 'accredited', 'is_met' => $met,
                'note' => $met ? 'Deskriptor terpenuhi dan didukung bukti.' : 'Belum seluruh deskriptor terpenuhi; RTL disusun.',
                'assessed_at' => now()->subDays(mt_rand(5, 60)),
            ]);
            Assessment::create([
                'work_item_id' => $item->id, 'assessor_id' => $pic->id, 'kind' => 'self', 'track' => 'excellent',
                'is_met' => $met && ($notMetExcellent === null ? mt_rand(1, 100) <= 58 : ! in_array($code, $notMetExcellent, true)), 'assessed_at' => now()->subDays(mt_rand(5, 60)),
            ]);

            if ($idx % 3 !== 2) {
                $agree = ! in_array($code, $internalDisagree, true);
                Assessment::create([
                    'work_item_id' => $item->id, 'assessor_id' => $asesor->id, 'kind' => 'internal', 'track' => 'accredited',
                    'is_met' => $agree ? $met : ! $met,
                    'note' => $agree ? 'Sesuai dengan penilaian mandiri.' : $plan['internal_note'],
                    'assessed_at' => now()->subDays(mt_rand(2, 30)),
                ]);
            }
        }

        return $project;
    }

    protected function seedEvidenceAndLed(AccreditationProject $project): void
    {
        $evidence = app(EvidenceService::class);
        $led = app(LedService::class);
        $validators = [$this->users['manajer'], $this->users['reviewer'], $this->users['reviewer2']];
        $publicWords = ['renstra', 'statuta', 'laporan auditor', 'sk akreditasi', 'kebijakan spmi', 'pedoman pmb', 'laporan keuangan'];
        $docsByIndicator = [];
        $counter = 0;

        $indicators = Indicator::where('instrument_version_id', $project->variant->instrument_version_id)->orderBy('number')->get();

        foreach ($indicators as $indicator) {
            $unit = $this->unitForIndicator($indicator->number);
            $uploader = $this->users['pic.'.$unit] ?? $this->users['pic.perencanaan'];
            $hints = collect(preg_split('/[;,]\s*/', (string) $indicator->evidence_hint))
                ->map(fn ($h) => trim($h))->filter(fn ($h) => mb_strlen($h) > 2)->take(4)->values();

            if ($hints->count() < 2) {
                $hints->push('Laporan pelaksanaan indikator '.$indicator->number);
            }

            foreach ($hints as $hint) {
                $counter++;
                $title = Str::ucfirst($hint).' '.$this->pt->name;
                $type = $this->docType($hint);
                $roll = mt_rand(1, 100);
                $status = match (true) {
                    $counter % 41 === 7 => DocumentStatus::Expired,
                    $counter % 29 === 5 => DocumentStatus::Rejected,
                    $roll <= $this->progressProfile()['doc_valid'] => DocumentStatus::Valid,
                    $roll <= 90 => DocumentStatus::Submitted,
                    default => DocumentStatus::Draft,
                };
                $docDate = now()->subDays(mt_rand(30, 900));
                $validUntil = match (true) {
                    $status === DocumentStatus::Expired => now()->subDays(mt_rand(5, 40)),
                    $counter % 17 === 3 => now()->addDays(mt_rand(10, 50)),
                    in_array($type, ['sk', 'sertifikat', 'kerjasama'], true) => now()->addMonths(mt_rand(8, 30)),
                    default => null,
                };
                $isPublic = $status === DocumentStatus::Valid && Str::contains(Str::lower($hint), $publicWords);

                $doc = Document::create([
                    'owner_unit_id' => $this->units[$unit]->id, 'title' => Str::limit($title, 250, ''), 'doc_type' => $type,
                    'number' => $type === 'sk' ? sprintf('%03d/SK/%s/%s', mt_rand(1, 400), $this->short, $docDate->format('Y')) : null,
                    'doc_date' => $docDate, 'approved_by' => in_array($type, ['kebijakan', 'peraturan', 'sk', 'pedoman', 'renstra'], true) ? $this->leaderTitle : null,
                    'valid_until' => $validUntil, 'confidentiality' => $isPublic ? 'publik' : ($type === 'data' ? 'rahasia' : 'internal'),
                    'is_public' => $isPublic, 'status' => DocumentStatus::Draft, 'uploaded_by' => $uploader->id,
                    'description' => 'Bukti untuk indikator '.$indicator->number.': '.Str::limit($indicator->statement, 160),
                ]);

                if ($counter % 9 === 4) {
                    $evidence->addVersion($doc, null, rtrim($this->pt->website, '/').'/dokumen/'.Str::slug(Str::limit($hint, 40, '')), $uploader);
                } else {
                    $path = "bukti/{$this->pt->id}/{$doc->id}/v1-".Str::slug(Str::limit($hint, 40, '')).'.pdf';
                    Storage::disk(EvidenceService::DISK)->put($path, $this->pdf($title, $doc->code, $indicator));
                    $evidence->addVersion($doc, $path, null, $uploader);
                }

                $validator = $validators[$counter % 3];
                $doc->forceFill(match ($status) {
                    DocumentStatus::Valid, DocumentStatus::Expired => ['status' => $status, 'validated_by' => $validator->id, 'validated_at' => now()->subDays(mt_rand(3, 80))],
                    DocumentStatus::Rejected => ['status' => $status, 'validated_by' => $validator->id, 'validated_at' => now()->subDays(mt_rand(1, 10)), 'rejection_reason' => 'Dokumen belum ditandatangani dan tidak mencantumkan nomor SK.'],
                    default => ['status' => $status],
                })->save();

                $docsByIndicator[$indicator->number][] = $doc;
            }
        }

        LinkCheck::query()->update(['is_ok' => true, 'last_status' => 200, 'last_checked_at' => now()->subHours(6)]);
        LinkCheck::query()->orderBy('id')->limit(2)->update(['is_ok' => false, 'last_status' => 404, 'failures' => 3, 'last_error' => 'HTTP 404 Not Found']);

        // Tautkan bukti ke kata kunci; status kertas kerja mengikuti status bukti.
        $items = WorkItem::with(['subIndicator.indicator', 'subIndicator.descriptors.keywords', 'assessments', 'ledSection', 'gapItems', 'project'])
            ->where('accreditation_project_id', $project->id)->get();
        $progress = $this->progressProfile();
        $ledPlan = $progress['led'];

        foreach ($items as $idx => $item) {
            $docs = $docsByIndicator[$item->subIndicator->indicator->number] ?? [];
            $self = $item->assessments->firstWhere(fn ($a) => $a->kind === 'self' && $a->track === $item->project->track);
            $met = $self?->is_met;
            $keywords = $item->subIndicator->descriptorFor($item->project->track)?->keywords ?? collect();
            $linked = [];

            foreach ($keywords->values() as $k => $kw) {
                $chance = $met === null ? 25 : ($met ? $progress['evidence_met'] : 45);

                if ($docs && mt_rand(1, 100) <= $chance) {
                    $doc = $docs[$k % count($docs)];
                    EvidenceLink::create(['document_id' => $doc->id, 'work_item_id' => $item->id, 'keyword_id' => $kw->id, 'relevance_note' => 'Mendukung: '.$kw->text]);
                    $linked[$kw->id] = $doc;
                }
            }

            foreach ($item->gapItems as $gap) {
                $doc = $linked[$gap->keyword_id] ?? null;
                $status = ! $doc ? 'belum' : ($doc->status === DocumentStatus::Valid ? 'terbukti' : 'sebagian');
                $gap->update([
                    'status' => $status,
                    'implementation_note' => $doc ? 'Telah dilaksanakan; bukti: '.$doc->code : null,
                    'rtl' => $status === 'terbukti' ? null : 'Lengkapi bukti pelaksanaan dan dokumen penetapan.',
                    'owner_id' => $status === 'terbukti' ? null : $item->pic_id,
                    'due_date' => $status === 'terbukti' ? null : now()->addDays(mt_rand(-10, 60)),
                ]);
            }

            if ($met === null) {
                continue;
            }

            // Narasi LED
            $plan = $this->weighted($ledPlan);
            $section = $item->ledSection;

            if ($plan === 'empty') {
                $item->update(['state' => count($linked) ? WorkItemState::Evidence : WorkItemState::GapAnalysis]);

                continue;
            }

            $pic = User::find($item->pic_id);
            $body = $this->narrative($item, $keywords, array_values($linked), $met, $idx === 5);
            $led->save($section, $body, $pic, 'Draf awal');

            if ($idx % 4 === 0) {
                $led->save($section->fresh(), $body."\n\nCatatan perbaikan: data diperbarui sesuai pelaporan PDDikti terakhir.", $pic, 'Pembaruan data');
            }

            $section->refresh();
            $reviewer = User::find($item->reviewer_id);
            $leader = $this->users['rektor'];

            match ($plan) {
                'ratified' => $section->update(['state' => LedState::Ratified, 'approved_by' => $reviewer->id, 'approved_at' => now()->subDays(mt_rand(5, 30)), 'ratified_by' => $leader->id, 'ratified_at' => now()->subDays(mt_rand(1, 5))]),
                'reviewer_approved' => $section->update(['state' => LedState::ReviewerApproved, 'approved_by' => $reviewer->id, 'approved_at' => now()->subDays(mt_rand(1, 20))]),
                'review' => $section->update(['state' => LedState::Review]),
                'revision' => $section->update(['state' => LedState::Revision, 'review_note' => 'Analisis akar masalah belum tajam dan belum memuat data tiga tahun.']),
                default => null,
            };

            $item->update(['state' => match ($plan) {
                'ratified', 'reviewer_approved' => WorkItemState::Approved,
                'review' => WorkItemState::Review,
                default => WorkItemState::Writing,
            }]);

            if (in_array($plan, ['review', 'revision'], true)) {
                LedComment::create(['led_section_id' => $section->id, 'user_id' => $reviewer->id, 'quote' => 'Evaluasi',
                    'body' => $plan === 'revision' ? 'Mohon tambahkan data tren TS-2 s.d. TS dan tautkan laporan AMI.' : 'Sudah cukup baik, periksa kembali konsistensi angka dengan LKPT.']);
            }
        }

        $this->seedHighlights($project);
    }

    /**
     * Kemajuan penyusunan: bobot status bagian LED, peluang kata kunci berbukti, dan porsi dokumen valid.
     *
     * @return array{led: array<string, int>, evidence_met: int, doc_valid: int}
     */
    protected function progressProfile(): array
    {
        return ['led' => ['ratified' => 16, 'reviewer_approved' => 28, 'review' => 20, 'revision' => 10, 'draft' => 20, 'empty' => 6], 'evidence_met' => 92, 'doc_valid' => 76];
    }

    /**
     * Struktur organisasi dan program studi dari daftar ringkas.
     *
     * @param  array{0: string, 1: string, 2: string}  $top  [kode, nama, pimpinan] unit pimpinan tertinggi
     * @param  array<string, array{string, string, string, string}>  $units  kunci => [kode, nama, jenis, kepala]
     * @param  array<string, array{string, string, string}>  $faculties  kunci => [kode, nama, kepala]
     * @param  list<array>  $programs  [kunci, fakultas, kode, nama, jenjang, dibuka, lembaga, peringkat, berlaku s.d., dosen tetap, mahasiswa aktif TS]
     * @param  array<string, array{string, int, int}>  $intl  kunci prodi => [lembaga internasional, mulai, berakhir]
     */
    protected function createOrganisation(array $top, array $units, array $faculties, array $programs, array $intl = [], string $periodStart = '09-01'): void
    {
        [$code, $name, $head] = $top;
        $rektorat = Unit::create(['code' => $code, 'name' => $name, 'type' => 'rektorat', 'head_name' => $head]);
        $this->units['rektorat'] = $rektorat;

        foreach ($units as $key => [$code, $name, $type, $head]) {
            $this->units[$key] = Unit::create(['parent_id' => $rektorat->id, 'code' => $code, 'name' => $name, 'type' => $type, 'head_name' => $head]);
        }

        foreach ($faculties as $key => [$code, $name, $head]) {
            $this->units[$key] = Unit::create(['parent_id' => $rektorat->id, 'code' => $code, 'name' => $name, 'type' => 'fakultas', 'head_name' => $head]);
        }

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

        foreach ([2023, 2024, 2025, 2026] as $y) {
            AcademicPeriod::create(['name' => $y.'/'.($y + 1), 'year_start' => $y, 'starts_at' => "{$y}-{$periodStart}", 'ends_at' => Carbon::parse("{$y}-{$periodStart}")->addYear()->subDay()]);
        }
    }

    /**
     * Bukti unggulan yang bersumber dari laman resmi institusi.
     *
     * @return list<array{int, string, string, ?string, string, ?string, string}> [indikator, jenis, judul, nomor, tanggal, berlaku s.d., URL]
     */
    protected function highlightEvidence(): array
    {
        return [];
    }

    protected function seedHighlights(AccreditationProject $project): void
    {
        $evidence = app(EvidenceService::class);
        $uploader = $this->users['pic.lpm'];

        foreach ($this->highlightEvidence() as [$no, $type, $title, $number, $date, $until, $url]) {
            $doc = Document::create([
                'owner_unit_id' => $this->units[$this->unitForIndicator($no)]->id, 'title' => $title, 'doc_type' => $type, 'number' => $number,
                'doc_date' => $date, 'valid_until' => $until, 'approved_by' => in_array($type, ['sk', 'peraturan', 'kebijakan'], true) ? 'Pejabat berwenang' : null,
                'confidentiality' => 'publik', 'is_public' => true, 'status' => DocumentStatus::Draft, 'uploaded_by' => $uploader->id,
                'description' => "Bukti unggulan dari laman resmi {$this->short} untuk indikator {$no}.",
            ]);
            $evidence->addVersion($doc, null, $url, $uploader);
            $doc->forceFill(['status' => DocumentStatus::Valid, 'validated_by' => $this->users['manajer']->id, 'validated_at' => now()->subDays(mt_rand(2, 20))])->save();

            $item = WorkItem::with('subIndicator.descriptors.keywords')->where('accreditation_project_id', $project->id)
                ->whereHas('subIndicator.indicator', fn ($query) => $query->where('number', $no))->first();
            $keyword = $item?->subIndicator->descriptorFor($project->track)?->keywords->first() ?? $item?->subIndicator->descriptorFor(Track::Accredited)?->keywords->first();

            if ($keyword) {
                EvidenceLink::firstOrCreate(['document_id' => $doc->id, 'work_item_id' => $item->id, 'keyword_id' => $keyword->id], ['relevance_note' => 'Bukti unggulan: '.$keyword->text]);
            }
        }
    }

    protected function seedMetrics(AccreditationProject $project): void
    {
        $engine = app(MetricEngine::class);
        $engine->compute($project, $this->users['admin']->id);

        $values = MetricValue::where('accreditation_project_id', $project->id)->get();

        foreach ($values as $i => $value) {
            if ($i % 7 !== 3) {
                $value->update(['status' => 'verified', 'verified_by' => $this->users['manajer']->id, 'verified_at' => now()->subDays(mt_rand(1, 14))]);
            }
        }

        $current = $engine->current($project);
        $vars = $engine->variables($this->pt->id, $this->ts);
        $rows = [
            ['RMDT', 'Rasio mahasiswa : dosen tetap', $current['RMDT']?->value, 1.012, 'akademik'],
            ['MHS_AKTIF', 'Mahasiswa aktif seluruh jenjang', $vars['mhs_aktif'], 1.118, 'akademik'],
            ['DOSEN_TETAP', 'Jumlah dosen tetap', $vars['dosen_tetap'], 0.985, 'sdm'],
            ['DTJA', 'Dosen tetap berjabatan akademik', $current['DTJA']?->value, 1.0, 'sdm'],
            ['LULUSAN_TS', 'Lulusan program utama TS', $vars['lulusan_ts'], 1.031, 'akademik'],
            ['PDTT', 'Proporsi dosen tidak tetap', $current['PDTT']?->value, 1.004, 'sdm'],
        ];

        foreach ($rows as $i => [$code, $label, $internal, $factor, $unit]) {
            $internal = round((float) $internal, 2);
            $r = Reconciliation::create([
                'accreditation_project_id' => $project->id, 'metric_code' => $code, 'scope_label' => $label,
                'internal_value' => $internal, 'pddikti_value' => round($internal / $factor, 2), 'tolerance_pct' => 2,
                'owner_unit_id' => $this->units[$unit]->id, 'owner_id' => $this->users['pic.'.$unit]->id,
                'note' => $this->reconciliationNotes()[$code] ?? null,
            ]);
            $r->update(['state' => match ($code) {
                'MHS_AKTIF' => 'investigation',
                'LULUSAN_TS' => 'open',
                'DOSEN_TETAP' => 'fixed',
                default => 'verified',
            }, 'verified_by' => in_array($code, ['RMDT', 'DTJA', 'PDTT'], true) ? $this->users['manajer']->id : null]);
        }
    }

    /** @return array<string, string> */
    protected function reconciliationNotes(): array
    {
        return [
            'MHS_AKTIF' => 'Selisih karena 612 mahasiswa cuti belum dilaporkan statusnya ke PDDikti.',
            'LULUSAN_TS' => 'Sebagian lulusan wisuda Agustus belum dilaporkan.',
        ];
    }

    /**
     * Keputusan, tugas, risiko, standar, AMI, dan RTM contoh.
     *
     * @return array<string, mixed>
     */
    protected function governanceProfile(): array
    {
        return [
            'decisions' => [
                ['Penetapan Tim Akreditasi Institusi 2027', '-110 days', 'rapat_pimpinan', 'Rektor menetapkan Tim Akreditasi Institusi yang diketuai Kepala LPM dengan anggota seluruh kepala biro dan lembaga.'],
                ['Penetapan jalur akreditasi: Terakreditasi', '-98 days', 'senat', 'Berdasarkan simulasi skor dan syarat perlu, institusi memilih jalur Terakreditasi dan menargetkan jalur Unggul pada siklus berikutnya.'],
                ['Percepatan kenaikan jabatan akademik dosen', '-80 days', 'rapat_pimpinan', 'Biro SDM menyiapkan klinik jabatan akademik; target 25 dosen naik jabatan sampai Juni 2027.'],
                ['Alokasi anggaran penyusunan LED dan LKPT', '-75 days', 'yayasan', 'Badan Penyelenggara menyetujui tambahan anggaran Rp 450 juta untuk kegiatan akreditasi.'],
                ['Pembentukan klinik penulisan LED', '-55 days', 'taskforce', 'Klinik penulisan diadakan setiap Kamis dengan pendampingan reviewer.'],
                ['Perbaikan pelaporan PDDikti semester gasal', '-30 days', 'rapat_pimpinan', 'UPT TIK dan Biro Akademik memutakhirkan status mahasiswa cuti dan lulusan sebelum 30 November.'],
                ['Tindak lanjut hasil AMI 2026', '-20 days', 'rtm', 'RTM menetapkan 9 tindakan koreksi dan 3 tindakan pencegahan.'],
                ['Jadwal simulasi asesmen lapangan', '-8 days', 'taskforce', 'Simulasi asesmen lapangan kedua dilaksanakan Januari 2027 dengan asesor eksternal.'],
            ],
            'decision_owners' => ['pic.sdm', 'pic.keuangan', 'pic.lpm', 'pic.tik', 'pic.lpm', 'pic.lpm'],
            'tasks' => [
                ['Lengkapi notulen RTM 2024 dan 2025', 'lpm'], ['Unggah SK tim penjaminan mutu fakultas', 'lpm'], ['Perbarui manual SPMI sesuai Permendikbudristek 53/2023', 'lpm'],
                ['Rekap data prestasi mahasiswa TS', 'kemahasiswaan'], ['Analisis tren mahasiswa baru 5 tahun', 'akademik'], ['Susun laporan evaluasi kurikulum OBE', 'akademik'],
                ['Validasi data dosen di PDDikti', 'sdm'], ['Perbarui katalog layanan TIK', 'tik'], ['Unggah laporan tracer study 2024', 'akademik'],
                ['Susun peta jalan penelitian 2025-2029', 'lppm'], ['Kumpulkan bukti adopsi HKI oleh mitra', 'lppm'], ['Lengkapi laporan PkM berbasis desa digital', 'lppm'],
                ['Perbarui SOP penanganan kekerasan', 'satgas'], ['Rekap penerima beasiswa KIP dan internal', 'kemahasiswaan'], ['Susun RKAT 2027', 'keuangan'],
                ['Laporan kinerja keuangan TS', 'keuangan'], ['Dokumentasikan benchmarking dengan universitas mitra', 'perencanaan'], ['Perbarui renstra 2025-2029', 'perencanaan'],
                ['Rekap kerja sama luar negeri aktif', 'perencanaan'], ['Lengkapi dokumen K3 dan aksesibilitas disabilitas', 'sarpras'], ['Susun laporan evaluasi pembelajaran semester genap', 'akademik'],
                ['Siapkan dokumen PAK pada RPS', 'akademik'], ['Rekap sertifikasi kompetensi lulusan', 'akademik'], ['Validasi data survei kepuasan tendik', 'lpm'],
                ['Perbaiki tautan bukti yang rusak', 'tik'], ['Lengkapi laporan monev hibah internal', 'lppm'], ['Susun laporan capaian diferensiasi misi', 'perencanaan'],
                ['Unggah laporan auditor independen 2025', 'keuangan'], ['Susun laporan layanan mahasiswa', 'kemahasiswaan'], ['Perbarui pedoman PMB inklusif', 'kemahasiswaan'],
            ],
            'risks' => [
                ['Syarat perlu 2A (implementasi PPEPP) belum terpenuhi', 4, 5, 'pic.lpm', 'Percepat RTM fakultas dan dokumentasi pengendalian standar.'],
                ['Selisih data mahasiswa aktif dengan PDDikti melebihi toleransi', 4, 4, 'pic.akademik', 'Pemutakhiran status cuti dan lulusan sebelum pelaporan semester.'],
                ['Prodi Teknik Elektro: akreditasi berakhir Februari 2027', 3, 5, 'pic.akademik', 'Pengajuan reakreditasi ke LAM Teknik paling lambat Oktober 2026.'],
                ['Keterlambatan penulisan LED kriteria 3', 3, 3, 'pic.sekretariat', 'Klinik penulisan LED dan pendampingan reviewer.'],
                ['Proporsi lektor dan lektor kepala di bawah ambang jalur Unggul', 3, 3, 'pic.sdm', 'Program percepatan jabatan akademik.'],
                ['Respons survei mahasiswa rendah', 2, 3, 'pic.lpm', 'Integrasi survei pada portal akademik sebelum pengisian KRS.'],
                ['Bukti kerja sama internasional kedaluwarsa', 2, 4, 'pic.perencanaan', 'Perpanjangan MoU dan dokumentasi implementasi.'],
                ['Kehilangan data bukti karena server tunggal', 1, 5, 'pic.tik', 'Cadangan harian otomatis ke penyimpanan terpisah.'],
                ['Pergantian personel kunci tim akreditasi', 2, 2, 'manajer', 'Dokumentasi peran dan pendamping cadangan setiap PIC.'],
                ['Kemampuan bahasa asing lulusan dinilai rendah oleh pengguna', 3, 2, 'pic.akademik', 'Program English Proficiency wajib.'],
            ],
            'standards' => [
                ['SM-01', 'Standar Kompetensi Lulusan', 'akademik', 'akademik'], ['SM-02', 'Standar Isi Pembelajaran', 'akademik', 'akademik'],
                ['SM-03', 'Standar Proses Pembelajaran', 'akademik', 'akademik'], ['SM-04', 'Standar Penilaian Pembelajaran', 'akademik', 'akademik'],
                ['SM-05', 'Standar Dosen dan Tenaga Kependidikan', 'akademik', 'sdm'], ['SM-06', 'Standar Sarana dan Prasarana', 'akademik', 'sarpras'],
                ['SM-07', 'Standar Pengelolaan Pembelajaran', 'akademik', 'akademik'], ['SM-08', 'Standar Pembiayaan Pembelajaran', 'akademik', 'keuangan'],
                ['SM-09', 'Standar Hasil Penelitian', 'akademik', 'lppm'], ['SM-10', 'Standar Proses Penelitian', 'akademik', 'lppm'],
                ['SM-11', 'Standar Hasil PkM', 'akademik', 'lppm'], ['SM-12', 'Standar Proses PkM', 'akademik', 'lppm'],
                ['SNA-01', 'Standar Tata Pamong', 'nonakademik', 'sekretariat'], ['SNA-02', 'Standar Kemahasiswaan', 'nonakademik', 'kemahasiswaan'],
                ['SNA-03', 'Standar Keuangan', 'nonakademik', 'keuangan'], ['SNA-04', 'Standar Kerja Sama', 'nonakademik', 'perencanaan'],
                ['SNA-05', 'Standar Sistem Informasi', 'nonakademik', 'tik'], ['SNA-06', 'Standar Pencegahan Kekerasan', 'nonakademik', 'satgas'],
                ['SNA-07', 'Standar Suasana Akademik', 'nonakademik', 'akademik'], ['SNA-08', 'Standar Diferensiasi Misi', 'nonakademik', 'perencanaan'],
            ],
            'standard_statement' => ' Universitas Nusantara Mandiri melampaui SN Dikti sesuai misi kewirausahaan berbasis teknologi.',
            'audits' => [
                ['name' => 'AMI Siklus 2025', 'cycle_year' => 2025, 'audit_date' => '2025-11-17', 'lead_auditor' => 'Prof. Dr. Made Suardana, M.Si.', 'state' => 'selesai', 'summary' => '24 unit diaudit; 3 temuan mayor, 11 minor, 6 observasi.'],
                ['name' => 'AMI Siklus 2026', 'cycle_year' => 2026, 'audit_date' => now()->subDays(25), 'lead_auditor' => 'Prof. Dr. Made Suardana, M.Si.', 'state' => 'pelaporan', 'summary' => 'Fokus pada kesiapan akreditasi institusi dan implementasi PPEPP di fakultas.'],
            ],
            // [AMI (indeks audits) atau null, kategori, uraian, indikator, unit, status, sumber]
            'findings' => [
                [0, 'mayor', 'RTM tingkat fakultas tidak dilaksanakan secara konsisten pada tiga fakultas.', 2, 'lpm', 'closed', 'ami'],
                [0, 'minor', 'Rencana pembelajaran semester belum memuat sisipan pendidikan antikorupsi pada 4 prodi.', 13, 'akademik', 'closed', 'ami'],
                [0, 'minor', 'Data beban kerja dosen belum diperbarui di sistem informasi kepegawaian.', 6, 'sdm', 'verification', 'ami'],
                [0, 'mayor', 'Dokumen peta jalan PkM belum ditetapkan dengan SK.', 20, 'lppm', 'closed', 'ami'],
                [1, 'mayor', 'Bukti pengendalian dan peningkatan standar akademik belum terdokumentasi lengkap.', 2, 'lpm', 'capa', 'ami'],
                [1, 'minor', 'Survei kepuasan mitra belum mencakup mitra luar negeri.', 34, 'lpm', 'capa', 'ami'],
                [1, 'minor', 'Pedoman PMB belum memuat ketentuan afirmasi penyandang disabilitas.', 31, 'kemahasiswaan', 'open', 'ami'],
                [1, 'observasi', 'Beberapa tautan bukti di laman universitas tidak dapat diakses.', 26, 'tik', 'open', 'ami'],
                [1, 'minor', 'Laporan monev hibah internal belum memuat analisis ketercapaian luaran.', 18, 'lppm', 'capa', 'ami'],
                [null, 'minor', 'Analisis akar masalah pada LED kriteria 2 masih deskriptif.', 12, 'akademik', 'open', 'simulasi'],
                [null, 'observasi', 'Narasumber mahasiswa belum memahami mekanisme penyampaian keluhan.', 32, 'kemahasiswaan', 'capa', 'simulasi'],
                [null, 'peluang', 'Kerja sama industri dapat diarahkan menjadi pusat unggulan kewirausahaan digital.', 39, 'perencanaan', 'open', 'simulasi'],
                [null, 'minor', 'Kepuasan mahasiswa terhadap kecepatan tanggapan keluhan di bawah 70%.', 34, 'kemahasiswaan', 'verification', 'survei'],
                [null, 'mayor', 'Bukti pelaksanaan PPEPP pada standar penelitian belum lengkap.', 17, 'lppm', 'capa', 'simulasi'],
            ],
            'reviews' => [
                ['meeting_date' => '2025-12-18', 'title' => 'RTM Universitas Semester Gasal 2025/2026', 'chair' => 'Rektor', 'minutes' => 'Membahas hasil AMI 2025, survei kepuasan, dan capaian IKU.', 'outcomes' => 'Penetapan 7 tindakan koreksi; revisi standar penelitian; percepatan jabatan akademik.'],
                ['meeting_date' => '2026-06-23', 'title' => 'RTM Universitas Semester Genap 2025/2026', 'chair' => 'Rektor', 'minutes' => 'Membahas kesiapan akreditasi institusi dan hasil simulasi skor.', 'outcomes' => 'Pemilihan jalur Terakreditasi; pembentukan klinik LED; perbaikan data PDDikti.'],
                ['meeting_date' => now()->subDays(20)->toDateString(), 'title' => 'RTM Khusus Kesiapan Akreditasi Institusi', 'chair' => 'Wakil Rektor Bidang Akademik', 'minutes' => 'Membahas hasil AMI 2026 dan syarat perlu 2A yang belum terpenuhi.', 'outcomes' => 'Rencana pemenuhan 2A dalam 60 hari; target AKI 85 pada Januari 2027.'],
            ],
        ];
    }

    protected function seedGovernance(AccreditationProject $project): void
    {
        $u = $this->users;

        // Keputusan pimpinan
        $g = $this->governanceProfile();
        $decisions = $g['decisions'];

        foreach ($decisions as $i => $row) {
            [$title, $when, $forum, $content] = $row;
            $decidedBy = $row[4] ?? match ($forum) { 'yayasan' => 'Ketua Yayasan', 'mwa' => 'Ketua Majelis Wali Amanat', default => $this->leaderTitle };
            $decision = Decision::create(['accreditation_project_id' => $project->id, 'title' => $title, 'decision_date' => now()->modify($when), 'forum' => $forum, 'decided_by' => $decidedBy, 'content' => $content]);

            if ($i >= 2) {
                $owner = $this->users[$g['decision_owners'][$i - 2]];
                $this->task($project, $title.': tindak lanjut', $owner, 'decision', $decision->id, $i * 9 - 60, $i < 6 ? 'done' : 'progress');
            }
        }

        // Tugas dari RTL kertas kerja dan tugas manual
        $titles = $g['tasks'];
        $items = WorkItem::where('accreditation_project_id', $project->id)->get();

        foreach ($titles as $i => [$title, $unitKey]) {
            $unitItems = $items->where('unit_id', $this->units[$unitKey]->id)->values();
            $item = $unitItems->isNotEmpty() ? $unitItems[$i % $unitItems->count()] : null;
            $offset = [-45, -38, -30, -25, -21, -18, -14, -10, -7, -5, -3, -2, 2, 4, 6, 8, 10, 12, 14, 18, 21, 25, 28, 32, 35, 40, 45, 50, 55, 60][$i % 30];
            $status = match (true) {
                $offset < 0 && $i % 5 === 3 => 'progress',
                $offset < 0 && $i % 7 === 6 => 'todo',
                $offset < 0 => 'done',
                $i % 3 === 0 => 'progress',
                default => 'todo',
            };
            $this->task($project, $title, $this->users['pic.'.$unitKey], $i % 2 ? 'rtl' : 'manual', null, $offset, $status, $item?->id, $this->units[$unitKey]->id);
        }

        // Risiko
        $risks = $g['risks'];

        foreach ($risks as $i => [$title, $p, $impact, $owner, $mitigation]) {
            Risk::create([
                'accreditation_project_id' => $project->id, 'title' => $title, 'probability' => $p, 'impact' => $impact,
                'owner_id' => $u[$owner]->id, 'mitigation' => $mitigation, 'target_date' => now()->addDays(20 + $i * 10),
                'state' => $i < 6 ? 'mitigating' : 'open', 'description' => 'Teridentifikasi pada rapat tim akreditasi.',
            ]);
        }

        // Standar mutu SPMI
        $standards = $g['standards'];
        $stages = array_keys(QualityStandard::STAGES);
        $standardModels = [];

        foreach ($standards as $i => [$code, $name, $category, $unit]) {
            $standardModels[] = QualityStandard::create([
                'owner_unit_id' => $this->units[$unit]->id, 'code' => $code, 'name' => $name, 'category' => $category,
                'statement' => $name.$g['standard_statement'],
                'ppepp_stage' => $stages[($i * 3) % 5], 'approved_at' => '2024-02-12', 'review_due' => now()->addMonths(($i % 6) * 2 - 1),
            ]);
        }

        // AMI dan temuan
        $audits = array_map(fn ($a) => InternalAudit::create(['unit_id' => $this->units['lpm']->id] + $a), $g['audits']);

        $indicatorIds = Indicator::pluck('id', 'number');
        $findings = $g['findings'];

        foreach ($findings as $i => [$audit, $category, $description, $indicatorNo, $unit, $state, $source]) {
            $finding = Finding::create([
                'internal_audit_id' => $audit === null ? null : $audits[$audit]->id, 'quality_standard_id' => $standardModels[$i % count($standardModels)]->id,
                'unit_id' => $this->units[$unit]->id, 'indicator_id' => $indicatorIds[$indicatorNo] ?? null,
                'category' => $category, 'description' => $description, 'state' => $state, 'source' => $source,
                'root_cause' => $state === 'open' ? null : 'Belum ada mekanisme pemantauan berkala dan penanggung jawab yang jelas.',
                'due_date' => now()->addDays(($i - 6) * 9),
            ]);

            if ($state === 'open') {
                continue;
            }

            $owner = $this->users['pic.'.$unit];
            $verifier = $this->users['asesor'];
            $done = in_array($state, ['closed', 'verification'], true);
            $due = $done ? now()->subDays(mt_rand(10, 55)) : now()->addDays(mt_rand(5, 45));
            Capa::create([
                'finding_id' => $finding->id, 'kind' => $i % 4 === 3 ? 'pencegahan' : 'korektif',
                'action' => 'Menetapkan penanggung jawab, menyusun jadwal pemantauan, dan melengkapi dokumentasi terkait: '.Str::lower(Str::limit($description, 80)),
                'owner_id' => $owner->id, 'due_date' => $due,
                'completed_at' => $done ? $due->copy()->subDays(mt_rand(0, 6)) : null,
                'verifier_id' => $state === 'closed' ? $verifier->id : null, 'verified_at' => $state === 'closed' ? now()->subDays(mt_rand(1, 9)) : null,
                'is_effective' => $state === 'closed' ? true : null,
                'verification_note' => $state === 'closed' ? 'Efektif: tidak ditemukan ketidaksesuaian berulang.' : null,
            ]);

            if (in_array($state, ['capa'], true)) {
                $this->task($project, 'CAPA '.$finding->number.': '.Str::limit($description, 60), $owner, 'finding', $finding->id, mt_rand(3, 40), 'progress');
            }
        }

        foreach ($g['reviews'] as $review) {
            ManagementReview::create($review);
        }
    }

    /**
     * Simulasi dan jadwal asesmen lapangan contoh.
     *
     * @return array<string, mixed>
     */
    protected function siteVisitProfile(): array
    {
        return [
            'mock' => [
                'title' => 'Simulasi Asesmen Lapangan I', 'starts_on' => now()->subDays(45), 'ends_on' => now()->subDays(44),
                'mode' => 'luring', 'assessors' => 'Prof. Dr. Made Suardana, M.Si.; Dr. Ir. Kusnadi, M.Eng. (asesor eksternal)', 'state' => 'selesai',
                'document_room' => 'Ruang Sidang Senat, Gedung Rektorat Lt. 3', 'minutes' => 'Simulasi berjalan baik; 6 rekomendasi perbaikan.',
            ],
            'recommendations' => [
                [2, 'Lengkapi bukti RTM tingkat fakultas untuk tiga tahun terakhir.', 'task'],
                [12, 'Tajamkan analisis akar masalah kelulusan tepat waktu per prodi.', 'task'],
                [15, 'Sajikan analisis tren mahasiswa baru dengan pembanding PT sejenis.', null],
                [28, 'Tunjukkan bukti sosialisasi Satgas PPK kepada mahasiswa baru.', 'capa'],
                [34, 'Perluas responden survei mitra hingga mitra luar negeri.', null],
                [39, 'Kumpulkan testimoni pihak eksternal atas keunggulan kewirausahaan digital.', null],
            ],
            'visit' => [
                'title' => 'Asesmen Lapangan BAN-PT', 'starts_on' => '2027-06-14', 'ends_on' => '2027-06-15',
                'mode' => 'luring', 'assessors' => 'Menunggu penugasan BAN-PT', 'rehearsal_on' => '2027-06-07', 'state' => 'rencana',
                'document_room' => 'Ruang Sidang Senat, Gedung Rektorat Lt. 3',
            ],
            'visit_date' => '2027-06-14',
            'sessions' => [
                ['08:00', '09:00', 'pimpinan', 'Pembukaan dan presentasi Rektor', 'Ruang Senat', 'Rektor'],
                ['09:00', '10:30', 'pimpinan', 'Wawancara pimpinan dan badan penyelenggara', 'Ruang Senat', 'Kepala LPM'],
                ['10:30', '11:30', 'dosen', 'Wawancara dosen', 'Ruang Rapat FEB', 'Dekan FEB'],
                ['11:30', '12:30', 'tendik', 'Wawancara tenaga kependidikan', 'Ruang Rapat BAA', 'Kepala BAA'],
                ['13:30', '14:30', 'mahasiswa', 'Wawancara mahasiswa', 'Aula Kemahasiswaan', 'Kepala BKM'],
                ['14:30', '15:30', 'lulusan', 'Wawancara lulusan dan pengguna lulusan', 'Ruang Alumni', 'Kepala BKM'],
                ['15:30', '17:00', 'observasi', 'Observasi sarana prasarana dan laboratorium', 'Kampus utama', 'Kepala Biro Umum'],
            ],
            'interviewees' => [
                ['pimpinan', 'Prof. Dr. Ir. Hendra Wiratmaja, M.T.', 'Rektor', true], ['pimpinan', 'H. Darmawan Kusuma, S.E., M.M.', 'Ketua Yayasan', true],
                ['pimpinan', 'Dr. Lestari Handayani, M.Pd.', 'Wakil Rektor I', true], ['dosen', 'Dr. Fajar Nugroho, M.Kom.', 'Dekan FIK', true],
                ['dosen', 'Sinta Rahayu, M.Pd.', 'Dosen PGSD', false], ['dosen', 'Bayu Setiawan, M.T.', 'Dosen Teknik Sipil', true],
                ['tendik', 'Nurul Hakim, S.E.', 'Staf Biro Keuangan', true], ['tendik', 'Galih Permana, A.Md.', 'Pranata laboratorium', false],
                ['mahasiswa', 'Rizki Maulana', 'Ketua BEM', true], ['mahasiswa', 'Putri Anggraini', 'Mahasiswa Informatika 2023', false],
                ['lulusan', 'Taufik Hidayat, S.Kom.', 'Alumni Informatika 2021', true], ['pengguna', 'Ir. Wawan Suryadi', 'HR Manager PT Mitra Teknologi', false],
            ],
            'questions' => [
                [1, 'pimpinan', 'Bagaimana kebijakan SPMI berbasis risiko ditetapkan dan disosialisasikan?', 'Kebijakan SPMI 2024, SK organisasi mutu, register risiko mutu.'],
                [2, 'pimpinan', 'Tunjukkan contoh satu siklus PPEPP yang menghasilkan peningkatan standar.', 'Standar penelitian 2023 → AMI → RTM → revisi target publikasi 2025.'],
                [6, 'pimpinan', 'Bagaimana rencana pemenuhan dosen pada prodi dengan rasio tinggi?', 'Renstra SDM, rekrutmen 2026, rasio per prodi.'],
                [10, 'dosen', 'Bagaimana hasil evaluasi pembelajaran digunakan untuk perbaikan?', 'Laporan EDOM, rapat prodi, perubahan RPS.'],
                [11, 'dosen', 'Apa dukungan institusi untuk kenaikan jabatan akademik?', 'Klinik jabatan akademik, insentif publikasi.'],
                [13, 'dosen', 'Bagaimana pendidikan antikorupsi disisipkan dalam pembelajaran?', 'RPS dengan sisipan PAK, modul PAK.'],
                [17, 'dosen', 'Bagaimana integritas penelitian dijaga?', 'Komisi etik, cek similaritas, pedoman penelitian.'],
                [26, 'tendik', 'Bagaimana integrasi data akademik dengan PDDikti?', 'Arsitektur SI, jadwal sinkronisasi, log pelaporan.'],
                [27, 'tendik', 'Bagaimana pendanaan bagi mahasiswa kurang beruntung dikelola?', 'Kebijakan beasiswa, data penerima KIP dan internal.'],
                [28, 'mahasiswa', 'Apakah Anda mengetahui cara melapor bila mengalami kekerasan?', 'Kanal Satgas, sosialisasi PKKMB, poster kampus.'],
                [30, 'mahasiswa', 'Kegiatan apa yang membangun suasana akademik?', 'Kuliah tamu, seminar, kompetisi.'],
                [32, 'mahasiswa', 'Layanan mahasiswa apa yang paling membantu?', 'Konseling, karier, kesehatan, beasiswa.'],
                [14, 'lulusan', 'Bagaimana kampus menyiapkan Anda memasuki dunia kerja?', 'Sertifikasi, magang, pusat karier.'],
                [14, 'pengguna', 'Bagaimana kinerja lulusan UNM di tempat kerja Anda?', 'Survei pengguna lulusan; masukan bahasa asing.'],
                [34, 'pimpinan', 'Bagaimana hasil survei kepuasan ditindaklanjuti?', 'Laporan survei, RTM, perbaikan layanan.'],
                [35, 'pimpinan', 'Bagaimana opini audit laporan keuangan tiga tahun terakhir?', 'WTP 2023-2025 dari KAP.'],
                [36, 'pimpinan', 'Apa diferensiasi misi UNM dan bagaimana diukur?', 'Misi kewirausahaan berbasis teknologi; IKU misi.'],
                [8, 'observasi', 'Tunjukkan fasilitas ramah disabilitas dan K3.', 'Ramp, toilet difabel, jalur evakuasi.'],
                [9, 'observasi', 'Tunjukkan layanan TIK dan sumber belajar daring.', 'LMS, e-journal, repositori.'],
                [39, 'pimpinan', 'Apa bukti pengakuan eksternal atas keunggulan UNM?', 'Penghargaan, pusat inkubator bisnis, testimoni mitra.'],
            ],
        ];
    }

    protected function seedSiteVisits(AccreditationProject $project): void
    {
        $v = $this->siteVisitProfile();
        $mock = SiteVisit::create(['accreditation_project_id' => $project->id] + $v['mock']);

        $recommendations = $v['recommendations'];
        $indicatorIds = Indicator::pluck('id', 'number');

        foreach ($recommendations as [$no, $text, $follow]) {
            VisitRecommendation::create(['site_visit_id' => $mock->id, 'indicator_id' => $indicatorIds[$no] ?? null, 'recommendation' => $text, 'follow_up' => $follow]);
        }

        $visit = SiteVisit::create(['accreditation_project_id' => $project->id] + $v['visit']);

        $sessions = $v['sessions'];

        foreach ($sessions as [$start, $end, $group, $topic, $room, $coord]) {
            VisitSession::create(['site_visit_id' => $visit->id, 'starts_at' => "{$v['visit_date']} {$start}", 'ends_at' => "{$v['visit_date']} {$end}", 'group' => $group, 'topic' => $topic, 'room' => $room, 'coordinator' => $coord]);
        }

        $interviewees = $v['interviewees'];

        foreach ($interviewees as [$group, $name, $position, $ok]) {
            VisitInterviewee::create(['site_visit_id' => $visit->id, 'group' => $group, 'name' => $name, 'position' => $position, 'phone' => '08'.mt_rand(1100000000, 1399999999), 'is_confirmed' => $ok]);
        }

        $questions = $v['questions'];

        foreach ($questions as $i => [$no, $group, $q, $points]) {
            VisitQuestion::create(['indicator_id' => $indicatorIds[$no] ?? null, 'group' => $group, 'question' => $q, 'answer_points' => $points, 'owner' => $this->units[$this->unitForIndicator($no)]->name, 'is_rehearsed' => $i % 3 !== 2]);
        }
    }

    /** Hitung kesiapan hari ini lalu isi riwayat mingguan 16 minggu agar grafik tren terisi. */
    protected function seedSnapshots(AccreditationProject $project): void
    {
        $result = app(ReadinessCalculator::class)->refresh($project);
        $components = $result['components'];

        for ($w = 16; $w >= 1; $w--) {
            $f = 1 - $w / 16 * 0.62 + (mt_rand(-15, 15) / 1000);
            $comp = [];

            foreach ($components as $key => $value) {
                $start = ['fulfilment' => 0.55, 'evidence' => 0.25, 'data' => 0.45, 'led' => 0.1, 'execution' => 0.8][$key];
                $comp[$key] = round(min(100, $value * ($start + (1 - $start) * max(0, $f))), 2);
            }

            $weights = $project->weights();
            $aki = array_sum(array_map(fn ($k) => $comp[$k] * $weights[$k], array_keys($comp))) / array_sum($weights);
            ReadinessSnapshot::create([
                'accreditation_project_id' => $project->id, 'taken_on' => today()->subWeeks($w),
                'aki' => round($aki, 2), 'components' => $comp,
                'projection_accredited' => round($result['projection_accredited'] * (0.55 + 0.45 * max(0, $f)), 2),
                'projection_excellent' => round($result['projection_excellent'] * (0.5 + 0.5 * max(0, $f)), 2),
                'prerequisites_failed' => $w > 10 ? 4 : ($w > 5 ? 2 : 1),
            ]);
        }
    }

    protected function seedAuditTrail(): void
    {
        $project = AccreditationProject::where('institution_id', $this->pt->id)->value('name');
        $samples = [
            ['manajer', 'diubah', $project, ['state' => ['dari' => 'preparation', 'ke' => 'drafting']]],
            ['rektor', 'diubah', $project, ['track_locked_at' => ['dari' => null, 'ke' => now()->subWeeks(14)->format('Y-m-d H:i')]]],
            ['reviewer', 'diubah', 'LED 1A', ['state' => ['dari' => 'review', 'ke' => 'reviewer_approved']]],
            ['pic.lpm', 'dibuat', 'BKT Kebijakan SPMI', null],
            ['manajer', 'diubah', 'Bukti laporan AMI', ['status' => ['dari' => 'submitted', 'ke' => 'valid']]],
            ['asesor', 'dibuat', 'Asesor internal 13A', null],
            ['pic.sdm', 'diubah', 'Dosen tetap', ['academic_rank' => ['dari' => 'l', 'ke' => 'lk']]],
            ['admin', 'masuk', null, null],
            ['manajer', 'diubah', 'DTJA 2025', ['status' => ['dari' => 'draft', 'ke' => 'verified']]],
            ['pic.akademik', 'diubah', 'Rekonsiliasi MHS_AKTIF', ['state' => ['dari' => 'open', 'ke' => 'investigation']]],
            ['reviewer2', 'diubah', 'LED 12A', ['state' => ['dari' => 'review', 'ke' => 'revision']]],
            ['rektor', 'diubah', 'LED 4A', ['state' => ['dari' => 'reviewer_approved', 'ke' => 'ratified']]],
        ];

        foreach (range(1, 4) as $round) {
            foreach ($samples as $i => [$user, $event, $label, $changes]) {
                AuditLog::withoutGlobalScopes()->create([
                    'institution_id' => $this->pt->id, 'user_id' => $this->users[$user]->id, 'event' => $event, 'label' => $label,
                    'changes' => $changes, 'ip_address' => '10.20.'.mt_rand(1, 30).'.'.mt_rand(2, 250),
                    'created_at' => now()->subDays(($round - 1) * 7 + $i % 7)->subMinutes(mt_rand(0, 600)),
                ]);
            }
        }
    }

    // ------------------------------------------------------------------ pembantu

    protected function task(AccreditationProject $project, string $title, User $assignee, string $source, ?int $sourceId, int $dueOffset, string $status, ?int $workItemId = null, ?int $unitId = null): Task
    {
        $due = now()->addDays($dueOffset)->startOfDay();
        $task = Task::create([
            'accreditation_project_id' => $project->id, 'work_item_id' => $workItemId, 'title' => $title,
            'description' => 'Tindak lanjut persiapan akreditasi institusi.', 'assignee_id' => $assignee->id,
            'unit_id' => $unitId ?? $assignee->activeMembership($this->pt)?->unit_id, 'due_date' => $due,
            'priority' => $dueOffset < 7 ? ($dueOffset < 0 && $status !== 'done' ? 'mendesak' : 'tinggi') : ['sedang', 'rendah', 'tinggi'][abs($dueOffset) % 3],
            'status' => $status, 'source' => $source, 'source_id' => $sourceId,
        ]);

        if ($status === 'done') {
            $late = mt_rand(1, 100) <= 12;
            $task->forceFill(['completed_at' => $due->copy()->addDays($late ? mt_rand(1, 6) : -mt_rand(0, 6))->setTime(15, 0)])->saveQuietly();
        }

        return $task;
    }

    protected function narrative(WorkItem $item, $keywords, array $docs, bool $met, bool $long): string
    {
        $cite = fn (int $i) => isset($docs[$i]) ? ' [B:'.$docs[$i]->code.']' : '';
        $kw = $keywords->pluck('text')->map(fn ($t) => Str::lower($t))->values();
        $metric = $item->subIndicator->metric_code ? ' Capaian terkini adalah {{'.$item->subIndicator->metric_code.'}}.' : '';
        $statement = Str::lcfirst(rtrim($item->subIndicator->statement, '.'));

        $paragraphs = [
            "Penetapan\n{$this->pt->name} menetapkan kebijakan agar {$statement}. ".($kw[0] ?? 'Standar').' dituangkan dalam dokumen resmi yang disahkan '.$this->leaderTitle.$cite(0).'.',
            "Pelaksanaan\nSelama TS-2 sampai TS, pelaksanaan dipantau oleh unit penanggung jawab. ".($kw[1] ?? 'Pelaksanaan standar').' dilakukan secara konsisten di seluruh fakultas'.$cite(1).'.'.$metric,
            "Evaluasi\nEvaluasi dilakukan melalui Audit Mutu Internal tahunan, survei kepuasan, dan monitoring capaian indikator kinerja. ".($kw[2] ?? 'Hasil evaluasi').' dianalisis dan dilaporkan pada rapat tinjauan manajemen.',
            "Pengendalian\nTemuan evaluasi ditindaklanjuti dengan tindakan koreksi dan pencegahan (CAPA) yang dipantau LPM hingga terverifikasi efektif.",
            "Peningkatan\n".($met ? 'Hasil pengendalian mendorong peningkatan standar pada siklus berikutnya, ditunjukkan oleh tren capaian yang membaik dalam tiga tahun terakhir.' : 'Peningkatan standar belum terdokumentasi lengkap dan menjadi prioritas tindak lanjut.'),
            "Akar masalah, faktor pendukung, dan faktor penghambat\nFaktor pendukung utama adalah komitmen pimpinan dan sistem informasi terpadu. Faktor penghambat adalah beban administrasi unit dan keterlambatan pelaporan data.",
            "Tindak lanjut\n".($met ? 'Mempertahankan capaian dan memperluas praktik baik ke seluruh unit.' : 'Menyusun rencana tindak lanjut dengan penanggung jawab dan tenggat yang jelas sebelum pengajuan.'),
        ];

        $body = implode("\n\n", $paragraphs);

        if ($long) {
            // Satu contoh narasi melewati batas kata agar peringatan terlihat.
            $body .= "\n\nLampiran uraian\n".str_repeat('Uraian rinci kegiatan pelaksanaan standar di setiap fakultas beserta capaian dan hambatannya disampaikan pada bagian ini secara lengkap. ', 85);
        }

        return $body;
    }

    protected function docType(string $hint): string
    {
        $h = Str::lower($hint);

        return match (true) {
            str_contains($h, 'kebijakan') => 'kebijakan',
            str_contains($h, 'statuta') || str_contains($h, 'peraturan') => 'peraturan',
            str_starts_with($h, 'sk') || str_contains($h, ' sk') => 'sk',
            str_contains($h, 'pedoman') || str_contains($h, 'manual') || str_contains($h, 'sop') => 'pedoman',
            str_contains($h, 'renstra') || str_contains($h, 'rencana') || str_contains($h, 'peta jalan') => 'renstra',
            str_contains($h, 'notulen') || str_contains($h, 'rtm') => 'notulen',
            str_contains($h, 'data') || str_contains($h, 'tabel') => 'data',
            str_contains($h, 'sertifika') => 'sertifikat',
            str_contains($h, 'kerja sama') || str_contains($h, 'mou') => 'kerjasama',
            str_contains($h, 'laporan') || str_contains($h, 'hasil') => 'laporan',
            default => 'lainnya',
        };
    }

    /** @param array<string, int> $weights */
    protected function weighted(array $weights): string
    {
        $roll = mt_rand(1, array_sum($weights));

        foreach ($weights as $key => $w) {
            if (($roll -= $w) <= 0) {
                return (string) $key;
            }
        }

        return (string) array_key_first($weights);
    }

    /** PDF satu halaman sederhana sebagai berkas bukti contoh. */
    protected function pdf(string $title, string $code, Indicator $indicator): string
    {
        $esc = fn (string $s) => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], Str::ascii($s));
        $lines = [
            [18, 760, $this->pt->name],
            [14, 730, Str::limit($title, 80)],
            [10, 705, 'Nomor bukti: '.$code],
            [10, 688, 'Indikator IAPT 4.1 nomor '.$indicator->number],
            [10, 660, Str::limit($indicator->statement, 95)],
            [9, 600, 'DOKUMEN CONTOH - data ilustrasi untuk demonstrasi aplikasi Akredia.'],
        ];
        $stream = "BT\n";

        foreach ($lines as [$size, $y, $text]) {
            $stream .= "/F1 {$size} Tf 1 0 0 1 60 {$y} Tm (".$esc($text).") Tj\n";
        }

        $stream .= "ET\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}endstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$obj}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref
0 '.(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
