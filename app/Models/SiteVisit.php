<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Rencana asesmen lapangan. */
class SiteVisit extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'rehearsal_on' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(VisitSession::class);
    }

    public function interviewees(): HasMany
    {
        return $this->hasMany(VisitInterviewee::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(VisitRecommendation::class);
    }

    public const STATES = ['rencana' => 'Rencana', 'gladi' => 'Gladi', 'berlangsung' => 'Berlangsung', 'selesai' => 'Selesai'];
}
