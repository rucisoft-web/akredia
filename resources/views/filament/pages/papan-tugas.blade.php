<x-filament-panels::page>
<div class="ak-row">
    <select wire:model.live="unit" class="ak-input" style="max-width: 280px">
        <option value="">Semua unit</option>
        @foreach ($units as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
    </select>
    <label class="ak-toggle"><input type="checkbox" wire:model.live="mine"> Tugas saya</label>
    <a href="{{ $listUrl }}" class="ak-link" style="align-self:center">Tampilan daftar →</a>
</div>
<div class="ak-kanban" x-data="{ dragging: null }">
    @foreach ($columns as $status => [$label, $tasks])
        <div class="ak-col" x-on:dragover.prevent="$el.classList.add('drop')" x-on:dragleave="$el.classList.remove('drop')"
             x-on:drop.prevent="$el.classList.remove('drop'); if (dragging) $wire.move(dragging, '{{ $status }}')">
            <h4>{{ $label }} <span class="ak-chip">{{ $tasks->count() }}</span></h4>
            @foreach ($tasks as $t)
                <div class="ak-task {{ $t->priority->value }} {{ $t->isOverdue() ? 'overdue' : '' }}" draggable="true" x-on:dragstart="dragging = {{ $t->id }}" wire:key="t{{ $t->id }}">
                    <b>{{ $t->title }}</b>
                    <small>{{ $t->number }} · {{ $t->assignee?->name ?? 'tanpa PJ' }}{{ $t->unit ? ' · '.$t->unit->name : '' }}</small>
                    <small class="due">{{ $t->due_date ? 'Tenggat '.$t->due_date->translatedFormat('d M Y') : 'Tanpa tenggat' }}{{ $t->isOverdue() ? ' · terlambat' : '' }} · {{ $t->priority->getLabel() }}</small>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
</x-filament-panels::page>
