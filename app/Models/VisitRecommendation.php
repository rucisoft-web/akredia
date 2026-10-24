<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Rekomendasi asesor yang ditindaklanjuti menjadi RTL atau CAPA. */
class VisitRecommendation extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['site_visit_id' => SiteVisit::class];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class, 'site_visit_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function auditLabel(): string
    {
        return 'Rekomendasi AL: '.\Illuminate\Support\Str::limit($this->recommendation, 80);
    }
}
