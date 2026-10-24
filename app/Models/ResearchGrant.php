<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

/** Dana penelitian dan PkM. */
class ResearchGrant extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }
}
