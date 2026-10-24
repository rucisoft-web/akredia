<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Deskriptor pemenuhan sub-indikator per jalur status. */
class Descriptor extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'track' => \App\Enums\Track::class,
        ];
    }

    public function subIndicator(): BelongsTo
    {
        return $this->belongsTo(SubIndicator::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }
}
