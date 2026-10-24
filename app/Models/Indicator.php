<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Indikator 1 sampai 39. */
class Indicator extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quality_target' => \App\Enums\QualityTarget::class,
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class, 'instrument_version_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(IndicatorGroup::class, 'indicator_group_id');
    }

    public function subIndicators(): HasMany
    {
        return $this->hasMany(SubIndicator::class);
    }

    public function label(): string
    {
        return $this->number.'. '.\Illuminate\Support\Str::limit($this->statement, 90);
    }
}
