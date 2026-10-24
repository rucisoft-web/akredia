@php
    $k = $d['kpi'];
    $p = $d['project'];
    $fmt = fn ($n, $dec = 1) => number_format((float) $n, $dec, ',', '.');
    $c = ['blue' => '#1f528c', 'sky' => '#0ea5e9', 'green' => '#10b981', 'amber' => '#f59e0b', 'red' => '#e11d48', 'gray' => '#94a3b8', 'violet' => '#7c3aed', 'teal' => '#14b8a6'];
    $heatColors = ['green' => $c['green'], 'yellow' => $c['amber'], 'red' => $c['red'], 'gray' => $c['gray']];

    // Langkah yang disarankan untuk pimpinan, disusun dari data.
    $actions = [];
    if (! $p) { $actions[] = ['Buat proyek akreditasi institusi agar butir kerja 39 indikator dibangkitkan.', $links['project'], 'Buat proyek']; }
    if ($k['prerequisites_failed'] > 0) { $actions[] = [$k['prerequisites_failed'].' sub-indikator syarat perlu belum memenuhi. Akreditasi tidak dapat diraih selama syarat perlu gagal.', $links['prerequisite'], 'Lihat syarat perlu']; }
    if ($k['unassigned'] > 0) { $actions[] = [$k['unassigned'].' sub-indikator belum memiliki PIC. Tetapkan penanggung jawab agar pekerjaan berjalan.', $links['unassigned'], 'Tetapkan PIC']; }
    if ($k['tasks_overdue'] > 0) { $actions[] = [$k['tasks_overdue'].' tugas melewati tenggat. Minta unit terkait melaporkan hambatan di rapat berikutnya.', $links['tasks_overdue'], 'Lihat tugas']; }
    if ($k['recon_high'] > 0) { $actions[] = [$k['recon_high'].' selisih data dengan PD Dikti berkeparahan tinggi. Gerbang pengajuan tertutup sampai selesai.', $links['recon'], 'Tinjau rekonsiliasi']; }
    if ($k['evidence_queue'] > 0) { $actions[] = [$k['evidence_queue'].' dokumen bukti menunggu validasi reviewer.', $links['doc_queue'], 'Buka antrean']; }
    if (count($d['programs']['expiring'])) { $actions[] = [count($d['programs']['expiring']).' akreditasi prodi berakhir dalam 18 bulan. Seluruh prodi aktif harus terakreditasi saat pengajuan.', $links['programs'], 'Lihat prodi']; }
    if ($k['risks_high'] > 0) { $actions[] = [$k['risks_high'].' risiko tingkat tinggi/kritis masih terbuka.', $links['risks'], 'Register risiko']; }
@endphp
<x-filament-panels::page>
<div class="ak-dash">
    <div class="ak-hero">
        <div class="ak-hero-text">
            <div class="ak-eyebrow">{{ $institution?->name }}</div>
            <h1>Kesiapan Akreditasi Institusi</h1>
            <p>
                @if ($p)
                    <a href="{{ $links['project'] }}">{{ $p->name }}</a> · jalur target <b>{{ $p->track->getLabel() }}</b> · status <b>{{ $p->state->getLabel() }}</b>
                    @if (! is_null($k['days_to_target'])) · <b>{{ $k['days_to_target'] >= 0 ? $k['days_to_target'].' hari' : 'lewat '.abs($k['days_to_target']).' hari' }}</b> menuju target pengajuan @endif
                @else
                    Belum ada proyek akreditasi. <a href="{{ $links['project'] }}">Buat proyek</a> untuk memulai.
                @endif
            </p>
            <p class="ak-muted">IAPT 4.1 (PerBAN-PT 35/2025) · klik kartu, grafik, atau sel untuk membuka data di baliknya · per {{ $d['today']->translatedFormat('d F Y') }} · <a href="{{ $links['guide'] }}">Cara membaca dasbor</a></p>
        </div>
        <a class="ak-gauge" href="{{ $links['project'] }}" style="--pct: {{ min(100, $k['aki']) }}" title="Indeks Kesiapan Akredia">
            <svg viewBox="0 0 120 70"><path d="M10 62 A50 50 0 0 1 110 62" class="bg"/><path d="M10 62 A50 50 0 0 1 110 62" class="fg" pathLength="100" style="stroke-dasharray: {{ min(100, $k['aki']) }} 100"/></svg>
            <strong>{{ $fmt($k['aki']) }}</strong>
            <span>Indeks Kesiapan Akredia</span>
        </a>
    </div>

    <div class="ak-kpis">
        <a class="ak-kpi {{ $k['projection_accredited'] >= $k['threshold_accredited'] ? 'good' : 'danger' }}" href="{{ $links['items'] }}">
            <span>Proyeksi Terakreditasi</span><strong>{{ $fmt($k['projection_accredited']) }}%</strong><small>ambang {{ $fmt($k['threshold_accredited'], 0) }}%</small></a>
        <a class="ak-kpi {{ $k['projection_excellent'] >= $k['threshold_excellent'] ? 'good' : 'warning' }}" href="{{ $links['items'] }}">
            <span>Proyeksi Unggul</span><strong>{{ $fmt($k['projection_excellent']) }}%</strong><small>ambang {{ $fmt($k['threshold_excellent'], 0) }}%</small></a>
        <a class="ak-kpi {{ $k['prerequisites_failed'] ? 'danger' : 'good' }}" href="{{ $links['prerequisite'] }}">
            <span>Syarat perlu gagal</span><strong>{{ $k['prerequisites_failed'] }}</strong><small>harus nol →</small></a>
        <a class="ak-kpi {{ $k['gate_passed'] === $k['gate_total'] ? 'good' : 'warning' }}" href="{{ $links['project'] }}">
            <span>Gerbang Siap Diajukan</span><strong>{{ $k['gate_passed'] }}/{{ $k['gate_total'] }}</strong><small>syarat terpenuhi →</small></a>
        <a class="ak-kpi" href="{{ $links['green'] }}">
            <span>Sub-indikator hijau</span><strong>{{ $k['sub_green'] }}/{{ $k['sub_total'] }}</strong><small>{{ $k['sub_red'] }} merah →</small></a>
        <a class="ak-kpi" href="{{ $links['documents'] }}">
            <span>Kata kunci berbukti</span><strong>{{ $fmt($k['keywords_supported_pct']) }}%</strong><small>{{ $k['evidence_queue'] }} bukti antre validasi →</small></a>
        <a class="ak-kpi" href="{{ $links['led'] }}">
            <span>LED disetujui</span><strong>{{ $fmt($k['led_approved_pct']) }}%</strong><small>{{ $fmt($k['led_words'], 0) }} kata ditulis →</small></a>
        <a class="ak-kpi {{ $k['tasks_overdue'] ? 'danger' : '' }}" href="{{ $links['tasks_overdue'] }}">
            <span>Tugas terlambat</span><strong>{{ $k['tasks_overdue'] }}</strong><small>dari {{ $k['tasks_open'] }} tugas terbuka →</small></a>
    </div>

    <div class="ak-grid">
        <section class="ak-panel span-2">
            <h3>Tren kesiapan mingguan <small>AKI dan proyeksi kedua jalur</small></h3>
            <div class="ak-chart h-260">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'line',
                    'data' => ['labels' => $d['trend']['labels'], 'datasets' => [
                        ['label' => 'AKI', 'data' => $d['trend']['aki'], 'borderColor' => $c['blue'], 'backgroundColor' => $c['blue'].'22', 'fill' => true, 'tension' => 0.35, 'borderWidth' => 3],
                        ['label' => 'Proyeksi Terakreditasi', 'data' => $d['trend']['accredited'], 'borderColor' => $c['green'], 'tension' => 0.35, 'borderWidth' => 2],
                        ['label' => 'Proyeksi Unggul', 'data' => $d['trend']['excellent'], 'borderColor' => $c['violet'], 'tension' => 0.35, 'borderWidth' => 2, 'borderDash' => [6, 4]],
                        ['label' => 'Ambang', 'data' => array_fill(0, count($d['trend']['labels']), $k['threshold_accredited']), 'borderColor' => $c['red'], 'borderWidth' => 1, 'borderDash' => [3, 3], 'pointRadius' => 0],
                    ]],
                    'options' => ['scales' => ['y' => ['min' => 0, 'max' => 100]]],
                ]) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel">
            <h3>Komponen AKI <small>bobot {{ collect($d['components'])->pluck('weight')->implode('/') }}</small></h3>
            <div class="ak-chart h-260">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'radar',
                    'data' => ['labels' => collect($d['components'])->pluck('label'), 'datasets' => [
                        ['label' => 'Skor', 'data' => collect($d['components'])->pluck('value'), 'borderColor' => $c['blue'], 'backgroundColor' => $c['blue'].'33', 'pointBackgroundColor' => $c['blue']],
                    ]],
                    'options' => ['scales' => ['r' => ['min' => 0, 'max' => 100, 'ticks' => ['stepSize' => 25]]], 'plugins' => ['legend' => ['display' => false]]],
                ]) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel span-3">
            <h3>Peta panas 39 indikator IAPT 4.1 <small>warna terburuk dari sub-indikatornya · klik untuk membuka</small></h3>
            @if ($d['heatmap']->isEmpty())
                <p class="ak-muted">Belum ada butir kerja.</p>
            @else
                <div class="ak-heatmap">
                    @foreach ($d['heatmap']->groupBy('criterion') as $crit => $rows)
                        <div class="ak-heat-row">
                            <span class="crit">K{{ $crit }}</span>
                            <div class="cells">
                                @foreach ($rows as $row)
                                    <a class="ak-heat {{ $row['color'] }} {{ $row['prerequisite'] ? 'prereq' : '' }}" href="{{ $links['indicator']($row['number']) }}"
                                       title="Indikator {{ $row['number'] }}: {{ \Illuminate\Support\Str::limit($row['indicator']->statement, 140) }}&#10;{{ $row['reason'] }}">
                                        {{ $row['number'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="ak-legend">
                    <span class="green">Memenuhi, bukti & LED lengkap</span><span class="yellow">Memenuhi, bukti/LED belum lengkap</span><span class="red">Tidak memenuhi</span><span class="gray">Belum dinilai</span><span class="prereq">Bertitik: memuat syarat perlu</span>
                </div>
            @endif
        </section>

        <section class="ak-panel span-2">
            <h3>Status sub-indikator per kriteria <small>klik batang untuk membuka</small></h3>
            <div class="ak-chart h-220">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'bar',
                    'data' => ['labels' => collect($d['criteria'])->pluck('label'), 'datasets' => [
                        ['label' => 'Hijau', 'data' => collect($d['criteria'])->pluck('green'), 'backgroundColor' => $c['green']],
                        ['label' => 'Kuning', 'data' => collect($d['criteria'])->pluck('yellow'), 'backgroundColor' => $c['amber']],
                        ['label' => 'Merah', 'data' => collect($d['criteria'])->pluck('red'), 'backgroundColor' => $c['red']],
                        ['label' => 'Belum dinilai', 'data' => collect($d['criteria'])->pluck('gray'), 'backgroundColor' => $c['gray']],
                    ]],
                    'options' => ['indexAxis' => 'y', 'scales' => ['x' => ['stacked' => true], 'y' => ['stacked' => true]]],
                ]) }}" data-links="{{ json_encode(collect($d['criteria'])->map(fn ($r) => $links['criterion']($r['id']))) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel">
            <h3>Langkah pimpinan minggu ini <small>disusun otomatis dari data</small></h3>
            @if ($actions)
                <ol class="ak-actions">
                    @foreach (array_slice($actions, 0, 6) as [$text, $url, $label])
                        <li><p>{{ $text }}</p><a href="{{ $url }}">{{ $label }} →</a></li>
                    @endforeach
                </ol>
            @else
                <p class="ak-good">Tidak ada hambatan utama. Pertahankan ritme reviu mingguan.</p>
            @endif
        </section>

        <section class="ak-panel span-2">
            <h3>Syarat perlu terakreditasi & unggul <small>status kedua jalur</small></h3>
            <div class="ak-table-wrap">
                <table class="ak-table">
                    <thead><tr><th>Kode</th><th>Sub-indikator</th><th>Syarat jalur</th><th>Terakreditasi</th><th>Unggul</th></tr></thead>
                    <tbody>
                    @forelse ($d['prerequisites'] as $r)
                        <tr onclick="window.location='{{ $links['item']($r['item']) }}'">
                            <td><b>{{ $r['code'] }}</b></td>
                            <td>{{ \Illuminate\Support\Str::limit($r['statement'], 110) }}</td>
                            <td>{{ $r['track'] }}</td>
                            @foreach (['met_accredited', 'met_excellent'] as $col)
                                <td><span class="ak-pill {{ $r[$col] === null ? 'gray' : ($r[$col] ? 'green' : 'red') }}">{{ $r[$col] === null ? 'Belum dinilai' : ($r[$col] ? 'Memenuhi' : 'Tidak') }}</span></td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="5" class="ak-muted">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ak-panel">
            <h3>Status dokumen bukti <small>klik irisan</small></h3>
            <div class="ak-chart h-220">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'doughnut',
                    'data' => ['labels' => collect($d['evidence'])->pluck('label'), 'datasets' => [[
                        'data' => collect($d['evidence'])->pluck('value'),
                        'backgroundColor' => [$c['gray'], $c['sky'], $c['green'], $c['red'], $c['amber']],
                    ]]],
                    'options' => ['cutout' => '62%', 'plugins' => ['legend' => ['position' => 'right']]],
                ]) }}" data-links="{{ json_encode(collect($d['evidence'])->map(fn ($r) => $links['doc_status']($r['key']))) }}"></canvas>
            </div>
            <p class="ak-muted"><a href="{{ $links['doc_expiring'] }}">{{ $k['evidence_expiring'] }} bukti kedaluwarsa ≤ 60 hari →</a></p>
        </section>

        <section class="ak-panel">
            <h3>Kemajuan LED <small>{{ $k['sub_total'] }} bagian</small></h3>
            <div class="ak-chart h-220">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'doughnut',
                    'data' => ['labels' => collect($d['led'])->pluck('label'), 'datasets' => [[
                        'data' => collect($d['led'])->pluck('value'),
                        'backgroundColor' => ['#e2e8f0', $c['gray'], $c['sky'], $c['amber'], $c['teal'], $c['green']],
                    ]]],
                    'options' => ['cutout' => '62%', 'plugins' => ['legend' => ['position' => 'right']]],
                ]) }}" data-links="{{ json_encode(collect($d['led'])->map(fn ($r) => $links['led_state']($r['key']))) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel span-2">
            <h3>Rasio kunci terhadap ambang jalur target <small>nilai TS · klik untuk rincian rumus</small></h3>
            <div class="ak-ratios">
                @forelse ($d['ratios'] as $r)
                    <a class="ak-ratio {{ $r['meets'] === false ? 'bad' : ($r['meets'] ? 'ok' : '') }}" href="{{ $links['metrics'] }}" title="{{ $r['name'] }} · ambang {{ $r['threshold'] }}">
                        <span class="label"><b>{{ $r['code'] }}</b> {{ \Illuminate\Support\Str::limit($r['name'], 46) }}</span>
                        <span class="track">
                            <span class="fill" style="width: {{ min(100, max(0, $r['value'] / $r['scale'] * 100)) }}%"></span>
                            @if (! is_null($r['min']))<span class="mark" style="left: {{ min(100, $r['min'] / $r['scale'] * 100) }}%"></span>@endif
                            @if (! is_null($r['max']))<span class="mark max" style="left: {{ min(100, $r['max'] / $r['scale'] * 100) }}%"></span>@endif
                        </span>
                        <b class="val">{{ $fmt($r['value'], 2) }}{{ $r['unit'] === '%' ? '%' : '' }}</b>
                    </a>
                @empty
                    <p class="ak-muted">Metrik belum dihitung. Klik "Perbarui angka".</p>
                @endforelse
            </div>
            <div class="ak-legend"><span class="ok">Memenuhi ambang</span><span class="bad">Belum memenuhi</span><span class="mark">Garis: batas ambang</span></div>
        </section>

        <section class="ak-panel">
            <h3>Matriks risiko akreditasi <small>probabilitas × dampak</small></h3>
            <div class="ak-matrix">
                @foreach ($d['risk_matrix'] as $cell)
                    <a class="cell {{ $cell['level'] }} {{ $cell['count'] ? 'has' : '' }}" href="{{ $links['risk']($cell['p'], $cell['i']) }}" title="P{{ $cell['p'] }} × D{{ $cell['i'] }}: {{ $cell['count'] }} risiko">
                        <b>{{ $cell['count'] ?: '' }}</b>
                    </a>
                @endforeach
            </div>
            <div class="ak-axis"><span>↑ Probabilitas</span><span>Dampak →</span></div>
        </section>

        <section class="ak-panel span-2">
            <h3>Tugas terbuka per unit <small>klik batang untuk membuka</small></h3>
            <div class="ak-chart h-220">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'bar',
                    'data' => ['labels' => collect($d['tasks_by_unit'])->pluck('label'), 'datasets' => [
                        ['label' => 'Terlambat', 'data' => collect($d['tasks_by_unit'])->pluck('overdue'), 'backgroundColor' => $c['red']],
                        ['label' => 'Terbuka', 'data' => collect($d['tasks_by_unit'])->map(fn ($r) => $r['open'] - $r['overdue']), 'backgroundColor' => $c['sky']],
                    ]],
                    'options' => ['scales' => ['x' => ['stacked' => true], 'y' => ['stacked' => true, 'ticks' => ['precision' => 0]]]],
                ]) }}" data-links="{{ json_encode(collect($d['tasks_by_unit'])->map(fn ($r) => $links['tasks_unit']($r['unit_id']))) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel">
            <h3>Temuan & rekonsiliasi terbuka</h3>
            <div class="ak-chart h-220">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'bar',
                    'data' => ['labels' => array_merge(array_keys($d['findings']), ['Selisih tinggi', 'Selisih sedang', 'Selisih rendah']), 'datasets' => [[
                        'label' => 'Jumlah',
                        'data' => array_merge(array_values($d['findings']), [$d['reconciliations']['tinggi'] ?? 0, $d['reconciliations']['sedang'] ?? 0, $d['reconciliations']['rendah'] ?? 0]),
                        'backgroundColor' => array_merge(array_fill(0, count($d['findings']), $c['violet']), [$c['red'], $c['amber'], $c['sky']]),
                    ]]],
                    'options' => ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['ticks' => ['precision' => 0]]]],
                ]) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel span-2">
            <h3>Tren mahasiswa <small>seluruh program studi</small></h3>
            <div class="ak-chart h-240">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'bar',
                    'data' => ['labels' => $d['students']['labels'], 'datasets' => [
                        ['type' => 'line', 'label' => 'Mahasiswa aktif', 'data' => $d['students']['active'], 'borderColor' => $c['blue'], 'yAxisID' => 'y1', 'tension' => 0.3, 'borderWidth' => 3],
                        ['label' => 'Pendaftar', 'data' => $d['students']['applicants'], 'backgroundColor' => $c['gray'].'99'],
                        ['label' => 'Mahasiswa baru', 'data' => $d['students']['new'], 'backgroundColor' => $c['sky']],
                        ['label' => 'Lulusan', 'data' => $d['students']['graduates'], 'backgroundColor' => $c['green']],
                    ]],
                    'options' => ['scales' => ['y' => ['position' => 'left'], 'y1' => ['position' => 'right', 'grid' => ['drawOnChartArea' => false]]]],
                ]) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel">
            <h3>Profil dosen tetap <small>{{ $d['lecturers']['total'] }} orang</small></h3>
            <div class="ak-chart h-180">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'bar',
                    'data' => ['labels' => array_keys($d['lecturers']['rank']), 'datasets' => [[
                        'label' => 'Jabatan akademik', 'data' => array_values($d['lecturers']['rank']),
                        'backgroundColor' => [$c['violet'], $c['blue'], $c['sky'], $c['teal'], $c['gray']],
                    ]]],
                    'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['ticks' => ['precision' => 0]]]],
                ]) }}"></canvas>
            </div>
            <div class="ak-minis">
                <div><b>{{ $fmt($d['lecturers']['doctor_pct']) }}%</b><span>doktor (S3)</span></div>
                <div><b>{{ $fmt($d['lecturers']['senior_pct']) }}%</b><span>LK & GB</span></div>
                <div><b>{{ $fmt($d['lecturers']['certified_pct']) }}%</b><span>bersertifikat</span></div>
            </div>
        </section>

        <section class="ak-panel">
            <h3>Akreditasi program studi <small>{{ $d['programs']['unggul'] }}/{{ $d['programs']['total'] }} unggul/A</small></h3>
            <div class="ak-chart h-200">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'doughnut',
                    'data' => ['labels' => array_keys($d['programs']['distribution']), 'datasets' => [[
                        'data' => array_values($d['programs']['distribution']),
                        'backgroundColor' => [$c['green'], $c['sky'], $c['blue'], $c['amber'], $c['violet'], $c['red'], $c['gray']],
                    ]]],
                    'options' => ['cutout' => '60%', 'plugins' => ['legend' => ['position' => 'right']]],
                ]) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel">
            <h3>Akreditasi prodi segera berakhir <small>≤ 18 bulan</small></h3>
            <ul class="ak-list">
                @forelse ($d['programs']['expiring'] as $e)
                    <li class="{{ $e['months'] <= 6 ? 'bad' : 'warn' }}"><a href="{{ $links['programs'] }}"><b>{{ $e['name'] }}</b><span>{{ $e['rank'] }} · s.d. {{ $e['until']->translatedFormat('d M Y') }} ({{ $e['months'] < 0 ? 'lewat' : $e['months'].' bln' }})</span></a></li>
                @empty
                    <li class="ak-muted">Tidak ada.</li>
                @endforelse
            </ul>
        </section>

        <section class="ak-panel">
            <h3>Masa tunggu lulusan <small>tracer study</small></h3>
            <div class="ak-chart h-200">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'bar',
                    'data' => ['labels' => $d['tracer']['labels'], 'datasets' => [
                        ['label' => 'Bekerja ≤ 1 tahun (%)', 'data' => $d['tracer']['within_one_year'], 'backgroundColor' => $c['teal']],
                        ['type' => 'line', 'label' => 'Rata-rata tunggu (bulan)', 'data' => $d['tracer']['wait'], 'borderColor' => $c['amber'], 'yAxisID' => 'y1', 'tension' => 0.3],
                    ]],
                    'options' => ['scales' => ['y' => ['min' => 0, 'max' => 100], 'y1' => ['position' => 'right', 'min' => 0, 'grid' => ['drawOnChartArea' => false]]]],
                ]) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel span-2">
            <h3>Kepuasan pemangku kepentingan <small>% jawaban Sangat Baik + Baik</small></h3>
            <div class="ak-chart h-220">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'bar',
                    'data' => ['labels' => $d['surveys']['labels'], 'datasets' => collect($d['surveys']['series'])->values()->map(fn ($data, $i) => [
                        'label' => array_keys($d['surveys']['series'])[$i], 'data' => $data,
                        'backgroundColor' => [$c['blue'], $c['sky'], $c['teal'], $c['green'], $c['violet'], $c['amber']][$i % 6],
                    ])],
                    'options' => ['scales' => ['y' => ['min' => 0, 'max' => 100]]],
                ]) }}"></canvas>
            </div>
        </section>

        <section class="ak-panel">
            <h3>Penelitian & PkM <small>dana (juta Rp) dan publikasi</small></h3>
            <div class="ak-chart h-220">
                <canvas data-ak-chart="{{ json_encode([
                    'type' => 'bar',
                    'data' => ['labels' => $d['research']['labels'], 'datasets' => [
                        ['label' => 'Dana penelitian', 'data' => $d['research']['research_funds'], 'backgroundColor' => $c['blue'], 'stack' => 'dana'],
                        ['label' => 'Dana PkM', 'data' => $d['research']['pkm_funds'], 'backgroundColor' => $c['sky'], 'stack' => 'dana'],
                        ['type' => 'line', 'label' => 'Publikasi internasional', 'data' => $d['research']['pubs_international'], 'borderColor' => $c['violet'], 'yAxisID' => 'y1', 'tension' => 0.3],
                        ['type' => 'line', 'label' => 'Publikasi nasional', 'data' => $d['research']['pubs_national'], 'borderColor' => $c['green'], 'yAxisID' => 'y1', 'tension' => 0.3],
                    ]],
                    'options' => ['scales' => ['y' => ['stacked' => true], 'y1' => ['position' => 'right', 'grid' => ['drawOnChartArea' => false], 'ticks' => ['precision' => 0]]]],
                ]) }}"></canvas>
            </div>
        </section>
    </div>

    <p class="ak-disclaimer">AKI dan proyeksi skor adalah alat manajemen internal untuk memantau kesiapan, bukan prediksi atau nilai resmi BAN-PT. Keputusan akreditasi sepenuhnya kewenangan BAN-PT.</p>
</div>
</x-filament-panels::page>
