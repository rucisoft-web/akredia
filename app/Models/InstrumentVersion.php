<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Versi instrumen akreditasi (mis. IAPT 4.1). */
class InstrumentVersion extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'effective_at' => 'date',
            'revoked_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(InstrumentVariant::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(Criterion::class);
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }
}
