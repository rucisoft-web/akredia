<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Publikasi dosen tetap. */
class Publication extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => \App\Enums\PublicationType::class,
        ];
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }
}
