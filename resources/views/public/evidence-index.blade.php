@extends('public.layout')
@section('title', 'Portal Bukti Akreditasi')
@section('content')
<div class="card">
    <p class="muted">Daftar dokumen bukti yang dibuka untuk publik dan asesor BAN-PT. Klik judul untuk melihat rincian dan berkasnya.</p>
    <table>
        <thead><tr><th>Kode</th><th>Dokumen</th><th>Tanggal</th></tr></thead>
        <tbody>
        @forelse ($docs as $d)
            <tr><td>{{ $d->code }}</td><td><a href="{{ $d->publicUrl() }}">{{ $d->title }}</a><div class="muted">{{ $d->number }}</div></td><td>{{ $d->doc_date?->translatedFormat('d M Y') }}</td></tr>
        @empty
            <tr><td colspan="3" class="muted">Belum ada bukti publik.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
