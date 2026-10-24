<?php

namespace App\Models;

use App\Enums\HeatColor;
use App\Enums\WorkItemState;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

/** Butir kerja: satu sub-indikator dalam satu proyek, dengan PIC, reviewer, dan warna heatmap. */
class WorkItem extends Model
{
    use Auditable, BelongsToInstitution;

    protected $guarded = ['id'];

    protected array $institutionParents = ['accreditation_project_id' => AccreditationProject::class];

    protected array $tracked = ['state', 'pic_id', 'reviewer_id', 'color', 'due_date'];

    protected function casts(): array
    {
        return [
            'state' => WorkItemState::class,
            'color' => HeatColor::class,
            'due_date' => 'date',
            'is_blocked' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (WorkItem $item) {
            if ($item->pic_id && $item->reviewer_id && (int) $item->pic_id === (int) $item->reviewer_id) {
                throw new LogicException('Reviewer tidak boleh sama dengan PIC.');
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }

    public function subIndicator(): BelongsTo
    {
        return $this->belongsTo(SubIndicator::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function secondPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'second_pic_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function gapItems(): HasMany
    {
        return $this->hasMany(GapItem::class);
    }

    public function evidenceLinks(): HasMany
    {
        return $this->hasMany(EvidenceLink::class);
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'evidence_links')->withPivot(['keyword_id', 'relevance_note'])->withTimestamps();
    }

    public function ledSection(): HasOne
    {
        return $this->hasOne(LedSection::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function assessment(string $kind): ?Assessment
    {
        $track = $this->project?->track?->value ?? 'accredited';

        return $this->assessments->first(fn (Assessment $a) => $a->kind === $kind && $a->track->value === $track);
    }

    /** Penilaian final: hasil rekonsiliasi; bila belum, asesor internal; bila belum, PIC. */
    public function finalAssessment(): ?Assessment
    {
        return $this->assessment('final') ?? $this->assessment('internal') ?? $this->assessment('self');
    }

    public function isVerified(): bool
    {
        return $this->assessment('final') !== null || $this->assessment('internal') !== null;
    }

    public function auditLabel(): string
    {
        return 'Butir '.$this->subIndicator?->code;
    }
}
