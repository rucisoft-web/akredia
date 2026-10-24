<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Narasumber asesmen lapangan. */
class VisitInterviewee extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['site_visit_id' => SiteVisit::class];

    protected function casts(): array
    {
        return [
            'group' => \App\Enums\VisitGroup::class,
            'is_confirmed' => 'boolean',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class, 'site_visit_id');
    }
}
