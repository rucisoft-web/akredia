<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Sesi jadwal asesmen lapangan. */
class VisitSession extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['site_visit_id' => SiteVisit::class];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'group' => \App\Enums\VisitGroup::class,
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class, 'site_visit_id');
    }
}
