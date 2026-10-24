<?php

namespace App\Models;

use App\Enums\LedState;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Narasi LED per sub-indikator. Sitasi ditulis sebagai [B:KODE] dan angka hidup sebagai {{KODE_METRIK}}. */
class LedSection extends Model
{
    use Auditable, BelongsToInstitution;

    protected $guarded = ['id'];

    protected array $institutionParents = ['work_item_id' => WorkItem::class];

    protected array $tracked = ['state', 'word_count'];

    protected function casts(): array
    {
        return [
            'state' => LedState::class,
            'missing_keywords' => 'array',
            'keyword_coverage' => 'float',
            'approved_at' => 'datetime',
            'ratified_at' => 'datetime',
        ];
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(LedRevision::class)->orderByDesc('revision');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(LedComment::class)->latest();
    }

    public function citations(): HasMany
    {
        return $this->hasMany(LedCitation::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function ratifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ratified_by');
    }

    public static function countWords(?string $text): int
    {
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        return $plain === '' ? 0 : count(preg_split('/\s+/u', $plain));
    }

    public function auditLabel(): string
    {
        return 'LED '.$this->workItem?->subIndicator?->code;
    }
}
