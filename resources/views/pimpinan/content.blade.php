{{-- Isi "Akredia untuk Pimpinan Perguruan Tinggi": dipakai di halaman publik /untuk-pimpinan dan sub menu Panduan Pimpinan. --}}
<div class="akp">
    <section class="akp-hero">
        <div>
            <div class="akp-eyebrow">Akredia untuk Pimpinan Perguruan Tinggi</div>
            <h1>Kesiapan akreditasi institusi dalam satu angka, satu dasbor, dan satu daftar tindakan setiap minggu</h1>
            <p>Akredia membantu rektor dan pimpinan menyiapkan akreditasi institusi dengan instrumen IAPT 4.1, sehingga keputusan diambil dari data, bukan dari laporan lisan menjelang tenggat.</p>
            @if ($standalone)
                <div class="akp-cta">
                    @if ($guestUrl)<a class="akp-btn" href="{{ $guestUrl }}">Coba sebagai tamu</a>@endif
                    <a class="akp-btn ghost" href="{{ $loginUrl }}">Masuk dengan akun</a>
                </div>
                @if ($guestUrl)<p class="akp-note">Tanpa pendaftaran. Tamu hanya dapat melihat, tidak dapat mengubah data.</p>@endif
            @endif
        </div>
    </section>

    <section class="akp-card">
        <h2>Mengapa sekarang</h2>
        <p>IAPT 4.1 (PerBAN-PT Nomor 35 Tahun 2025) menilai institusi lewat <b>4 kriteria, 39 indikator, dan 64 sub-indikator</b>. Sebagian sub-indikator adalah <b>syarat perlu</b>: satu kegagalan saja menutup peluang peringkat yang dituju, berapa pun skor lainnya.</p>
        <p>Di banyak kampus, persiapan masih tersebar di folder bersama, spreadsheet, dan grup percakapan. Pimpinan baru tahu ada masalah ketika tenggat sudah dekat: bukti kedaluwarsa, angka mahasiswa berbeda dengan PDDikti, atau narasi LED belum disahkan. Akredia memindahkan seluruh persiapan ke satu alur yang bisa dipantau, dan setiap bukti, angka, narasi, serta tugas tertaut ke sub-indikator yang dinilai asesor.</p>
    </section>

    <section class="akp-card">
        <h2>Yang Bapak/Ibu lihat dalam lima menit</h2>
        <p>Dasbor Eksekutif menjawab tiga pertanyaan rapat pimpinan: seberapa siap kita, apa yang menghambat, dan siapa yang harus bergerak minggu ini.</p>
        <figure>
            <img src="{{ asset('img/pimpinan-dasbor.jpg') }}" alt="Dasbor Eksekutif dengan data demo Universitas Negeri Jakarta" loading="lazy">
            <figcaption>Dasbor Eksekutif dengan <b>data demo</b> Universitas Negeri Jakarta (reakreditasi jalur Unggul). Angka kinerja adalah ilustrasi.</figcaption>
        </figure>
        <div class="akp-grid">
            <div><b>Indeks Kesiapan Akreditasi (AKI)</b><span>Satu angka dari lima komponen: pemenuhan deskriptor, bukti, data kinerja, kematangan LED, dan ketepatan tugas, lengkap dengan tren 16 minggu.</span></div>
            <div><b>Proyeksi skor dua jalur</b><span>Terakreditasi dan Unggul dihitung berdampingan dari penilaian per sub-indikator, dengan ambang tiap jalur.</span></div>
            <div><b>Syarat perlu yang gagal</b><span>Disorot di bagian atas karena ini keputusan pimpinan, bukan pekerjaan operator.</span></div>
            <div><b>Peta panas 39 indikator</b><span>Hijau, kuning, dan merah langsung menunjukkan indikator yang perlu dibahas di rapat.</span></div>
            <div><b>Langkah pimpinan minggu ini</b><span>Disusun otomatis: tugas lewat tenggat, selisih data PDDikti, akreditasi prodi yang segera berakhir.</span></div>
            <div><b>Setiap angka bisa diklik</b><span>Kartu dan grafik membuka daftar rinci di baliknya, sampai ke PIC dan buktinya.</span></div>
        </div>
    </section>

    <section class="akp-card">
        <h2>Sebelum dan sesudah Akredia</h2>
        <div class="akp-table">
            <table>
                <thead><tr><th>Pekerjaan</th><th>Tanpa Akredia</th><th>Dengan Akredia</th></tr></thead>
                <tbody>
                    <tr><td>Mengetahui kesiapan</td><td>Laporan lisan Ketua LPM di rapat</td><td>AKI, tren mingguan, dan proyeksi skor di dasbor</td></tr>
                    <tr><td>Memilih jalur Terakreditasi atau Unggul</td><td>Perkiraan berdasarkan pengalaman</td><td>Simulasi skor dan cek syarat perlu per jalur, lalu jalur dikunci pimpinan</td></tr>
                    <tr><td>Mengumpulkan bukti</td><td>Folder bersama, nama berkas tidak seragam</td><td>Pustaka bukti bernomor, berversi, divalidasi orang kedua, dengan pengingat kedaluwarsa</td></tr>
                    <tr><td>Menulis LED</td><td>Satu berkas Word dikirim bolak-balik</td><td>Editor per sub-indikator dengan batas kata, sitasi bukti, angka otomatis, dan riwayat revisi</td></tr>
                    <tr><td>Rasio dan tabel LKPT</td><td>Spreadsheet manual, rawan beda angka</td><td>18 metrik dihitung dari data kinerja; 12 tabel LKPT siap ekspor XLSX</td></tr>
                    <tr><td>Mencocokkan dengan PDDikti</td><td>Baru ketahuan saat asesmen</td><td>Rekonsiliasi dengan tingkat keparahan dan alur investigasi</td></tr>
                    <tr><td>Mengendalikan tindak lanjut</td><td>Daftar di grup percakapan</td><td>Tugas, Kanban, kalender tenggat, eskalasi otomatis ke manajer</td></tr>
                    <tr><td>Menyiapkan asesmen lapangan</td><td>Gladi tanpa daftar pertanyaan</td><td>Jadwal sesi, narasumber, bank pertanyaan per kelompok, status gladi</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="akp-card">
        <h2>Peran Akredia di tiap tahap persiapan</h2>
        <p>Akredia mengikuti peta jalan 12 bulan menuju pengajuan. Pimpinan cukup hadir pada dua keputusan; pekerjaan harian dipantau lewat dasbor.</p>
        <ol class="akp-road">
            <li><span class="when">T-12 s.d. T-10 bulan</span><b>Bentuk tim, pilih jalur</b><span>64 butir kerja dibuat otomatis lengkap dengan PIC dan reviewer; simulasi skor jalur Terakreditasi dan Unggul.</span></li>
            <li class="gate"><b>Keputusan 1:</b> pimpinan mengunci jalur akreditasi.</li>
            <li><span class="when">T-10 s.d. T-6 bulan</span><b>Rapikan data, kumpulkan bukti</b><span>Pustaka bukti berversi dengan validasi empat mata; impor data kinerja dan 18 metrik IAPT terhitung otomatis.</span></li>
            <li><span class="when">T-6 s.d. T-3 bulan</span><b>Tulis LED, susun LKPT</b><span>Editor LED per sub-indikator dengan batas kata; 12 tabel LKPT; rekonsiliasi angka dengan PDDikti.</span></li>
            <li class="gate"><b>Keputusan 2:</b> gerbang Siap Diajukan, semua syarat perlu terpenuhi.</li>
            <li><span class="when">T-3 s.d. T-1 bulan</span><b>Kunci data, sahkan, ajukan</b><span>Kunci data TS; pimpinan mengesahkan narasi LED; ekspor LED (DOCX) dan LKPT (XLSX) untuk SAPTO.</span></li>
            <li><span class="when">Pasca pengajuan</span><b>Asesmen kecukupan dan lapangan</b><span>Portal bukti publik untuk asesor; jadwal sesi, narasumber, bank pertanyaan, dan gladi.</span></li>
        </ol>
    </section>

    <section class="akp-card">
        <h2>Coba dalam 15 menit</h2>
        <p>Cukup peramban, tanpa instalasi. Ada tiga perguruan tinggi contoh dengan <b>data demo</b> yang disusun untuk menjelaskan fitur Akredia: profilnya diambil dari laman resmi, sedangkan angka kinerja, nama staf, dan aktivitas adalah ilustrasi.</p>
        @php($tamu = fn (string $slug) => $guestUrl ? \App\Http\Controllers\DemoController::guestUrl($slug) : null)
        <div class="akp-samples">
            @foreach ([
                ['unj', 'Universitas Negeri Jakarta', 'PTN Badan Hukum · jalur Unggul', 'AKI sekitar 75. Satu syarat perlu Unggul (proporsi lektor kepala dan guru besar) belum terpenuhi; SK berakhir Februari 2027.', 'rektor@unj.akredia.test'],
                ['binus', 'Universitas Bina Nusantara', 'PTS Akademik · jalur Unggul', 'AKI sekitar 81. Semua syarat perlu terpenuhi; tim menuntaskan bukti hilirisasi riset sebelum unggah Januari 2027.', 'rektor@binus.akredia.test'],
                ['polban', 'Politeknik Negeri Bandung', 'PTN Vokasi · jalur Unggul', 'AKI sekitar 68 dengan tenggat sebulan. Syarat perlu proporsi prodi Unggul belum terpenuhi; dua prodi segera habis masa akreditasinya.', 'direktur@polban.akredia.test'],
            ] as [$slug, $name, $kind, $story, $email])
                <div class="akp-sample">
                    <b>{{ $name }}</b><small>{{ $kind }}</small>
                    <span>{{ $story }}</span>
                    <span class="akp-sample-foot">@if ($url = $tamu($slug))<a href="{{ $url }}">Masuk sebagai tamu</a> · @endif<code>{{ $email }}</code></span>
                </div>
            @endforeach
        </div>
        <ol class="akp-list">
            <li>@if ($guestUrl)Pilih <b>Masuk sebagai tamu</b> pada salah satu perguruan tinggi di atas, atau masuk @else Masuk @endif dengan akun pimpinannya (kata sandi <code>{{ config('akredia.demo.password') }}</code>).</li>
            <li>Lihat <b>Dasbor Eksekutif</b>: AKI, proyeksi skor, dan syarat perlu yang gagal. Klik kartunya untuk melihat sub-indikator, PIC, dan bukti yang kurang.</li>
            <li>Buka <b>Simulasi Penilaian</b>, centang sub-indikator yang akan dipenuhi, dan lihat proyeksi skor jalur Unggul berubah.</li>
            <li>Buka <b>Penyusunan LED</b> dan baca narasi yang sudah disetujui reviewer dan disahkan pimpinan.</li>
            <li>Buka <b>Panduan Pimpinan</b> untuk peta jalan, cara membaca dasbor, dan peran pimpinan di setiap tahap.</li>
        </ol>
        <figure>
            <img src="{{ asset('img/pimpinan-simulasi.jpg') }}" alt="Simulasi Penilaian" loading="lazy">
            <figcaption>Simulasi Penilaian: dampak setiap sub-indikator pada proyeksi skor dan syarat perlu.</figcaption>
        </figure>
    </section>

    <section class="akp-card">
        <h2>Uji coba 30 hari di kampus Anda</h2>
        <p>Dalam 30 hari kampus sudah punya AKI pertama dari data sendiri dan daftar celah untuk rapat pimpinan. Akredia berdiri sendiri, sehingga tidak mengganggu sistem yang ada.</p>
        <div class="akp-weeks">
            <div><span>Minggu 1</span>Rektor menunjuk Ketua Tim Akreditasi. Institusi didaftarkan, varian instrumen dipilih, akun pimpinan dan PIC dibuat.</div>
            <div><span>Minggu 2</span>PIC menerima butir kerja. Data dosen, mahasiswa, dan lulusan diimpor dari XLSX/CSV; metrik terhitung otomatis.</div>
            <div><span>Minggu 3</span>PIC melakukan penilaian mandiri dan mengunggah bukti yang sudah ada. Reviewer memvalidasi bukti.</div>
            <div><span>Minggu 4</span>Rapat pimpinan memakai Dasbor Eksekutif: memilih jalur, menetapkan tindak lanjut, dan memutuskan kelanjutan.</div>
        </div>
        <p>Komitmen pimpinan cukup tiga: menunjuk ketua tim, meminta PIC memakai Akredia sebagai satu-satunya tempat bukti, dan membuka dasbor di rapat pimpinan setiap minggu.</p>
    </section>

    <section class="akp-card">
        <h2>Data, keamanan, dan biaya infrastruktur</h2>
        <ul class="akp-bullets">
            <li><b>Di server kampus sendiri.</b> Cukup hosting cPanel dengan PHP 8.3 dan MySQL 8, tanpa Node.js, Redis, atau layanan berbayar tambahan.</li>
            <li><b>Hak akses per peran.</b> Delapan peran, cakupan unit, dan kerahasiaan dokumen per unit.</li>
            <li><b>Prinsip empat mata.</b> PIC tidak mereviu butirnya sendiri, pengunggah tidak memvalidasi buktinya sendiri, pelaksana CAPA tidak memverifikasi dirinya sendiri.</li>
            <li><b>Jejak audit dan autentikasi dua langkah.</b> Setiap perubahan penting tercatat dan dapat diekspor.</li>
        </ul>
    </section>

    <section class="akp-card">
        <h2>Pertanyaan yang sering diajukan pimpinan</h2>
        <details><summary>Apakah ini menambah beban kerja tim?</summary><p>Tidak. Pekerjaannya sama: menilai, mengumpulkan bukti, menulis LED. Bedanya, semua di satu tempat sehingga waktu mencari berkas dan menyamakan angka berkurang.</p></details>
        <details><summary>Apakah instrumennya sesuai jenis kampus kami?</summary><p>Akredia memuat delapan varian Buku 3 IAPT 4.1, termasuk PTN-BH, PTN, PTS, vokasi, dan PJJ. Deskriptor dan syarat perlu dapat diperbarui admin bila BAN-PT merevisi lampiran.</p></details>
        <details><summary>Bisakah memakai data yang sudah ada?</summary><p>Bisa. Data dosen, statistik prodi, kohort, publikasi, dan survei dapat diimpor dari XLSX atau CSV.</p></details>
        <details><summary>Apakah AKI sama dengan nilai BAN-PT?</summary><p>Tidak. AKI dan proyeksi skor adalah alat manajemen internal. Keputusan akreditasi sepenuhnya kewenangan BAN-PT.</p></details>
        <details><summary>Bagaimana jika pengajuan sudah dekat?</summary><p>Justru paling bermanfaat. Gerbang Siap Diajukan, cek syarat perlu, rekonsiliasi PDDikti, dan bank pertanyaan asesmen lapangan membantu menutup celah dalam hitungan minggu.</p></details>
    </section>

    @if ($standalone && $guestUrl)
        <section class="akp-hero akp-end">
            <div>
                <h2>Lihat sendiri dengan data demo UNJ, BINUS, atau POLBAN</h2>
                <p>Masuk sebagai tamu dan jelajahi dasbor, kertas kerja, LED, dan simulasi skor. Tamu hanya dapat melihat, dan perguruan tinggi contoh dapat diganti dari pita di atas layar.</p>
                <div class="akp-cta"><a class="akp-btn" href="{{ $guestUrl }}">Masuk sebagai tamu</a></div>
            </div>
        </section>
    @endif
</div>
