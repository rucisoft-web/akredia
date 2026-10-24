<?php

namespace App\Services;

use App\Enums\AccreditationRank;
use App\Enums\DocumentStatus;
use App\Enums\HeatColor;
use App\Enums\LedState;
use App\Enums\Track;
use App\Models\AccreditationProject;
use App\Models\Criterion;
use App\Models\Document;
use App\Models\Finding;
use App\Models\Lecturer;
use App\Models\LedSection;
use App\Models\Metric;
use App\Models\MetricValue;
use App\Models\ProgramAccreditation;
use App\Models\ProgramYearStat;
use App\Models\Publication;
use App\Models\Reconciliation;
use App\Models\ResearchGrant;
use App\Models\Risk;
use App\Models\Survey;
use App\Models\Task;
use App\Models\TracerStudy;
use App\Models\Unit;
use Illuminate\Support\Collection;

/**
 * Data dasbor eksekutif pimpinan: semua angka diambil dari modul lain sehingga
 * setiap kartu dan grafik dapat ditelusuri ke data sumbernya.
 */
class ExecutiveDashboard
{
    public function __construct(protected ReadinessCalculator $calculator, protected MetricEngine $engine) {}

    public function data(?AccreditationProject $project): array
    {
        $items = $project ? $this->calculator->items($project) : collect();
        $evaluation = $project && $items->isNotEmpty() ? $this->calculator->evaluate($project, $items) : null;

        return [
            'project' => $project,
            'evaluation' => $evaluation,
            'kpi' => $this->kpi($project, $items, $evaluation),
            'trend' => $this->trend($project),
            'components' => $this->components($project),
            'criteria' => $this->criteria($items),
            'heatmap' => $items->isNotEmpty() ? $this->calculator->heatmap($items) : collect(),
            'prerequisites' => $this->prerequisites($project, $items),
            'evidence' => $this->evidence(),
            'led' => $this->led($project),
            'tasks_by_unit' => $this->tasksByUnit(),
            'risk_matrix' => $this->riskMatrix(),
            'ratios' => $this->ratios($project),
            'students' => $this->students(),
            'lecturers' => $this->lecturers(),
            'programs' => $this->programs(),
            'surveys' => $this->surveys(),
            'research' => $this->research(),
            'tracer' => $this->tracer(),
            'reconciliations' => Reconciliation::query()->where('state', '!=', 'verified')->get()->countBy(fn ($r) => $r->severity->value)->all(),
            'findings' => Finding::query()->where('state', '!=', 'closed')->get()->countBy(fn ($f) => $f->category->getLabel())->all(),
            'today' => now(),
        ];
    }

    protected function kpi(?AccreditationProject $project, Collection $items, ?array $e): array
    {
        $docs = Document::query()->get(['status', 'valid_until']);
        $led = $project ? LedSection::query()->whereIn('work_item_id', $items->pluck('id'))->get(['state', 'word_count']) : collect();
        $approved = $led->filter(fn ($l) => in_array($l->state, [LedState::ReviewerApproved, LedState::Ratified], true))->count();

        return [
            'aki' => $project?->aki ?? 0,
            'projection_accredited' => $e['projection_accredited'] ?? 0,
            'projection_excellent' => $e['projection_excellent'] ?? 0,
            'threshold_accredited' => $project?->threshold(Track::Accredited) ?? 80,
            'threshold_excellent' => $project?->threshold(Track::Excellent) ?? 80,
            'prerequisites_failed' => $e ? $e['failed_prerequisites']->count() : 0,
            'days_to_target' => $project?->daysToTarget(),
            'sub_total' => $items->count(),
            'sub_green' => $items->where('color', HeatColor::Green)->count(),
            'sub_red' => $items->where('color', HeatColor::Red)->count(),
            'unassigned' => $items->whereNull('pic_id')->count(),
            'evidence_valid_pct' => $docs->count() ? round($docs->where('status', DocumentStatus::Valid)->count() / $docs->count() * 100, 1) : 0,
            'evidence_queue' => $docs->where('status', DocumentStatus::Submitted)->count(),
            'evidence_expiring' => $docs->filter(fn ($d) => $d->valid_until && $d->valid_until->between(now(), now()->addDays(60)))->count(),
            'keywords_supported_pct' => ($e['keywords']['total'] ?? 0) ? round($e['keywords']['supported'] / $e['keywords']['total'] * 100, 1) : 0,
            'led_approved_pct' => $led->count() ? round($approved / $led->count() * 100, 1) : 0,
            'led_words' => $led->sum('word_count'),
            'tasks_open' => Task::query()->open()->count(),
            'tasks_overdue' => Task::query()->overdue()->count(),
            'recon_high' => Reconciliation::query()->where('state', '!=', 'verified')->where('severity', 'tinggi')->count(),
            'risks_high' => Risk::query()->whereIn('state', ['open', 'mitigating'])->whereIn('level', ['tinggi', 'kritis'])->count(),
            'gate_passed' => $project ? collect($this->calculator->gate($project))->where('ok', true)->count() : 0,
            'gate_total' => 7,
        ];
    }

    protected function trend(?AccreditationProject $project): array
    {
        $snaps = $project ? $project->snapshots()->orderBy('taken_on')->get() : collect();

        return [
            'labels' => $snaps->map(fn ($s) => $s->taken_on->translatedFormat('d M'))->all(),
            'aki' => $snaps->pluck('aki')->map(fn ($v) => (float) $v)->all(),
            'accredited' => $snaps->pluck('projection_accredited')->map(fn ($v) => (float) $v)->all(),
            'excellent' => $snaps->pluck('projection_excellent')->map(fn ($v) => (float) $v)->all(),
        ];
    }

    protected function components(?AccreditationProject $project): array
    {
        $c = $project?->aki_components ?? [];

        return collect(AccreditationProject::WEIGHT_LABELS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'value' => (float) ($c[$key] ?? 0), 'weight' => $project?->weights()[$key] ?? 0])->values()->all();
    }

    protected function criteria(Collection $items): array
    {
        $criteria = Criterion::query()->orderBy('number')->get();

        return $criteria->map(function ($c) use ($items) {
            $group = $items->filter(fn ($w) => $w->subIndicator->indicator->criterion_id === $c->id);

            return [
                'id' => $c->id,
                'label' => 'K'.$c->number.' '.$c->name,
                'short' => 'Kriteria '.$c->number,
                'green' => $group->where('color', HeatColor::Green)->count(),
                'yellow' => $group->where('color', HeatColor::Yellow)->count(),
                'red' => $group->where('color', HeatColor::Red)->count(),
                'gray' => $group->where('color', HeatColor::Gray)->count(),
                'total' => $group->count(),
            ];
        })->filter(fn ($r) => $r['total'] > 0)->values()->all();
    }

    protected function prerequisites(?AccreditationProject $project, Collection $items): array
    {
        if (! $project) {
            return [];
        }

        return $items->filter(fn ($w) => $w->subIndicator->is_prerequisite_accredited || $w->subIndicator->is_prerequisite_excellent)
            ->map(fn ($w) => [
                'item' => $w,
                'code' => $w->subIndicator->code,
                'statement' => $w->subIndicator->statement,
                'track' => $w->subIndicator->is_prerequisite_accredited ? 'Terakreditasi' : 'Unggul',
                'met_accredited' => $this->calculator->isMet($w, Track::Accredited),
                'met_excellent' => $this->calculator->isMet($w, Track::Excellent),
            ])->values()->all();
    }

    protected function evidence(): array
    {
        $counts = Document::query()->get(['status'])->countBy(fn ($d) => $d->status->value);

        return collect(DocumentStatus::cases())->map(fn ($s) => ['key' => $s->value, 'label' => $s->getLabel(), 'value' => $counts[$s->value] ?? 0])->all();
    }

    protected function led(?AccreditationProject $project): array
    {
        if (! $project) {
            return [];
        }

        $counts = LedSection::query()->whereHas('workItem', fn ($q) => $q->where('accreditation_project_id', $project->id))->get(['state', 'word_count'])
            ->groupBy(fn ($l) => $l->word_count === 0 ? 'empty' : $l->state->value)->map->count();
        $rows = [['key' => 'empty', 'label' => 'Belum ditulis', 'value' => $counts['empty'] ?? 0]];

        foreach (LedState::cases() as $s) {
            $rows[] = ['key' => $s->value, 'label' => $s->getLabel(), 'value' => $counts[$s->value] ?? 0];
        }

        return $rows;
    }

    protected function tasksByUnit(): array
    {
        $units = Unit::query()->pluck('name', 'id');

        return Task::query()->open()->get(['unit_id', 'due_date', 'status'])
            ->groupBy('unit_id')
            ->map(fn ($g, $unitId) => [
                'unit_id' => $unitId ?: null,
                'label' => $units[$unitId] ?? 'Tanpa unit',
                'open' => $g->count(),
                'overdue' => $g->filter(fn ($t) => $t->isOverdue())->count(),
            ])
            ->sortByDesc('overdue')->take(10)->values()->all();
    }

    protected function riskMatrix(): array
    {
        $risks = Risk::query()->whereIn('state', ['open', 'mitigating'])->get(['probability', 'impact']);
        $cells = [];

        for ($p = 5; $p >= 1; $p--) {
            for ($i = 1; $i <= 5; $i++) {
                $score = $p * $i;
                $cells[] = [
                    'p' => $p, 'i' => $i,
                    'count' => $risks->where('probability', $p)->where('impact', $i)->count(),
                    'level' => Risk::levelFor($score),
                ];
            }
        }

        return $cells;
    }

    protected function ratios(?AccreditationProject $project): array
    {
        if (! $project) {
            return [];
        }

        $values = $this->engine->current($project);
        $track = $project->track;

        return Metric::query()->orderBy('sort')->get()->map(function (Metric $m) use ($values, $track) {
            $v = $values[$m->code]?->value ?? null;
            $min = $track === Track::Excellent ? ($m->excellent_min ?? $m->accredited_min) : $m->accredited_min;
            $max = $track === Track::Excellent ? ($m->excellent_max ?? $m->accredited_max) : $m->accredited_max;
            $scale = max(1, (float) ($m->unit === '%' ? 100 : max($v ?? 0, $max ?? 0, $min ?? 0) * 1.25));

            return [
                'code' => $m->code,
                'name' => $m->name,
                'unit' => $m->unit,
                'value' => $v,
                'min' => $min,
                'max' => $max,
                'scale' => $scale,
                'meets' => $this->engine->meets($m, $v, $track),
                'threshold' => MetricEngine::thresholdText($m, $track),
            ];
        })->filter(fn ($r) => $r['value'] !== null)->values()->all();
    }

    protected function students(): array
    {
        $rows = ProgramYearStat::query()->selectRaw('year, sum(applicants) applicants, sum(new_students) new_students, sum(active_students) active_students, sum(graduates) graduates')
            ->groupBy('year')->orderBy('year')->get();

        return [
            'labels' => $rows->pluck('year')->map(fn ($y) => (string) $y)->all(),
            'applicants' => $rows->pluck('applicants')->map(fn ($v) => (int) $v)->all(),
            'new' => $rows->pluck('new_students')->map(fn ($v) => (int) $v)->all(),
            'active' => $rows->pluck('active_students')->map(fn ($v) => (int) $v)->all(),
            'graduates' => $rows->pluck('graduates')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    protected function lecturers(): array
    {
        $l = Lecturer::query()->where('is_active', true)->where('employment', 'tetap')->get(['highest_degree', 'academic_rank', 'is_certified']);
        $total = max(1, $l->count());
        $rank = fn (string ...$values) => $l->filter(fn ($x) => in_array($x->academic_rank instanceof \BackedEnum ? $x->academic_rank->value : $x->academic_rank, $values, true))->count();

        return [
            'total' => $l->count(),
            'degree' => ['S3' => $l->where('highest_degree', 'S3')->count(), 'S2' => $l->where('highest_degree', 'S2')->count(), 'Lainnya' => $l->whereNotIn('highest_degree', ['S2', 'S3'])->count()],
            'rank' => ['Guru Besar' => $rank('gb'), 'Lektor Kepala' => $rank('lk'), 'Lektor' => $rank('l'), 'Asisten Ahli' => $rank('aa'), 'Belum JAD' => $rank('none')],
            'doctor_pct' => round($l->where('highest_degree', 'S3')->count() / $total * 100, 1),
            'senior_pct' => round($rank('gb', 'lk') / $total * 100, 1),
            'certified_pct' => round($l->where('is_certified', true)->count() / $total * 100, 1),
        ];
    }

    protected function programs(): array
    {
        $acc = ProgramAccreditation::query()->where('is_current', true)->with('studyProgram')->get();
        $dist = $acc->groupBy(fn ($a) => $a->rank->getLabel())->map->count()->sortDesc();

        return [
            'distribution' => $dist->all(),
            'expiring' => $acc->filter(fn ($a) => $a->valid_until && $a->valid_until->lte(now()->addMonths(18)))->sortBy('valid_until')->take(8)
                ->map(fn ($a) => ['name' => $a->studyProgram?->level.' '.$a->studyProgram?->name, 'rank' => $a->rank->getLabel(), 'until' => $a->valid_until, 'months' => (int) floor(now()->diffInMonths($a->valid_until, false)), 'program_id' => $a->study_program_id])
                ->values()->all(),
            'total' => $acc->count(),
            'unggul' => $acc->filter(fn ($a) => in_array($a->rank, [AccreditationRank::Unggul, AccreditationRank::A, AccreditationRank::Internasional], true))->count(),
        ];
    }

    protected function surveys(): array
    {
        $rows = Survey::query()->where('respondents', '>', 0)->orderBy('year')->get();
        $years = $rows->pluck('year')->unique()->sort()->values();
        $series = $rows->groupBy(fn ($s) => $s->stakeholder->getLabel())->map(fn ($g) => $years->map(fn ($y) => ($s = $g->firstWhere('year', $y)) ? $s->satisfaction() : null)->all());

        return ['labels' => $years->map(fn ($y) => (string) $y)->all(), 'series' => $series->all()];
    }

    protected function research(): array
    {
        $years = range(now()->year - 5, now()->year - 1);
        $grants = ResearchGrant::query()->whereIn('year', $years)->get(['year', 'category', 'amount']);
        $pubs = Publication::query()->whereIn('year', $years)->get(['year', 'type']);

        return [
            'labels' => array_map('strval', $years),
            'research_funds' => array_map(fn ($y) => round($grants->where('year', $y)->where('category', 'penelitian')->sum('amount') / 1e6, 1), $years),
            'pkm_funds' => array_map(fn ($y) => round($grants->where('year', $y)->where('category', 'pkm')->sum('amount') / 1e6, 1), $years),
            'pubs_international' => array_map(fn ($y) => $pubs->where('year', $y)->filter(fn ($p) => str_contains($p->type->value, 'intl'))->count(), $years),
            'pubs_national' => array_map(fn ($y) => $pubs->where('year', $y)->filter(fn ($p) => str_contains($p->type->value, 'nas'))->count(), $years),
        ];
    }

    protected function tracer(): array
    {
        $rows = TracerStudy::query()->orderBy('graduation_year')->get()->groupBy('graduation_year');

        return [
            'labels' => $rows->keys()->map(fn ($y) => (string) $y)->values()->all(),
            'within_one_year' => $rows->map(fn ($g) => $g->sum('respondents') ? round($g->sum('within_one_year') / $g->sum('respondents') * 100, 1) : 0)->values()->all(),
            'wait' => $rows->map(fn ($g) => round((float) $g->avg('avg_wait_months'), 1))->values()->all(),
        ];
    }
}
