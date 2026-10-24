@php
    $target = $project?->target_date;
    $phase = function (int $from, int $to) use ($target) {
        if (! $target) return null;
        return $target->copy()->subMonths($from)->translatedFormat('M Y').' – '.$target->copy()->subMonths($to)->translatedFormat('M Y');
    };
@endphp
<x-filament-panels::page>
<div class="ak-guide">
    <div class="ak-guide-hero">
        <h2>Peta jalan persiapan akreditasi institusi</h2>
        <p>Urutan kerja yang direkomendasikan dari rancangan Akredia, dihitung mundur dari tanggal target pengajuan (T). @if ($target) Untuk proyek <b>{{ $project->name }}</b>, T = <b>{{ $target->translatedFormat('d F Y') }}</b>, sehingga tanggal setiap tahap di bawah sudah disesuaikan. @else Isi tanggal target di proyek akreditasi agar tanggal tahap dihitung otomatis. @endif</p>
    </div>

    <div class="ak-steps">
        <div class="ak-step">
            <div class="when">T-12 s.d. T-10 bulan {{ $phase(12, 10) ? '· '.$phase(12, 10) : '' }}</div>
            <h3>Bentuk tim, pilih jalur, dan petakan kesenjangan</h3>
            <ul>
                <li>Terbitkan SK tim taskforce akreditasi dan tetapkan PIC serta reviewer untuk setiap sub-indikator.</li>
                <li>Pilih jalur target (Terakreditasi atau Unggul) dan varian instrumen sesuai jenis PT; tetapkan Tahun Sekarang (TS).</li>
                <li>Periksa eligibilitas dan syarat perlu sejak awal; syarat perlu yang gagal adalah risiko terbesar.</li>
                <li>PIC menganalisis kesenjangan per kata kunci deskriptor dan menyusun rencana tindak lanjut (RTL).</li>
            </ul>
            <p><b>Keluaran:</b> proyek, matriks PIC, daftar kesenjangan, RTL.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['project'] }}">Proyek Akreditasi</a><a class="ak-chip" href="{{ $l['items'] }}">Butir Kerja</a><a class="ak-chip" href="{{ $l['gaps'] }}">Kertas Kerja</a><a class="ak-chip" href="{{ $l['members'] }}">Pengguna & Peran</a></div>
        </div>
        <div class="ak-step">
            <div class="when">T-10 s.d. T-6 bulan {{ $phase(10, 6) ? '· '.$phase(10, 6) : '' }}</div>
            <h3>Rapikan data dan kumpulkan bukti</h3>
            <ul>
                <li>Perbaiki data PD Dikti: dosen, status akreditasi prodi, luaran penelitian, dan mahasiswa.</li>
                <li>Kumpulkan, validasi, dan unggah bukti ke Pustaka Bukti; tandai bukti yang perlu tampil publik.</li>
                <li>Jalankan survei kepuasan enam kelompok pemangku kepentingan dan tracer study.</li>
                <li>Tutup kesenjangan melalui tugas RTL dan pantau keterlambatan per unit.</li>
            </ul>
            <p><b>Keluaran:</b> pustaka bukti, data terverifikasi, hasil survei.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['documents'] }}">Pustaka Bukti</a><a class="ak-chip" href="{{ $l['lecturers'] }}">Data Kinerja</a><a class="ak-chip" href="{{ $l['surveys'] }}">Survei</a><a class="ak-chip" href="{{ $l['tasks'] }}">Tugas & RTL</a></div>
        </div>
        <div class="ak-step">
            <div class="when">T-6 s.d. T-3 bulan {{ $phase(6, 3) ? '· '.$phase(6, 3) : '' }}</div>
            <h3>Tulis LED, susun LKPT, dan uji kesiapan</h3>
            <ul>
                <li>PIC menulis LED per sub-indikator mengikuti PPEPP dan analisis akar masalah; reviewer mereviu.</li>
                <li>Susun tabel LKPT dari Data Kinerja; hitung rasio dan bandingkan dengan ambang.</li>
                <li>Rekonsiliasi data internal dengan PD Dikti; selesaikan selisih berkeparahan tinggi.</li>
                <li>Lakukan audit mutu internal atau simulasi asesmen oleh asesor internal.</li>
            </ul>
            <p><b>Keluaran:</b> draf LED, draf LKPT, laporan simulasi.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['led'] }}">Penyusunan LED</a><a class="ak-chip" href="{{ $l['lkpt'] }}">Tabel LKPT</a><a class="ak-chip" href="{{ $l['recon'] }}">Rekonsiliasi</a><a class="ak-chip" href="{{ $l['simulation'] }}">Simulasi Penilaian</a><a class="ak-chip" href="{{ $l['findings'] }}">Temuan</a></div>
        </div>
        <div class="ak-step">
            <div class="when">T-3 s.d. T-1 bulan {{ $phase(3, 1) ? '· '.$phase(3, 1) : '' }}</div>
            <h3>Kunci data, sahkan, dan ajukan</h3>
            <ul>
                <li>Kunci data TS (freeze) agar angka di LED dan LKPT tidak berubah lagi.</li>
                <li>Ajukan sinkronisasi SAPTO pada tanggal 1 sampai 10.</li>
                <li>Pimpinan mengesahkan bagian LED yang sudah disetujui reviewer.</li>
                <li>Pastikan gerbang Siap Diajukan terbuka, lalu salin narasi dan unggah ke SAPTO.</li>
            </ul>
            <p><b>Keluaran:</b> paket pengajuan final.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['project'] }}">Gerbang Siap Diajukan</a><a class="ak-chip" href="{{ $l['reports'] }}">Laporan & Ekspor</a></div>
        </div>
        <div class="ak-step">
            <div class="when">Pasca pengajuan</div>
            <h3>Asesmen kecukupan dan asesmen lapangan</h3>
            <ul>
                <li>Tanggapi klarifikasi asesmen kecukupan dengan bukti yang sudah tersusun.</li>
                <li>Siapkan asesmen lapangan: jadwal, narasumber per kelompok, ruang dokumen, bank pertanyaan, dan gladi.</li>
                <li>Tindak lanjuti rekomendasi asesor menjadi tugas atau CAPA, lalu salin proyek untuk siklus berikutnya.</li>
            </ul>
            <p><b>Keluaran:</b> berita acara asesmen lapangan, rencana perbaikan.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['visit'] }}">Asesmen Lapangan</a></div>
        </div>
    </div>
    <p class="ak-muted">Durasi adalah rekomendasi. PT dengan data PD Dikti yang sudah rapi dapat mempersingkatnya.</p>
</div>
</x-filament-panels::page>
