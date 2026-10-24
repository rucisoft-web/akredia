@php($tenant = filament()->getTenant())
@if ($tenant?->is_demo)
<div class="ak-demo-banner" role="note">
    <span class="ak-demo-tag">DATA DEMO</span>
    <span><b>{{ $tenant->name }}</b> di aplikasi ini disusun untuk menjelaskan fitur-fitur Akredia. Profil institusi diambil dari laman resminya; angka kinerja, nama staf, bukti, dan aktivitas adalah ilustrasi, bukan data resmi institusi.
    @if (auth()->user()?->isDemoGuest())
        Anda masuk sebagai <b>tamu (hanya lihat)</b>. <a href="{{ route('pimpinan') }}">Akredia untuk Pimpinan PT</a>
    @endif
    </span>
</div>
@endif
