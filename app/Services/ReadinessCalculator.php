<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\HeatColor;
use App\Enums\LedState;
use App\Enums\ReconciliationState;
use App\Enums\Severity;
use App\Enums\TaskStatus;
use App\Enums\Track;
use App\Models\AccreditationProject;
use App\Models\Capa;
use App\Models\Document;
use App\Models\MetricValue;
use App\Models\ReadinessSnapshot;
use App\Models\Reconciliation;
use App\Models\Task;
use App\Models\WorkItem;
use Illuminate\Support\Collection;

/**
 * Mesin penilaian (bagian 4.6 rancangan): proyeksi skor kedua jalur, syarat perlu,
 * Indeks Kesiapan Akredia (AKI), warna heatmap, dan gerbang "Siap Diajukan".
 *
 * AKI dan proyeksi adalah alat manajemen internal, bukan nilai resmi BAN-PT.
 */
class ReadinessCalculator
{
    public const LED_SCORES = ['ratified' => 1.0, 'reviewer_approved' => 1.0, 'review' => 0.6, 'revision' => 0.3, 'draft' => 0.3];

    /** Penalti rekonsiliasi terbuka (poin) pada komponen kualitas data. */
    public const RECON_PENALTY = ['tinggi' => 5, 'sedang' => 2, 'rendah' => 0.5];

    /** @return Collection<int, WorkItem> */
    public function items(AccreditationProject $project): Collection
    {
        return WorkItem::withoutGlobalScopes()
            ->where('accreditation_project_id', $project->id)
            ->with(['subIndicator.indicator.criterion', 'subIndicator.descriptors.keywords', 'assessments', 'ledSection',
                'evidenceLinks.document.linkCheck', 'pic', 'reviewer', 'project'])
            ->get()
            ->sortBy(fn (WorkItem $w) => [$w->subIndicator->indicator->number, $w->subIndicator->sort])
            ->values();
    }

    public function assessmentFor(WorkItem $item, Track $track): ?\App\Models\Assessment
    {
        foreach (['final', 'internal', 'self'] as $kind) {
            $a = $item->assessments->first(fn ($x) => $x->kind === $kind && $x->track === $track);

            if ($a) {
                return $a;
            }
        }

        return null;
    }

    public function isMet(WorkItem $item, Track $track, array $assumeMet = []): ?bool
    {
        if (in_array($item->id, $assumeMet, true)) {
            return true;
        }

        return $this->assessmentFor($item, $track)?->is_met;
    }

    /** Proyeksi skor (0-100) untuk satu jalur; $assumeMet untuk analisis what-if. */
    public function projection(Collection $items, Track $track, array $assumeMet = []): float
    {
        $total = $items->sum(fn (WorkItem $w) => $w->subIndicator->weight);

        if ($total <= 0) {
            return 0;
        }

        $met = $items->sum(fn (WorkItem $w) => $this->isMet($w, $track, $assumeMet) ? $w->subIndicator->weight : 0);

        return round($met / $total * 100, 2);
    }

    /** @return Collection<int, WorkItem> sub-indikator syarat perlu yang belum memenuhi. */
    public function failedPrerequisites(Collection $items, Track $track, array $assumeMet = []): Collection
    {
        return $items->filter(fn (WorkItem $w) => $w->subIndicator->isPrerequisiteFor($track) && ! $this->isMet($w, $track, $assumeMet))->values();
    }

    /** Kata kunci jalur proyek beserta status dukungan bukti. @return array{total:int, supported:int, missing: array<int,string>} */
    public function keywordSupport(WorkItem $item, Track $track): array
    {
        $descriptor = $item->subIndicator->descriptorFor($track);
        $keywords = $descriptor?->keywords ?? collect();
        $usableLinks = $item->evidenceLinks->filter(fn ($l) => $l->document && $l->document->isUsable());
        $supportedIds = $usableLinks->pluck('keyword_id')->filter()->unique();
        $missing = $keywords->reject(fn ($k) => $supportedIds->contains($k->id))->pluck('text')->values()->all();

        return ['total' => $keywords->count(), 'supported' => $keywords->count() - count($missing), 'missing' => $missing];
    }

    public function ledScore(WorkItem $item, int $wordLimit): float
    {
        $led = $item->ledSection;

        if (! $led || $led->word_count === 0) {
            return 0;
        }

        $score = self::LED_SCORES[$led->state->value] ?? 0.3;

        if ($led->word_count > $wordLimit) {
            $score -= 0.2;
        }

        $missing = count($led->missing_keywords ?? []);
        $keywords = max(1, $item->subIndicator->descriptorFor($item->project->track)?->keywords->count() ?? 1);
        $score -= 0.2 * min(1, $missing / $keywords);

        return max(0, $score);
    }

    /** @return array{0: HeatColor, 1: string} warna dan alasannya. */
    public function color(WorkItem $item, Track $track, int $wordLimit): array
    {
        $met = $this->isMet($item, $track);

        if ($met === null) {
            return [HeatColor::Gray, 'Belum dinilai'];
        }

        if (! $met) {
            return [HeatColor::Red, $item->subIndicator->isPrerequisiteFor($track) ? 'Syarat perlu tidak memenuhi' : 'Penilaian: tidak memenuhi deskriptor'];
        }

        $support = $this->keywordSupport($item, $track);
        $ledOk = $item->ledSection && in_array($item->ledSection->state, [LedState::ReviewerApproved, LedState::Ratified], true)
            && $item->ledSection->word_count <= $wordLimit;
        $reasons = [];

        if ($support['total'] > 0 && $support['supported'] < $support['total']) {
            $reasons[] = ($support['total'] - $support['supported']).' kata kunci belum didukung bukti valid';
        }

        if (! $ledOk) {
            $reasons[] = 'LED belum disetujui';
        }

        return $reasons === []
            ? [HeatColor::Green, 'Memenuhi, bukti lengkap, LED disetujui']
            : [HeatColor::Yellow, 'Memenuhi, tetapi '.implode('; ', $reasons)];
    }

    /** Hitung semua komponen; tidak menyimpan. */
    public function evaluate(AccreditationProject $project, ?Collection $items = null): array
    {
        $project->loadMissing('variant');
        $items ??= $this->items($project);
        $track = $project->track;
        $limit = $project->word_limit;

        $projAcc = $this->projection($items, Track::Accredited);
        $projExc = $this->projection($items, Track::Excellent);
        $failed = $this->failedPrerequisites($items, $track);

        // Kesiapan bukti
        $kwTotal = 0;
        $kwSupported = 0;

        foreach ($items as $item) {
            $s = $this->keywordSupport($item, $track);
            $kwTotal += $s['total'];
            $kwSupported += $s['supported'];
        }

        $evidence = $kwTotal > 0 ? $kwSupported / $kwTotal * 100 : 0;

        // Kualitas data
        $values = MetricValue::withoutGlobalScopes()->where('accreditation_project_id', $project->id)->where('source', 'internal')->get();
        $verified = $values->count() > 0 ? $values->whereIn('status', ['verified', 'locked'])->count() / $values->count() * 100 : 0;
        $openRecon = Reconciliation::withoutGlobalScopes()->where('institution_id', $project->institution_id)
            ->where('state', '!=', ReconciliationState::Verified->value)->get();
        $penalty = $openRecon->sum(fn ($r) => self::RECON_PENALTY[$r->severity->value] ?? 0);
        $data = max(0, min(100, $verified - $penalty));

        // Kematangan LED
        $led = $items->count() > 0 ? $items->avg(fn ($w) => $this->ledScore($w, $limit)) * 100 : 0;

        // Eksekusi: tugas dan CAPA yang jatuh tempo dalam 60 hari terakhir
        $from = now()->subDays(60)->startOfDay();
        $tasks = Task::withoutGlobalScopes()->where('institution_id', $project->institution_id)
            ->where('status', '!=', TaskStatus::Cancelled->value)->whereBetween('due_date', [$from, now()])->get();
        $capas = Capa::withoutGlobalScopes()->where('institution_id', $project->institution_id)->whereBetween('due_date', [$from, now()])->get();
        $due = $tasks->count() + $capas->count();
        $onTime = $tasks->filter(fn ($t) => $t->completed_at && $t->completed_at->lte($t->due_date->endOfDay()))->count()
            + $capas->filter(fn ($c) => $c->isOnTime())->count();
        $execution = $due > 0 ? $onTime / $due * 100 : 100;

        $components = [
            'fulfilment' => round($track === Track::Excellent ? $projExc : $projAcc, 2),
            'evidence' => round($evidence, 2),
            'data' => round($data, 2),
            'led' => round($led, 2),
            'execution' => round($execution, 2),
        ];
        $weights = $project->weights();
        $aki = array_sum(array_map(fn ($k) => $components[$k] * $weights[$k], array_keys($components))) / max(1, array_sum($weights));

        return [
            'aki' => round($aki, 2),
            'components' => $components,
            'projection_accredited' => $projAcc,
            'projection_excellent' => $projExc,
            'failed_prerequisites' => $failed,
            'unverified' => $items->filter(fn ($w) => $this->isMet($w, $track) !== null && ! $w->assessments->contains(fn ($a) => in_array($a->kind, ['internal', 'final'], true) && $a->track === $track))->count(),
            'keywords' => ['total' => $kwTotal, 'supported' => $kwSupported],
            'open_reconciliations' => $openRecon,
            'tasks_due' => $due,
            'tasks_on_time' => $onTime,
        ];
    }

    /** Hitung, simpan ke proyek, perbarui warna butir kerja, dan rekam snapshot hari ini. */
    public function refresh(AccreditationProject $project): array
    {
        $items = $this->items($project);
        $result = $this->evaluate($project, $items);

        foreach ($items as $item) {
            [$color, $reason] = $this->color($item, $project->track, $project->word_limit);

            if ($item->color !== $color || $item->color_reason !== $reason) {
                WorkItem::withoutGlobalScopes()->whereKey($item->id)->update(['color' => $color->value, 'color_reason' => $reason]);
            }
        }

        AccreditationProject::withoutGlobalScopes()->whereKey($project->id)->update([
            'aki' => $result['aki'],
            'aki_components' => json_encode($result['components']),
            'projection_accredited' => $result['projection_accredited'],
            'projection_excellent' => $result['projection_excellent'],
            'computed_at' => now(),
        ]);

        ReadinessSnapshot::withoutGlobalScopes()->updateOrCreate(
            ['accreditation_project_id' => $project->id, 'taken_on' => today()->toDateString()],
            [
                'institution_id' => $project->institution_id,
                'aki' => $result['aki'],
                'components' => $result['components'],
                'projection_accredited' => $result['projection_accredited'],
                'projection_excellent' => $result['projection_excellent'],
                'prerequisites_failed' => $result['failed_prerequisites']->count(),
            ],
        );

        $project->refresh();

        return $result;
    }

    /**
     * Gerbang "Siap Diajukan" (FR-PRJ-07). @return array<int, array{label:string, ok:bool, detail:string}>
     */
    public function gate(AccreditationProject $project): array
    {
        $project->loadMissing(['variant', 'eligibilityItems']);
        $items = $this->items($project);
        $result = $this->evaluate($project, $items);
        $track = $project->track;
        $threshold = $project->threshold($track);
        $projection = $track === Track::Excellent ? $result['projection_excellent'] : $result['projection_accredited'];

        $failed = $result['failed_prerequisites'];
        $highRecon = $result['open_reconciliations']->filter(fn ($r) => $r->severity === Severity::Tinggi);
        $ledPending = $items->filter(fn ($w) => ! $w->ledSection || ! in_array($w->ledSection->state, [LedState::ReviewerApproved, LedState::Ratified], true));
        $docIds = $items->flatMap(fn ($w) => $w->evidenceLinks->pluck('document_id'))->unique();
        $badDocs = Document::withoutGlobalScopes()->whereIn('id', $docIds)->with('linkCheck')->get()
            ->filter(fn ($d) => $d->status === DocumentStatus::Expired || ($d->valid_until && $d->valid_until->isPast()) || ($d->linkCheck && ! $d->linkCheck->is_ok));
        $eligPending = $project->eligibilityItems->where('is_met', false);

        return [
            ['label' => 'Seluruh syarat perlu memenuhi', 'ok' => $failed->isEmpty(),
                'detail' => $failed->isEmpty() ? 'Semua syarat perlu jalur '.$track->getLabel().' memenuhi.' : 'Belum memenuhi: '.$failed->map(fn ($w) => $w->subIndicator->code)->implode(', ')],
            ['label' => 'Proyeksi skor minimal ambang jalur', 'ok' => $projection >= $threshold,
                'detail' => 'Proyeksi '.number_format($projection, 1, ',', '.').'% dari ambang '.number_format($threshold, 0).'%.'],
            ['label' => 'Tidak ada rekonsiliasi tingkat tinggi yang terbuka', 'ok' => $highRecon->isEmpty(),
                'detail' => $highRecon->isEmpty() ? 'Tidak ada.' : $highRecon->count().' selisih tinggi: '.$highRecon->pluck('metric_code')->unique()->implode(', ')],
            ['label' => 'Semua bagian LED disetujui', 'ok' => $ledPending->isEmpty(),
                'detail' => $ledPending->isEmpty() ? 'Semua bagian disetujui reviewer atau disahkan pimpinan.' : $ledPending->count().' bagian belum disetujui.'],
            ['label' => 'Tidak ada bukti dikutip yang kedaluwarsa atau tautannya rusak', 'ok' => $badDocs->isEmpty(),
                'detail' => $badDocs->isEmpty() ? 'Semua bukti dikutip berlaku.' : $badDocs->count().' bukti bermasalah: '.$badDocs->take(3)->pluck('title')->implode('; ')],
            ['label' => 'Daftar eligibilitas lengkap', 'ok' => $project->eligibilityItems->isNotEmpty() && $eligPending->isEmpty(),
                'detail' => $eligPending->isEmpty() ? 'Semua butir eligibilitas terpenuhi.' : $eligPending->count().' butir belum terpenuhi.'],
            ['label' => 'Data TS sudah dikunci (freeze)', 'ok' => $project->isFrozen(),
                'detail' => $project->isFrozen() ? 'Dikunci '.$project->frozen_at->translatedFormat('d M Y H:i').'.' : 'Data belum dikunci.'],
        ];
    }

    public function gatePasses(AccreditationProject $project): bool
    {
        return collect($this->gate($project))->every(fn ($c) => $c['ok']);
    }

    /**
     * Ringkasan per indikator untuk heatmap 39 indikator: warna terburuk dari sub-indikatornya.
     *
     * @return Collection<int, array>
     */
    public function heatmap(Collection $items): Collection
    {
        $rank = ['red' => 4, 'yellow' => 3, 'gray' => 2, 'green' => 1];

        return $items->groupBy(fn ($w) => $w->subIndicator->indicator_id)->map(function ($group) use ($rank) {
            $indicator = $group->first()->subIndicator->indicator;
            $worst = $group->sortByDesc(fn ($w) => $rank[$w->color->value] ?? 0)->first();
            $colors = $group->map(fn ($w) => $w->color->value);
            // Abu-abu hanya bila semua belum dinilai; campuran hijau/abu tampil kuning.
            $color = $worst->color->value;

            if ($color === 'gray' && $colors->contains('green')) {
                $color = 'yellow';
            }

            return [
                'indicator' => $indicator,
                'number' => $indicator->number,
                'criterion' => $indicator->criterion->number,
                'color' => $color,
                'reason' => $group->map(fn ($w) => $w->subIndicator->code.': '.$w->color_reason)->implode(' · '),
                'items' => $group,
                'prerequisite' => $group->contains(fn ($w) => $w->subIndicator->is_prerequisite_accredited || $w->subIndicator->is_prerequisite_excellent),
            ];
        })->sortBy('number')->values();
    }
}
