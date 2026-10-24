<?php

namespace Database\Seeders;

use App\Enums\Track;

/**
 * Data demo Universitas Bina Nusantara (BINUS University, PTS akademik) yang menyiapkan reakreditasi
 * institusi jalur Unggul dengan IAPT 4.1.
 *
 * Diambil dari binus.ac.id (Oktober 2026): identitas, badan penyelenggara, Rektor, visi, delapan kampus,
 * sekolah dan fakultas, akreditasi institusi Unggul (SK 289/SK/BAN-PT/AK-ISK/PT/VI/2022), akreditasi
 * internasional AACSB dan ABET, serta pemeringkatan QS dan THE. Selebihnya (nama Wakil Rektor dan staf,
 * dosen, angka mahasiswa, kode dan tanggal akreditasi prodi, publikasi, survei, temuan, tugas, risiko,
 * narasi) adalah data ilustrasi untuk demonstrasi, bukan data resmi BINUS.
 */
class BinusDemoSeeder extends DemoSeeder
{
    public const SLUG = 'binus';

    protected string $emailDomain = 'binus.akredia.test';

    protected string $short = 'BINUS';

    protected bool $guestAccess = true;

    protected function institutionAttributes(): array
    {
        return [
            'name' => 'Universitas Bina Nusantara',
            'slug' => static::SLUG,
            'code' => '031037',
            'type' => 'pts_akademik',
            'legal_entity' => 'Yayasan Bina Nusantara',
            'sk_number' => 'Keputusan Mendikbud Tahun 1996',
            'sk_date' => '1996-08-08',
            'first_intake_year' => 1996,
            'city' => 'Jakarta Barat',
            'address' => 'Jl. K.H. Syahdan No. 9, Kemanggisan, Palmerah, Jakarta Barat 11480',
            'website' => 'https://binus.ac.id',
            'vision' => 'A world-class university contributing in building and serving the nation.',
            'mission_focus' => 'Universitas berbasis teknologi digital dan kewirausahaan dengan delapan kampus (Kemanggisan, Alam Sutera, Senayan, Bekasi, Bandung, Malang, Semarang, Medan) dan BINUS Online; fokus pada talenta digital, riset terapan, dan kemitraan industri global.',
            'last_accreditation_rank' => 'Unggul',
            'last_accreditation_sk' => '289/SK/BAN-PT/AK-ISK/PT/VI/2022',
            'last_accreditation_until' => '2027-06-14',
            'is_demo' => true,
        ];
    }

    // ------------------------------------------------------------------ organisasi

    protected function seedOrganisation(): void
    {
        $units = [
            'lpm' => ['QA', 'Quality Assurance (Lembaga Penjaminan Mutu)', 'lembaga', 'Dr. Lidya Kartikasari, S.Kom., M.M.'],
            'lppm' => ['RTTO', 'Research and Technology Transfer Office', 'lembaga', 'Prof. Dr. Ir. Hendro Gunawan, M.Sc.'],
            'spi' => ['IA', 'Internal Audit', 'lembaga', 'Hartono Wijaya, S.E., M.Ak., CIA'],
            'akademik' => ['AAD', 'Academic Affairs Directorate', 'biro', 'Dr. Meiliana Santoso, S.Kom., M.T.I.'],
            'kemahasiswaan' => ['SDC', 'Student Advisory and Development Center', 'biro', 'Dr. Yohanes Kristanto, M.Psi.'],
            'keuangan' => ['FIN', 'Finance and Accounting Directorate', 'biro', 'Dr. Lina Gozali, S.E., M.Ak., CA'],
            'sdm' => ['HCM', 'Human Capital Management', 'biro', 'Dr. Arief Setiawan, M.M.'],
            'sarpras' => ['FM', 'Facility Management', 'biro', 'Ir. Bambang Hartanto, M.T.'],
            'perencanaan' => ['SPD', 'Strategic Planning and Development Office', 'biro', 'Dr. Wenny Hartono, S.Kom., M.B.A.'],
            'kerjasama' => ['IO', 'International Office and Partnership', 'biro', 'Dr. Kevin Tanuwijaya, M.B.A.'],
            'sekretariat' => ['RO', 'Rector Office (Sekretariat Universitas)', 'biro', 'Dra. Imelda Susanti, M.Hum.'],
            'tik' => ['ITD', 'Information Technology Directorate', 'upt', 'Andreas Wibowo, S.Kom., M.T.I.'],
            'perpustakaan' => ['LIB', 'BINUS Library and Knowledge Center', 'upt', 'Dewi Kurniasari, S.Hum., M.Si.'],
            'satgas' => ['SATGAS', 'Satgas Pencegahan dan Penanganan Kekerasan', 'satgas', 'Dr. Ratih Puspitasari, M.Psi., Psikolog'],
        ];

        $faculties = [
            'socs' => ['SOCS', 'School of Computer Science', 'Dr. Fredy Purnomo, S.Kom., M.Kom.'],
            'sois' => ['SOIS', 'School of Information Systems', 'Dr. Rina Kurniawati, S.Kom., M.T.I.'],
            'bbs' => ['BBS', 'BINUS Business School', 'Prof. Dr. Hendra Gunadi, M.B.A.'],
            'soa' => ['SOA', 'School of Accounting', 'Dr. Sri Handayani, S.E., M.Ak., CA'],
            'fhum' => ['FHUM', 'Faculty of Humanities', 'Dr. Lucia Hartanti, M.Hum.'],
            'feng' => ['FENG', 'Faculty of Engineering', 'Dr. Ir. Johan Setiadi, M.T., IPU'],
            'sod' => ['SOD', 'School of Design', 'Dr. Andreas Pramudya, S.Sn., M.Ds.'],
            'fdch' => ['FDCHT', 'Faculty of Digital Communication and Hotel & Tourism', 'Dr. Maria Lestari, M.Si.'],
            'bgp' => ['BGP', 'BINUS Graduate Program', 'Prof. Dr. Ir. Michael Hartono, M.Sc.'],
            'online' => ['BOL', 'BINUS Online (Pendidikan Jarak Jauh)', 'Dr. Yanti Kosasih, S.Kom., M.M.'],
        ];

        // Akreditasi internasional yang disebut laman BINUS: ABET (teknik) dan AACSB (BINUS Business School, reakreditasi 2024).
        $intl = [
            'tind' => ['ABET', 2022, 2028], 'tsip' => ['ABET', 2022, 2028], 'tk' => ['ABET', 2022, 2028],
            'mnj' => ['AACSB', 2024, 2029], 'kwu' => ['AACSB', 2024, 2029], 'bisdig' => ['AACSB', 2024, 2029],
            's2mnj' => ['AACSB', 2024, 2029], 's3mnj' => ['AACSB', 2024, 2029],
        ];

        // [kunci, fakultas, kode prodi, nama, jenjang, dibuka, lembaga, peringkat, berlaku s.d., dosen tetap, mahasiswa aktif TS]
        $programs = [
            ['ti', 'socs', '55201', 'Teknik Informatika', 'S1', 1996, 'lam_infokom', 'unggul', '2029-02-10', 190, 5200],
            ['ds', 'socs', '49202', 'Sains Data', 'S1', 2020, 'lam_infokom', 'baik_sekali', '2028-03-11', 30, 1100],
            ['stat', 'socs', '49201', 'Statistika', 'S1', 2004, 'lam_sama', 'unggul', '2028-07-19', 22, 640],
            ['si', 'sois', '57201', 'Sistem Informasi', 'S1', 1996, 'lam_infokom', 'unggul', '2028-11-20', 130, 3800],
            ['sia', 'sois', '57202', 'Sistem Informasi Akuntansi', 'S1', 2008, 'lam_infokom', 'unggul', '2028-04-09', 26, 760],
            ['mnj', 'bbs', '61201', 'Manajemen', 'S1', 1996, 'lam_emba', 'unggul', '2029-04-01', 170, 5200],
            ['kwu', 'bbs', '61208', 'Kewirausahaan (Business Creation)', 'S1', 2010, 'lam_emba', 'unggul', '2028-09-12', 52, 1500],
            ['bisdig', 'bbs', '61209', 'Bisnis Digital', 'S1', 2021, 'lam_emba', 'baik_sekali', '2027-05-20', 36, 1200],
            ['akt', 'soa', '62201', 'Akuntansi', 'S1', 1996, 'lam_emba', 'unggul', '2029-01-15', 110, 3300],
            ['keu', 'soa', '61206', 'Keuangan', 'S1', 2012, 'lam_emba', 'unggul', '2028-06-30', 40, 1200],
            ['sasing', 'fhum', '79202', 'Sastra Inggris', 'S1', 1996, 'banpt', 'unggul', '2028-03-20', 30, 860],
            ['sasjep', 'fhum', '79205', 'Sastra Jepang', 'S1', 1996, 'banpt', 'unggul', '2028-05-14', 24, 680],
            ['sascin', 'fhum', '79206', 'Sastra Cina', 'S1', 1997, 'banpt', 'baik_sekali', '2027-10-22', 22, 600],
            ['psi', 'fhum', '73201', 'Psikologi', 'S1', 2006, 'banpt', 'unggul', '2028-12-01', 44, 1400],
            ['hi', 'fhum', '67201', 'Hubungan Internasional', 'S1', 2005, 'banpt', 'unggul', '2028-08-08', 32, 1000],
            ['hukum', 'fhum', '74201', 'Hukum Bisnis', 'S1', 2009, 'banpt', 'unggul', '2027-12-15', 26, 760],
            ['pgsd', 'fhum', '86206', 'Pendidikan Guru Sekolah Dasar', 'S1', 2013, 'lam_dik', 'baik_sekali', '2027-07-01', 18, 400],
            ['tk', 'feng', '56201', 'Teknik Komputer', 'S1', 1998, 'lam_infokom', 'unggul', '2028-10-05', 42, 1250],
            ['tind', 'feng', '26201', 'Teknik Industri', 'S1', 1996, 'lam_teknik', 'unggul', '2029-03-18', 44, 1350],
            ['tsip', 'feng', '22201', 'Teknik Sipil', 'S1', 1996, 'lam_teknik', 'unggul', '2028-07-25', 34, 900],
            ['ars', 'feng', '23201', 'Arsitektur', 'S1', 1996, 'lam_teknik', 'unggul', '2028-04-17', 32, 860],
            ['tp', 'feng', '41221', 'Teknologi Pangan', 'S1', 2012, 'lam_teknik', 'baik_sekali', '2027-09-30', 22, 500],
            ['dkv', 'sod', '90241', 'Desain Komunikasi Visual', 'S1', 1996, 'banpt', 'unggul', '2028-11-11', 86, 2600],
            ['dint', 'sod', '90231', 'Desain Interior', 'S1', 1996, 'banpt', 'unggul', '2028-02-28', 30, 760],
            ['film', 'sod', '91241', 'Film', 'S1', 2012, 'banpt', 'baik_sekali', '2027-08-15', 22, 620],
            ['kom', 'fdch', '70201', 'Ilmu Komunikasi', 'S1', 2004, 'banpt', 'unggul', '2029-01-21', 58, 1900],
            ['hotel', 'fdch', '93203', 'Manajemen Perhotelan', 'S1', 2002, 'banpt', 'unggul', '2028-06-10', 30, 860],
            ['par', 'fdch', '93201', 'Pariwisata', 'S1', 2015, 'banpt', 'baik_sekali', '2027-11-02', 20, 480],
            ['s2mnj', 'bgp', '61101', 'Manajemen', 'S2', 2000, 'lam_emba', 'unggul', '2028-12-20', 40, 1050],
            ['s2ti', 'bgp', '55101', 'Teknik Informatika', 'S2', 2003, 'lam_infokom', 'unggul', '2028-09-28', 26, 580],
            ['s2si', 'bgp', '57101', 'Sistem Informasi', 'S2', 2005, 'lam_infokom', 'unggul', '2028-05-30', 20, 400],
            ['s3cs', 'bgp', '55001', 'Ilmu Komputer', 'S3', 2015, 'lam_infokom', 'unggul', '2029-02-14', 14, 150],
            ['s3mnj', 'bgp', '61001', 'Ilmu Manajemen', 'S3', 2014, 'lam_emba', 'unggul', '2028-10-10', 16, 230],
            ['ol_si', 'online', '57203', 'Sistem Informasi (PJJ)', 'S1', 2011, 'lam_infokom', 'unggul', '2028-03-01', 30, 2000],
            ['ol_ti', 'online', '55203', 'Teknik Informatika (PJJ)', 'S1', 2014, 'lam_infokom', 'baik_sekali', '2027-12-01', 26, 1600],
            ['ol_mnj', 'online', '61213', 'Manajemen (PJJ)', 'S1', 2011, 'lam_emba', 'unggul', '2028-08-01', 34, 2400],
            ['ol_akt', 'online', '62203', 'Akuntansi (PJJ)', 'S1', 2014, 'lam_emba', 'baik_sekali', '2027-06-20', 20, 1200],
        ];

        $this->createOrganisation(['REK', 'Rektorat', 'Dr. Nelly, S.Kom., M.M.'], $units, $faculties, $programs, $intl);
    }

    protected function people(): array
    {
        return [
            ['admin', 'admin.pt', 'Andreas Wibowo, S.Kom., M.T.I.', 'admin', 'tik', 'Direktur Teknologi Informasi'],
            ['rektor', 'rektor', 'Dr. Nelly, S.Kom., M.M.', 'pimpinan', 'rektorat', 'Rektor'],
            ['wr1', 'wr1', 'Prof. Dr. Ir. Bambang Widjaja, M.Sc.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Akademik'],
            ['wr2', 'wr2', 'Dr. Stephanie Gunawan, S.E., M.M.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Keuangan dan Operasional'],
            ['wr3', 'wr3', 'Prof. Dr. Ir. Hendro Gunawan, M.Sc.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Riset dan Transfer Teknologi'],
            ['wr4', 'wr4', 'Dr. Richard Halim, M.B.A.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Kemahasiswaan, Alumni, dan Kemitraan Global'],
            ['manajer', 'manajer', 'Dr. Lidya Kartikasari, S.Kom., M.M.', 'manajer', 'lpm', 'Direktur Quality Assurance / Ketua Tim Reakreditasi Institusi'],
            ['reviewer', 'reviewer', 'Prof. Dr. Theresia Wulandari, M.Si.', 'reviewer', 'lpm', 'Reviewer LED'],
            ['reviewer2', 'reviewer2', 'Dr. Ir. Johan Setiadi, M.T., IPU', 'reviewer', 'feng', 'Reviewer LED'],
            ['asesor', 'asesor', 'Prof. Dr. Antonius Prasetya, M.Kom.', 'asesor', 'lpm', 'Asesor internal (asesor BAN-PT)'],
            ['pic.lpm', 'pic.lpm', 'Natalia Kusuma, S.Kom., M.M.', 'pic', 'lpm', 'Manajer Akreditasi dan Sertifikasi'],
            ['pic.akademik', 'pic.akademik', 'Dr. Meiliana Santoso, S.Kom., M.T.I.', 'pic', 'akademik', 'Direktur Academic Affairs'],
            ['pic.sdm', 'pic.sdm', 'Dr. Arief Setiawan, M.M.', 'pic', 'sdm', 'Direktur Human Capital Management'],
            ['pic.keuangan', 'pic.keuangan', 'Dr. Lina Gozali, S.E., M.Ak., CA', 'pic', 'keuangan', 'Direktur Keuangan dan Akuntansi'],
            ['pic.kemahasiswaan', 'pic.kemahasiswaan', 'Dr. Yohanes Kristanto, M.Psi.', 'pic', 'kemahasiswaan', 'Direktur Student Advisory and Development'],
            ['pic.sarpras', 'pic.sarpras', 'Ir. Bambang Hartanto, M.T.', 'pic', 'sarpras', 'Kepala Facility Management'],
            ['pic.perencanaan', 'pic.perencanaan', 'Dr. Wenny Hartono, S.Kom., M.B.A.', 'pic', 'perencanaan', 'Direktur Strategic Planning and Development'],
            ['pic.kerjasama', 'pic.kerjasama', 'Dr. Kevin Tanuwijaya, M.B.A.', 'pic', 'kerjasama', 'Direktur International Office'],
            ['pic.sekretariat', 'pic.sekretariat', 'Dra. Imelda Susanti, M.Hum.', 'pic', 'sekretariat', 'Kepala Rector Office'],
            ['pic.spi', 'pic.spi', 'Hartono Wijaya, S.E., M.Ak., CIA', 'pic', 'spi', 'Kepala Internal Audit'],
            ['pic.lppm', 'pic.lppm', 'Dr. Grace Tanudjaja, M.Sc.', 'pic', 'lppm', 'Manajer Research and Technology Transfer'],
            ['pic.tik', 'pic.tik', 'Stefanus Wijaya, S.Kom., M.T.I.', 'pic', 'tik', 'Koordinator Pangkalan Data dan PDDikti'],
            ['pic.satgas', 'pic.satgas', 'Dr. Ratih Puspitasari, M.Psi., Psikolog', 'pic', 'satgas', 'Ketua Satgas PPK'],
            ['data.bbs', 'data.bbs', 'Felicia Anggraini, S.E., M.M.', 'pemilik_data', 'bbs', 'Operator Data BINUS Business School'],
            ['pengamat', 'pengamat', 'Pengurus Yayasan Bina Nusantara', 'pengamat', 'rektorat', 'Badan Penyelenggara'],
        ];
    }

    protected function performanceProfile(): array
    {
        return [
            'nidn_start' => 310000001,
            'doctor_pct' => 34,
            'professor_pct' => 6,
            'events' => ['Gemastik (Pagelaran Mahasiswa Nasional TIK)', 'ICPC Asia Jakarta Regional', 'Google Solution Challenge', 'Microsoft Imagine Cup', 'Hackathon BI-OJK', 'Kompetisi Bisnis Plan Nasional', 'PIMNAS', 'Indonesia International Model United Nations', 'Lomba Desain Interior Nasional', 'Festival Film Pendek Mahasiswa', 'Kompetisi Robotik Indonesia', 'National Accounting Olympiad', 'Esports Mahasiswa Nasional', 'Kompetisi Debat Bahasa Inggris (NUDC)'],
            'achievement_base' => 88,
            'topics' => ['kecerdasan buatan', 'pemrosesan bahasa alami Indonesia', 'visi komputer', 'keamanan siber', 'analitik big data', 'sistem informasi kesehatan', 'fintech dan pembayaran digital', 'e-commerce UMKM', 'transformasi digital perusahaan', 'kewirausahaan teknologi', 'pemasaran digital', 'perilaku konsumen generasi Z', 'game edukasi', 'animasi dan efek visual', 'arsitektur hijau', 'beton berkelanjutan', 'rantai pasok pangan', 'teknologi pangan fungsional', 'pariwisata cerdas', 'hukum perlindungan data pribadi', 'psikologi kerja hibrida', 'akuntansi berkelanjutan', 'IoT untuk kota cerdas', 'blockchain untuk logistik'],
            'places' => ['DKI Jakarta', 'Indonesia', 'Asia Tenggara', 'kawasan Jabodetabek', 'UMKM binaan', 'perusahaan rintisan Indonesia'],
            'publication_types' => ['jurnal_intl_bereputasi' => 34, 'jurnal_intl' => 8, 'jurnal_nas_terakreditasi' => 22, 'jurnal_nas' => 6, 'seminar_intl' => 24, 'seminar_nas' => 3, 'media_massa' => 3],
            'publication_base' => 720,
            'publication_growth' => 110,
            'venues' => ['Procedia Computer Science / IEEE Xplore (Scopus)', 'Jurnal Nasional Terakreditasi SINTA'],
            'ip_base' => 46,
            'ip_adopter' => 'PT Bina Nusantara Teknologi Mitra',
            'recognitions' => ['Asesor LAM INFOKOM / LAMEMBA', 'Mitra bestari jurnal internasional bereputasi', 'Narasumber Kemkomdigi', 'Visiting professor universitas mitra luar negeri', 'Juri kompetisi startup nasional', 'Penghargaan peneliti berprestasi'],
            'recognition_base' => 34,
            'grant_base' => 64,
            'grant_scale' => 1.4,
            'auditors' => [2021 => 'KAP Ilustrasi Nusantara & Rekan', 2022 => 'KAP Ilustrasi Nusantara & Rekan', 2023 => 'KAP Ilustrasi Nusantara & Rekan', 2024 => 'KAP Ilustrasi Kemanggisan & Rekan', 2025 => 'KAP Ilustrasi Kemanggisan & Rekan'],
            'survey_population' => ['mahasiswa' => 46000, 'dosen' => 1700, 'tendik' => 1500, 'mitra' => 520, 'lulusan' => 11000, 'pengguna' => 1100],
            'survey_open_population' => 47000,
            'tracer_graduates' => 10400,
            'tracer_growth' => 220,
            'employer_respondents' => 460,
            'employer_follow_up' => 'Penguatan soft skills kepemimpinan melalui program Enrichment (magang, riset, kewirausahaan, studi independen) wajib di semester 5-6.',
        ];
    }

    /** Pemetaan PIC mengikuti struktur direktorat BINUS. */
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
            'variant' => '3c',
            'name' => 'Reakreditasi Institusi 2027 · Jalur Unggul',
            'track' => Track::Excellent,
            'target_date' => '2027-01-29',
            'notes' => 'SK peringkat Unggul (289/SK/BAN-PT/AK-ISK/PT/VI/2022) berakhir pertengahan 2027. Target unggah LED dan LKPT ke SAPTO 29 Januari 2027; seluruh syarat perlu jalur Unggul sudah terpenuhi.',
            'locked_weeks' => 18,
            'eligibility_note' => 'Menunggu sinkronisasi PDDikti untuk mahasiswa baru BINUS @Medan semester gasal 2026/2027.',
            // Semua syarat perlu terpenuhi; tersisa penguatan di jalur Unggul.
            'not_met' => ['23B'],
            'not_met_excellent' => ['19B', '22A', '34B'],
            'unassessed' => ['37A'],
            'internal_disagree' => ['22A'],
            'internal_note' => 'Analisis luaran PkM belum memisahkan dampak per kampus; dinilai belum memenuhi.',
            'due_start' => '2026-11-02',
        ];
    }

    protected function progressProfile(): array
    {
        return ['led' => ['ratified' => 34, 'reviewer_approved' => 32, 'review' => 14, 'revision' => 6, 'draft' => 10, 'empty' => 4], 'evidence_met' => 96, 'doc_valid' => 84];
    }

    protected function highlightEvidence(): array
    {
        return [
            [4, 'sk', 'SK BAN-PT Akreditasi Institusi Peringkat Unggul Universitas Bina Nusantara', '289/SK/BAN-PT/AK-ISK/PT/VI/2022', '2022-06-14', '2027-06-14', 'https://binus.ac.id/2022/06/binus-resmi-peroleh-akreditasi-institusi-unggul-dari-ban-pt/'],
            [4, 'sertifikat', 'Akreditasi ABET untuk program studi Teknik Industri, Teknik Sipil, dan Teknik Komputer', null, '2022-06-10', null, 'https://binus.ac.id/2022/06/akreditasi-abet-kukuhkan-binus-university-sebagai-kampus-unggulan-di-bidang-teknik/'],
            [4, 'sertifikat', 'Akreditasi AACSB BINUS Business School (pertama 2019, reakreditasi 2024)', null, '2024-04-01', null, 'https://binus.ac.id/semarang/digitalbusiness/accreditation-and-global-recognition/'],
            [39, 'lainnya', 'QS World University Rankings 2026: peringkat 851-900 dunia, PTS terbaik di Indonesia', null, '2025-06-19', null, 'https://binus.ac.id/kemanggisan/2026/02/peringkat-binus-university-di-dunia-2026-analisis-pencapaian/'],
            [39, 'lainnya', 'THE World University Rankings 2026: peringkat 1201-1500 dunia', null, '2025-10-09', null, 'https://binus.ac.id/2025/10/binus-university-tunjukkan-konsistensi-mutu-global-di-times-higher-education-world-university-rankings-2026/'],
            [39, 'lainnya', 'THE Asia University Rankings 2025: peringkat 351-400 Asia, 2 nasional', null, '2025-04-23', null, 'https://research.binus.ac.id/?p=12317'],
            [36, 'kebijakan', 'Visi BINUS 2035: A world-class university contributing in building and serving the nation', null, '2024-01-15', null, 'https://binus.ac.id/'],
        ];
    }

    protected function reconciliationNotes(): array
    {
        return [
            'MHS_AKTIF' => 'Selisih karena 412 mahasiswa BINUS Online dan BINUS @Medan angkatan 2026 belum terlapor di PDDikti.',
            'LULUSAN_TS' => 'Lulusan Wisuda 74 (Oktober 2026) belum seluruhnya dilaporkan.',
        ];
    }

    protected function governanceProfile(): array
    {
        $base = parent::governanceProfile();

        return [
            'decisions' => [
                ['Penetapan Tim Reakreditasi Institusi 2027', '-120 days', 'rapat_pimpinan', 'Rektor menetapkan Tim Reakreditasi Institusi yang diketuai Direktur Quality Assurance dengan anggota para dekan, direktur, dan kepala kampus.'],
                ['Penetapan jalur akreditasi: Unggul', '-112 days', 'senat', 'Senat Universitas menyetujui jalur Unggul berdasarkan simulasi skor IAPT 4.1 dan status akreditasi internasional ABET dan AACSB.'],
                ['Dukungan anggaran reakreditasi', '-98 days', 'yayasan', 'Yayasan Bina Nusantara menyetujui anggaran penyusunan LED, LKPT, dan simulasi asesmen lapangan di delapan kampus.'],
                ['Satu data mahasiswa delapan kampus', '-75 days', 'rapat_pimpinan', 'ITD dan Academic Affairs menyatukan pelaporan PDDikti seluruh kampus dan BINUS Online ke satu pangkalan data.'],
                ['Klinik penulisan LED setiap Rabu', '-60 days', 'taskforce', 'Klinik penulisan daring dengan reviewer dan asesor internal untuk seluruh kampus.'],
                ['Penguatan bukti hilirisasi riset', '-35 days', 'rapat_pimpinan', 'RTTO mendokumentasikan pemanfaatan hasil riset oleh mitra industri dan pemerintah tiga tahun terakhir.'],
                ['Tindak lanjut hasil AMI 2026', '-16 days', 'rtm', 'RTM menetapkan 6 tindakan koreksi dan 3 tindakan pencegahan.'],
                ['Simulasi asesmen lapangan kedua', '-5 days', 'taskforce', 'Simulasi kedua dijadwalkan pertengahan Desember 2026 di Kampus Anggrek dan Alam Sutera.'],
            ],
            'decision_owners' => ['pic.akademik', 'pic.keuangan', 'pic.tik', 'pic.lpm', 'pic.lppm', 'pic.lpm'],
            'tasks' => [
                ['Lengkapi notulen RTM sekolah dan fakultas 2023-2025', 'lpm'], ['Unggah SK Quality Assurance tiap kampus', 'lpm'], ['Selaraskan manual SPMI dengan standar AACSB dan ABET', 'lpm'],
                ['Rekap prestasi internasional mahasiswa TS', 'kemahasiswaan'], ['Analisis tren mahasiswa baru per kampus', 'akademik'], ['Laporan evaluasi kurikulum OBE dan Enrichment', 'akademik'],
                ['Validasi jabatan akademik dosen di SISTER', 'sdm'], ['Perbarui katalog layanan ITD', 'tik'], ['Unggah laporan tracer study 2025', 'kemahasiswaan'],
                ['Peta jalan riset 2025-2029 (AI, data, bisnis digital)', 'lppm'], ['Kumpulkan surat pemanfaatan hasil riset dari mitra industri', 'lppm'], ['Laporan PkM UMKM digital', 'lppm'],
                ['Perbarui SOP Satgas PPK seluruh kampus', 'satgas'], ['Rekap beasiswa dan bantuan pendidikan', 'kemahasiswaan'], ['Susun RKAT 2027', 'keuangan'],
                ['Laporan keuangan audited 2025 dan opini KAP', 'keuangan'], ['Dokumentasi kerja sama gelar ganda luar negeri', 'kerjasama'], ['Rekap kerja sama luar negeri aktif dan implementasinya', 'kerjasama'],
                ['Laporan capaian pemeringkatan QS dan THE 2026', 'perencanaan'], ['Lengkapi dokumen K3 dan aksesibilitas kampus baru', 'sarpras'], ['Laporan evaluasi pembelajaran daring BINUS Online', 'akademik'],
                ['Sisipkan pendidikan antikorupsi pada RPS', 'akademik'], ['Rekap sertifikasi profesi lulusan (LSP P1 BINUS)', 'akademik'], ['Validasi survei kepuasan tendik 2026', 'lpm'],
                ['Perbaiki tautan bukti yang rusak', 'tik'], ['Monev hibah riset internal 2026', 'lppm'], ['Laporan capaian diferensiasi misi', 'perencanaan'],
                ['Laporan Internal Audit atas pengendalian internal 2025', 'spi'], ['Laporan layanan konseling dan karier', 'kemahasiswaan'], ['Perbarui pedoman penerimaan mahasiswa inklusif', 'kemahasiswaan'],
            ],
            'risks' => [
                ['Prodi Bisnis Digital: akreditasi berakhir Mei 2027', 3, 4, 'pic.akademik', 'Pengajuan reakreditasi ke LAMEMBA paling lambat Desember 2026.'],
                ['Selisih data mahasiswa delapan kampus dengan PDDikti', 3, 4, 'pic.tik', 'Satu pangkalan data dan rekonsiliasi mingguan sebelum 30 November.'],
                ['Bukti pemanfaatan hasil riset oleh mitra belum merata antarsekolah', 3, 3, 'pic.lppm', 'Kumpulkan surat pemanfaatan dan studi kasus hilirisasi.'],
                ['Konsistensi mutu pembelajaran antarkampus dan BINUS Online', 2, 4, 'pic.akademik', 'Audit mutu lintas kampus dan kalibrasi penilaian.'],
                ['Proporsi guru besar di bawah PT sejenis', 3, 2, 'pic.sdm', 'Program percepatan guru besar dan mentoring publikasi Q1.'],
                ['Respons survei mitra luar negeri rendah', 2, 3, 'pic.lpm', 'Survei terintegrasi dengan rapat mitra tahunan.'],
                ['Bukti kerja sama gelar ganda kedaluwarsa', 2, 3, 'pic.kerjasama', 'Perpanjangan perjanjian dan dokumentasi implementasi.'],
                ['Gangguan layanan daring saat masa ujian', 1, 4, 'pic.tik', 'Kapasitas cadangan dan uji beban sebelum ujian.'],
                ['Pergantian dekan dan kepala kampus', 2, 2, 'manajer', 'Dokumentasi peran dan pendamping cadangan setiap PIC.'],
                ['Kemampuan kepemimpinan lulusan dinilai perlu ditingkatkan', 2, 2, 'pic.kemahasiswaan', 'Program kepemimpinan dalam Enrichment.'],
            ],
            'standards' => $base['standards'],
            'standard_statement' => ' Universitas Bina Nusantara melampaui SN Dikti sesuai visi universitas kelas dunia yang berkontribusi membangun dan melayani bangsa.',
            'audits' => [
                ['name' => 'AMI Siklus 2025', 'cycle_year' => 2025, 'audit_date' => '2025-11-03', 'lead_auditor' => 'Prof. Dr. Antonius Prasetya, M.Kom.', 'state' => 'selesai', 'summary' => '37 prodi, 8 kampus, dan 14 unit diaudit; 1 temuan mayor, 9 minor, 7 observasi.'],
                ['name' => 'AMI Siklus 2026', 'cycle_year' => 2026, 'audit_date' => now()->subDays(22), 'lead_auditor' => 'Prof. Dr. Antonius Prasetya, M.Kom.', 'state' => 'pelaporan', 'summary' => 'Fokus pada kesiapan jalur Unggul, konsistensi mutu antarkampus, dan hilirisasi riset.'],
            ],
            'findings' => [
                [0, 'mayor', 'Bukti RTM BINUS @Malang dan @Semarang siklus 2023 belum lengkap.', 2, 'lpm', 'closed', 'ami'],
                [0, 'minor', 'RPS belum memuat sisipan pendidikan antikorupsi pada 3 prodi.', 13, 'akademik', 'closed', 'ami'],
                [0, 'minor', 'Data jabatan akademik di SISTER belum selaras dengan data HCM.', 11, 'sdm', 'closed', 'ami'],
                [1, 'minor', 'Bukti pemanfaatan hasil riset oleh mitra belum mencakup seluruh sekolah.', 19, 'lppm', 'capa', 'ami'],
                [1, 'minor', 'Survei kepuasan mitra belum mencakup mitra luar negeri gelar ganda.', 34, 'lpm', 'capa', 'ami'],
                [1, 'observasi', 'Beberapa tautan bukti di laman kampus daerah tidak dapat diakses.', 26, 'tik', 'open', 'ami'],
                [1, 'minor', 'Analisis luaran PkM belum memisahkan dampak per kampus.', 22, 'lppm', 'capa', 'ami'],
                [null, 'observasi', 'Narasumber mahasiswa kampus daerah belum memahami kanal Satgas PPK.', 28, 'satgas', 'capa', 'simulasi'],
                [null, 'peluang', 'Akreditasi ABET dan AACSB dapat dijadikan narasi utama kriteria 4.', 39, 'kerjasama', 'open', 'simulasi'],
                [null, 'minor', 'Tindak lanjut survei kepuasan layanan BINUS Online belum terdokumentasi.', 34, 'akademik', 'verification', 'survei'],
            ],
            'reviews' => [
                ['meeting_date' => '2025-12-11', 'title' => 'RTM Universitas Semester Gasal 2025/2026', 'chair' => 'Rektor', 'minutes' => 'Membahas hasil AMI 2025 delapan kampus, survei kepuasan, dan capaian IKU.', 'outcomes' => 'Penetapan 5 tindakan koreksi; kalibrasi penilaian antarkampus.'],
                ['meeting_date' => '2026-06-18', 'title' => 'RTM Universitas Semester Genap 2025/2026', 'chair' => 'Rektor', 'minutes' => 'Membahas kesiapan reakreditasi institusi dan simulasi skor IAPT 4.1.', 'outcomes' => 'Pemilihan jalur Unggul; satu data PDDikti; klinik LED.'],
                ['meeting_date' => now()->subDays(16)->toDateString(), 'title' => 'RTM Khusus Kesiapan Reakreditasi Institusi', 'chair' => 'Wakil Rektor Bidang Akademik', 'minutes' => 'Membahas hasil AMI 2026 dan bukti hilirisasi riset.', 'outcomes' => 'Target AKI 90 sebelum unggah SAPTO; penguatan 19B dan 34B.'],
            ],
        ];
    }

    protected function siteVisitProfile(): array
    {
        $base = parent::siteVisitProfile();

        return [
            'mock' => [
                'title' => 'Simulasi Asesmen Lapangan I', 'starts_on' => now()->subDays(30), 'ends_on' => now()->subDays(29),
                'mode' => 'luring', 'assessors' => 'Prof. Dr. Antonius Prasetya, M.Kom.; Prof. Dr. Ir. Kusnadi, M.Eng. (asesor eksternal)', 'state' => 'selesai',
                'document_room' => 'Ruang Rapat Rektorat, BINUS Kampus Anggrek', 'minutes' => 'Simulasi berjalan baik; 5 rekomendasi perbaikan untuk jalur Unggul.',
            ],
            'recommendations' => [
                [19, 'Lengkapi bukti pemanfaatan hasil riset oleh mitra industri untuk seluruh sekolah.', 'task'],
                [22, 'Pisahkan analisis luaran PkM per kampus dan tunjukkan dampaknya.', 'task'],
                [34, 'Tunjukkan tindak lanjut survei kepuasan BINUS Online dan mitra luar negeri.', 'capa'],
                [28, 'Perkuat sosialisasi Satgas PPK di kampus daerah.', null],
                [39, 'Jadikan ABET, AACSB, dan capaian QS/THE sebagai bukti keunggulan utama.', null],
            ],
            'visit' => [
                'title' => 'Asesmen Lapangan BAN-PT', 'starts_on' => '2027-04-12', 'ends_on' => '2027-04-13',
                'mode' => 'luring', 'assessors' => 'Menunggu penugasan BAN-PT', 'rehearsal_on' => '2027-04-05', 'state' => 'rencana',
                'document_room' => 'Ruang Rapat Rektorat, BINUS Kampus Anggrek',
            ],
            'visit_date' => '2027-04-12',
            'sessions' => [
                ['08:00', '09:00', 'pimpinan', 'Pembukaan dan presentasi Rektor', 'Auditorium Kampus Anggrek', 'Rektor'],
                ['09:00', '10:30', 'pimpinan', 'Wawancara pimpinan, senat, dan yayasan', 'Ruang Rapat Rektorat', 'Direktur QA'],
                ['10:30', '11:30', 'dosen', 'Wawancara dosen', 'Ruang Rapat SOCS', 'Dekan SOCS'],
                ['11:30', '12:30', 'tendik', 'Wawancara tenaga kependidikan', 'Ruang Rapat Academic Affairs', 'Direktur Academic Affairs'],
                ['13:30', '14:30', 'mahasiswa', 'Wawancara mahasiswa', 'BINUS Square', 'Direktur Student Advisory'],
                ['14:30', '15:30', 'lulusan', 'Wawancara lulusan dan pengguna lulusan', 'BINUS Career Center', 'Direktur Student Advisory'],
                ['15:30', '17:00', 'observasi', 'Observasi laboratorium, perpustakaan, dan studio', 'Kampus Anggrek dan Alam Sutera', 'Kepala Facility Management'],
            ],
            'interviewees' => [
                ['pimpinan', 'Dr. Nelly, S.Kom., M.M.', 'Rektor', true], ['pimpinan', 'Pengurus Yayasan Bina Nusantara', 'Badan Penyelenggara', true],
                ['pimpinan', 'Prof. Dr. Ir. Bambang Widjaja, M.Sc.', 'Wakil Rektor Bidang Akademik', true], ['dosen', 'Dr. Fredy Purnomo, S.Kom., M.Kom.', 'Dekan SOCS', true],
                ['dosen', 'Clara Wijaya, S.Ds., M.Ds.', 'Dosen DKV', false], ['dosen', 'Dr. Ir. Johan Setiadi, M.T., IPU', 'Dekan Faculty of Engineering', true],
                ['tendik', 'Rina Oktaviani, S.E.', 'Staf Keuangan', true], ['tendik', 'Dimas Pratama, A.Md.', 'Laboran SOCS', false],
                ['mahasiswa', 'Kevin Pratama', 'Presiden Mahasiswa BINUS', true], ['mahasiswa', 'Jessica Halim', 'Mahasiswa Sistem Informasi 2023', false],
                ['lulusan', 'Andre Wijaya, S.Kom.', 'Alumni Teknik Informatika, pendiri startup', true], ['pengguna', 'HR Manager perusahaan teknologi mitra', 'Pengguna lulusan', false],
            ],
            'questions' => array_map(fn ($q) => [$q[0], $q[1], str_replace('UNM', 'BINUS', $q[2]), match ($q[0]) {
                36 => 'Visi BINUS 2035; IKU per kampus; peran teknologi digital dan kewirausahaan.',
                39 => 'ABET (teknik), AACSB (BINUS Business School), QS WUR 851-900, THE Asia 351-400.',
                35 => 'Opini WTP 2021-2025 dari KAP; laporan keuangan yayasan.',
                default => $q[3],
            }], $base['questions']),
        ];
    }
}
