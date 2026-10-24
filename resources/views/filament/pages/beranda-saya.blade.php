<x-filament-panels::page>
<p class="ak-muted">Peran Anda: <b>{{ $role?->getLabel() }}</b> — {{ $role?->description() }}</p>
<div class="ak-grid">
    <section class="ak-panel span-2">
        <h3>Sub-indikator yang saya pegang <small>{{ $items->count() }} butir</small></h3>
        <div class="ak-table-wrap"><table class="ak-table">
            <thead><tr><th></th><th>Kode</th><th>Sub-indikator</th><th>Status</th><th>LED</th><th>Tenggat</th></tr></thead>
            <tbody>
            @forelse ($items as $w)
                <tr onclick="window.location='{{ $urls['item']($w) }}'">
                    <td><span class="ak-dot {{ $w->color->value }}" title="{{ $w->color_reason }}"></span></td>
                    <td><b>{{ $w->subIndicator->code }}</b></td>
                    <td>{{ \Illuminate\Support\Str::limit($w->subIndicator->statement, 90) }}</td>
                    <td>{{ $w->state->getLabel() }}</td>
                    <td>{{ $w->ledSection?->state->getLabel() }} · {{ $w->ledSection?->word_count }} kata</td>
                    <td>{{ $w->due_date?->translatedFormat('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="ak-muted">Anda belum ditetapkan sebagai PIC sub-indikator.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>
    <section class="ak-panel">
        <h3>Tugas saya <small><a href="{{ $urls['tasks'] }}">Kanban →</a></small></h3>
        <ul class="ak-list">
            @forelse ($tasks as $t)
                <li class="{{ $t->isOverdue() ? 'bad' : 'warn' }}"><a href="{{ $urls['tasks'] }}"><b>{{ $t->title }}</b><span>{{ $t->due_date ? 'Tenggat '.$t->due_date->translatedFormat('d M Y') : 'Tanpa tenggat' }} · {{ $t->status->getLabel() }}</span></a></li>
            @empty
                <li class="ak-muted">Tidak ada tugas terbuka.</li>
            @endforelse
        </ul>
    </section>
    <section class="ak-panel">
        <h3>LED menunggu reviu saya <small>{{ $reviews->count() }}</small></h3>
        <ul class="ak-list">
            @forelse ($reviews as $s)
                <li class="warn"><a href="{{ $urls['led']($s) }}"><b>{{ $s->workItem->subIndicator->code }}</b><span>{{ \Illuminate\Support\Str::limit($s->workItem->subIndicator->statement, 70) }} · {{ $s->word_count }} kata</span></a></li>
            @empty
                <li class="ak-muted">Tidak ada.</li>
            @endforelse
        </ul>
    </section>
    <section class="ak-panel">
        <h3>Bukti menunggu validasi <small>{{ $validations->count() }}</small></h3>
        <ul class="ak-list">
            @forelse ($validations as $d)
                <li class="warn"><a href="{{ $urls['doc']($d) }}"><b>{{ $d->code }}</b><span>{{ \Illuminate\Support\Str::limit($d->title, 70) }}</span></a></li>
            @empty
                <li class="ak-muted">Tidak ada.</li>
            @endforelse
        </ul>
    </section>
</div>
</x-filament-panels::page>
