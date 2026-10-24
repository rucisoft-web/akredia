<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

/** Rapat Tinjauan Manajemen (RTM). */
class ManagementReview extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }
}
