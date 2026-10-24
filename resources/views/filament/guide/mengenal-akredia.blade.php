<x-filament-panels::page>
<div class="ak-guide">
    <div class="ak-guide-hero">
        <h2>Akredia: ruang kendali persiapan akreditasi institusi</h2>
        <p>Akreditasi institusi dengan IAPT 4.1 (PerBAN-PT No. 35 Tahun 2025) menilai 39 indikator dalam empat kriteria: Budaya Mutu, Relevansi, Akuntabilitas, dan Diferensiasi Misi. Setiap indikator dinilai biner, ada syarat perlu yang menggugurkan bila gagal, dan narasi LED dibatasi jumlah katanya. Akredia mengubah pekerjaan besar itu menjadi butir kerja yang jelas pemiliknya, buktinya, dan statusnya, sehingga pimpinan dapat melihat kesiapan institusi kapan saja tanpa menunggu rapat.</p>
    </div>

    <div class="ak-guide-grid">
        <div class="ak-card">
            <div class="icon">◎</div>
            <h3>Satu angka kesiapan yang dapat dipertanggungjawabkan</h3>
            <p>Indeks Kesiapan Akredia (AKI) menggabungkan pemenuhan deskriptor (40%), kesiapan bukti (20%), kualitas data (15%), kematangan LED (15%), dan eksekusi tugas (10%). Setiap komponen dapat ditelusuri sampai ke dokumen atau tugas di baliknya.</p>
            <a class="more" href="{{ $l['dashboard'] }}">Buka dasbor →</a>
        </div>
        <div class="ak-card">
            <div class="icon">⇆</div>
            <h3>Dua jalur diproyeksikan berdampingan</h3>
            <p>Proyeksi skor Terakreditasi dan Unggul dihitung bersamaan dari penilaian per sub-indikator. Pimpinan dapat memutuskan jalur berdasarkan data, lalu mengunci jalur agar seluruh tim bekerja pada deskriptor yang sama.</p>
            <a class="more" href="{{ $l['project'] }}">Lihat proyek →</a>
        </div>
        <div class="ak-card">
            <div class="icon">⚑</div>
            <h3>Syarat perlu tidak terlewat</h3>
            <p>Sub-indikator syarat perlu (misalnya SPMI berjalan konsisten, seluruh prodi terakreditasi, kecukupan dosen tetap, dan jabatan akademik dosen) ditandai khusus. Satu syarat perlu yang gagal langsung tampak merah di dasbor dan menutup gerbang pengajuan.</p>
            <a class="more" href="{{ $l['items'] }}">Butir kerja →</a>
        </div>
        <div class="ak-card">
            <div class="icon">▤</div>
            <h3>Bukti tunggal, dipakai berkali-kali</h3>
            <p>Satu dokumen bukti dapat mendukung banyak sub-indikator. Setiap versi disimpan dengan sidik SHA-256, divalidasi orang yang berbeda dari pengunggah, dan diberi tanggal kedaluwarsa. Tautan bukti publik siap ditempel di LED SAPTO.</p>
            <a class="more" href="{{ $l['documents'] }}">Pustaka Bukti →</a>
        </div>
        <div class="ak-card">
            <div class="icon">✎</div>
            <h3>LED tersusun rapi dan terkendali</h3>
            <p>Editor per sub-indikator menyediakan templat PPEPP dan analisis akar masalah, penghitung kata langsung terhadap batas 1.500 kata, panel kata kunci deskriptor, sitasi bukti, dan angka hidup dari data kinerja. Alurnya draf, reviu, revisi, disetujui reviewer, lalu disahkan pimpinan.</p>
            <a class="more" href="{{ $l['led'] }}">Penyusunan LED →</a>
        </div>
        <div class="ak-card">
            <div class="icon">∑</div>
            <h3>Data kinerja dan LKPT konsisten</h3>
            <p>Rasio seperti RMDT, PDTT, persentase doktor, DTJA, kelulusan tepat waktu, dan masa tunggu lulusan dihitung otomatis dari data dosen, mahasiswa, dan tracer. Selisih dengan PD Dikti dicatat sebagai rekonsiliasi berkeparahan.</p>
            <a class="more" href="{{ $l['metrics'] }}">Nilai metrik →</a>
        </div>
        <div class="ak-card">
            <div class="icon">✓</div>
            <h3>Gerbang "Siap Diajukan"</h3>
            <p>Proyek baru dapat dinyatakan siap diajukan bila tujuh syarat terpenuhi: semua syarat perlu memenuhi, proyeksi melewati ambang, tidak ada selisih data tinggi, seluruh LED disetujui, tidak ada bukti dikutip yang kedaluwarsa, eligibilitas lengkap, dan data TS sudah dikunci.</p>
        </div>
        <div class="ak-card">
            <div class="icon">⚙</div>
            <h3>Pengendalian dan SPMI dalam satu tempat</h3>
            <p>Tugas dan RTL, register risiko 5×5, keputusan pimpinan, AMI, temuan, CAPA, dan rapat tinjauan manajemen terhubung ke indikator. Hasilnya langsung menjadi bukti indikator Budaya Mutu.</p>
            <a class="more" href="{{ $l['tasks'] }}">Tugas & RTL →</a>
        </div>
        <div class="ak-card">
            <div class="icon">☷</div>
            <h3>Siap asesmen lapangan</h3>
            <p>Agenda sesi, daftar narasumber per kelompok (pimpinan, dosen, tendik, mahasiswa, lulusan, pengguna), bank pertanyaan per indikator, gladi, dan tindak lanjut rekomendasi asesor disiapkan sejak awal.</p>
            <a class="more" href="{{ $l['visit'] }}">Asesmen lapangan →</a>
        </div>
    </div>

    <div class="ak-card">
        <h3>Enam belas modul Akredia</h3>
        <ul>
            <li><b>M01 Institusi & Struktur</b>: profil PT, unit, program studi dan riwayat akreditasinya, periode akademik.</li>
            <li><b>M02 Pustaka Instrumen</b>: IAPT 4.1 delapan varian, kriteria, indikator, sub-indikator, deskriptor per jalur, kata kunci, syarat perlu, rumus.</li>
            <li><b>M03 Proyek Akreditasi</b>: jalur, TS, target, matriks PIC, eligibilitas, gerbang kesiapan, kunci data.</li>
            <li><b>M04 Analisis Kesenjangan</b>: kertas kerja per kata kunci deskriptor dan RTL.</li>
            <li><b>M05 Pustaka Bukti</b>: dokumen berversi, validasi empat mata, bukti publik, pemeriksa tautan.</li>
            <li><b>M06 Data Kinerja & LKPT</b>: dosen, mahasiswa, kohort, prestasi, publikasi, HKI, rekognisi, hibah, audit keuangan, tabel LKPT.</li>
            <li><b>M07 Rekonsiliasi Data</b>: perbandingan dengan PD Dikti dan alur investigasinya.</li>
            <li><b>M08 Penyusunan LED</b>: editor, reviu berlapis, revisi, ekspor.</li>
            <li><b>M09 Simulasi Penilaian & Kesiapan</b>: penilaian mandiri dan asesor internal, proyeksi, analisis what-if, AKI.</li>
            <li><b>M10 SPMI</b>: standar, AMI, temuan, CAPA, RTM.</li>
            <li><b>M11 Survei & Tracer</b>: kepuasan enam kelompok pemangku kepentingan, tracer study, kepuasan pengguna lulusan.</li>
            <li><b>M12 Tugas, Risiko, Keputusan</b>: Kanban, kalender tenggat, risiko 5×5, keputusan pimpinan yang menurunkan tugas.</li>
            <li><b>M13 Persiapan Asesmen Lapangan</b>: jadwal, narasumber, bank pertanyaan, rekomendasi.</li>
            <li><b>M14 Dasbor & Laporan</b>: dasbor eksekutif, laporan mingguan, ekspor LED, LKPT, kertas kerja, syarat perlu.</li>
            <li><b>M15 Administrasi</b>: pengguna, peran, MFA, log audit.</li>
            <li><b>M16 Asisten Narasi</b>: pemeriksaan narasi terhadap deskriptor; saran AI opsional yang selalu menunggu keputusan manusia.</li>
        </ul>
    </div>

    <div class="ak-card">
        <h3>Lanjutkan membaca</h3>
        <ul>
            <li><a href="{{ $l['guide_roadmap'] }}">Peta jalan persiapan 12 bulan</a> dan modul yang dipakai di setiap tahap.</li>
            <li><a href="{{ $l['guide_iapt'] }}">Memahami IAPT 4.1</a>: empat kriteria, 39 indikator, dan syarat perlu.</li>
            <li><a href="{{ $l['guide_role'] }}">Peran pimpinan</a>: keputusan yang hanya dapat diambil pimpinan.</li>
            <li><a href="{{ $l['guide_dashboard'] }}">Cara membaca dasbor eksekutif</a>.</li>
            <li><a href="{{ $l['guide_faq'] }}">Tanya jawab</a> yang sering diajukan pimpinan.</li>
        </ul>
    </div>
</div>
</x-filament-panels::page>
