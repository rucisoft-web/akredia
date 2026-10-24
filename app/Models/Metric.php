<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Definisi rasio kuantitatif dengan rumus sebagai data. */
class Metric extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'accredited_min' => 'float',
            'accredited_max' => 'float',
            'excellent_min' => 'float',
            'excellent_max' => 'float',
            'tolerance_pct' => 'float',
            'is_prerequisite' => 'boolean',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(InstrumentVariant::class, 'instrument_variant_id');
    }
}
