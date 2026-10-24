<x-filament-panels::page>
<div class="ak-guide">
    <div class="ak-guide-hero">
        <h2>Apa yang dibutuhkan dari pimpinan</h2>
        <p>Sebagian besar pekerjaan dikerjakan PIC, pemilik data, dan reviewer. Namun ada keputusan yang hanya dapat diambil pimpinan, dan kecepatan keputusan itu sering menentukan apakah institusi siap tepat waktu. Akredia menyiapkan data agar keputusan tersebut dapat diambil dalam rapat singkat.</p>
    </div>

    <div class="ak-steps">
        <div class="ak-step">
            <h3>Menetapkan tim dan memberi mandat</h3>
            <p>Terbitkan SK tim taskforce, tetapkan Manajer Akreditasi (biasanya LPM), dan pastikan setiap sub-indikator memiliki PIC dan reviewer yang berbeda orang. Dasbor menunjukkan berapa sub-indikator yang belum memiliki PIC.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['members'] }}">Pengguna & Peran</a><a class="ak-chip" href="{{ $l['items'] }}">Butir Kerja</a></div>
        </div>
        <div class="ak-step">
            <h3>Memutuskan dan mengunci jalur</h3>
            <p>Bandingkan proyeksi Terakreditasi dan Unggul yang dihitung berdampingan, perhatikan syarat perlu Unggul (misalnya proporsi prodi Unggul dan pengakuan eksternal atas diferensiasi misi), lalu kunci jalur target. Setelah dikunci, deskriptor dan kata kunci kertas kerja mengikuti jalur tersebut.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['project'] }}">Proyek Akreditasi</a><a class="ak-chip" href="{{ $l['simulation'] }}">Simulasi what-if</a></div>
        </div>
        <div class="ak-step">
            <h3>Memutus hambatan lintas unit</h3>
            <p>Gunakan Keputusan Pimpinan untuk mencatat keputusan rapat, lalu turunkan menjadi tugas dengan penanggung jawab dan tenggat. Register risiko 5×5 membantu memprioritaskan sumber daya pada risiko yang paling mengancam status akreditasi.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['decisions'] }}">Keputusan Pimpinan</a><a class="ak-chip" href="{{ $l['risks'] }}">Register Risiko</a><a class="ak-chip" href="{{ $l['tasks'] }}">Tugas</a></div>
        </div>
        <div class="ak-step">
            <h3>Memimpin reviu mingguan</h3>
            <p>Dua puluh menit setiap minggu cukup: buka dasbor, lihat tren AKI, peta panas 39 indikator, syarat perlu, tugas terlambat per unit, dan panel "Langkah pimpinan minggu ini" yang disusun otomatis dari data.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['dashboard'] }}">Dasbor Eksekutif</a><a class="ak-chip" href="{{ $l['reports'] }}">Laporan mingguan</a></div>
        </div>
        <div class="ak-step">
            <h3>Mengesahkan LED dan membuka gerbang pengajuan</h3>
            <p>Bagian LED yang sudah disetujui reviewer menunggu pengesahan pimpinan. Setelah data TS dikunci dan tujuh syarat gerbang terpenuhi, Manajer dapat mengubah status proyek menjadi Siap Diajukan.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['led'] }}">Penyusunan LED</a><a class="ak-chip" href="{{ $l['project'] }}">Gerbang</a></div>
        </div>
        <div class="ak-step">
            <h3>Hadir dan konsisten saat asesmen lapangan</h3>
            <p>Asesor mengonfirmasi narasi kepada pimpinan, dosen, tendik, mahasiswa, lulusan, dan pengguna. Pelajari bank pertanyaan kelompok pimpinan dan pastikan jawaban konsisten dengan LED dan data.</p>
            <div class="modules"><a class="ak-chip" href="{{ $l['visit'] }}">Asesmen Lapangan</a></div>
        </div>
    </div>

    <div class="ak-card">
        <h3>Pembagian peran di Akredia</h3>
        <ul>
            @foreach (\App\Enums\Role::cases() as $role)
                <li><b>{{ $role->getLabel() }}</b>: {{ $role->description() }}</li>
            @endforeach
        </ul>
        <p class="ak-muted">Prinsip empat mata diterapkan pada validasi bukti, reviu LED, penilaian asesor internal, verifikasi metrik, dan verifikasi CAPA: orang yang mengerjakan tidak boleh menyetujui hasilnya sendiri.</p>
    </div>
</div>
</x-filament-panels::page>
