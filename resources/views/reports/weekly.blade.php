@extends('reports.layout')
@section('title', 'Laporan Mingguan Kesiapan Akreditasi')
@php($k = $d['kpi'])
@php($fmt = fn ($n, $dec = 1) => number_format((float) $n, $dec, ',', '.'))
@section('content')
@if ($d['project'])
    <p><b>{{ $d['project']->name }}</b> · jalur {{ $d['project']->track->getLabel() }} · status {{ $d['project']->state->getLabel() }}
        @if (! is_null($k['days_to_target'])) · {{ $k['days_to_target'] }} hari menuju target @endif</p>
@endif
<div class="kpis">
    <div class="kpi"><span>AKI</span><b>{{ $fmt($k['aki']) }}</b></div>
    <div class="kpi"><span>Proyeksi Terakreditasi</span><b>{{ $fmt($k['projection_accredited']) }}%</b></div>
    <div class="kpi"><span>Proyeksi Unggul</span><b>{{ $fmt($k['projection_excellent']) }}%</b></div>
    <div class="kpi"><span>Syarat perlu gagal</span><b class="{{ $k['prerequisites_failed'] ? 'bad' : 'ok' }}">{{ $k['prerequisites_failed'] }}</b></div>
    <div class="kpi"><span>Sub-indikator hijau</span><b>{{ $k['sub_green'] }}/{{ $k['sub_total'] }}</b></div>
    <div class="kpi"><span>LED disetujui</span><b>{{ $fmt($k['led_approved_pct']) }}%</b></div>
    <div class="kpi"><span>Tugas terlambat</span><b class="{{ $k['tasks_overdue'] ? 'bad' : '' }}">{{ $k['tasks_overdue'] }}</b></div>
    <div class="kpi"><span>Gerbang</span><b>{{ $k['gate_passed'] }}/{{ $k['gate_total'] }}</b></div>
</div>
@if (count($d['trend']['labels']) > 1)
    @php($n = count($d['trend']['aki']))
    <p>Perubahan AKI sejak snapshot sebelumnya: <b>{{ $fmt($d['trend']['aki'][$n - 1] - $d['trend']['aki'][$n - 2]) }}</b> poin.</p>
@endif
<h2>Status per kriteria</h2>
<table><thead><tr><th>Kriteria</th><th>Hijau</th><th>Kuning</th><th>Merah</th><th>Belum dinilai</th></tr></thead><tbody>
@foreach ($d['criteria'] as $c)<tr><td>{{ $c['label'] }}</td><td>{{ $c['green'] }}</td><td>{{ $c['yellow'] }}</td><td>{{ $c['red'] }}</td><td>{{ $c['gray'] }}</td></tr>@endforeach
</tbody></table>
<h2>Gerbang Siap Diajukan</h2>
<table>@foreach ($gate as $g)<tr><td style="width:24px" class="{{ $g['ok'] ? 'ok' : 'bad' }}">{{ $g['ok'] ? '✓' : '✕' }}</td><td><b>{{ $g['label'] }}</b> · <span class="muted">{{ $g['detail'] }}</span></td></tr>@endforeach</table>
<h2>Tugas terlambat per unit</h2>
<table><thead><tr><th>Unit</th><th>Terbuka</th><th>Terlambat</th></tr></thead><tbody>
@forelse ($d['tasks_by_unit'] as $t)<tr><td>{{ $t['label'] }}</td><td>{{ $t['open'] }}</td><td class="{{ $t['overdue'] ? 'bad' : '' }}">{{ $t['overdue'] }}</td></tr>@empty<tr><td colspan="3" class="muted">Tidak ada tugas terbuka.</td></tr>@endforelse
</tbody></table>
<h2>Akreditasi prodi segera berakhir</h2>
<table><tbody>@forelse ($d['programs']['expiring'] as $e)<tr><td>{{ $e['name'] }}</td><td>{{ $e['rank'] }}</td><td>{{ $e['until']->translatedFormat('d M Y') }}</td></tr>@empty<tr><td class="muted">Tidak ada.</td></tr>@endforelse</tbody></table>
@endsection
