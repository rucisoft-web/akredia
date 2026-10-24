@php
    use App\Enums\Track;
    use App\Models\AccreditationProject;
    $project = $getRecord();
    $calc = app(\App\Services\ReadinessCalculator::class);
    $gate = $calc->gate($project);
    $components = $project->aki_components ?? [];
    $weights = $project->weights();
    $tracks = [
        ['label' => 'Terakreditasi', 'value' => $project->projection_accredited ?? 0, 'threshold' => $project->threshold(Track::Accredited), 'active' => $project->track === Track::Accredited],
        ['label' => 'Terakreditasi Unggul', 'value' => $project->projection_excellent ?? 0, 'threshold' => $project->threshold(Track::Excellent), 'active' => $project->track === Track::Excellent],
    ];
    $passed = collect($gate)->where('ok', true)->count();
    $fmt = fn ($n, $d = 1) => number_format((float) $n, $d, ',', '.');
@endphp
<div class="ak-summary">
    <div class="ak-summary-head">
        <div class="ak-aki" style="--pct: {{ min(100, $project->aki ?? 0) }}%">
            <div><strong>{{ $fmt($project->aki ?? 0) }}</strong><span>AKI</span></div>
        </div>
        <div class="ak-summary-meta">
            <div class="ak-chips">
                <span class="ak-chip state-{{ $project->state->value }}">{{ $project->state->getLabel() }}</span>
                <span class="ak-chip">Jalur {{ $project->track->getLabel() }}{{ $project->track_locked_at ? ' · dikunci' : '' }}</span>
                <span class="ak-chip {{ $project->isFrozen() ? 'ok' : '' }}">{{ $project->isFrozen() ? 'Data TS dikunci' : 'Data TS belum dikunci' }}</span>
                @if (($d = $project->daysToTarget()) !== null)
                    <span class="ak-chip {{ $d < 30 ? 'bad' : '' }}">{{ $d >= 0 ? $d.' hari menuju target' : abs($d).' hari melewati target' }}</span>
                @endif
            </div>
            <p class="ak-muted">Indeks Kesiapan Akredia (AKI) adalah alat manajemen internal, bukan nilai resmi BAN-PT. Dihitung terakhir {{ $project->computed_at?->translatedFormat('d M Y H:i') ?? 'belum pernah' }}.</p>
            <div class="ak-components">
                @foreach (AccreditationProject::WEIGHT_LABELS as $key => $label)
                    @php($v = $components[$key] ?? 0)
                    <div class="ak-comp">
                        <span>{{ $label }} <small>bobot {{ $weights[$key] }}</small></span>
                        <span class="track"><span class="fill {{ $v < 60 ? 'bad' : ($v < 80 ? 'mid' : '') }}" style="width: {{ min(100, $v) }}%"></span></span>
                        <b>{{ $fmt($v) }}</b>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ak-summary-grid">
        <section>
            <h4>Proyeksi skor kedua jalur</h4>
            @foreach ($tracks as $t)
                <div class="ak-proj {{ $t['active'] ? 'active' : '' }}">
                    <div class="ak-proj-label"><span>{{ $t['label'] }}{{ $t['active'] ? ' (target)' : '' }}</span><b>{{ $fmt($t['value']) }}%</b></div>
                    <div class="ak-proj-bar">
                        <span class="fill {{ $t['value'] >= $t['threshold'] ? 'ok' : 'bad' }}" style="width: {{ min(100, $t['value']) }}%"></span>
                        <span class="mark" style="left: {{ $t['threshold'] }}%" title="Ambang {{ $fmt($t['threshold'], 0) }}%"></span>
                    </div>
                    <small class="ak-muted">Ambang {{ $fmt($t['threshold'], 0) }}% · {{ $t['value'] >= $t['threshold'] ? 'di atas ambang' : 'kurang '.$fmt($t['threshold'] - $t['value']).' poin' }}</small>
                </div>
            @endforeach
        </section>
        <section>
            <h4>Gerbang Siap Diajukan <small>{{ $passed }}/{{ count($gate) }} terpenuhi</small></h4>
            <ul class="ak-gate">
                @foreach ($gate as $c)
                    <li class="{{ $c['ok'] ? 'ok' : 'bad' }}">
                        <span class="icon">{{ $c['ok'] ? '✓' : '✕' }}</span>
                        <div><b>{{ $c['label'] }}</b><small>{{ $c['detail'] }}</small></div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</div>
