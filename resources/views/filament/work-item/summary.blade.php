@php
    use App\Enums\Track;
    $item = $getRecord();
    $item->loadMissing(['subIndicator.descriptors.keywords', 'subIndicator.indicator', 'evidenceLinks.document', 'assessments.assessor', 'project', 'ledSection']);
    $sub = $item->subIndicator;
    $calc = app(\App\Services\ReadinessCalculator::class);
    $project = $item->project;
    $mv = $sub->metric_code ? (app(\App\Services\MetricEngine::class)->current($project)[$sub->metric_code] ?? null) : null;
    $metricDef = $sub->metric_code ? \App\Models\Metric::where('code', $sub->metric_code)->first() : null;
@endphp
<div class="ak-wi">
    <div class="ak-wi-head">
        <span class="ak-dot {{ $item->color->value }}"></span>
        <div>
            <p class="ak-wi-statement">{{ $sub->statement }}</p>
            <p class="ak-muted">{{ $item->color_reason }}</p>
        </div>
    </div>
    <div class="ak-chips">
        <span class="ak-chip">Status: {{ $item->state->getLabel() }}</span>
        <span class="ak-chip">PIC: {{ $item->pic?->name ?? 'belum ditetapkan' }}</span>
        <span class="ak-chip">Reviewer: {{ $item->reviewer?->name ?? '—' }}</span>
        @if ($item->due_date)<span class="ak-chip {{ $item->due_date->isPast() ? 'bad' : '' }}">Tenggat {{ $item->due_date->translatedFormat('d M Y') }}</span>@endif
        @if ($sub->is_prerequisite_accredited)<span class="ak-chip bad">Syarat perlu Terakreditasi</span>@endif
        @if ($sub->is_prerequisite_excellent)<span class="ak-chip warn">Syarat perlu Unggul</span>@endif
        @if ($sub->lkpt_table)<span class="ak-chip">LKPT: {{ $sub->lkpt_table }}</span>@endif
    </div>
    <div class="ak-wi-grid">
        @foreach ([Track::Accredited, Track::Excellent] as $track)
            @php
                $d = $sub->descriptorFor($track);
                $support = $calc->keywordSupport($item, $track);
                $a = $calc->assessmentFor($item, $track);
            @endphp
            <section class="{{ $project->track === $track ? 'active' : '' }}">
                <h4>Deskriptor {{ $track->getLabel() }} @if ($project->track === $track)<small>jalur target</small>@endif</h4>
                <p>{{ $d?->text ?? 'Tidak ada deskriptor untuk jalur ini.' }}</p>
                @if ($d && $d->keywords->isNotEmpty())
                    <div class="ak-kw">
                        @foreach ($d->keywords as $kw)
                            <span class="{{ in_array($kw->text, $support['missing'], true) ? 'missing' : 'ok' }}" title="{{ in_array($kw->text, $support['missing'], true) ? 'Belum didukung bukti valid' : 'Didukung bukti valid' }}">{{ $kw->text }}</span>
                        @endforeach
                    </div>
                    <small class="ak-muted">{{ $support['supported'] }} dari {{ $support['total'] }} kata kunci didukung bukti valid.</small>
                @endif
                <div class="ak-assess">
                    @if ($a)
                        <b class="{{ $a->is_met ? 'ok' : 'bad' }}">{{ $a->is_met ? 'Memenuhi' : 'Tidak memenuhi' }}</b>
                        <span>penilaian {{ ['self' => 'mandiri PIC', 'internal' => 'asesor internal', 'final' => 'hasil asesmen'][$a->kind] ?? $a->kind }}{{ $a->assessor ? ' · '.$a->assessor->name : '' }}</span>
                    @else
                        <span class="ak-muted">Belum dinilai.</span>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
    @if ($metricDef)
        <div class="ak-metric">
            <b>{{ $metricDef->code }} · {{ $metricDef->name }}</b>
            <span>{{ $mv?->value === null ? 'belum dihitung' : number_format($mv->value, 2, ',', '.').' '.$metricDef->unit }}</span>
            <small class="ak-muted">Ambang {{ \App\Services\MetricEngine::thresholdText($metricDef, $project->track) }} · status {{ $mv?->status ?? '—' }}</small>
        </div>
    @endif
    @if ($sub->data_sources)
        <p class="ak-muted"><b>Sumber data / bukti yang lazim:</b> {{ $sub->data_sources }}</p>
    @endif
    @if ($item->current_condition)
        <p><b>Kondisi saat ini:</b> {{ $item->current_condition }}</p>
    @endif
</div>
