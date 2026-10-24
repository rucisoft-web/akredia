<x-filament-panels::page>
<div class="ak-lkpt" x-data="{ tab: @entangle('active') }">
    <div class="ak-tabs">
        @foreach ($tables as $t)
            <button type="button" :class="{ 'on': tab === '{{ $t['key'] }}' }" @click="tab = '{{ $t['key'] }}'">{{ \Illuminate\Support\Str::before($t['title'], ' (') }}</button>
        @endforeach
    </div>
    @foreach ($tables as $t)
        <section class="ak-panel" x-show="tab === '{{ $t['key'] }}'" x-cloak>
            <h3>{{ $t['title'] }} <small>{{ $t['note'] }}</small></h3>
            @if (! empty($t['meta']))
                <dl class="ak-meta">@foreach ($t['meta'] as $k => $v)<div><dt>{{ $k }}</dt><dd>{{ $v ?: '—' }}</dd></div>@endforeach</dl>
            @endif
            <div class="ak-row"><x-filament::button size="sm" color="gray" icon="heroicon-o-clipboard" x-on:click="navigator.clipboard.writeText([...$refs.t{{ $loop->index }}.querySelectorAll('tr')].map(r => [...r.children].map(c => c.innerText.trim()).join('\t')).join('\n')); $el.querySelector('span') && ($el.querySelector('span').innerText = 'Tersalin ✓')">Salin tabel</x-filament::button></div>
            <div class="ak-table-wrap">
                <table class="ak-table plain" x-ref="t{{ $loop->index }}">
                    <thead><tr>@foreach ($t['header'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
                    <tbody>
                        @forelse ($t['rows'] as $row)
                            <tr>@foreach ($row as $cell)<td>{{ is_float($cell) ? number_format($cell, 2, ',', '.') : $cell }}</td>@endforeach</tr>
                        @empty
                            <tr><td colspan="{{ count($t['header']) }}" class="ak-muted">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
</div>
</x-filament-panels::page>
