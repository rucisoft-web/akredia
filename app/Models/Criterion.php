<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kriteria instrumen (Budaya Mutu, Relevansi, Akuntabilitas, Diferensiasi Misi). */
class Criterion extends Model
{
    protected $table = 'criteria';

    protected $guarded = ['id'];

    public function version(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class, 'instrument_version_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(IndicatorGroup::class);
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }
}
