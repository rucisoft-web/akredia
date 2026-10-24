<x-filament-panels::page>
<div class="ak-row" style="align-items:center">
    <x-filament::button size="sm" color="gray" wire:click="shift(-1)" icon="heroicon-o-chevron-left">Sebelumnya</x-filament::button>
    <b style="font-size:1.1rem;min-width:10rem;text-align:center">{{ $label }}</b>
    <x-filament::button size="sm" color="gray" wire:click="shift(1)" icon="heroicon-o-chevron-right" icon-position="after">Berikutnya</x-filament::button>
    <span class="ak-legend" style="margin:0 0 0 1rem"><span>Tugas</span><span class="red">Terlambat</span></span>
</div>
<div class="ak-cal">
    @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dow)<div class="dow">{{ $dow }}</div>@endforeach
    @foreach ($days as $day)
        <div class="day {{ $day['out'] ? 'out' : '' }} {{ $day['date']->isToday() ? 'today' : '' }}">
            <span class="n">{{ $day['date']->day }}</span>
            @foreach ($day['events']->take(4) as $e)
                <a class="ev {{ $e['class'] }}" href="{{ $e['url'] }}" title="{{ $e['label'] }}">{{ $e['label'] }}</a>
            @endforeach
            @if ($day['events']->count() > 4)<small class="ak-muted">+{{ $day['events']->count() - 4 }} lagi</small>@endif
        </div>
    @endforeach
</div>
<p class="ak-muted">Ungu: tenggat butir kerja · kuning: masa berlaku bukti berakhir · merah: tugas terlambat.</p>
</x-filament-panels::page>
