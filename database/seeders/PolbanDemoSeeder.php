<?php

namespace Database\Seeders;

use App\Enums\Track;

/**
 * Data demo Politeknik Negeri Bandung (POLBAN, PTN vokasi) yang menyiapkan reakreditasi institusi
 * jalur Unggul dengan IAPT 4.1 (varian 3e, PTN Vokasi).
 *
 * Diambil dari polban.ac.id (Oktober 2026): nama, alamat, visi, 10 jurusan dan 41 program studi
 * (D3, Sarjana Terapan, Magister Terapan), riwayat peringkat akreditasi institusi dan prodi, serta
 * capaian UI GreenMetric. Kode PT dan nama KAP adalah ilustrasi. Masa jabatan Direktur 2022-2026 berakhir Oktober 2026, sehingga nama
 * pimpinan, pejabat, dan dosen sengaja dibuat fiktif. Selebihnya (kode dan tanggal SK prodi, angka
 * mahasiswa, publikasi, survei, temuan, tugas, risiko, narasi) adalah data ilustrasi untuk
 * demonstrasi, bukan data resmi POLBAN.
 */
class PolbanDemoSeeder extends DemoSeeder
{
    public const SLUG = 'polban';

    protected string $emailDomain = 'polban.akredia.test';

    protected string $short = 'POLBAN';

    protected string $leaderTitle = 'Direktur';

    protected bool $guestAccess = true;

    protected function institutionAttributes(): array
    {
        return [
            'name' => 'Politeknik Negeri Bandung',
            'slug' => static::SLUG,
            'code' => '005012',
            'type' => 'ptn_vokasi',
            'legal_entity' => 'PTN Badan Layanan Umum (Kemdiktisaintek)',
            'sk_number' => 'Kepmendikbud Tahun 1997 (Politeknik ITB menjadi Politeknik Negeri Bandung)',
            'sk_date' => '1997-09-01',
            'first_intake_year' => 1982,
            'city' => 'Bandung Barat',
            'address' => 'Jl. Gegerkalong Hilir, Ciwaruga, Kec. Parongpong, Kabupaten Bandung Barat, Jawa Barat 40559',
            'website' => 'https://www.polban.ac.id',
            'email' => 'polban@polban.ac.id',
            'vision' => 'Menjadi institusi yang unggul dan terdepan dalam Pendidikan Vokasi yang inovatif dan adaptif terhadap perkembangan ilmu pengetahuan dan teknologi terapan.',
            'mission_focus' => 'Pendidikan vokasi rekayasa dan tata niaga yang menghasilkan lulusan kompeten, berjiwa kewirausahaan, dan berwawasan lingkungan; riset terapan bersama industri; pengabdian berbasis teknologi tepat guna.',
            'last_accreditation_rank' => 'Unggul',
            'last_accreditation_sk' => 'SK BAN-PT Akreditasi Institusi Peringkat Unggul (2023)',
            'last_accreditation_until' => '2026-12-20',
            'is_demo' => true,
        ];
    }

    // ------------------------------------------------------------------ organisasi

    protected function seedOrganisation(): void
    {
        $units = [
            'lpm' => ['P2MPP', 'Pusat Penjaminan Mutu dan Pengembangan Pembelajaran', 'lembaga', 'Dr. Ir. Siti Nurhayati, M.T.'],
            'lppm' => ['P3M', 'Pusat Penelitian dan Pengabdian kepada Masyarakat', 'lembaga', 'Dr. Eng. Rudi Kurniawan, M.T.'],
            'spi' => ['SPI', 'Satuan Pengawas Internal', 'lembaga', 'Dra. Nenden Rohaeni, M.Ak.'],
            'akademik' => ['BAKAD', 'Bagian Akademik', 'biro', 'Asep Supriatna, S.T., M.M.'],
            'kemahasiswaan' => ['BKM', 'Bagian Kemahasiswaan dan Alumni', 'biro', 'Dedi Mulyadi, S.Pd., M.Pd.'],
            'keuangan' => ['BKEU', 'Bagian Keuangan dan BLU', 'biro', 'Rina Marlina, S.E., M.Ak.'],
            'sdm' => ['BUK', 'Bagian Umum dan Kepegawaian', 'biro', 'Iwan Setiawan, S.H., M.M.'],
            'sarpras' => ['UPT-PP', 'UPT Pemeliharaan dan Perbaikan', 'upt', 'Ir. Agus Gunawan, M.T.'],
            'perencanaan' => ['BPSI', 'Bagian Perencanaan dan Sistem Informasi', 'biro', 'Dr. Yeni Herdiyeni, S.Kom., M.T.'],
            'kerjasama' => ['UKH', 'Unit Kerja Sama dan Hubungan Masyarakat', 'biro', 'Dr. Dian Permana, S.E., M.M.'],
            'sekretariat' => ['SEKDIR', 'Sekretariat Direktur dan Tata Laksana', 'biro', 'Lilis Suryani, S.Sos., M.Si.'],
            'tik' => ['UPT-TIK', 'UPT Teknologi Informasi dan Komunikasi', 'upt', 'Ridwan Saputra, S.T., M.T.'],
            'perpustakaan' => ['UPT-PUS', 'UPT Perpustakaan', 'upt', 'Euis Kartika, S.IP., M.Hum.'],
            'satgas' => ['SATGAS', 'Satgas Pencegahan dan Penanganan Kekerasan', 'satgas', 'Dr. Neneng Sri Wahyuni, M.Psi., Psikolog'],
        ];

        $faculties = [
            'sipil' => ['JTS', 'Jurusan Teknik Sipil', 'Dr. Ir. Hendra Setiawan, M.T.'],
            'mesin' => ['JTM', 'Jurusan Teknik Mesin', 'Dr. Ir. Bambang Purwanto, M.T.'],
            'refri' => ['JTRTU', 'Jurusan Teknik Refrigerasi dan Tata Udara', 'Ir. Dadang Suherman, M.T.'],
            'konversi' => ['JTKE', 'Jurusan Teknik Konversi Energi', 'Dr. Ir. Yusuf Hidayat, M.T.'],
            'elektro' => ['JTE', 'Jurusan Teknik Elektro', 'Dr. Ir. Ratna Sari Dewi, M.T.'],
            'kimia' => ['JTK', 'Jurusan Teknik Kimia', 'Dr. Ir. Lia Amalia, M.T.'],
            'jtk' => ['JTKI', 'Jurusan Teknik Komputer dan Informatika', 'Dr. Andri Firmansyah, S.Kom., M.T.'],
            'akt' => ['JAK', 'Jurusan Akuntansi', 'Dr. Nurhayati, S.E., M.Ak., CA'],
            'an' => ['JAN', 'Jurusan Administrasi Niaga', 'Dr. Tatang Sutisna, S.E., M.M.'],
            'bing' => ['JBI', 'Jurusan Bahasa Inggris', 'Dr. Ani Rosliani, M.Pd.'],
        ];

        // [kunci, jurusan, kode prodi, nama, jenjang, dibuka, lembaga, peringkat, berlaku s.d., dosen tetap, mahasiswa aktif TS]
        // Peringkat mengikuti laman akreditasi Polban bila tersedia; kode dan tanggal SK adalah ilustrasi.
        $programs = [
            ['d3tks', 'sipil', '22401', 'Teknik Konstruksi Sipil', 'D3', 1982, 'lam_teknik', 'unggul', '2031-04-30', 14, 290],
            ['d3tkg', 'sipil', '22402', 'Teknik Konstruksi Gedung', 'D3', 1982, 'banpt', 'unggul', '2028-03-14', 13, 280],
            ['d3tm', 'mesin', '21401', 'Teknik Mesin', 'D3', 1982, 'lam_teknik', 'unggul', '2031-02-20', 16, 300],
            ['d3aero', 'mesin', '21402', 'Teknik Aeronautika', 'D3', 1982, 'banpt', 'baik_sekali', '2027-05-10', 12, 240],
            ['d3tptu', 'refri', '21403', 'Teknik Pendingin dan Tata Udara', 'D3', 1982, 'banpt', 'unggul', '2028-06-12', 12, 250],
            ['d3tke', 'konversi', '21404', 'Teknik Konversi Energi', 'D3', 1982, 'lam_teknik', 'baik_sekali', '2029-08-20', 13, 260],
            ['d3el', 'elektro', '20401', 'Teknik Elektronika', 'D3', 1982, 'banpt', 'unggul', '2028-09-01', 14, 280],
            ['d3tl', 'elektro', '20402', 'Teknik Listrik', 'D3', 1982, 'lam_teknik', 'baik_sekali', '2030-04-15', 13, 270],
            ['d3tel', 'elektro', '20403', 'Teknik Telekomunikasi', 'D3', 1982, 'lam_teknik', 'baik_sekali', '2030-06-30', 13, 260],
            ['d3tk', 'kimia', '24401', 'Teknik Kimia', 'D3', 1982, 'banpt', 'unggul', '2027-08-31', 14, 270],
            ['d3ak', 'kimia', '24402', 'Analis Kimia', 'D3', 2002, 'banpt', 'baik_sekali', '2027-03-15', 11, 230],
            ['d3ti', 'jtk', '55401', 'Teknik Informatika', 'D3', 1999, 'lam_infokom', 'unggul', '2028-07-22', 14, 320],
            ['d3akt', 'akt', '62401', 'Akuntansi', 'D3', 1982, 'lam_emba', 'baik_sekali', '2028-05-18', 14, 300],
            ['d3kp', 'akt', '61401', 'Keuangan dan Perbankan', 'D3', 1997, 'lam_emba', 'baik_sekali', '2030-03-01', 12, 270],
            ['d3ab', 'an', '63411', 'Administrasi Bisnis', 'D3', 1982, 'lam_emba', 'baik_sekali', '2028-10-11', 13, 290],
            ['d3mp', 'an', '63412', 'Manajemen Pemasaran', 'D3', 2004, 'lam_emba', 'unggul', '2029-07-01', 12, 280],
            ['d3upw', 'an', '93401', 'Usaha Perjalanan Wisata', 'D3', 2006, 'banpt', 'unggul', '2026-12-15', 10, 230],
            ['d3bing', 'bing', '79401', 'Bahasa Inggris', 'D3', 1990, 'banpt', 'baik_sekali', '2027-04-20', 12, 250],
            ['d4tpjj', 'sipil', '22301', 'Teknik Perancangan Jalan dan Jembatan', 'D4', 2007, 'banpt', 'unggul', '2027-09-30', 12, 230],
            ['d4tppg', 'sipil', '22302', 'Teknik Perawatan dan Perbaikan Gedung', 'D4', 2007, 'banpt', 'baik_sekali', '2027-10-15', 11, 210],
            ['d4tpkm', 'mesin', '21301', 'Teknik Perancangan dan Konstruksi Mesin', 'D4', 2008, 'banpt', 'baik_sekali', '2027-06-25', 12, 220],
            ['d4pm', 'mesin', '21302', 'Proses Manufaktur', 'D4', 2013, 'lam_teknik', 'baik_sekali', '2028-08-10', 11, 200],
            ['d4tptu', 'refri', '21303', 'Teknik Pendingin dan Tata Udara', 'D4', 2008, 'lam_teknik', 'baik_sekali', '2029-05-30', 11, 200],
            ['d4tptl', 'konversi', '21304', 'Teknologi Pembangkit Tenaga Listrik', 'D4', 2007, 'lam_teknik', 'baik_sekali', '2028-07-15', 11, 210],
            ['d4tkse', 'konversi', '21305', 'Teknik Konservasi Energi', 'D4', 2012, 'lam_teknik', 'baik_sekali', '2028-04-01', 10, 190],
            ['d4el', 'elektro', '20301', 'Teknik Elektronika', 'D4', 2007, 'banpt', 'unggul', '2027-11-20', 12, 230],
            ['d4tel', 'elektro', '20302', 'Teknik Telekomunikasi', 'D4', 2007, 'banpt', 'baik_sekali', '2027-08-05', 11, 210],
            ['d4toi', 'elektro', '20303', 'Teknik Otomasi Industri', 'D4', 2011, 'lam_teknik', 'baik_sekali', '2026-12-20', 11, 220],
            ['d4tkpb', 'kimia', '24301', 'Teknik Kimia Produksi Bersih', 'D4', 2007, 'banpt', 'baik_sekali', '2027-07-12', 11, 200],
            ['d4ti', 'jtk', '55301', 'Teknik Informatika', 'D4', 2007, 'lam_infokom', 'unggul', '2030-02-28', 14, 300],
            ['d4amp', 'akt', '62301', 'Akuntansi Manajemen Pemerintahan', 'D4', 2007, 'lam_emba', 'unggul', '2031-01-31', 12, 250],
            ['d4akt', 'akt', '62302', 'Akuntansi', 'D4', 2013, 'lam_emba', 'baik_sekali', '2028-06-05', 11, 230],
            ['d4ks', 'akt', '61301', 'Keuangan Syariah', 'D4', 2007, 'lam_emba', 'unggul', '2031-02-15', 11, 230],
            ['d4ma', 'an', '61302', 'Manajemen Aset', 'D4', 2007, 'lam_emba', 'unggul', '2026-11-30', 11, 220],
            ['d4ab', 'an', '63311', 'Administrasi Bisnis', 'D4', 2011, 'lam_emba', 'unggul', '2027-09-01', 11, 240],
            ['d4mp', 'an', '63312', 'Manajemen Pemasaran', 'D4', 2012, 'lam_emba', 'baik_sekali', '2027-12-10', 10, 220],
            ['d4dp', 'an', '93301', 'Destinasi Pariwisata', 'D4', 2018, 'banpt', 'baik', '2031-03-20', 9, 180],
            ['d4bing', 'bing', '79301', 'Bahasa Inggris untuk Komunikasi Bisnis dan Profesional', 'D4', 2024, 'banpt', 'terakreditasi', '2028-03-01', 8, 140],
            ['s2ri', 'sipil', '22101', 'Rekayasa Infrastruktur (Magister Terapan)', 'S2', 2014, 'lam_teknik', 'baik_sekali', '2030-05-31', 6, 60],
            ['s2kps', 'akt', '61101', 'Keuangan dan Perbankan Syariah (Magister Terapan)', 'S2', 2017, 'lam_emba', 'baik_sekali', '2029-06-30', 6, 55],
            ['s2pit', 'an', '61102', 'Pemasaran, Inovasi, dan Teknologi (Magister Terapan)', 'S2', 2021, 'lam_emba', 'baik', '2027-02-28', 5, 40],
        ];

        $this->createOrganisation(['DIR', 'Direktorat', 'Dr. Ir. Ahmad Rusdiana, M.T.'], $units, $faculties, $programs);
    }

    protected function people(): array
    {
        return [
            ['admin', 'admin.pt', 'Ridwan Saputra, S.T., M.T.', 'admin', 'tik', 'Kepala UPT TIK'],
            ['rektor', 'direktur', 'Dr. Ir. Ahmad Rusdiana, M.T.', 'pimpinan', 'rektorat', 'Direktur'],
            ['wr1', 'wadir1', 'Dr. Ir. Teti Sumiati, M.T.', 'pimpinan', 'rektorat', 'Wakil Direktur Bidang Akademik'],
            ['wr2', 'wadir2', 'Dr. Eddy Supriadi, S.E., M.Si.', 'pimpinan', 'rektorat', 'Wakil Direktur Bidang Perencanaan, Keuangan, dan Umum'],
            ['wr3', 'wadir3', 'Dr. Ir. Gugun Gumilar, M.T.', 'pimpinan', 'rektorat', 'Wakil Direktur Bidang Kemahasiswaan'],
            ['wr4', 'wadir4', 'Dr. Fitria Rahmawati, S.T., M.T.', 'pimpinan', 'rektorat', 'Wakil Direktur Bidang Kerja Sama dan Hubungan Masyarakat'],
            ['manajer', 'manajer', 'Dr. Ir. Siti Nurhayati, M.T.', 'manajer', 'lpm', 'Kepala P2MPP / Ketua Tim Reakreditasi Institusi'],
            ['reviewer', 'reviewer', 'Dr. Ir. Hari Santoso, M.T.', 'reviewer', 'lpm', 'Reviewer LED'],
            ['reviewer2', 'reviewer2', 'Dr. Mira Sundari, S.E., M.Ak.', 'reviewer', 'akt', 'Reviewer LED'],
            ['asesor', 'asesor', 'Dr. Ir. Wahyu Hidayat, M.Eng.', 'asesor', 'lpm', 'Asesor internal (asesor BAN-PT)'],
            ['pic.lpm', 'pic.lpm', 'Nita Kurniasih, S.T., M.T.', 'pic', 'lpm', 'Koordinator Akreditasi P2MPP'],
            ['pic.akademik', 'pic.akademik', 'Asep Supriatna, S.T., M.M.', 'pic', 'akademik', 'Kepala Bagian Akademik'],
            ['pic.sdm', 'pic.sdm', 'Iwan Setiawan, S.H., M.M.', 'pic', 'sdm', 'Kepala Bagian Umum dan Kepegawaian'],
            ['pic.keuangan', 'pic.keuangan', 'Rina Marlina, S.E., M.Ak.', 'pic', 'keuangan', 'Kepala Bagian Keuangan dan BLU'],
            ['pic.kemahasiswaan', 'pic.kemahasiswaan', 'Dedi Mulyadi, S.Pd., M.Pd.', 'pic', 'kemahasiswaan', 'Kepala Bagian Kemahasiswaan dan Alumni'],
            ['pic.sarpras', 'pic.sarpras', 'Ir. Agus Gunawan, M.T.', 'pic', 'sarpras', 'Kepala UPT Pemeliharaan dan Perbaikan'],
            ['pic.perencanaan', 'pic.perencanaan', 'Dr. Yeni Herdiyeni, S.Kom., M.T.', 'pic', 'perencanaan', 'Kepala Bagian Perencanaan dan Sistem Informasi'],
            ['pic.kerjasama', 'pic.kerjasama', 'Dr. Dian Permana, S.E., M.M.', 'pic', 'kerjasama', 'Kepala Unit Kerja Sama dan Humas'],
            ['pic.sekretariat', 'pic.sekretariat', 'Lilis Suryani, S.Sos., M.Si.', 'pic', 'sekretariat', 'Kepala Sekretariat Direktur'],
            ['pic.spi', 'pic.spi', 'Dra. Nenden Rohaeni, M.Ak.', 'pic', 'spi', 'Ketua SPI'],
            ['pic.lppm', 'pic.lppm', 'Dr. Eng. Rudi Kurniawan, M.T.', 'pic', 'lppm', 'Kepala P3M'],
            ['pic.tik', 'pic.tik', 'Fajar Nugraha, S.Tr.Kom., M.T.', 'pic', 'tik', 'Koordinator Pangkalan Data dan PDDikti'],
            ['pic.satgas', 'pic.satgas', 'Dr. Neneng Sri Wahyuni, M.Psi., Psikolog', 'pic', 'satgas', 'Ketua Satgas PPK'],
            ['data.akt', 'data.akt', 'Yuliani Fitri, S.Tr.Ak.', 'pemilik_data', 'akt', 'Operator Data Jurusan Akuntansi'],
            ['pengamat', 'pengamat', 'Ketua Senat Politeknik Negeri Bandung', 'pengamat', 'rektorat', 'Senat'],
        ];
    }

    protected function performanceProfile(): array
    {
        return [
            'nidn_start' => 410500001,
            'doctor_pct' => 24,
            'professor_pct' => 2,
            'events' => ['Kontes Robot Indonesia (KRI)', 'Kontes Mobil Hemat Energi (KMHE)', 'Kompetisi Jembatan Indonesia (KJI)', 'Kompetisi Bangunan Gedung Indonesia (KBGI)', 'Gemastik', 'PIMNAS', 'Lomba Kompetensi Mahasiswa Vokasi', 'Kontes Kapal Cepat Tak Berawak Nasional', 'Kompetisi Muatan Roket dan Satelit Indonesia', 'Olimpiade Akuntansi Vokasi', 'National English Debate Championship', 'Lomba Inovasi Energi Terbarukan', 'Lomba Desain Destinasi Wisata', 'Kompetisi Bisnis Mahasiswa Indonesia'],
            'achievement_base' => 52,
            'topics' => ['otomasi industri', 'Internet of Things industri', 'sistem refrigerasi ramah lingkungan', 'pembangkit listrik tenaga surya', 'konservasi energi gedung', 'beton pracetak', 'perawatan jembatan', 'perancangan mesin tepat guna', 'manufaktur aditif', 'pengolahan limbah industri', 'kimia produksi bersih', 'jaringan 5G', 'aplikasi cerdas untuk UMKM', 'akuntansi sektor publik', 'keuangan syariah', 'pemasaran digital UMKM', 'manajemen aset daerah', 'desa wisata Bandung Barat', 'komunikasi bisnis bahasa Inggris', 'drone untuk pertanian', 'kendaraan listrik', 'mutu air sungai Citarum'],
            'places' => ['Jawa Barat', 'Kabupaten Bandung Barat', 'Indonesia', 'kawasan industri Jawa Barat', 'UMKM binaan', 'politeknik negeri'],
            'publication_types' => ['jurnal_intl_bereputasi' => 14, 'jurnal_intl' => 10, 'jurnal_nas_terakreditasi' => 34, 'jurnal_nas' => 14, 'seminar_intl' => 18, 'seminar_nas' => 7, 'media_massa' => 3],
            'publication_base' => 240,
            'publication_growth' => 42,
            'venues' => ['Prosiding Internasional Teknik Terapan (Scopus)', 'Jurnal Terapan Terakreditasi SINTA'],
            'ip_base' => 24,
            'ip_adopter' => 'PT Mitra Industri Jawa Barat',
            'recognitions' => ['Asesor LAM Teknik', 'Asesor kompetensi BNSP', 'Juri lomba kompetensi vokasi', 'Narasumber Ditjen Pendidikan Vokasi', 'Tenaga ahli industri mitra', 'Penghargaan inovasi teknologi tepat guna'],
            'recognition_base' => 14,
            'grant_base' => 30,
            'grant_scale' => 1,
            'auditors' => [2021 => 'KAP Ilustrasi Priangan & Rekan (audit BLU)', 2022 => 'KAP Ilustrasi Priangan & Rekan (audit BLU)', 2023 => 'KAP Ilustrasi Parahyangan & Rekan (audit BLU)', 2024 => 'KAP Ilustrasi Parahyangan & Rekan (audit BLU)', 2025 => 'KAP Ilustrasi Parahyangan & Rekan (audit BLU)'],
            'survey_population' => ['mahasiswa' => 9100, 'dosen' => 520, 'tendik' => 380, 'mitra' => 260, 'lulusan' => 2600, 'pengguna' => 340],
            'survey_open_population' => 9300,
            'tracer_graduates' => 2350,
            'tracer_growth' => 60,
            'employer_respondents' => 150,
            'employer_follow_up' => 'Perluasan sertifikasi kompetensi BNSP melalui LSP P1 Polban dan program magang industri satu semester.',
        ];
    }

    /** Pemetaan PIC mengikuti struktur organisasi Polban. */
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
            'variant' => '3e',
            'name' => 'Reakreditasi Institusi 2026 · Jalur Unggul',
            'track' => Track::Excellent,
            'target_date' => '2026-11-27',
            'notes' => 'SK peringkat Unggul periode 2023-2026 berakhir akhir 2026 (tanggal ilustrasi). Target unggah LED dan LKPT ke SAPTO 27 November 2026; waktu tersisa sempit sehingga rapat kendali dilakukan dua kali seminggu.',
            'locked_weeks' => 10,
            'eligibility_note' => 'Menunggu SK penetapan Direktur periode 2026-2030 untuk lampiran dokumen tata pamong.',
            'not_met' => ['12B', '15C', '23B', '30A'],
            // 4B (proporsi prodi Unggul) adalah syarat perlu jalur Unggul; sebagian besar prodi masih Baik Sekali.
            'not_met_excellent' => ['4B', '16A', '19B', '22A', '27B', '38B'],
            'unassessed' => ['18B', '37A'],
            'internal_disagree' => ['18C'],
            'internal_note' => 'Laporan monev hibah P3M belum memuat analisis ketercapaian luaran; dinilai belum memenuhi.',
            'due_start' => '2026-10-27',
        ];
    }

    protected function progressProfile(): array
    {
        return ['led' => ['ratified' => 6, 'reviewer_approved' => 18, 'review' => 22, 'revision' => 14, 'draft' => 26, 'empty' => 14], 'evidence_met' => 82, 'doc_valid' => 66];
    }

    protected function highlightEvidence(): array
    {
        return [
            [4, 'sertifikat', 'Sertifikat akreditasi D3 Teknik Konstruksi Gedung peringkat Unggul', '244/SK/BAN-PT/Ak.KP/D3/II/2025', '2025-02-25', '2028-03-14', 'https://www.polban.ac.id/wp-content/uploads/2025/10/2.-2025-2028-Sertifikat-Akreditasi-D3-Teknik-Konstruksi-Gedung_244-Unggul-1.pdf'],
            [4, 'sertifikat', 'Sertifikat akreditasi D4 Destinasi Pariwisata periode 2026-2031', '407/2026', '2026-03-20', '2031-03-20', 'https://www.polban.ac.id/wp-content/uploads/2026/03/2026.407-Sertifikat-Peringkat-Akreditasi-D4-Despar-2026-2031.pdf'],
            [4, 'data', 'Daftar akreditasi institusi dan 41 program studi Politeknik Negeri Bandung', null, '2026-10-01', null, 'https://www.polban.ac.id/tentang-polban/akreditasi-pt-dan-prodi/'],
            [36, 'kebijakan', 'Visi dan misi Politeknik Negeri Bandung', null, '2025-01-06', null, 'https://www.polban.ac.id/visi-misi/'],
            [39, 'lainnya', 'UI GreenMetric 2026: peringkat 3 politeknik berkelanjutan di Indonesia', null, '2026-10-15', null, 'https://www.polban.ac.id/'],
            [25, 'lainnya', 'Pembangunan Zona Integritas menuju Wilayah Birokrasi Bersih dan Melayani (WBBM)', null, '2026-06-01', null, 'https://www.polban.ac.id/'],
        ];
    }

    protected function reconciliationNotes(): array
    {
        return [
            'MHS_AKTIF' => 'Selisih karena 186 mahasiswa magang industri satu semester tercatat cuti di sistem internal.',
            'LULUSAN_TS' => 'Lulusan wisuda Oktober 2026 belum seluruhnya dilaporkan.',
        ];
    }

    protected function governanceProfile(): array
    {
        $base = parent::governanceProfile();

        return [
            'decisions' => [
                ['Penetapan Tim Reakreditasi Institusi 2026', '-72 days', 'rapat_pimpinan', 'Direktur menetapkan Tim Reakreditasi Institusi yang diketuai Kepala P2MPP dengan anggota ketua jurusan, kepala bagian, dan kepala pusat.'],
                ['Penetapan jalur akreditasi: Unggul', '-65 days', 'senat', 'Senat menyetujui jalur Unggul dengan catatan percepatan reakreditasi prodi yang masih Baik Sekali.', 'Ketua Senat'],
                ['Rapat kendali dua kali seminggu', '-50 days', 'rapat_pimpinan', 'Rapat kendali Senin dan Kamis dipimpin Wakil Direktur Bidang Akademik sampai unggah SAPTO.'],
                ['Percepatan reakreditasi prodi D4', '-42 days', 'rapat_pimpinan', 'Prodi Manajemen Aset dan Teknik Otomasi Industri mengajukan reakreditasi paling lambat November 2026.'],
                ['Klinik penulisan LED setiap Selasa', '-35 days', 'taskforce', 'Klinik penulisan dengan pendampingan reviewer untuk kriteria 2 dan 3.'],
                ['Sinkronisasi data PDDikti mahasiswa magang', '-21 days', 'rapat_pimpinan', 'UPT TIK dan Bagian Akademik memperbaiki status mahasiswa magang industri.'],
                ['Tindak lanjut hasil AMI 2026', '-12 days', 'rtm', 'RTM menetapkan 10 tindakan koreksi dan 4 tindakan pencegahan.'],
                ['Simulasi asesmen lapangan', '-4 days', 'taskforce', 'Simulasi dijadwalkan pekan kedua November 2026 di Gedung Direktorat Polban.'],
            ],
            'decision_owners' => ['pic.lpm', 'pic.akademik', 'pic.akademik', 'pic.lpm', 'pic.tik', 'pic.lpm'],
            'tasks' => [
                ['Lengkapi notulen RTM jurusan 2023-2025', 'lpm'], ['Unggah SK Gugus Kendali Mutu jurusan', 'lpm'], ['Perbarui manual SPMI sesuai Permendikbudristek 53/2023', 'lpm'],
                ['Rekap prestasi mahasiswa KRI, KMHE, dan KJI', 'kemahasiswaan'], ['Analisis tren mahasiswa baru per jalur seleksi', 'akademik'], ['Laporan evaluasi kurikulum berbasis industri', 'akademik'],
                ['Validasi jabatan akademik dosen di SISTER', 'sdm'], ['Perbarui katalog layanan UPT TIK', 'tik'], ['Unggah laporan tracer study 2025', 'kemahasiswaan'],
                ['Peta jalan riset terapan 2025-2029', 'lppm'], ['Kumpulkan bukti adopsi teknologi oleh industri mitra', 'lppm'], ['Laporan PkM teknologi tepat guna Bandung Barat', 'lppm'],
                ['Perbarui SOP Satgas PPK', 'satgas'], ['Rekap penerima KIP Kuliah dan beasiswa', 'kemahasiswaan'], ['Susun RBA BLU 2027', 'keuangan'],
                ['Laporan keuangan BLU audited 2025', 'keuangan'], ['Dokumentasi kerja sama industri dan teaching factory', 'kerjasama'], ['Rekap kerja sama luar negeri aktif', 'kerjasama'],
                ['Laporan capaian UI GreenMetric 2026', 'perencanaan'], ['Lengkapi dokumen K3 laboratorium dan bengkel', 'sarpras'], ['Laporan evaluasi pembelajaran semester genap', 'akademik'],
                ['Sisipkan pendidikan antikorupsi pada RPS', 'akademik'], ['Rekap sertifikasi kompetensi BNSP lulusan', 'akademik'], ['Validasi survei kepuasan mitra industri', 'lpm'],
                ['Perbaiki tautan bukti yang rusak', 'tik'], ['Monev hibah penelitian terapan 2026', 'lppm'], ['Laporan capaian IKU Polban', 'perencanaan'],
                ['Laporan SPI atas pengendalian internal BLU', 'spi'], ['Laporan layanan konseling dan karier', 'kemahasiswaan'], ['Dokumentasi Zona Integritas WBBM', 'sekretariat'],
            ],
            'risks' => [
                ['SK akreditasi institusi berakhir akhir 2026', 4, 5, 'manajer', 'Unggah SAPTO 27 November 2026; rapat kendali dua kali seminggu.'],
                ['Syarat perlu Unggul 4B: proporsi prodi Unggul belum memenuhi', 4, 5, 'pic.akademik', 'Percepat reakreditasi prodi Baik Sekali yang skornya mendekati Unggul.'],
                ['Prodi Manajemen Aset dan Teknik Otomasi Industri: akreditasi berakhir akhir 2026', 4, 4, 'pic.akademik', 'Pengajuan reakreditasi ke LAMEMBA dan LAM Teknik November 2026.'],
                ['Proporsi lektor kepala dan guru besar rendah', 3, 4, 'pic.sdm', 'Klinik jabatan akademik dan pendampingan publikasi bereputasi.'],
                ['Transisi Direktur periode 2026-2030 saat penyusunan LED', 3, 3, 'manajer', 'Serah terima tim akreditasi dan pengesahan ulang dokumen tata pamong.'],
                ['Selisih data mahasiswa magang dengan PDDikti', 3, 3, 'pic.tik', 'Perbaikan status mahasiswa magang sebelum pelaporan semester.'],
                ['Bukti adopsi teknologi oleh industri belum lengkap', 3, 3, 'pic.lppm', 'Kumpulkan surat pemanfaatan dari industri mitra.'],
                ['Kerusakan alat praktik saat asesmen lapangan', 2, 3, 'pic.sarpras', 'Pemeliharaan preventif bengkel dan laboratorium.'],
                ['Respons survei pengguna lulusan rendah', 2, 3, 'pic.lpm', 'Survei terintegrasi dengan kunjungan industri.'],
                ['Kehilangan data bukti karena server tunggal', 1, 5, 'pic.tik', 'Cadangan harian otomatis ke pusat data sekunder.'],
            ],
            'standards' => $base['standards'],
            'standard_statement' => ' Politeknik Negeri Bandung melampaui SN Dikti sesuai visi menjadi institusi unggul dan terdepan dalam pendidikan vokasi yang inovatif dan adaptif.',
            'audits' => [
                ['name' => 'AMI Siklus 2025', 'cycle_year' => 2025, 'audit_date' => '2025-11-24', 'lead_auditor' => 'Dr. Ir. Wahyu Hidayat, M.Eng.', 'state' => 'selesai', 'summary' => '41 prodi dan 14 unit diaudit; 3 temuan mayor, 15 minor, 8 observasi.'],
                ['name' => 'AMI Siklus 2026', 'cycle_year' => 2026, 'audit_date' => now()->subDays(20), 'lead_auditor' => 'Dr. Ir. Wahyu Hidayat, M.Eng.', 'state' => 'pelaporan', 'summary' => 'Fokus pada kesiapan reakreditasi institusi, proporsi prodi Unggul, dan bukti adopsi teknologi.'],
            ],
            'findings' => [
                [0, 'mayor', 'RTM tingkat jurusan tidak konsisten pada tiga jurusan.', 2, 'lpm', 'closed', 'ami'],
                [0, 'minor', 'RPS belum memuat sisipan pendidikan antikorupsi pada 7 prodi.', 13, 'akademik', 'closed', 'ami'],
                [0, 'minor', 'Data jabatan akademik di SISTER belum selaras dengan data kepegawaian.', 11, 'sdm', 'verification', 'ami'],
                [1, 'mayor', 'Bukti adopsi teknologi oleh industri mitra belum terdokumentasi tiga tahun.', 19, 'lppm', 'capa', 'ami'],
                [1, 'mayor', 'Rencana reakreditasi prodi Baik Sekali belum memuat target peringkat Unggul.', 4, 'akademik', 'capa', 'ami'],
                [1, 'minor', 'Laporan monev hibah P3M belum memuat analisis ketercapaian luaran.', 18, 'lppm', 'open', 'ami'],
                [1, 'minor', 'Rencana pendanaan beasiswa non-KIP belum ditetapkan.', 27, 'keuangan', 'capa', 'ami'],
                [1, 'observasi', 'Beberapa tautan bukti di laman jurusan tidak dapat diakses.', 26, 'tik', 'open', 'ami'],
                [null, 'minor', 'Analisis akar masalah kelulusan tepat waktu D3 masih deskriptif.', 12, 'akademik', 'open', 'simulasi'],
                [null, 'observasi', 'Mahasiswa belum memahami kanal pelaporan Satgas PPK.', 28, 'satgas', 'capa', 'simulasi'],
                [null, 'peluang', 'Prestasi KRI dan KMHE dapat dijadikan narasi keunggulan vokasi.', 39, 'kerjasama', 'open', 'simulasi'],
                [null, 'minor', 'Kepuasan mahasiswa terhadap ketersediaan alat praktik di bawah 75%.', 34, 'sarpras', 'verification', 'survei'],
            ],
            'reviews' => [
                ['meeting_date' => '2025-12-15', 'title' => 'RTM Semester Gasal 2025/2026', 'chair' => 'Direktur', 'minutes' => 'Membahas hasil AMI 2025, survei kepuasan, dan capaian IKU.', 'outcomes' => 'Penetapan 8 tindakan koreksi; peremajaan alat praktik prioritas.'],
                ['meeting_date' => '2026-06-29', 'title' => 'RTM Semester Genap 2025/2026', 'chair' => 'Direktur', 'minutes' => 'Membahas kesiapan reakreditasi institusi dan simulasi skor IAPT 4.1.', 'outcomes' => 'Pemilihan jalur Unggul; percepatan reakreditasi prodi; klinik LED.'],
                ['meeting_date' => now()->subDays(12)->toDateString(), 'title' => 'RTM Khusus Kesiapan Reakreditasi Institusi', 'chair' => 'Wakil Direktur Bidang Akademik', 'minutes' => 'Membahas hasil AMI 2026 dan syarat perlu Unggul 4B.', 'outcomes' => 'Rencana pemenuhan 4B; target AKI 80 sebelum unggah SAPTO.'],
            ],
        ];
    }

    protected function siteVisitProfile(): array
    {
        $base = parent::siteVisitProfile();

        return [
            'mock' => [
                'title' => 'Simulasi Asesmen Lapangan I', 'starts_on' => now()->subDays(18), 'ends_on' => now()->subDays(17),
                'mode' => 'luring', 'assessors' => 'Dr. Ir. Wahyu Hidayat, M.Eng.; Dr. Ir. Kusnadi, M.Eng. (asesor eksternal)', 'state' => 'selesai',
                'document_room' => 'Ruang Rapat Direktorat, Gedung Direktorat Polban', 'minutes' => 'Simulasi berjalan; 7 rekomendasi perbaikan termasuk syarat perlu 4B.',
            ],
            'recommendations' => [
                [4, 'Sajikan peta jalan kenaikan peringkat prodi Baik Sekali menuju Unggul.', 'task'],
                [19, 'Lengkapi bukti adopsi teknologi oleh industri mitra.', 'task'],
                [11, 'Tunjukkan rencana kenaikan jabatan akademik per jurusan.', 'task'],
                [12, 'Tajamkan analisis kelulusan tepat waktu program D3.', null],
                [28, 'Tunjukkan bukti sosialisasi Satgas PPK pada PKKMB.', 'capa'],
                [34, 'Tindak lanjuti keluhan ketersediaan alat praktik.', null],
                [39, 'Jadikan prestasi KRI, KMHE, dan UI GreenMetric sebagai bukti keunggulan.', null],
            ],
            'visit' => [
                'title' => 'Asesmen Lapangan BAN-PT', 'starts_on' => '2027-02-15', 'ends_on' => '2027-02-16',
                'mode' => 'luring', 'assessors' => 'Menunggu penugasan BAN-PT', 'rehearsal_on' => '2027-02-08', 'state' => 'rencana',
                'document_room' => 'Ruang Rapat Direktorat, Gedung Direktorat Polban',
            ],
            'visit_date' => '2027-02-15',
            'sessions' => [
                ['08:00', '09:00', 'pimpinan', 'Pembukaan dan presentasi Direktur', 'Aula Polban', 'Direktur'],
                ['09:00', '10:30', 'pimpinan', 'Wawancara pimpinan dan senat', 'Ruang Rapat Direktorat', 'Kepala P2MPP'],
                ['10:30', '11:30', 'dosen', 'Wawancara dosen', 'Ruang Rapat Jurusan Teknik Mesin', 'Ketua Jurusan Teknik Mesin'],
                ['11:30', '12:30', 'tendik', 'Wawancara tenaga kependidikan dan teknisi', 'Ruang Rapat Bagian Akademik', 'Kepala Bagian Akademik'],
                ['13:30', '14:30', 'mahasiswa', 'Wawancara mahasiswa', 'Gedung Kemahasiswaan', 'Kepala Bagian Kemahasiswaan'],
                ['14:30', '15:30', 'lulusan', 'Wawancara lulusan dan pengguna lulusan (industri)', 'Ruang Alumni', 'Kepala Bagian Kemahasiswaan'],
                ['15:30', '17:00', 'observasi', 'Observasi bengkel, laboratorium, dan teaching factory', 'Kampus Ciwaruga', 'Kepala UPT Pemeliharaan dan Perbaikan'],
            ],
            'interviewees' => [
                ['pimpinan', 'Dr. Ir. Ahmad Rusdiana, M.T.', 'Direktur', true], ['pimpinan', 'Ketua Senat Politeknik Negeri Bandung', 'Senat', true],
                ['pimpinan', 'Dr. Ir. Teti Sumiati, M.T.', 'Wakil Direktur Bidang Akademik', true], ['dosen', 'Dr. Ir. Bambang Purwanto, M.T.', 'Ketua Jurusan Teknik Mesin', true],
                ['dosen', 'Siska Amelia, S.T., M.T.', 'Dosen Teknik Kimia', false], ['dosen', 'Dr. Andri Firmansyah, S.Kom., M.T.', 'Ketua Jurusan Teknik Komputer dan Informatika', true],
                ['tendik', 'Ujang Suherman, A.Md.', 'Teknisi bengkel Teknik Mesin', true], ['tendik', 'Rani Puspita, S.E.', 'Staf Bagian Keuangan', false],
                ['mahasiswa', 'Raka Pratama', 'Ketua BEM Polban', true], ['mahasiswa', 'Nadia Salsabila', 'Mahasiswa D4 Teknik Informatika 2023', false],
                ['lulusan', 'Ilham Maulana, A.Md.T.', 'Alumni D3 Teknik Aeronautika, teknisi industri dirgantara', true], ['pengguna', 'Manajer SDM industri manufaktur mitra', 'Pengguna lulusan', false],
            ],
            'questions' => array_map(fn ($q) => [$q[0], $q[1], str_replace(['UNM', 'Rektor'], ['Polban', 'Direktur'], $q[2]), match ($q[0]) {
                36 => 'Visi pendidikan vokasi yang inovatif dan adaptif; IKU Polban; kerja sama industri.',
                39 => 'UI GreenMetric 2026 peringkat 3 politeknik; prestasi KRI, KMHE, dan KJI.',
                35 => 'Laporan keuangan BLU audited 2021-2025 dengan opini WTP.',
                default => $q[3],
            }], $base['questions']),
        ];
    }
}
