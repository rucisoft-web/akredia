<x-filament-panels::page>
<div class="ak-editor" x-data="akLedEditor({{ $limit }})" x-init="init()">
    <div class="ak-editor-main">
        <div class="ak-chips">
            <span class="ak-chip state-{{ $section->state->value }}">{{ $section->state->getLabel() }}</span>
            <span class="ak-chip">Jalur {{ $item->project->track->getLabel() }}</span>
            <span class="ak-chip">PIC {{ $item->pic?->name ?? '—' }}</span>
            <span class="ak-chip">Reviewer {{ $item->reviewer?->name ?? '—' }}</span>
            @if ($section->approver)<span class="ak-chip ok">Disetujui {{ $section->approver->name }} · {{ $section->approved_at?->translatedFormat('d M Y') }}</span>@endif
            @if ($section->ratifier)<span class="ak-chip ok">Disahkan {{ $section->ratifier->name }}</span>@endif
        </div>

        <p class="ak-wi-statement">{{ $item->subIndicator->statement }}</p>

        @if ($section->review_note && $section->state->value === 'revision')
            <div class="ak-alert warn"><b>Catatan reviewer:</b> {{ $section->review_note }}</div>
        @endif

        <div class="ak-editor-toolbar">
            <div class="ak-counter" :class="{ 'over': words > limit, 'near': words > limit * 0.9 && words <= limit }">
                <strong x-text="words.toLocaleString('id-ID')"></strong> / {{ number_format($limit, 0, ',', '.') }} kata
                <span class="bar"><span :style="`width: ${Math.min(100, words / limit * 100)}%`"></span></span>
            </div>
            @if ($canWrite)
                <div class="ak-insert">
                    <select x-ref="doc" @change="insert($event.target.value); $event.target.value = ''">
                        <option value="">+ Sisip sitasi bukti…</option>
                        @if ($linkedDocs->isNotEmpty())
                            <optgroup label="Bukti butir ini">
                                @foreach ($linkedDocs as $doc)<option value="[B:{{ $doc->code }}]">{{ $doc->code }} · {{ \Illuminate\Support\Str::limit($doc->title, 60) }}</option>@endforeach
                            </optgroup>
                        @endif
                        <optgroup label="Bukti valid lain">
                            @foreach ($otherDocs as $doc)<option value="[B:{{ $doc->code }}]">{{ $doc->code }} · {{ \Illuminate\Support\Str::limit($doc->title, 60) }}</option>@endforeach
                        </optgroup>
                    </select>
                    <select @change="insert($event.target.value); $event.target.value = ''">
                        <option value="">+ Sisip angka hidup…</option>
                        @foreach ($metrics as $m)
                            <option value="{{ str_repeat(chr(123), 2).$m['code'].str_repeat(chr(125), 2) }}">{{ $m['code'] }} · {{ $m['name'] }} ({{ $m['value'] === null ? 'belum dihitung' : number_format($m['value'], 2, ',', '.').' '.$m['unit'] }})</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <label class="ak-toggle"><input type="checkbox" wire:model.live="preview"> Pratinjau</label>
        </div>

        @if ($preview)
            <div class="ak-preview">{!! $rendered !!}</div>
        @else
            <textarea x-ref="body" wire:model="body" @input="count()" rows="26" class="ak-textarea" @disabled(! $canWrite)
                placeholder="Tulis narasi mengikuti PPEPP…"></textarea>
        @endif

        @if ($canWrite)
            <div class="ak-editor-actions">
                <input type="text" wire:model="note" placeholder="Catatan revisi (opsional)" class="ak-input">
                <x-filament::button wire:click="save" icon="heroicon-o-check" wire:loading.attr="disabled">Simpan revisi</x-filament::button>
            </div>
        @else
            <p class="ak-muted">Narasi hanya dapat diubah oleh PIC butir ini atau Manajer, dan tidak dapat diubah selama reviu.</p>
        @endif
    </div>

    <aside class="ak-editor-side">
        <section>
            <h4>Kata kunci deskriptor <small>{{ count($missing) ? count($missing).' belum disebut' : 'lengkap' }}</small></h4>
            <p class="ak-muted">{{ $descriptor?->text }}</p>
            <ul class="ak-kwlist">
                @foreach ($descriptor?->keywords ?? [] as $kw)
                    <li class="{{ in_array($kw->text, $missing, true) ? 'missing' : 'ok' }}">{{ in_array($kw->text, $missing, true) ? '○' : '●' }} {{ $kw->text }}</li>
                @endforeach
            </ul>
            <small class="ak-muted">Diperbarui saat disimpan.</small>
        </section>

        <section>
            <h4>Asisten narasi</h4>
            <div class="ak-row">
                <x-filament::button size="sm" color="gray" wire:click="runCheck" icon="heroicon-o-clipboard-document-check">Periksa narasi</x-filament::button>
                @if ($aiAvailable)
                    <x-filament::button size="sm" color="gray" wire:click="runAi" icon="heroicon-o-sparkles" wire:loading.attr="disabled" wire:target="runAi">Saran AI</x-filament::button>
                @endif
            </div>
            @if ($check)
                <div class="ak-score">Skor kelengkapan <b>{{ $check['score'] }}</b>/100</div>
                <ul class="ak-gate compact">
                    @foreach ($check['checks'] as $c)
                        <li class="{{ $c['ok'] ? 'ok' : 'bad' }}"><span class="icon">{{ $c['ok'] ? '✓' : '✕' }}</span><div><b>{{ $c['label'] }}</b><small>{{ $c['detail'] }}</small></div></li>
                    @endforeach
                </ul>
                @if ($check['suggestions'])
                    <ol class="ak-suggest">@foreach ($check['suggestions'] as $s)<li>{{ $s }}</li>@endforeach</ol>
                @endif
            @endif
            @if ($ai)
                <div class="ak-ai {{ $ai['ok'] ? '' : 'warn' }}">{!! nl2br(e($ai['text'])) !!}</div>
            @endif
            <small class="ak-muted">Asisten memberi saran; keputusan isi tetap pada PIC dan reviewer.</small>
        </section>

        <section>
            <h4>Komentar reviu</h4>
            @forelse ($section->comments->sortByDesc('created_at') as $c)
                <div class="ak-comment {{ $c->is_resolved ? 'resolved' : '' }}">
                    <b>{{ $c->user?->name }}</b> <small>{{ $c->created_at->diffForHumans() }}</small>
                    <p>{{ $c->body }}</p>
                    @unless ($c->is_resolved)<button type="button" wire:click="resolveComment({{ $c->id }})" class="ak-link">Tandai selesai</button>@endunless
                </div>
            @empty
                <p class="ak-muted">Belum ada komentar.</p>
            @endforelse
            <textarea wire:model="comment" rows="2" class="ak-input" placeholder="Tulis komentar…"></textarea>
            <x-filament::button size="sm" color="gray" wire:click="addComment">Kirim komentar</x-filament::button>
        </section>

        <section>
            <h4>Riwayat revisi</h4>
            <ul class="ak-revs">
                @forelse ($section->revisions->sortByDesc('revision')->take(15) as $rev)
                    <li>
                        <b>r{{ $rev->revision }}</b> · {{ number_format($rev->word_count, 0, ',', '.') }} kata · {{ $rev->user?->name }}
                        <small>{{ $rev->created_at->translatedFormat('d M Y H:i') }}{{ $rev->note ? ' · '.$rev->note : '' }}</small>
                        @if ($canWrite)<button type="button" class="ak-link" wire:click="restore({{ $rev->id }})">Muat</button>@endif
                    </li>
                @empty
                    <li class="ak-muted">Belum ada revisi tersimpan.</li>
                @endforelse
            </ul>
        </section>
    </aside>
</div>
</x-filament-panels::page>
