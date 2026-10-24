<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Sitasi bukti atau angka dalam narasi LED. */
class LedCitation extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(LedSection::class, 'led_section_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'document_version_id');
    }
}
