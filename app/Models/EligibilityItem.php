<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Butir daftar periksa eligibilitas. */
class EligibilityItem extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['accreditation_project_id' => AccreditationProject::class];

    protected function casts(): array
    {
        return [
            'is_met' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function auditLabel(): string
    {
        return 'Eligibilitas: '.$this->label;
    }
}
