<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Komentar reviu pada narasi LED. */
class LedComment extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(LedSection::class, 'led_section_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
