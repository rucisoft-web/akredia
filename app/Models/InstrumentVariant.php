<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Varian Buku 3 per jenis PT, dengan ambang skor per jalur. */
class InstrumentVariant extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'threshold_accredited' => 'float',
            'threshold_excellent' => 'float',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class, 'instrument_version_id');
    }

    public function subIndicators(): HasMany
    {
        return $this->hasMany(SubIndicator::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }
}
