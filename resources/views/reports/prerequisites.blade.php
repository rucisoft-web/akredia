@extends('reports.layout')
@section('title', 'Status Syarat Perlu & Gerbang Pengajuan')
@section('content')
<p><b>{{ $project->name }}</b> · jalur target {{ $project->track->getLabel() }} · AKI {{ number_format($project->aki ?? 0, 1, ',', '.') }}</p>
<table>
    <thead><tr><th>Kode</th><th>Sub-indikator</th><th>Syarat</th><th>Terakreditasi</th><th>Unggul</th><th>PIC</th></tr></thead>
    <tbody>
    @foreach ($items as $w)
        <tr>
            <td><b>{{ $w->subIndicator->code }}</b></td>
            <td>{{ $w->subIndicator->statement }}</td>
            <td>{{ $w->subIndicator->is_prerequisite_accredited ? 'Terakreditasi' : 'Unggul' }}</td>
            @foreach ([\App\Enums\Track::Accredited, \App\Enums\Track::Excellent] as $t)
                @php($m = $calc->isMet($w, $t))
                <td class="{{ $m === null ? 'muted' : ($m ? 'ok' : 'bad') }}">{{ $m === null ? 'Belum dinilai' : ($m ? 'Memenuhi' : 'Tidak') }}</td>
            @endforeach
            <td>{{ $w->pic?->name ?? '—' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<h2>Gerbang Siap Diajukan</h2>
<table>
    @foreach ($gate as $g)
        <tr><td style="width:24px" class="{{ $g['ok'] ? 'ok' : 'bad' }}">{{ $g['ok'] ? '✓' : '✕' }}</td><td><b>{{ $g['label'] }}</b><br><span class="muted">{{ $g['detail'] }}</span></td></tr>
    @endforeach
</table>
@endsection
