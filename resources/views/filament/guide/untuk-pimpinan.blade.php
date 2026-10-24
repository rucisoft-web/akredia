<x-filament-panels::page>
<link rel="stylesheet" href="{{ asset('css/pimpinan.css') }}?v={{ @filemtime(public_path('css/pimpinan.css')) }}">
<p class="ak-muted" style="margin-bottom:.5rem">Halaman ini juga dapat dibuka tanpa login dan dibagikan kepada pimpinan lain: <a class="ak-link" href="{{ $publicUrl }}" target="_blank">{{ $publicUrl }}</a></p>
@include('pimpinan.content')
</x-filament-panels::page>
