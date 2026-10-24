<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Baris kertas kerja PIC per kata kunci deskriptor. */
class GapItem extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['work_item_id' => WorkItem::class];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\GapStatus::class,
            'due_date' => 'date',
        ];
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    protected array $tracked = ['status'];

    public function auditLabel(): string
    {
        return 'Kertas kerja '.$this->workItem?->subIndicator?->code.': '.\Illuminate\Support\Str::limit((string) $this->keyword_text, 80);
    }
}
