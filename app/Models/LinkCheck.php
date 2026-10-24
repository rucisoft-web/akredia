<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Hasil pemeriksaan tautan bukti dan hyperlink LED. */
class LinkCheck extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_ok' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function ledSection(): BelongsTo
    {
        return $this->belongsTo(LedSection::class);
    }
}
