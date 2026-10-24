<x-filament-panels::page>
<div class="ak-row">
    <select wire:model.live="variant" class="ak-input" style="max-width: 360px">
        @foreach ($variants as $v)<option value="{{ $v->id }}">{{ $v->version->code }} · Lampiran {{ $v->code }} · {{ $v->name }}</option>@endforeach
    </select>
    <input type="search" wire:model.live.debounce.400ms="search" class="ak-input" style="max-width: 320px" placeholder="Cari indikator, kode, atau kata…">
</div>
@if ($variantModel)
    <p class="ak-muted">Ambang proyeksi: Terakreditasi {{ number_format($variantModel->threshold_accredited, 0) }}% · Unggul {{ number_format($variantModel->threshold_excellent, 0) }}%. {{ $variantModel->source_note }}</p>
@endif
<div class="ak-tree">
    @foreach ($criteria as $c)
        <h3 style="font-weight:800;margin:1rem 0 .5rem">Kriteria {{ $c->number }} · {{ $c->name }} <small class="ak-muted">{{ $c->indicators->count() }} indikator</small></h3>
        @foreach ($c->indicators as $ind)
            <details>
                <summary><b>Indikator {{ $ind->number }}</b> <span class="ak-chip">{{ $ind->quality_target?->getLabel() }}</span> {{ $ind->statement }}
                    @if ($ind->subIndicators->contains(fn ($s) => $s->is_prerequisite_accredited || $s->is_prerequisite_excellent)) <span class="ak-pill red">syarat perlu</span>@endif
                </summary>
                <div class="body">
                    <p class="ak-muted"><b>Bukti utama:</b> {{ $ind->evidence_hint }} · <b>PIC usulan:</b> {{ $ind->pic_suggestion }} · <b>Kriteria lama:</b> {{ $ind->legacy_criteria }}</p>
                    @foreach ($ind->subIndicators as $sub)
                        <div class="sub">
                            <p><b>{{ $sub->code }}</b> {{ $sub->statement }}
                                @if ($sub->is_prerequisite_accredited)<span class="ak-pill red">SP Terakreditasi</span>@endif
                                @if ($sub->is_prerequisite_excellent)<span class="ak-pill gray">SP Unggul</span>@endif
                                @if ($sub->metric_code)<span class="ak-chip">{{ $sub->metric_code }}</span>@endif
                                @if ($sub->lkpt_table)<span class="ak-chip">LKPT {{ $sub->lkpt_table }}</span>@endif
                            </p>
                            <div class="ak-wi-grid" style="margin-top:.4rem">
                                @foreach (['accredited' => 'Terakreditasi', 'excellent' => 'Unggul'] as $t => $label)
                                    @php($d = $sub->descriptorFor($t))
                                    <section><h4>{{ $label }}</h4>
                                        <div class="ak-kw">@foreach ($d?->keywords ?? [] as $kw)<span class="ok">{{ $kw->text }}</span>@endforeach</div>
                                    </section>
                                @endforeach
                            </div>
                            <p class="ak-muted">Sumber data: {{ $sub->data_sources }}</p>
                        </div>
                    @endforeach
                </div>
            </details>
        @endforeach
    @endforeach
</div>
</x-filament-panels::page>
