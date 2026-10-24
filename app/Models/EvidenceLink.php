<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tautan bukti ke butir kerja (banyak-ke-banyak). */
class EvidenceLink extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['document_id' => Document::class, 'work_item_id' => WorkItem::class];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

    public function auditLabel(): string
    {
        return 'Tautan '.$this->document?->title.' → '.$this->workItem?->subIndicator?->code;
    }
}
