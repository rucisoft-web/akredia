<?php

namespace App\Services;

use App\Enums\GapStatus;
use App\Enums\LedState;
use App\Enums\Priority;
use App\Enums\ProjectState;
use App\Enums\TaskStatus;
use App\Enums\WorkItemState;
use App\Models\AccreditationProject;
use App\Models\AuditLog;
use App\Models\DataSnapshot;
use App\Models\EligibilityItem;
use App\Models\EvidenceLink;
use App\Models\GapItem;
use App\Models\LedSection;
use App\Models\MetricValue;
use App\Models\SubIndicator;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkItem;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Siklus hidup proyek akreditasi (Tabel 14): pembangkitan butir kerja, transisi status,
 * data freeze, dan penyalinan proyek untuk siklus berikutnya.
 */
class ProjectWorkflow
{
    /** Transisi yang diizinkan: dari => [ke, ...]. */
    public const TRANSITIONS = [
        'draft' => ['preparation'],
        'preparation' => ['drafting'],
        'drafting' => ['finalization'],
        'finalization' => ['ready', 'drafting'],
        'ready' => ['submitted', 'drafting'],
        'submitted' => ['assessment'],
        'assessment' => ['done'],
        'done' => [],
    ];

    public const ELIGIBILITY = [
        ['Izin pendirian dan penyelenggaraan PT sah dan berlaku', 'SK pendirian PT dan izin penyelenggaraan dari kementerian.'],
        ['Statuta atau AD/ART badan penyelenggara', 'Statuta PT dan akta badan penyelenggara.'],
        ['Rencana induk pengembangan atau renstra memuat diferensiasi misi dan SPMI', 'RIP/renstra memuat visi, misi, tujuan, sasaran, nilai, organisasi, pengambilan keputusan, dan SPMI.'],
        ['Seluruh program studi aktif terakreditasi', 'Diperiksa terhadap riwayat akreditasi prodi (indikator 4).'],
        ['Data PD Dikti mutakhir dan tersinkronisasi', 'Laporan PD Dikti semester terakhir dan status sinkronisasi SAPTO.'],
    ];

    public function __construct(protected ReadinessCalculator $calculator) {}

    /** Bangkitkan butir kerja untuk setiap sub-indikator varian, kertas kerja per kata kunci, dan daftar eligibilitas. */
    public function generate(AccreditationProject $project): int
    {
        return DB::transaction(function () use ($project) {
            $subs = SubIndicator::where('instrument_variant_id', $project->instrument_variant_id)->with('descriptors.keywords')->get();
            $created = 0;

            foreach ($subs as $sub) {
                $item = WorkItem::firstOrCreate(
                    ['accreditation_project_id' => $project->id, 'sub_indicator_id' => $sub->id],
                    ['institution_id' => $project->institution_id, 'due_date' => $project->target_date?->copy()->subMonths(3)],
                );

                if ($item->wasRecentlyCreated) {
                    $created++;

                    foreach ($sub->descriptorFor($project->track)?->keywords ?? [] as $kw) {
                        GapItem::create(['institution_id' => $project->institution_id, 'work_item_id' => $item->id, 'keyword_id' => $kw->id, 'keyword_text' => $kw->text]);
                    }

                    LedSection::create(['institution_id' => $project->institution_id, 'work_item_id' => $item->id, 'state' => LedState::Draft]);
                }
            }

            if ($project->eligibilityItems()->count() === 0) {
                foreach (self::ELIGIBILITY as $i => [$label, $description]) {
                    EligibilityItem::create(['institution_id' => $project->institution_id, 'accreditation_project_id' => $project->id, 'label' => $label, 'description' => $description, 'sort' => $i]);
                }
            }

            return $created;
        });
    }

    /** @return array<int, string> status tujuan yang diizinkan dari status saat ini. */
    public function nextStates(AccreditationProject $project): array
    {
        return self::TRANSITIONS[$project->state->value] ?? [];
    }

    public function transition(AccreditationProject $project, ProjectState $to, ?string $reason = null): void
    {
        $from = $project->state;

        if (! in_array($to->value, $this->nextStates($project), true)) {
            throw new LogicException("Tidak dapat berpindah dari {$from->getLabel()} ke {$to->getLabel()}.");
        }

        if ($to === ProjectState::Finalization && ! $project->isFrozen()) {
            throw new LogicException('Finalisasi membutuhkan data TS yang sudah dikunci (freeze).');
        }

        if ($to === ProjectState::Ready && ! $this->calculator->gatePasses($project)) {
            $failed = collect($this->calculator->gate($project))->where('ok', false)->pluck('label')->implode('; ');
            throw new LogicException('Gerbang Siap Diajukan belum terbuka: '.$failed.'.');
        }

        if ($to === ProjectState::Drafting && in_array($from, [ProjectState::Finalization, ProjectState::Ready], true) && blank($reason)) {
            throw new LogicException('Kembali ke Penyusunan wajib disertai alasan.');
        }

        $project->state = $to;
        $project->state_note = $reason;

        if ($to === ProjectState::Submitted) {
            $project->submitted_at ??= today();
        }

        $project->save();
    }

    /** Kunci data TS: nilai metrik dikunci dan snapshot disimpan (FR-PRJ-05). */
    public function freeze(AccreditationProject $project, User $user, MetricEngine $engine): void
    {
        if ($project->isFrozen()) {
            throw new LogicException('Data sudah dikunci.');
        }

        DB::transaction(function () use ($project, $user, $engine) {
            $values = MetricValue::withoutGlobalScopes()->where('accreditation_project_id', $project->id)->get();
            $payload = [
                'ts' => $project->tsYear(),
                'metrics' => $values->map(fn ($v) => ['code' => $v->metric_code, 'prodi' => $v->study_program_id, 'value' => $v->value, 'inputs' => $v->inputs])->values(),
                'variables' => $engine->variables($project->institution_id, $project->tsYear()),
                'documents' => EvidenceLink::withoutGlobalScopes()->whereIn('work_item_id', $project->workItems()->pluck('id'))
                    ->with('document.latestVersion')->get()
                    ->map(fn ($l) => ['document' => $l->document_id, 'version' => $l->document?->latestVersion?->version, 'sha256' => $l->document?->latestVersion?->sha256])
                    ->unique('document')->values(),
            ];

            DataSnapshot::create([
                'institution_id' => $project->institution_id, 'accreditation_project_id' => $project->id,
                'label' => 'Freeze TS '.$project->tsYear().' · '.now()->translatedFormat('d M Y H:i'), 'kind' => 'ts', 'payload' => $payload, 'created_by' => $user->id,
            ]);

            MetricValue::withoutGlobalScopes()->where('accreditation_project_id', $project->id)->update(['status' => 'locked']);
            $project->forceFill(['frozen_at' => now(), 'frozen_by' => $user->id])->save();
        });
    }

    /** Buka kunci data dengan alasan tercatat. */
    public function unfreeze(AccreditationProject $project, string $reason): void
    {
        if (blank($reason)) {
            throw new LogicException('Alasan membuka kunci wajib diisi.');
        }

        MetricValue::withoutGlobalScopes()->where('accreditation_project_id', $project->id)->where('status', 'locked')->update(['status' => 'verified']);
        $project->forceFill(['frozen_at' => null, 'frozen_by' => null])->save();
        AuditLog::record('buka_kunci', $project, ['alasan' => $reason]);
    }

    /** Salin proyek untuk siklus berikutnya, termasuk PIC dan tautan bukti yang masih berlaku (FR-PRJ-06). */
    public function duplicate(AccreditationProject $source, array $overrides): AccreditationProject
    {
        return DB::transaction(function () use ($source, $overrides) {
            $copy = $source->replicate(['aki', 'aki_components', 'projection_accredited', 'projection_excellent', 'computed_at', 'frozen_at', 'frozen_by', 'track_locked_at', 'track_locked_by', 'submitted_at', 'state_note']);
            $copy->fill($overrides);
            $copy->state = ProjectState::Draft;
            $copy->save();
            $this->generate($copy);

            $old = $source->workItems()->with(['evidenceLinks.document'])->get()->keyBy('sub_indicator_id');

            foreach ($copy->workItems as $item) {
                $prev = $old->get($item->sub_indicator_id);

                if (! $prev) {
                    continue;
                }

                $item->update(['pic_id' => $prev->pic_id, 'second_pic_id' => $prev->second_pic_id, 'reviewer_id' => $prev->reviewer_id, 'unit_id' => $prev->unit_id]);

                foreach ($prev->evidenceLinks as $link) {
                    if ($link->document && (! $link->document->valid_until || $link->document->valid_until->isFuture())) {
                        EvidenceLink::create(['institution_id' => $copy->institution_id, 'document_id' => $link->document_id, 'work_item_id' => $item->id, 'keyword_id' => $link->keyword_id, 'relevance_note' => $link->relevance_note]);
                    }
                }
            }

            return $copy;
        });
    }

    /** Kata kunci belum terbukti menjadi tugas RTL (FR-GAP-02). @return int jumlah tugas baru */
    public function createRtlTasks(WorkItem $item): int
    {
        $count = 0;

        foreach ($item->gapItems()->where('status', '!=', GapStatus::Terbukti->value)->whereNull('task_id')->get() as $gap) {
            $task = Task::create([
                'institution_id' => $item->institution_id,
                'accreditation_project_id' => $item->accreditation_project_id,
                'work_item_id' => $item->id,
                'title' => 'RTL '.$item->subIndicator->code.': '.\Illuminate\Support\Str::limit($gap->keyword_text, 120),
                'description' => $gap->rtl ?: 'Lengkapi bukti untuk kata kunci deskriptor: '.$gap->keyword_text,
                'assignee_id' => $gap->owner_id ?? $item->pic_id,
                'unit_id' => $item->unit_id,
                'due_date' => $gap->due_date ?? $item->due_date,
                'priority' => $item->subIndicator->is_prerequisite_accredited ? Priority::Tinggi : Priority::Sedang,
                'status' => TaskStatus::Todo,
                'source' => 'rtl',
                'source_id' => $gap->id,
            ]);
            $gap->update(['task_id' => $task->id]);
            $count++;
        }

        return $count;
    }

    /** Status butir kerja dengan aturan: Disetujui hanya bila bukti minimum valid, LED disetujui, penilaian internal tercatat. */
    public function setItemState(WorkItem $item, WorkItemState $state): void
    {
        if ($state === WorkItemState::Approved) {
            $item->loadMissing(['evidenceLinks.document', 'ledSection', 'assessments', 'project']);
            $reasons = [];

            if (! $item->evidenceLinks->contains(fn ($l) => $l->document?->isUsable())) {
                $reasons[] = 'belum ada bukti valid';
            }

            if (! $item->ledSection || ! in_array($item->ledSection->state, [LedState::ReviewerApproved, LedState::Ratified], true)) {
                $reasons[] = 'LED belum disetujui';
            }

            if (! $item->assessments->contains(fn ($a) => in_array($a->kind, ['internal', 'final'], true))) {
                $reasons[] = 'penilaian asesor internal belum tercatat';
            }

            if ($reasons) {
                throw new LogicException('Butir belum dapat disetujui: '.implode(', ', $reasons).'.');
            }
        }

        $item->update(['state' => $state]);
    }
}
