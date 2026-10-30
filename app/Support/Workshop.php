<?php

namespace App\Support;

/** Isi penawaran workshop dua hari di halaman depan (ringkas) dan /workshop (materi lengkap). */
class Workshop
{
    public const TITLE = 'Persiapan Akreditasi Institusi Perguruan Tinggi dengan Akredia';

    public const TAGLINE = 'Workshop dua hari: dari membaca IAPT 4.1 sampai menulis LED dan menggladi asesmen lapangan, langsung dipraktikkan di aplikasi.';

    public const PARTNER = 'Pusat Studi Manajemen Perguruan Tinggi, UIN Sunan Kalijaga Yogyakarta';

    public const EMAIL = 'rucisoft@gmail.com';

    public const PHONE = '0811-9989-2939';

    /** Hasil yang dibawa pulang setiap tim peserta. */
    public const OUTCOMES = [
        ['Peta syarat perlu institusi', 'Daftar syarat perlu Terakreditasi dan Unggul sesuai varian Lampiran 3, lengkap dengan status awal kampus Anda.'],
        ['Struktur tim dan pembagian PIC', 'Siapa memegang sub-indikator mana, siapa reviewer, dan siapa pemilik data, siap ditetapkan dengan SK.'],
        ['Analisis kesenjangan awal', 'Kertas kerja per kata kunci untuk sub-indikator prioritas beserta rencana tindak lanjut.'],
        ['Draf satu bagian LED', 'Narasi yang ditulis mengikuti Panduan LED (Lampiran 4a), sudah direviu sejawat.'],
        ['Rencana aksi 90 hari', 'Prioritas, tenggat, dan keputusan pimpinan yang dibutuhkan agar persiapan bergerak setelah workshop.'],
        ['Akses sandbox Akredia', 'Ruang latihan berisi data contoh untuk dicoba ulang bersama tim di kampus.'],
    ];

    /** Sasaran peserta. */
    public const AUDIENCE = [
        'Wakil Rektor/Wakil Ketua bidang akademik dan perencanaan',
        'Ketua LPM/SPM dan tim penjaminan mutu',
        'Ketua dan anggota tim akreditasi institusi',
        'Kepala biro akademik, SDM, keuangan, dan pengelola data PDDikti',
        'Dekan, kaprodi, dan gugus mutu fakultas',
    ];

    /** Bekal peserta. */
    public const PREPARE = [
        'Laptop dengan peramban terbaru (satu laptop untuk setiap 1–2 peserta).',
        'Renstra, statuta, kebijakan SPMI, dan laporan AMI/RTM terakhir (salinan digital).',
        'Rekap data PDDikti institusi untuk TS yang akan dipakai (dosen, mahasiswa, lulusan), bila ada.',
        'Disarankan datang sebagai tim 3–5 orang dari satu perguruan tinggi.',
    ];

    /**
     * Agenda: [hari => [judul, tema, [[jam, judul, materi[], praktik, luaran], ...]]].
     * Rujukan resmi: PerBAN-PT 35/2025 (IAPT 4.1) Lampiran 2, 3, 4a, dan 4b.
     */
    public const DAYS = [
        1 => ['Hari 1', 'Memahami instrumen, data, dan tim', [
            ['08.00–08.30', 'Registrasi dan pembukaan', [
                'Sambutan Pusat Studi Manajemen Perguruan Tinggi UIN Sunan Kalijaga dan Rucisoft Indonesia.',
                'Tes awal singkat: seberapa siap institusi Anda?',
            ], null, null],
            ['08.30–10.00', 'Sesi 1 · Lanskap IAPT 4.1', [
                'PerBAN-PT No. 35/2025: apa yang berubah dari IAPT 3.0 dan 4.0, serta masa transisi.',
                'Empat kriteria (Budaya Mutu, Relevansi, Akuntabilitas, Diferensiasi Misi), 39 indikator, dan sasaran mutu masukan–proses–luaran–dampak.',
                'Delapan varian Lampiran 3 (PTN-BH, PTN, PTS, vokasi, PJJ, dan lainnya) serta perbedaan ambangnya.',
                'Cara menilai: deskriptor bernilai 1 atau 0, ambang 80%, syarat perlu Terakreditasi dan Unggul, asesmen kecukupan, asesmen lapangan, dan masa berlaku 8 tahun.',
                'Syarat eligibilitas sebelum mengajukan.',
            ], 'Menjelajah Peta Instrumen dan panel Bantuan berisi teks resmi indikator.', 'Peta syarat perlu institusi (draf).'],
            ['10.15–12.00', 'Sesi 2 · Data sebagai fondasi: LKPT dan PDDikti', [
                'Panduan LKPT (Lampiran 4b): TS dan TS-n, definisi dosen tetap, program utama, dan tabel-tabel kunci.',
                'Metrik kuantitatif: RM/DT, PDTT, persentase doktor, DTJA, RPL, PKMTK, PK2MTK, PPM, PMAP, PLLK, dan persentase prodi terakreditasi.',
                'Mengapa angka asesor selalu PDDikti, dan cara merekonsiliasi selisih jauh sebelum data dikunci.',
            ], 'Impor data dosen dan statistik prodi, menghitung metrik, dan membaca selisih rekonsiliasi.', 'Daftar selisih data yang harus dibereskan.'],
            ['13.00–14.30', 'Sesi 3 · Mengorganisasi tim akreditasi', [
                'Peran pimpinan, manajer akreditasi, PIC, pemilik data, reviewer, dan asesor internal.',
                'Pembagian 64 butir kerja sesuai struktur organisasi; prinsip empat mata (penulis ≠ reviewer).',
                'Peta jalan 12 bulan: penilaian mandiri, pengumpulan bukti, penulisan, reviu, finalisasi, dan gladi.',
            ], 'Membuat proyek akreditasi, memilih varian dan TS, lalu menetapkan PIC dan reviewer.', 'Struktur tim dan matriks PIC.'],
            ['14.45–16.15', 'Sesi 4 · Analisis kesenjangan per kata kunci', [
                'Membaca deskripsi pemenuhan Terakreditasi dan Unggul secara berdampingan.',
                'Memecah deskriptor menjadi kata kunci yang harus dibuktikan; status Belum, Sebagian, Terbukti.',
                'Dari kesenjangan menjadi rencana tindak lanjut dan tugas; Simulasi Penilaian untuk memilih perbaikan berdampak terbesar.',
            ], 'Analisis kesenjangan tiga sub-indikator contoh (rasio dosen, kecukupan dosen, kepuasan pemangku kepentingan).', 'Kertas kerja dan RTL sub-indikator prioritas.'],
            ['16.15–16.30', 'Refleksi hari pertama', [
                'Pertanyaan terbuka dan klinik singkat.',
                'Tugas malam: siapkan lima dokumen bukti dari kampus masing-masing.',
            ], null, null],
        ]],
        2 => ['Hari 2', 'Membuktikan, menulis, dan menggladi', [
            ['08.00–09.45', 'Sesi 5 · Pustaka bukti yang sahih', [
                'Ciri bukti sahih: ditetapkan pejabat berwenang, bernomor, bertanggal, masih berlaku, dan dapat diakses asesor.',
                'Satu bukti untuk banyak indikator; versi, validasi, masa berlaku, dan kerahasiaan per unit.',
                'Tautan publik untuk asesor dan pemeriksaan tautan rusak.',
            ], 'Mengunggah dan memvalidasi bukti yang dibawa peserta, lalu menautkannya ke butir kerja.', 'Pustaka bukti awal institusi.'],
            ['10.00–12.00', 'Sesi 6 · Menulis LED yang meyakinkan', [
                'Sistematika dan format LED menurut Lampiran 4a: Pendahuluan, Kriteria 1–4, Penutup; A4, Arial 11, spasi 1,15, paling banyak 300 halaman.',
                'Pola narasi: kebijakan → implementasi → hasil dan analisis (data, tren, akar masalah) → tindak lanjut.',
                'Menyitasi bukti, menyisipkan angka hidup dari data kinerja, dan menghindari narasi normatif.',
            ], 'Menulis satu bagian LED, reviu sejawat lintas tim, lalu revisi.', 'Draf satu bagian LED yang sudah direviu.'],
            ['13.00–14.15', 'Sesi 7 · SPMI dan budaya mutu sebagai bukti', [
                'Siklus PPEPP yang terdokumentasi: standar, AMI, temuan, CAPA, verifikasi efektivitas, dan RTM.',
                'Indikator 1–3 (syarat perlu) dan cara menunjukkan konsistensi minimal tiga tahun.',
                'Manajemen risiko akreditasi dan keputusan pimpinan yang diturunkan menjadi tugas.',
            ], 'Menelusuri satu temuan AMI sampai CAPA terverifikasi dan dibahas di RTM.', 'Daftar celah bukti SPMI.'],
            ['14.30–15.45', 'Sesi 8 · Siap diasesmen', [
                'Dasbor Eksekutif dan gerbang Siap Diajukan: syarat perlu, LED disahkan, data dikunci, bukti dapat diakses.',
                'Asesmen lapangan: susunan sesi, narasumber per kelompok, bank pertanyaan asesor, dan gladi.',
                'Kesalahan umum menjelang unggah SAPTO.',
            ], 'Simulasi wawancara asesor dengan bank pertanyaan per indikator.', 'Bank pertanyaan dan daftar narasumber awal.'],
            ['15.45–16.30', 'Rencana aksi 90 hari dan penutupan', [
                'Setiap tim memaparkan rencana aksi 90 hari dan keputusan pimpinan yang dibutuhkan.',
                'Umpan balik fasilitator, tes akhir, dan penyerahan sertifikat.',
            ], null, 'Rencana aksi 90 hari institusi.'],
        ]],
    ];

    public static function waUrl(): string
    {
        return 'https://wa.me/6281199892939?text='.rawurlencode('Halo Rucisoft, saya ingin informasi Workshop "'.self::TITLE.'" (jadwal, biaya, dan pendaftaran).');
    }

    public static function mailUrl(): string
    {
        return 'mailto:'.self::EMAIL.'?subject='.rawurlencode('Workshop '.self::TITLE);
    }
}
