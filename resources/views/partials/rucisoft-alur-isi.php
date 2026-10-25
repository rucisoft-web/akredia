<?php

// Isi blok halaman awal seragam (partials/rucisoft-alur.blade.php) khusus produk ini.
return [
    'slug' => 'akredia',
    'nama' => 'Akredia',
    'aksen' => '#17406f',
    'aksen_2' => '#f5c26b',
    'coba_url' => '#data-contoh',
    'masalah_judul' => 'Persiapan akreditasi sering menumpuk menjelang tenggat.',
    'masalah_lead' => 'Tim akreditasi bekerja keras, tetapi pimpinan baru melihat masalahnya ketika waktu tinggal sedikit.',
    'masalah' => [
        ['Kesiapan tidak terukur', 'Pimpinan bergantung pada laporan lisan di rapat, sehingga masalah baru terlihat ketika tenggat sudah dekat.'],
        ['Bukti tercecer', 'Dokumen tersebar di folder bersama dan grup percakapan, nama berkas tidak seragam, dan SK kedaluwarsa tidak ada yang mengingatkan.'],
        ['Syarat perlu terlewat', 'Satu syarat perlu yang gagal menutup peringkat yang dituju, dan sering baru ketahuan saat penilaian.'],
        ['Angka berbeda', 'Jumlah mahasiswa, dosen, dan lulusan di laporan internal tidak sama dengan PDDikti, dan selisihnya muncul di depan asesor.'],
    ],
    'penyebab_judul' => 'Akreditasi diperlakukan sebagai proyek musiman, bukan pekerjaan rutin.',
    'penyebab_lead' => 'Akibatnya data dan bukti baru dikumpulkan ketika dibutuhkan, dari banyak unit sekaligus.',
    'penyebab' => [
        ['Tidak ada peta instrumen', 'Indikator IAPT 4.1 belum dipetakan ke unit, PIC, dan bukti yang dibutuhkan.'],
        ['Bukti tanpa pemilik dan versi', 'Dokumen tidak punya penanggung jawab, status validasi, dan masa berlaku.'],
        ['Data induk tidak direkonsiliasi', 'Data internal dan PDDikti dikelola terpisah dan tidak pernah dicocokkan.'],
        ['Tidak ada ukuran kesiapan', 'Pimpinan tidak punya indikator yang menunjukkan bagian mana yang tertinggal.'],
    ],
    'solusi_judul' => 'Akredia menjadikan persiapan akreditasi terukur sepanjang tahun.',
    'solusi_lead' => 'Evaluasi diri, bukti, LED, LKPT, dan tindak lanjut dikelola di satu tempat dengan dasbor pimpinan.',
    'solusi' => [
        ['Indeks Kesiapan Akreditasi', 'Dasbor pimpinan dengan proyeksi skor dan jalur peringkat yang dituju.', 'Kesiapan tidak terukur'],
        ['Pustaka bukti berversi', 'Setiap bukti punya pemilik, status validasi, dan pengingat masa berlaku.', 'Bukti tercecer'],
        ['Peta instrumen IAPT 4.1', '39 indikator dan syarat perlu dipantau sejak awal, bukan saat penilaian.', 'Syarat perlu terlewat'],
        ['Tabel LKPT & rekonsiliasi PDDikti', 'Angka laporan dicocokkan dengan PDDikti sebelum diserahkan.', 'Angka berbeda'],
    ],
];
