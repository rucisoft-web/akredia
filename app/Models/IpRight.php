<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Kekayaan intelektual (HKI) dan karya diadopsi. */
class IpRight extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'ip_type' => \App\Enums\IpType::class,
            'is_adopted' => 'boolean',
        ];
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }
}
