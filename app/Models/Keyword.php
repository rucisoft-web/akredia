<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Kata kunci deskriptor yang harus dibuktikan. */
class Keyword extends Model
{
    protected $guarded = ['id'];

    public function descriptor(): BelongsTo
    {
        return $this->belongsTo(Descriptor::class);
    }
}
