<x-filament-panels::page>
<div class="ak-panel" style="max-width: 820px">
    <form wire:submit="import">
        {{ $this->form }}
        <div class="ak-row" style="margin-top:1rem">
            <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray" wire:loading.attr="disabled">Impor sekarang</x-filament::button>
        </div>
    </form>
    <p class="ak-muted">Data yang diimpor masuk ke Data Kinerja institusi aktif. Setelah impor, hitung ulang metrik di halaman Nilai Metrik atau tombol "Perbarui angka" di dasbor. Rekap PD Dikti membuat atau memperbarui baris rekonsiliasi.</p>
</div>
@if ($result)
    <div class="ak-panel" style="max-width: 820px">
        <h3>Hasil impor</h3>
        <p><b>{{ $result['created'] }}</b> baris baru · <b>{{ $result['updated'] }}</b> diperbarui · <b>{{ count($result['errors']) }}</b> galat</p>
        @if ($result['errors'])
            <ul class="ak-suggest">@foreach ($result['errors'] as $line => $err)<li>Baris {{ $line }}: {{ $err }}</li>@endforeach</ul>
        @endif
    </div>
@endif
</x-filament-panels::page>
