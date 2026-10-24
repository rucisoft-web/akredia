@extends('public.layout')
@section('title', $doc->title)
@section('content')
<div class="card">
    <table>
        <tr><th style="width:180px">Kode bukti</th><td>{{ $doc->code }}</td></tr>
        <tr><th>Jenis</th><td>{{ $doc->doc_type->getLabel() }}</td></tr>
        <tr><th>Nomor</th><td>{{ $doc->number ?: '—' }}</td></tr>
        <tr><th>Tanggal</th><td>{{ $doc->doc_date?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><th>Disahkan oleh</th><td>{{ $doc->approved_by ?: '—' }}</td></tr>
        <tr><th>Status</th><td>{{ $doc->status->getLabel() }}{{ $doc->valid_until ? ' · berlaku sampai '.$doc->valid_until->translatedFormat('d F Y') : '' }}</td></tr>
        @if ($version?->sha256)<tr><th>Sidik SHA-256</th><td style="font-family:monospace;font-size:12px;word-break:break-all">{{ $version->sha256 }}</td></tr>@endif
    </table>
    @if ($doc->description)<p>{{ $doc->description }}</p>@endif
    <p>
        @if ($version?->file_path)<a class="btn" href="?unduh=1" target="_blank">Buka berkas (versi {{ $version->version }})</a>@endif
        @if ($version?->url)<a class="btn" href="{{ $version->url }}" target="_blank" rel="noopener">Buka tautan</a>@endif
    </p>
</div>
<p class="muted"><a href="{{ route('bukti.index', $institution) }}">← Semua bukti publik</a></p>
@endsection
