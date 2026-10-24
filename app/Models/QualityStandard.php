<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Standar mutu SPMI dengan tahap PPEPP. */
class QualityStandard extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'approved_at' => 'date',
            'review_due' => 'date',
        ];
    }

    public function ownerUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'owner_unit_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }

    public const STAGES = ['penetapan' => 'Penetapan', 'pelaksanaan' => 'Pelaksanaan', 'evaluasi' => 'Evaluasi', 'pengendalian' => 'Pengendalian', 'peningkatan' => 'Peningkatan'];
}
