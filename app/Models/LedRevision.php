<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Riwayat versi narasi LED. */
class LedRevision extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(LedSection::class, 'led_section_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
