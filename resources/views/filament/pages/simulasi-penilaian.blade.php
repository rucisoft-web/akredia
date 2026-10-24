@php $fmt = fn ($n, $d = 1) => number_format((float) $n, $d, ',', '.'); @endphp
<x-filament-panels::page>
@if (! $project)
    <p class="ak-muted">Belum ada proyek akreditasi aktif.</p>
@else
<div class="ak-sim-grid">
    <div class="ak-sim-result">
        @foreach (['accredited' => 'Terakreditasi', 'excellent' => 'Terakreditasi Unggul'] as $key => $label)
            @php($b = $before[$key]) @php($a = $after[$key])
            <section class="ak-panel">
                <h3>{{ $label }} <small>ambang {{ $fmt($a['threshold'], 0) }}%</small></h3>
                <div class="ak-proj">
                    <div class="ak-proj-label"><span>Saat ini</span><b>{{ $fmt($b['projection']) }}%</b></div>
                    <div class="ak-proj-bar"><span class="fill {{ $b['projection'] >= $b['threshold'] ? 'ok' : 'bad' }}" style="width: {{ min(100, $b['projection']) }}%"></span><span class="mark" style="left: {{ $b['threshold'] }}%"></span></div>
                </div>
                <div class="ak-proj active">
                    <div class="ak-proj-label"><span>Simulasi</span><b>{{ $fmt($a['projection']) }}% <span class="ak-delta">{{ $a['projection'] > $b['projection'] ? '+'.$fmt($a['projection'] - $b['projection']) : '' }}</span></b></div>
                    <div class="ak-proj-bar"><span class="fill {{ $a['projection'] >= $a['threshold'] ? 'ok' : 'bad' }}" style="width: {{ min(100, $a['projection']) }}%"></span><span class="mark" style="left: {{ $a['threshold'] }}%"></span></div>
                </div>
                <p class="ak-muted">Syarat perlu gagal: <b>{{ $b['failed'] }}</b> → <b class="{{ $a['failed'] ? 'text-danger-600' : 'text-success-600' }}">{{ $a['failed'] }}</b>.
                    {{ $a['failed'] === 0 && $a['projection'] >= $a['threshold'] ? 'Dengan skenario ini jalur '.$label.' tercapai.' : '' }}</p>
            </section>
        @endforeach
        <div class="ak-row">
            <x-filament::button size="sm" color="gray" wire:click="selectPrerequisites({{ json_encode(array_values($prereqIds)) }})">Centang semua syarat perlu</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="clear">Kosongkan</x-filament::button>
        </div>
    </div>
    <section class="ak-panel">
        <h3>Sub-indikator yang belum memenuhi <small>{{ $notMet->count() }} butir · jalur acuan</small></h3>
        <div class="ak-row">
            <select wire:model.live="track" class="ak-input" style="max-width: 260px">
                <option value="accredited">Deskriptor Terakreditasi</option>
                <option value="excellent">Deskriptor Unggul</option>
            </select>
            <label class="ak-toggle"><input type="checkbox" wire:model.live="prereqOnly"> Hanya syarat perlu</label>
        </div>
        @forelse ($notMet as $w)
            <label class="ak-sim-row">
                <input type="checkbox" value="{{ $w->id }}" wire:model.live="assume">
                <span class="code">{{ $w->subIndicator->code }}</span>
                <span>{{ $w->subIndicator->statement }}
                    <small class="ak-muted" style="display:block">{{ $w->color_reason }} · PIC {{ $w->pic?->name ?? '—' }} · bobot +{{ $fmt($gain($w), 2) }} poin
                        @if ($w->subIndicator->isPrerequisiteFor($trackEnum)) · <b style="color:#be123c">syarat perlu</b>@endif</small>
                </span>
            </label>
        @empty
            <p class="ak-good">Semua sub-indikator sudah dinilai memenuhi untuk jalur ini.</p>
        @endforelse
    </section>
</div>
@endif
</x-filament-panels::page>
