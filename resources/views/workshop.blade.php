{{-- Halaman publik penawaran workshop dua hari: sasaran, luaran, agenda lengkap, dan persiapan peserta. --}}
@php
    use App\Support\Workshop;

    $signedIn = auth()->check();
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Workshop · {{ Workshop::TITLE }}</title>
<meta name="description" content="{{ Workshop::TAGLINE }} Bekerja sama dengan {{ Workshop::PARTNER }}.">
<link rel="icon" href="{{ asset('favicon.svg') }}">
<link rel="stylesheet" href="{{ asset('fonts/filament/filament/inter/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ @filemtime(public_path('css/landing.css')) }}">
</head>
<body class="akl">
<header class="akl-nav">
    <div class="akl-wrap akl-nav-in">
        <a class="akl-brand" href="{{ route('home') }}">
            <img src="{{ asset('favicon.svg') }}" width="34" height="34" alt="">
            <span><b>Akredia</b><small>Persiapan akreditasi institusi</small></span>
        </a>
        <nav class="akl-links" aria-label="Navigasi halaman">
            <a href="#sasaran">Untuk siapa</a>
            <a href="#luaran">Luaran</a>
            <a href="#agenda">Agenda</a>
            <a href="#persiapan">Persiapan</a>
            <a href="{{ route('home') }}">Halaman depan</a>
        </nav>
        <a class="akl-btn outline small" href="{{ $signedIn ? url('/app') : $loginUrl }}">{{ $signedIn ? 'Buka aplikasi' : 'Masuk' }}</a>
    </div>
</header>

<main>
    <section class="akl-sec akl-ws akw-hero">
        <div class="akl-wrap">
            <div class="akl-ws-head">
                <div>
                    <div class="akl-eyebrow">Workshop 2 hari · IAPT 4.1</div>
                    <h1 style="font-size:clamp(1.8rem,4vw,2.7rem);line-height:1.15;font-weight:800;margin:.5rem 0 1rem">{{ Workshop::TITLE }}</h1>
                    <p class="akl-lead">{{ Workshop::TAGLINE }}</p>
                    <ul class="akw-meta">
                        <li>2 hari · 8 sesi</li>
                        <li>08.00–16.30</li>
                        <li>Praktik langsung di Akredia</li>
                        <li>Kelas terbuka atau in-house</li>
                    </ul>
                </div>
                <div class="akl-ws-partner">
                    <img src="{{ asset('img/mitra-uin-sunan-kalijaga.png') }}" alt="Logo UIN Sunan Kalijaga Yogyakarta" width="72" height="101">
                    <span>Bekerja sama dengan<b>{{ Workshop::PARTNER }}</b></span>
                </div>
            </div>
            <div class="akl-cta">
                <a class="akl-btn" href="{{ Workshop::waUrl() }}" target="_blank" rel="noopener">Tanya jadwal &amp; daftar via WhatsApp</a>
                <a class="akl-btn outline" href="{{ Workshop::mailUrl() }}">Kirim email</a>
            </div>
        </div>
    </section>

    <section class="akl-sec" id="sasaran">
        <div class="akl-wrap">
            <div class="akl-split">
                <div>
                    <div class="akl-eyebrow">Untuk siapa</div>
                    <h2>Tim inti yang akan menyiapkan akreditasi institusi</h2>
                    <p>Workshop paling bermanfaat bila diikuti sebagai tim 3–5 orang dari satu perguruan tinggi, sehingga keputusan pembagian kerja dan rencana aksi dapat langsung disepakati.</p>
                </div>
                <div class="akl-card">
                    <ul class="akl-check" style="margin-top:0">
                        @foreach (Workshop::AUDIENCE as $who)
                            <li>{{ $who }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="akl-sec soft" id="luaran">
        <div class="akl-wrap">
            <div class="akl-eyebrow">Yang dibawa pulang</div>
            <h2>Enam luaran kerja, bukan sekadar catatan</h2>
            <div class="akl-grid three">
                @foreach (Workshop::OUTCOMES as [$title, $desc])
                    <div class="akl-card">
                        <h3><i></i>{{ $title }}</h3>
                        <p>{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="akl-sec" id="agenda">
        <div class="akl-wrap">
            <div class="akl-eyebrow">Agenda dan materi</div>
            <h2>Dua hari, delapan sesi</h2>
            <p class="akl-sub">Setiap sesi memadukan paparan singkat, praktik di Akredia dengan data contoh, dan satu luaran yang dikerjakan untuk kampus peserta sendiri.</p>
            @foreach (Workshop::DAYS as $n => [$label, $theme, $sessions])
                <div class="akw-day">
                    <div class="akl-ws-daylabel">{{ $label }}</div>
                    <h3>{{ $theme }}</h3>
                    @foreach ($sessions as [$time, $title, $topics, $practice, $output])
                        <div class="akw-sess">
                            <div class="akw-time">{{ $time }}</div>
                            <div>
                                <h4>{{ $title }}</h4>
                                <ul>
                                    @foreach ($topics as $topic)
                                        <li>{{ $topic }}</li>
                                    @endforeach
                                </ul>
                                @if ($practice || $output)
                                    <div class="akw-do">
                                        @if ($practice)<div><b>Praktik di Akredia</b>{{ $practice }}</div>@endif
                                        @if ($output)<div class="out"><b>Luaran</b>{{ $output }}</div>@endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <p class="akw-ref">Istirahat dan ishoma mengikuti jeda antarsesi. Materi merujuk pada PerBAN-PT Nomor 35 Tahun 2025 tentang Instrumen Akreditasi Perguruan Tinggi (IAPT 4.1): Lampiran 2 (kriteria dan indikator), Lampiran 3 (deskripsi pemenuhan per varian), Lampiran 4a (Panduan LED), dan Lampiran 4b (Panduan LKPT). Susunan sesi dapat disesuaikan untuk penyelenggaraan in-house.</p>
        </div>
    </section>

    <section class="akl-sec soft" id="persiapan">
        <div class="akl-wrap">
            <div class="akl-grid two">
                <div class="akl-card">
                    <div class="akl-eyebrow">Persiapan peserta</div>
                    <h3 style="margin-top:.4rem">Yang perlu dibawa</h3>
                    <ul class="akl-check">
                        @foreach (Workshop::PREPARE as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="akl-card">
                    <div class="akl-eyebrow">Penyelenggaraan</div>
                    <h3 style="margin-top:.4rem">Kelas terbuka atau in-house</h3>
                    <ul class="akl-check">
                        <li>Kelas terbuka: beberapa perguruan tinggi belajar bersama dan saling mereviu draf.</li>
                        <li>In-house: diselenggarakan di kampus Anda dengan data dan dokumen sendiri, agenda dapat disesuaikan.</li>
                        <li>Fasilitator dari Pusat Studi Manajemen Perguruan Tinggi UIN Sunan Kalijaga dan tim Rucisoft.</li>
                        <li>Jadwal, lokasi, dan biaya diinformasikan melalui email {{ Workshop::EMAIL }} atau WhatsApp {{ Workshop::PHONE }}.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="akl-end">
        <div class="akl-wrap">
            <h2>Siapkan tim akreditasi Anda dalam dua hari</h2>
            <p>Hubungi kami untuk jadwal kelas terbuka berikutnya atau penyelenggaraan in-house.</p>
            <div class="akl-cta center">
                <a class="akl-btn gold" href="{{ Workshop::waUrl() }}" target="_blank" rel="noopener">WhatsApp {{ Workshop::PHONE }}</a>
                <a class="akl-btn ghost" href="{{ Workshop::mailUrl() }}">{{ Workshop::EMAIL }}</a>
            </div>
        </div>
    </section>
</main>

<footer class="akl-foot">
    <div class="akl-wrap">
        <b>Akredia</b> · persiapan akreditasi institusi IAPT 4.1 · <a href="https://rucisoft.id" style="color:inherit;text-decoration:underline">Rucisoft</a> · <a href="https://rucisoft.id" style="color:inherit">rucisoft.id</a>
        <p>Workshop diselenggarakan bersama {{ Workshop::PARTNER }}. Akredia adalah alat bantu persiapan internal, bukan bagian dari BAN-PT.</p>
    </div>
</footer>
</body>
</html>
