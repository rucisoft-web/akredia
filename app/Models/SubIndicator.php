<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Sub-indikator: unit penilaian terkecil, dengan deskriptor per jalur. */
class SubIndicator extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'is_prerequisite_accredited' => 'boolean',
            'is_prerequisite_excellent' => 'boolean',
        ];
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(InstrumentVariant::class, 'instrument_variant_id');
    }

    public function descriptors(): HasMany
    {
        return $this->hasMany(Descriptor::class);
    }

    public function descriptorFor(\App\Enums\Track|string $track): ?Descriptor
    {
        $track = $track instanceof \App\Enums\Track ? $track->value : $track;

        return $this->descriptors->firstWhere(fn (Descriptor $d) => $d->track->value === $track);
    }

    public function isPrerequisiteFor(\App\Enums\Track $track): bool
    {
        return $track === \App\Enums\Track::Excellent ? ($this->is_prerequisite_excellent || $this->is_prerequisite_accredited) : $this->is_prerequisite_accredited;
    }
}
