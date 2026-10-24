<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Rekam AKI berkala untuk grafik tren. */
class ReadinessSnapshot extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
            'components' => 'array',
            'aki' => 'float',
            'projection_accredited' => 'float',
            'projection_excellent' => 'float',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }
}
