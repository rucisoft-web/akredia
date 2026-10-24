<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Nilai metrik per tahun dan cakupan; Draf → Terverifikasi → Dikunci. */
class MetricValue extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'inputs' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function computer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'computed_by');
    }

    public const STATUSES = ['draft' => 'Draf', 'verified' => 'Terverifikasi', 'locked' => 'Dikunci'];

    protected array $tracked = ['value', 'status'];

    public function auditLabel(): string
    {
        return $this->metric_code.' '.$this->year;
    }
}
