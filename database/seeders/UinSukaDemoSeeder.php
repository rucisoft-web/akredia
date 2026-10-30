<?php

namespace Database\Seeders;

use App\Enums\Track;

/**
 * Data demo Universitas Islam Negeri Sunan Kalijaga Yogyakarta (UIN Suka; PTN akademik di bawah
 * Kementerian Agama) yang menyiapkan reakreditasi institusi jalur Unggul dengan IAPT 4.1 (varian 3b).
 *
 * Diambil dari uin-suka.ac.id dan laman LPM (Oktober 2026): nama, alamat, visi, Rektor periode
 * 2024-2028 (Prof. Noorhaidi Hasan), delapan fakultas dan Pascasarjana, akreditasi institusi Unggul
 * (SK 1121/SK/BAN-PT/Ak.Ppj/PT/XII/2023; sebelumnya SK 899/2021), akreditasi internasional ASIIN
 * untuk Matematika, Fisika, Kimia, dan Biologi (sampai 2030) dan FIBAA untuk Studi Agama-Agama, peringkat
 * pertama PTKIN di Webometrics Juli 2025, serta jabatan Rektor sebagai Presiden AIUA 2026-2028.
 * Selebihnya (kode PT dan prodi, masa berlaku SK, nama Wakil Rektor dan pejabat lain, peringkat dan
 * tanggal akreditasi prodi, dosen, angka mahasiswa, publikasi, survei, temuan, tugas, risiko, narasi)
 * adalah data ilustrasi untuk demonstrasi, bukan data resmi UIN Sunan Kalijaga.
 */
class UinSukaDemoSeeder extends DemoSeeder
{
    public const SLUG = 'uin-suka';

    protected string $emailDomain = 'uin-suka.akredia.test';

    protected string $short = 'UIN Suka';

    protected bool $guestAccess = true;

    protected function institutionAttributes(): array
    {
        return [
            'name' => 'Universitas Islam Negeri Sunan Kalijaga Yogyakarta',
            'slug' => static::SLUG,
            'code' => null,
            'type' => 'ptn_akademik',
            'legal_entity' => 'PTN Badan Layanan Umum, Kementerian Agama Republik Indonesia',
            'sk_number' => 'Keputusan Presiden Nomor 50 Tahun 2004 (perubahan IAIN menjadi UIN)',
            'sk_date' => '2004-06-21',
            'first_intake_year' => 1951,
            'city' => 'Sleman, Daerah Istimewa Yogyakarta',
            'address' => 'Jl. Laksda Adisucipto, Papringan, Caturtunggal, Depok, Sleman, Yogyakarta 55281',
            'website' => 'https://uin-suka.ac.id',
            'vision' => 'Unggul dan Terkemuka dalam Pemaduan dan Pengembangan Keislaman dan Keilmuan bagi Peradaban.',
            'mission_focus' => 'Integrasi-interkoneksi keislaman dan keilmuan: studi Islam interdisipliner bereputasi internasional, sains dan teknologi yang berpijak pada nilai keislaman, serta pengabdian inklusif bagi masyarakat dan peradaban.',
            'last_accreditation_rank' => 'Unggul',
            'last_accreditation_sk' => '1121/SK/BAN-PT/Ak.Ppj/PT/XII/2023',
            'last_accreditation_until' => '2028-12-20',
            'is_demo' => true,
        ];
    }

    // ------------------------------------------------------------------ organisasi

    protected function seedOrganisation(): void
    {
        $units = [
            'lpm' => ['LPM', 'Lembaga Penjaminan Mutu', 'lembaga', 'Dr. Siti Rohmah, M.Pd.'],
            'lppm' => ['LPPM', 'Lembaga Penelitian dan Pengabdian kepada Masyarakat', 'lembaga', 'Prof. Dr. Ahmad Zainuddin, M.Ag.'],
            'spi' => ['SPI', 'Satuan Pengawas Internal', 'lembaga', 'Drs. Haryanto Saputro, M.Ak., CA'],
            'akademik' => ['BAAK', 'Biro Administrasi Akademik dan Kemahasiswaan', 'biro', 'Dr. Nur Hidayati, M.Si.'],
            'kemahasiswaan' => ['KMHS', 'Pusat Layanan Kemahasiswaan dan Karier', 'biro', 'Dr. Muhammad Fauzan, M.Psi.'],
            'keuangan' => ['BAUK', 'Biro Administrasi Umum, Perencanaan, dan Keuangan', 'biro', 'Dr. Endang Lestari, S.E., M.Ak.'],
            'sdm' => ['SDM', 'Bagian Organisasi, Kepegawaian, dan Hukum', 'biro', 'Dr. Agus Wibowo, M.M.'],
            'sarpras' => ['SARPRAS', 'Bagian Umum dan Sarana Prasarana', 'biro', 'Ir. Bambang Sutrisno, M.T.'],
            'perencanaan' => ['PRC', 'Bagian Perencanaan dan Pelaporan', 'biro', 'Dr. Rina Wulandari, M.E.'],
            'kerjasama' => ['KUI', 'Kantor Urusan Internasional dan Kerja Sama', 'biro', 'Dr. Hamdan Rasyid, M.A.'],
            'sekretariat' => ['SEKR', 'Sekretariat Rektor', 'biro', 'Dra. Umi Kulsum, M.Hum.'],
            'tik' => ['PTIPD', 'Pusat Teknologi Informasi dan Pangkalan Data', 'upt', 'Dr. Arif Rahman, S.T., M.Kom.'],
            'perpustakaan' => ['PERPUS', 'UPT Perpustakaan', 'upt', 'Dr. Dewi Maryam, S.IP., M.Hum.'],
            'satgas' => ['SATGAS', 'Satgas Pencegahan dan Penanganan Kekerasan', 'satgas', 'Dr. Laila Nurjanah, M.Psi., Psikolog'],
        ];

        $faculties = [
            'fadib' => ['FADIB', 'Fakultas Adab dan Ilmu Budaya', 'Dr. Muhammad Wildan, M.A.'],
            'fdk' => ['FDK', 'Fakultas Dakwah dan Komunikasi', 'Dr. Hamdan Daulay, M.Si.'],
            'fsh' => ['FSH', 'Fakultas Syariah dan Hukum', 'Dr. Ali Sodiqin, M.Ag.'],
            'fitk' => ['FITK', 'Fakultas Ilmu Tarbiyah dan Keguruan', 'Prof. Dr. Sri Sumarni, M.Pd.'],
            'fupi' => ['FUPI', 'Fakultas Ushuluddin dan Pemikiran Islam', 'Dr. Inayah Rohmaniyah, M.Hum.'],
            'fst' => ['FST', 'Fakultas Sains dan Teknologi', 'Dr. Khurul Wardati, M.Si.'],
            'fishum' => ['FISHUM', 'Fakultas Ilmu Sosial dan Humaniora', 'Dr. Erika Setyanti, M.Si.'],
            'febi' => ['FEBI', 'Fakultas Ekonomi dan Bisnis Islam', 'Dr. Misnen Ardiansyah, M.Si., Ak.'],
            'pasca' => ['PASCA', 'Pascasarjana', 'Prof. Dr. Ahmad Rafiq, M.Ag.'],
        ];

        // Akreditasi internasional yang diberitakan laman UIN Suka: ASIIN (empat prodi FST, sampai 2030) dan FIBAA (Studi Agama-Agama).
        $intl = [
            'mat' => ['ASIIN', 2025, 2030], 'fis' => ['ASIIN', 2025, 2030], 'kim' => ['ASIIN', 2025, 2030], 'bio' => ['ASIIN', 2025, 2030],
            'saa' => ['FIBAA', 2023, 2028],
        ];

        // [kunci, fakultas, kode prodi (ilustrasi), nama, jenjang, dibuka, lembaga, peringkat, berlaku s.d., dosen tetap, mahasiswa aktif TS]
        $programs = [
            ['spi', 'fadib', '70901', 'Sejarah Peradaban Islam', 'S1', 1963, 'banpt', 'unggul', '2028-08-14', 18, 560],
            ['bsa', 'fadib', '70902', 'Bahasa dan Sastra Arab', 'S1', 1963, 'banpt', 'unggul', '2028-05-20', 20, 640],
            ['sasing', 'fadib', '79202', 'Sastra Inggris', 'S1', 2006, 'banpt', 'unggul', '2028-11-02', 16, 620],
            ['ip', 'fadib', '71201', 'Ilmu Perpustakaan', 'S1', 2006, 'banpt', 'baik_sekali', '2027-09-18', 14, 540],
            ['kpi', 'fdk', '70903', 'Komunikasi dan Penyiaran Islam', 'S1', 1970, 'banpt', 'unggul', '2028-03-11', 18, 780],
            ['bki', 'fdk', '70904', 'Bimbingan dan Konseling Islam', 'S1', 1970, 'banpt', 'unggul', '2028-06-07', 16, 640],
            ['pmi', 'fdk', '70905', 'Pengembangan Masyarakat Islam', 'S1', 1970, 'banpt', 'baik_sekali', '2027-12-04', 12, 420],
            ['md', 'fdk', '70906', 'Manajemen Dakwah', 'S1', 1970, 'banpt', 'unggul', '2028-09-25', 14, 520],
            ['iks', 'fdk', '63201', 'Ilmu Kesejahteraan Sosial', 'S1', 2006, 'lam_spak', 'baik_sekali', '2027-07-30', 12, 460],
            ['hki', 'fsh', '70907', 'Hukum Keluarga Islam', 'S1', 1951, 'banpt', 'unggul', '2028-10-16', 18, 620],
            ['pm', 'fsh', '70908', 'Perbandingan Mazhab', 'S1', 1951, 'banpt', 'unggul', '2028-04-22', 12, 380],
            ['htn', 'fsh', '70909', 'Hukum Tata Negara (Siyasah)', 'S1', 1998, 'banpt', 'unggul', '2028-07-09', 14, 520],
            ['hes', 'fsh', '70910', 'Hukum Ekonomi Syariah', 'S1', 1951, 'banpt', 'unggul', '2029-01-12', 16, 640],
            ['ih', 'fsh', '74201', 'Ilmu Hukum', 'S1', 2006, 'banpt', 'unggul', '2028-02-26', 18, 760],
            ['pai', 'fitk', '86208', 'Pendidikan Agama Islam', 'S1', 1951, 'lam_dik', 'unggul', '2029-03-03', 26, 980],
            ['pba', 'fitk', '88204', 'Pendidikan Bahasa Arab', 'S1', 1951, 'lam_dik', 'unggul', '2028-12-01', 18, 640],
            ['mpi', 'fitk', '86209', 'Manajemen Pendidikan Islam', 'S1', 1998, 'lam_dik', 'unggul', '2028-05-13', 16, 600],
            ['pgmi', 'fitk', '86232', 'Pendidikan Guru Madrasah Ibtidaiyah', 'S1', 2006, 'lam_dik', 'unggul', '2028-08-28', 20, 760],
            ['piaud', 'fitk', '86207', 'Pendidikan Islam Anak Usia Dini', 'S1', 2010, 'lam_dik', 'baik_sekali', '2027-06-24', 14, 520],
            ['afi', 'fupi', '70911', 'Aqidah dan Filsafat Islam', 'S1', 1951, 'banpt', 'unggul', '2028-09-10', 14, 420],
            ['saa', 'fupi', '70912', 'Studi Agama-Agama', 'S1', 1951, 'banpt', 'unggul', '2028-11-19', 14, 400],
            ['iat', 'fupi', '70913', 'Ilmu Al-Qur\'an dan Tafsir', 'S1', 1951, 'banpt', 'unggul', '2028-04-05', 16, 560],
            ['ilha', 'fupi', '70914', 'Ilmu Hadis', 'S1', 2016, 'banpt', 'baik_sekali', '2027-10-27', 10, 300],
            ['mat', 'fst', '44201', 'Matematika', 'S1', 2004, 'lam_sama', 'unggul', '2028-06-30', 14, 360],
            ['fis', 'fst', '45201', 'Fisika', 'S1', 2004, 'lam_sama', 'unggul', '2028-03-21', 12, 300],
            ['kim', 'fst', '47201', 'Kimia', 'S1', 2004, 'lam_sama', 'unggul', '2028-10-08', 12, 320],
            ['bio', 'fst', '46201', 'Biologi', 'S1', 2004, 'lam_sama', 'unggul', '2028-07-17', 12, 330],
            ['tif', 'fst', '55201', 'Informatika', 'S1', 2004, 'lam_infokom', 'unggul', '2028-12-14', 16, 620],
            ['tind', 'fst', '26201', 'Teknik Industri', 'S1', 2004, 'lam_teknik', 'baik_sekali', '2027-08-09', 12, 420],
            ['ars', 'fst', '23201', 'Arsitektur', 'S1', 2021, 'lam_teknik', 'baik', '2027-05-31', 6, 180],
            ['soc', 'fishum', '69201', 'Sosiologi', 'S1', 2006, 'lam_spak', 'unggul', '2028-02-13', 12, 460],
            ['psi', 'fishum', '73201', 'Psikologi', 'S1', 2006, 'lam_ptkes', 'unggul', '2028-09-03', 16, 600],
            ['ikom', 'fishum', '70201', 'Ilmu Komunikasi', 'S1', 2006, 'lam_spak', 'unggul', '2028-05-26', 14, 560],
            ['es', 'febi', '60202', 'Ekonomi Syariah', 'S1', 2013, 'lam_emba', 'unggul', '2028-11-27', 16, 720],
            ['ps', 'febi', '61206', 'Perbankan Syariah', 'S1', 1998, 'lam_emba', 'unggul', '2028-01-30', 16, 680],
            ['aks', 'febi', '62202', 'Akuntansi Syariah', 'S1', 2013, 'lam_emba', 'baik_sekali', '2027-11-15', 12, 520],
            ['mks', 'febi', '61209', 'Manajemen Keuangan Syariah', 'S1', 2015, 'lam_emba', 'baik_sekali', '2027-04-18', 10, 440],
            ['s2pai', 'fitk', '86108', 'Pendidikan Agama Islam', 'S2', 2007, 'lam_dik', 'unggul', '2028-10-20', 10, 180],
            ['s2hi', 'fsh', '74130', 'Ilmu Syariah', 'S2', 2010, 'banpt', 'unggul', '2028-06-12', 8, 150],
            ['s2afi', 'fupi', '76131', 'Aqidah dan Filsafat Islam', 'S2', 2010, 'banpt', 'unggul', '2028-08-02', 8, 120],
            ['s2iis', 'pasca', '76101', 'Interdisciplinary Islamic Studies', 'S2', 1983, 'banpt', 'unggul', '2028-12-18', 18, 420],
            ['s3si', 'pasca', '76001', 'Studi Islam', 'S3', 1983, 'banpt', 'unggul', '2029-02-22', 16, 260],
        ];

        $this->createOrganisation(['REK', 'Rektorat', 'Prof. Noorhaidi Hasan, S.Ag., M.A., M.Phil., Ph.D.'], $units, $faculties, $programs, $intl);
    }

    protected function people(): array
    {
        return [
            ['admin', 'admin.pt', 'Dr. Arif Rahman, S.T., M.Kom.', 'admin', 'tik', 'Kepala Pusat Teknologi Informasi dan Pangkalan Data'],
            ['rektor', 'rektor', 'Prof. Noorhaidi Hasan, S.Ag., M.A., M.Phil., Ph.D.', 'pimpinan', 'rektorat', 'Rektor'],
            ['wr1', 'wr1', 'Prof. Dr. Siti Aminah, M.Ag.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Akademik dan Pengembangan Lembaga'],
            ['wr2', 'wr2', 'Dr. Ahmad Mustofa, S.E., M.Si.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Administrasi Umum, Perencanaan, dan Keuangan'],
            ['wr3', 'wr3', 'Prof. Dr. Hasan Basri, M.A.', 'pimpinan', 'rektorat', 'Wakil Rektor Bidang Kemahasiswaan dan Kerja Sama'],
            ['manajer', 'manajer', 'Dr. Siti Rohmah, M.Pd.', 'manajer', 'lpm', 'Ketua LPM / Ketua Tim Reakreditasi Institusi'],
            ['reviewer', 'reviewer', 'Prof. Dr. Mahmud Arif, M.Ag.', 'reviewer', 'lpm', 'Reviewer LED'],
            ['reviewer2', 'reviewer2', 'Dr. Khurul Wardati, M.Si.', 'reviewer', 'fst', 'Reviewer LED'],
            ['asesor', 'asesor', 'Prof. Dr. Sutrisno Hadi, M.Ag.', 'asesor', 'lpm', 'Asesor internal (asesor BAN-PT)'],
            ['pic.lpm', 'pic.lpm', 'Nurul Fitriani, S.Pd.I., M.Pd.', 'pic', 'lpm', 'Kepala Pusat Akreditasi dan Audit Mutu'],
            ['pic.akademik', 'pic.akademik', 'Dr. Nur Hidayati, M.Si.', 'pic', 'akademik', 'Kepala Biro AAK'],
            ['pic.sdm', 'pic.sdm', 'Dr. Agus Wibowo, M.M.', 'pic', 'sdm', 'Kepala Bagian Organisasi, Kepegawaian, dan Hukum'],
            ['pic.keuangan', 'pic.keuangan', 'Dr. Endang Lestari, S.E., M.Ak.', 'pic', 'keuangan', 'Kepala Biro AUPK'],
            ['pic.kemahasiswaan', 'pic.kemahasiswaan', 'Dr. Muhammad Fauzan, M.Psi.', 'pic', 'kemahasiswaan', 'Kepala Pusat Layanan Kemahasiswaan dan Karier'],
            ['pic.sarpras', 'pic.sarpras', 'Ir. Bambang Sutrisno, M.T.', 'pic', 'sarpras', 'Kepala Bagian Umum dan Sarana Prasarana'],
            ['pic.perencanaan', 'pic.perencanaan', 'Dr. Rina Wulandari, M.E.', 'pic', 'perencanaan', 'Kepala Bagian Perencanaan dan Pelaporan'],
            ['pic.kerjasama', 'pic.kerjasama', 'Dr. Hamdan Rasyid, M.A.', 'pic', 'kerjasama', 'Kepala Kantor Urusan Internasional'],
            ['pic.sekretariat', 'pic.sekretariat', 'Dra. Umi Kulsum, M.Hum.', 'pic', 'sekretariat', 'Kepala Sekretariat Rektor'],
            ['pic.spi', 'pic.spi', 'Drs. Haryanto Saputro, M.Ak., CA', 'pic', 'spi', 'Kepala SPI'],
            ['pic.lppm', 'pic.lppm', 'Dr. Fatimah Az-Zahra, M.Si.', 'pic', 'lppm', 'Kepala Pusat Penelitian dan Publikasi'],
            ['pic.tik', 'pic.tik', 'Rizki Ramadhan, S.Kom., M.Cs.', 'pic', 'tik', 'Koordinator Pangkalan Data dan PDDikti'],
            ['pic.satgas', 'pic.satgas', 'Dr. Laila Nurjanah, M.Psi., Psikolog', 'pic', 'satgas', 'Ketua Satgas PPK'],
            ['data.fitk', 'data.fitk', 'Siti Mukaromah, S.Pd., M.Pd.', 'pemilik_data', 'fitk', 'Operator Data FITK'],
            ['pengamat', 'pengamat', 'Dewan Pengawas BLU UIN Sunan Kalijaga', 'pengamat', 'rektorat', 'Dewan Pengawas'],
        ];
    }

    protected function performanceProfile(): array
    {
        return [
            'nidn_start' => 320000001,
            'doctor_pct' => 46,
            'professor_pct' => 9,
            'events' => ['Musabaqah Tilawatil Quran Mahasiswa Nasional (MTQMN)', 'PIMNAS', 'Pekan Ilmiah, Olahraga, Seni, dan Riset (PIONIR) PTKIN', 'Lomba Debat Bahasa Arab Nasional', 'NUDC (Debat Bahasa Inggris)', 'Gemastik', 'Olimpiade Sains Nasional Pertamina', 'Kompetisi Peradilan Semu Nasional', 'Festival Film Dokumenter Mahasiswa', 'Lomba Karya Tulis Ilmiah Ekonomi Syariah', 'Kompetisi Robotik Indonesia', 'Pekan Seni Mahasiswa Nasional'],
            'achievement_base' => 64,
            'topics' => ['integrasi-interkoneksi keilmuan', 'moderasi beragama', 'studi Al-Qur\'an kontemporer', 'filsafat Islam dan etika', 'hukum keluarga Islam', 'fikih dan hak perempuan', 'ekonomi dan keuangan syariah', 'zakat dan wakaf produktif', 'pendidikan Islam inklusif', 'madrasah dan literasi digital', 'dakwah digital', 'komunikasi lintas agama', 'sejarah dan manuskrip Nusantara', 'studi agama dan perdamaian', 'psikologi agama', 'sosiologi masyarakat Yogyakarta', 'kimia bahan alam halal', 'biologi konservasi Merapi', 'fisika kebencanaan', 'kecerdasan buatan untuk teks Arab', 'sistem informasi masjid dan pesantren', 'difabilitas dan aksesibilitas kampus'],
            'places' => ['Daerah Istimewa Yogyakarta', 'Indonesia', 'Asia Tenggara', 'lereng Merapi', 'pesantren mitra', 'madrasah binaan'],
            'publication_types' => ['jurnal_intl_bereputasi' => 22, 'jurnal_intl' => 10, 'jurnal_nas_terakreditasi' => 36, 'jurnal_nas' => 10, 'seminar_intl' => 16, 'seminar_nas' => 4, 'media_massa' => 2],
            'publication_base' => 640,
            'publication_growth' => 90,
            'venues' => ['Al-Jami\'ah: Journal of Islamic Studies (Scopus Q1)', 'Jurnal Nasional Terakreditasi SINTA'],
            'ip_base' => 28,
            'ip_adopter' => 'Lembaga Amil Zakat dan pesantren mitra di DIY',
            'recognitions' => ['Asesor BAN-PT / LAMDIK', 'Mitra bestari jurnal internasional bereputasi', 'Narasumber Kementerian Agama', 'Visiting scholar universitas mitra luar negeri', 'Anggota komisi fatwa dan dewan pengawas syariah', 'Penghargaan peneliti berprestasi PTKIN'],
            'recognition_base' => 30,
            'grant_base' => 52,
            'grant_scale' => 1.0,
            'auditors' => [2021 => 'KAP Ilustrasi Malioboro & Rekan', 2022 => 'KAP Ilustrasi Malioboro & Rekan', 2023 => 'KAP Ilustrasi Malioboro & Rekan', 2024 => 'KAP Ilustrasi Adisucipto & Rekan', 2025 => 'KAP Ilustrasi Adisucipto & Rekan'],
            'survey_population' => ['mahasiswa' => 22000, 'dosen' => 720, 'tendik' => 520, 'mitra' => 260, 'lulusan' => 5200, 'pengguna' => 600],
            'survey_open_population' => 22500,
            'tracer_graduates' => 4800,
            'tracer_growth' => 120,
            'employer_respondents' => 280,
            'employer_follow_up' => 'Penguatan kemampuan bahasa asing dan literasi digital melalui sertifikasi TOEFL/TOAFL dan mata kuliah literasi data lintas fakultas.',
        ];
    }

    /** Pemetaan PIC mengikuti struktur biro dan lembaga UIN Suka. */
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
            'variant' => '3b',
            'name' => 'Reakreditasi Institusi 2028 · Jalur Unggul',
            'track' => Track::Excellent,
            'target_date' => '2027-09-30',
            'notes' => 'Akreditasi institusi peringkat Unggul (SK 1121/SK/BAN-PT/Ak.Ppj/PT/XII/2023). Persiapan dini dengan IAPT 4.1: target unggah LED dan LKPT ke SAPTO 30 September 2027 agar asesmen selesai jauh sebelum masa berlaku berakhir.',
            'locked_weeks' => 8,
            'eligibility_note' => 'Statuta dan renstra 2025-2029 sudah ditetapkan; menunggu SK izin operasional prodi Arsitektur diperbarui di PDDikti.',
            // Jalur Terakreditasi praktis terpenuhi; jalur Unggul masih butuh penguatan dampak dan data.
            'not_met' => ['15C', '23B'],
            'not_met_excellent' => ['12B', '19B', '27B', '30A', '34B', '38B'],
            'unassessed' => ['37A', '22A'],
            'internal_disagree' => ['12B'],
            'internal_note' => 'Analisis capaian kelulusan belum memisahkan prodi keagamaan dan prodi umum; dinilai belum memenuhi.',
            'due_start' => '2026-11-09',
        ];
    }

    protected function progressProfile(): array
    {
        return ['led' => ['ratified' => 18, 'reviewer_approved' => 22, 'review' => 16, 'revision' => 10, 'draft' => 20, 'empty' => 14], 'evidence_met' => 84, 'doc_valid' => 72];
    }

    protected function highlightEvidence(): array
    {
        return [
            [4, 'sk', 'SK BAN-PT Akreditasi Institusi Peringkat Unggul UIN Sunan Kalijaga Yogyakarta', '1121/SK/BAN-PT/Ak.Ppj/PT/XII/2023', '2023-12-20', '2028-12-20', 'https://lpm.uin-suka.ac.id/'],
            [4, 'sertifikat', 'Sertifikat dan SK akreditasi institusi (konversi peringkat Unggul, SK 899/SK/BAN-PT/AK-ISK/PT/X/2021)', '899/SK/BAN-PT/AK-ISK/PT/X/2021', '2021-10-13', '2023-12-20', 'https://lpm.uin-suka.ac.id/media/pengumuman/011_20211013_Sertifikat%20dan%20SK%20Akreditasi%20UIN%202021-2023.pdf'],
            [4, 'sertifikat', 'Akreditasi internasional ASIIN prodi Matematika, Fisika, Kimia, dan Biologi sampai 2030', null, '2026-07-14', '2030-09-30', 'https://uin-suka.ac.id/en/show/berita/3034-1/four-fst-study-programs-at-uin-sunan-kalijaga-secure-international-asiin-accreditation-through-2030'],
            [4, 'sertifikat', 'Akreditasi internasional FIBAA prodi Studi Agama-Agama', null, '2023-03-01', null, 'https://saa.uin-suka.ac.id/en/liputan/detail/2466/sitemap.html'],
            [39, 'lainnya', 'Webometrics Juli 2025: peringkat pertama PTKIN se-Indonesia', null, '2025-07-20', null, 'https://www.kompas.tv/pendidikan/608515/uin-sunan-kalijaga-jadi-kampus-terbaik-di-kalangan-ptkin-versi-webometrics-2025'],
            [39, 'lainnya', 'Rektor UIN Sunan Kalijaga terpilih sebagai Presiden Asian Islamic Universities Association (AIUA) 2026-2028', null, '2026-06-20', null, 'https://uin-suka.ac.id/en/show/berita/2827-1/uin-sunan-kalijaga-rector-prof-noorhaidi-hasan-elected-president-of-aiua-20262028-to-lead-network-of-islamic-universities-across-asia'],
            [36, 'kebijakan', 'Visi UIN Sunan Kalijaga: Unggul dan Terkemuka dalam Pemaduan dan Pengembangan Keislaman dan Keilmuan bagi Peradaban', null, '2024-09-18', null, 'https://uin-suka.ac.id/'],
        ];
    }

    protected function reconciliationNotes(): array
    {
        return [
            'MHS_AKTIF' => 'Selisih karena 286 mahasiswa Pascasarjana angkatan 2026 belum terlapor di PDDikti.',
            'LULUSAN_TS' => 'Lulusan wisuda periode IV (Agustus 2026) belum seluruhnya dilaporkan.',
        ];
    }

    protected function governanceProfile(): array
    {
        $base = parent::governanceProfile();

        return [
            'decisions' => [
                ['Penetapan Tim Reakreditasi Institusi 2028', '-90 days', 'rapat_pimpinan', 'Rektor menetapkan Tim Reakreditasi Institusi yang diketuai Ketua LPM dengan anggota para wakil dekan, kepala biro, dan kepala lembaga.'],
                ['Penetapan jalur akreditasi: Unggul', '-80 days', 'senat', 'Senat Universitas menyetujui jalur Unggul berdasarkan simulasi skor IAPT 4.1 varian PTN akademik.'],
                ['Dukungan anggaran BLU untuk reakreditasi', '-70 days', 'rapat_pimpinan', 'Anggaran penyusunan LED, LKPT, dan simulasi asesmen lapangan dimasukkan dalam RBA BLU 2027.'],
                ['Satu data PDDikti dan PTIPD', '-55 days', 'rapat_pimpinan', 'PTIPD dan Biro AAK menyatukan pelaporan PDDikti seluruh fakultas dan Pascasarjana.'],
                ['Klinik penulisan LED setiap Kamis', '-40 days', 'taskforce', 'Klinik penulisan dengan reviewer dan asesor internal untuk seluruh PIC.'],
                ['Penguatan bukti dampak integrasi-interkoneksi', '-25 days', 'rapat_pimpinan', 'LPPM mendokumentasikan pemanfaatan hasil riset dan PkM oleh Kementerian Agama, madrasah, dan pesantren mitra.'],
                ['Tindak lanjut hasil AMI 2026', '-12 days', 'rtm', 'RTM menetapkan 7 tindakan koreksi dan 4 tindakan pencegahan.'],
                ['Simulasi asesmen lapangan kedua', '-4 days', 'taskforce', 'Simulasi kedua dijadwalkan Februari 2027 di Gedung Rektorat dan Convention Hall.'],
            ],
            'decision_owners' => ['pic.akademik', 'pic.keuangan', 'pic.tik', 'pic.lpm', 'pic.lppm', 'pic.lpm'],
            'tasks' => [
                ['Lengkapi notulen RTM fakultas 2023-2025', 'lpm'], ['Unggah SK Gugus Penjaminan Mutu tiap fakultas', 'lpm'], ['Selaraskan manual SPMI dengan standar ASIIN dan FIBAA', 'lpm'],
                ['Rekap prestasi mahasiswa tingkat nasional dan internasional TS', 'kemahasiswaan'], ['Analisis tren mahasiswa baru prodi keagamaan dan umum', 'akademik'], ['Laporan evaluasi kurikulum OBE dan MBKM', 'akademik'],
                ['Validasi jabatan akademik dosen di SISTER', 'sdm'], ['Perbarui katalog layanan PTIPD', 'tik'], ['Unggah laporan tracer study 2025', 'kemahasiswaan'],
                ['Peta jalan riset 2025-2029 (integrasi-interkoneksi)', 'lppm'], ['Kumpulkan surat pemanfaatan hasil riset dari mitra', 'lppm'], ['Laporan PkM pesantren dan madrasah binaan', 'lppm'],
                ['Perbarui SOP Satgas PPK', 'satgas'], ['Rekap beasiswa KIP Kuliah dan afirmasi difabel', 'kemahasiswaan'], ['Susun RBA BLU 2027', 'keuangan'],
                ['Laporan keuangan BLU audited 2025 dan opini KAP', 'keuangan'], ['Dokumentasi kerja sama luar negeri aktif', 'kerjasama'], ['Rekap mobilitas dosen dan mahasiswa internasional', 'kerjasama'],
                ['Laporan capaian IKU dan pemeringkatan 2026', 'perencanaan'], ['Lengkapi dokumen K3 dan aksesibilitas difabel', 'sarpras'], ['Laporan evaluasi pembelajaran daring', 'akademik'],
                ['Sisipkan pendidikan antikorupsi pada RPS', 'akademik'], ['Rekap sertifikasi kompetensi lulusan', 'akademik'], ['Validasi survei kepuasan tendik 2026', 'lpm'],
                ['Perbaiki tautan bukti yang rusak', 'tik'], ['Monev hibah riset internal 2026', 'lppm'], ['Laporan capaian diferensiasi misi', 'perencanaan'],
                ['Laporan SPI atas pengendalian internal 2025', 'spi'], ['Laporan layanan konseling dan karier', 'kemahasiswaan'], ['Perbarui pedoman penerimaan mahasiswa inklusif', 'kemahasiswaan'],
            ],
            'risks' => [
                ['Prodi Arsitektur: peringkat Baik dan dosen tetap baru 6 orang', 3, 4, 'pic.akademik', 'Rekrutmen 3 dosen tetap dan pengajuan reakreditasi ke LAM Teknik pada 2027.'],
                ['Selisih data mahasiswa Pascasarjana dengan PDDikti', 3, 4, 'pic.tik', 'Rekonsiliasi mingguan sebelum data TS dikunci.'],
                ['Bukti pemanfaatan hasil riset oleh mitra belum merata antarfakultas', 3, 3, 'pic.lppm', 'Kumpulkan surat pemanfaatan dan studi kasus dampak.'],
                ['Analisis capaian kelulusan belum dipilah per kelompok prodi', 2, 4, 'pic.akademik', 'Dasbor kohort per prodi dan analisis akar masalah.'],
                ['Proporsi guru besar di fakultas umum masih rendah', 3, 2, 'pic.sdm', 'Program percepatan guru besar dan pendampingan publikasi Q1.'],
                ['Respons survei mitra luar negeri rendah', 2, 3, 'pic.lpm', 'Survei terintegrasi dengan pertemuan mitra AIUA.'],
                ['Bukti kerja sama luar negeri kedaluwarsa', 2, 3, 'pic.kerjasama', 'Perpanjangan perjanjian dan dokumentasi implementasi.'],
                ['Gangguan layanan daring saat masa ujian', 1, 4, 'pic.tik', 'Kapasitas cadangan dan uji beban sebelum ujian.'],
                ['Pergantian pejabat fakultas', 2, 2, 'manajer', 'Dokumentasi peran dan pendamping cadangan setiap PIC.'],
                ['Kemampuan bahasa asing lulusan dinilai perlu ditingkatkan', 2, 2, 'pic.kemahasiswaan', 'Sertifikasi TOEFL/TOAFL dan kelas bahasa intensif.'],
            ],
            'standards' => $base['standards'],
            'standard_statement' => ' UIN Sunan Kalijaga Yogyakarta melampaui SN Dikti sesuai visi unggul dan terkemuka dalam pemaduan dan pengembangan keislaman dan keilmuan bagi peradaban.',
            'audits' => [
                ['name' => 'AMI Siklus 2025', 'cycle_year' => 2025, 'audit_date' => '2025-11-10', 'lead_auditor' => 'Prof. Dr. Sutrisno Hadi, M.Ag.', 'state' => 'selesai', 'summary' => '42 prodi, 9 fakultas/Pascasarjana, dan 14 unit diaudit; 2 temuan mayor, 11 minor, 8 observasi.'],
                ['name' => 'AMI Siklus 2026', 'cycle_year' => 2026, 'audit_date' => now()->subDays(20), 'lead_auditor' => 'Prof. Dr. Sutrisno Hadi, M.Ag.', 'state' => 'pelaporan', 'summary' => 'Fokus pada kesiapan jalur Unggul IAPT 4.1, analisis capaian kelulusan, dan dampak riset.'],
            ],
            'findings' => [
                [0, 'mayor', 'Bukti RTM Fakultas Dakwah dan Komunikasi serta FISHUM siklus 2023 belum lengkap.', 2, 'lpm', 'closed', 'ami'],
                [0, 'minor', 'RPS belum memuat sisipan pendidikan antikorupsi pada 4 prodi.', 13, 'akademik', 'closed', 'ami'],
                [0, 'minor', 'Data jabatan akademik di SISTER belum selaras dengan data kepegawaian.', 11, 'sdm', 'closed', 'ami'],
                [1, 'minor', 'Bukti pemanfaatan hasil riset oleh mitra belum mencakup seluruh fakultas.', 19, 'lppm', 'capa', 'ami'],
                [1, 'minor', 'Survei kepuasan mitra belum mencakup mitra luar negeri.', 34, 'lpm', 'capa', 'ami'],
                [1, 'observasi', 'Beberapa tautan bukti di laman fakultas tidak dapat diakses.', 26, 'tik', 'open', 'ami'],
                [1, 'mayor', 'Analisis capaian kelulusan belum memisahkan prodi keagamaan dan prodi umum.', 12, 'akademik', 'capa', 'ami'],
                [null, 'observasi', 'Narasumber mahasiswa belum memahami kanal pelaporan Satgas PPK.', 28, 'satgas', 'capa', 'simulasi'],
                [null, 'peluang', 'Integrasi-interkoneksi keilmuan dan akreditasi ASIIN/FIBAA dapat dijadikan narasi utama kriteria 4.', 39, 'kerjasama', 'open', 'simulasi'],
                [null, 'minor', 'Tindak lanjut survei kepuasan layanan akademik belum terdokumentasi.', 34, 'akademik', 'verification', 'survei'],
            ],
            'reviews' => [
                ['meeting_date' => '2025-12-18', 'title' => 'RTM Universitas Semester Gasal 2025/2026', 'chair' => 'Rektor', 'minutes' => 'Membahas hasil AMI 2025, survei kepuasan, dan capaian IKU.', 'outcomes' => 'Penetapan 6 tindakan koreksi; satu data PDDikti.'],
                ['meeting_date' => '2026-06-25', 'title' => 'RTM Universitas Semester Genap 2025/2026', 'chair' => 'Rektor', 'minutes' => 'Membahas peralihan ke IAPT 4.1 dan simulasi skor.', 'outcomes' => 'Pemilihan jalur Unggul; persiapan dini reakreditasi; klinik LED.'],
                ['meeting_date' => now()->subDays(12)->toDateString(), 'title' => 'RTM Khusus Kesiapan Reakreditasi Institusi', 'chair' => 'Wakil Rektor Bidang Akademik dan Pengembangan Lembaga', 'minutes' => 'Membahas hasil AMI 2026 dan analisis capaian kelulusan.', 'outcomes' => 'Target AKI 85 sebelum simulasi asesmen lapangan; penguatan 12B, 19B, dan 34B.'],
            ],
        ];
    }

    protected function siteVisitProfile(): array
    {
        $base = parent::siteVisitProfile();

        return [
            'mock' => [
                'title' => 'Simulasi Asesmen Lapangan I', 'starts_on' => now()->subDays(26), 'ends_on' => now()->subDays(25),
                'mode' => 'luring', 'assessors' => 'Prof. Dr. Sutrisno Hadi, M.Ag.; asesor eksternal dari PTKIN mitra', 'state' => 'selesai',
                'document_room' => 'Ruang Sidang Gedung Rektorat Lama, Kampus UIN Sunan Kalijaga', 'minutes' => 'Simulasi pertama berjalan baik; 5 rekomendasi perbaikan untuk jalur Unggul.',
            ],
            'recommendations' => [
                [12, 'Pisahkan analisis capaian kelulusan prodi keagamaan dan prodi umum, lengkap dengan akar masalah.', 'task'],
                [19, 'Lengkapi bukti pemanfaatan hasil riset oleh Kementerian Agama, madrasah, dan pesantren mitra.', 'task'],
                [34, 'Tunjukkan tindak lanjut survei kepuasan layanan akademik dan mitra luar negeri.', 'capa'],
                [28, 'Perkuat sosialisasi kanal pelaporan Satgas PPK.', null],
                [39, 'Jadikan integrasi-interkoneksi, ASIIN, FIBAA, dan kepemimpinan AIUA sebagai bukti keunggulan.', null],
            ],
            'visit' => [
                'title' => 'Asesmen Lapangan BAN-PT', 'starts_on' => '2028-02-14', 'ends_on' => '2028-02-15',
                'mode' => 'luring', 'assessors' => 'Menunggu penugasan BAN-PT', 'rehearsal_on' => '2028-02-07', 'state' => 'rencana',
                'document_room' => 'Ruang Sidang Gedung Rektorat Lama, Kampus UIN Sunan Kalijaga',
            ],
            'visit_date' => '2028-02-14',
            'sessions' => [
                ['08:00', '09:00', 'pimpinan', 'Pembukaan dan presentasi Rektor', 'Convention Hall', 'Rektor'],
                ['09:00', '10:30', 'pimpinan', 'Wawancara pimpinan, senat, dan Dewan Pengawas', 'Ruang Sidang Rektorat', 'Ketua LPM'],
                ['10:30', '11:30', 'dosen', 'Wawancara dosen', 'Ruang Sidang FITK', 'Dekan FITK'],
                ['11:30', '12:30', 'tendik', 'Wawancara tenaga kependidikan', 'Ruang Rapat Biro AAK', 'Kepala Biro AAK'],
                ['13:30', '14:30', 'mahasiswa', 'Wawancara mahasiswa', 'Gedung Student Center', 'Kepala Pusat Layanan Kemahasiswaan'],
                ['14:30', '15:30', 'lulusan', 'Wawancara lulusan dan pengguna lulusan', 'Pusat Karier', 'Kepala Pusat Layanan Kemahasiswaan'],
                ['15:30', '17:00', 'observasi', 'Observasi laboratorium terpadu, perpustakaan, dan layanan difabel', 'Laboratorium Terpadu dan UPT Perpustakaan', 'Kepala Bagian Umum dan Sarpras'],
            ],
            'interviewees' => [
                ['pimpinan', 'Prof. Noorhaidi Hasan, S.Ag., M.A., M.Phil., Ph.D.', 'Rektor', true], ['pimpinan', 'Dewan Pengawas BLU', 'Dewan Pengawas', true],
                ['pimpinan', 'Prof. Dr. Siti Aminah, M.Ag.', 'Wakil Rektor Bidang Akademik', true], ['dosen', 'Prof. Dr. Sri Sumarni, M.Pd.', 'Dekan FITK', true],
                ['dosen', 'Dr. Lukman Hakim, M.Si.', 'Dosen Kimia', false], ['dosen', 'Dr. Khurul Wardati, M.Si.', 'Dekan FST', true],
                ['tendik', 'Wahyu Widodo, S.E.', 'Staf Keuangan BLU', true], ['tendik', 'Anisa Rahma, A.Md.', 'Laboran Laboratorium Terpadu', false],
                ['mahasiswa', 'Muhammad Iqbal', 'Ketua Dewan Eksekutif Mahasiswa', true], ['mahasiswa', 'Zahra Aulia', 'Mahasiswa Ilmu Al-Qur\'an dan Tafsir 2023', false],
                ['lulusan', 'Ahmad Fauzi, S.H.', 'Alumni Hukum Keluarga Islam, hakim Pengadilan Agama', true], ['pengguna', 'Kepala Kantor Kementerian Agama mitra', 'Pengguna lulusan', false],
            ],
            'questions' => array_map(fn ($q) => [$q[0], $q[1], str_replace('UNM', 'UIN Suka', $q[2]), match ($q[0]) {
                36 => 'Visi integrasi-interkoneksi keislaman dan keilmuan; IKU per fakultas.',
                39 => 'ASIIN (Matematika, Fisika, Kimia, Biologi), FIBAA (Studi Agama-Agama), Webometrics PTKIN terbaik, Presiden AIUA.',
                35 => 'Opini WTP 2021-2025 dari KAP atas laporan keuangan BLU.',
                default => $q[3],
            }], $base['questions']),
        ];
    }
}
