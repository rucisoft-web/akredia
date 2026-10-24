@php
    $faq = [
        ['Apakah AKI sama dengan nilai akreditasi?', 'Tidak. AKI dan proyeksi skor adalah alat manajemen internal untuk memantau kesiapan. Penilaian dan keputusan akreditasi sepenuhnya kewenangan BAN-PT melalui asesmen kecukupan dan asesmen lapangan.'],
        ['Kapan sebaiknya jalur dikunci?', 'Setelah analisis kesenjangan awal dan penilaian mandiri selesai, biasanya pada bulan ke-10 sebelum target. Bandingkan proyeksi kedua jalur dan syarat perlu Unggul. Menunda keputusan membuat PIC bekerja pada deskriptor yang berbeda-beda.'],
        ['Mengapa proyeksi tinggi tetapi gerbang belum terbuka?', 'Gerbang menuntut tujuh syarat sekaligus: syarat perlu, ambang proyeksi, rekonsiliasi data tinggi, persetujuan LED, keabsahan bukti dikutip, eligibilitas, dan data TS terkunci. Rinciannya tampil di halaman proyek.'],
        ['Siapa yang boleh menilai sub-indikator?', 'PIC mencatat penilaian mandiri. Asesor internal atau reviewer yang bukan PIC mencatat penilaian independen yang lebih diutamakan dalam proyeksi. Manajer dapat mencatat hasil asesmen resmi setelah keputusan BAN-PT.'],
        ['Bagaimana bukti rahasia dilindungi?', 'Berkas disimpan di luar folder publik dan hanya dapat diunduh lewat tautan bertanda tangan yang berlaku 30 menit. Bukti berklasifikasi Rahasia tidak terlihat oleh PIC dan pemilik data dari unit lain. Setiap unduhan bukti rahasia dicatat di log audit.'],
        ['Apa itu kunci data (freeze)?', 'Penguncian nilai metrik TS dan penyimpanan snapshot data, variabel, serta versi bukti. Setelah dikunci, perubahan data tidak mengubah angka yang dikutip di LED dan LKPT. Kunci dapat dibuka Manajer dengan alasan yang tercatat.'],
        ['Apakah narasi bisa ditulis oleh AI?', 'Akredia menyediakan pemeriksaan narasi lokal (kata kunci, PPEPP, akar masalah, sitasi, batas kata) dan, bila diaktifkan, saran perbaikan dari AI. Asisten tidak pernah menyimpan perubahan; PIC dan reviewer tetap memutuskan isi.'],
        ['Bagaimana dengan data PD Dikti yang berbeda?', 'Impor rekap PD Dikti di menu Impor Data. Akredia menghitung selisih terhadap data internal, memberi tingkat keparahan berdasarkan toleransi, dan menyediakan alur investigasi sampai terverifikasi.'],
        ['Bisakah dipakai untuk siklus berikutnya?', 'Ya. Proyek dapat disalin beserta PIC dan bukti yang masih berlaku, sehingga persiapan reakreditasi tidak dimulai dari nol.'],
        ['Apakah aplikasi ini harus di server khusus?', 'Tidak. Akredia berjalan di hosting cPanel dengan PHP 8.3 dan MySQL 8. Panduan pemasangan disertakan bersama paket aplikasi.'],
    ];
@endphp
<x-filament-panels::page>
<div class="ak-guide">
    <div class="ak-guide-hero">
        <h2>Tanya jawab pimpinan</h2>
        <p>Pertanyaan yang sering muncul ketika pimpinan mulai memakai Akredia untuk memimpin persiapan akreditasi institusi.</p>
    </div>
    <div class="ak-faq">
        @foreach ($faq as [$q, $a])
            <details @if ($loop->first) open @endif><summary>{{ $q }}</summary><p>{{ $a }}</p></details>
        @endforeach
    </div>
</div>
</x-filament-panels::page>
