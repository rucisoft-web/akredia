<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Riwayat akreditasi prodi (indikator 4, syarat perlu). */
class ProgramAccreditation extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['study_program_id' => StudyProgram::class];

    protected function casts(): array
    {
        return [
            'agency' => \App\Enums\AccreditationAgency::class,
            'rank' => \App\Enums\AccreditationRank::class,
            'sk_date' => 'date',
            'valid_until' => 'date',
            'is_current' => 'boolean',
            'is_international' => 'boolean',
        ];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function monthsLeft(): ?int
    {
        return $this->valid_until ? (int) floor(now()->startOfDay()->diffInMonths($this->valid_until, false)) : null;
    }

    public function isAccredited(): bool
    {
        return $this->rank !== \App\Enums\AccreditationRank::Tidak && (! $this->valid_until || $this->valid_until->isFuture() || $this->valid_until->isToday());
    }

    /** Unggul, A, atau internasional dihitung sebagai prodi unggul untuk jalur Unggul. */
    public function isExcellent(): bool
    {
        return $this->isAccredited() && in_array($this->rank, [\App\Enums\AccreditationRank::Unggul, \App\Enums\AccreditationRank::A, \App\Enums\AccreditationRank::Internasional], true);
    }

    public function auditLabel(): string
    {
        return 'Akreditasi '.$this->studyProgram?->label();
    }
}
