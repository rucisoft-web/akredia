<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Penilaian sub-indikator: mandiri (PIC), asesor internal, atau final. */
class Assessment extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['work_item_id' => WorkItem::class];

    protected function casts(): array
    {
        return [
            'is_met' => 'boolean',
            'assessed_at' => 'datetime',
            'track' => \App\Enums\Track::class,
        ];
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public const KINDS = ['self' => 'Penilaian mandiri PIC', 'internal' => 'Asesor internal', 'final' => 'Final (rekonsiliasi)'];

    protected array $tracked = ['is_met'];

    public function auditLabel(): string
    {
        return (self::KINDS[$this->kind] ?? $this->kind).' '.$this->workItem?->subIndicator?->code;
    }
}
