<?php

namespace App\Models;

use App\Enums\FindingCategory;
use App\Enums\FindingState;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use App\Support\Sequence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Temuan AMI atau simulasi asesmen, bernomor otomatis per tahun. */
class Finding extends Model
{
    use Auditable, BelongsToInstitution;

    public const SOURCES = ['ami' => 'Audit Mutu Internal', 'simulasi' => 'Simulasi asesmen', 'asesmen' => 'Asesmen lapangan BAN-PT', 'survei' => 'Hasil survei'];

    protected $guarded = ['id'];

    protected array $tracked = ['state', 'category'];

    protected function casts(): array
    {
        return ['category' => FindingCategory::class, 'state' => FindingState::class, 'due_date' => 'date'];
    }

    protected static function booted(): void
    {
        static::creating(fn (Finding $f) => $f->number ??= Sequence::next('finding', $f->institution_id ?? app(\App\Support\CurrentInstitution::class)->id()));
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(InternalAudit::class, 'internal_audit_id');
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class, 'quality_standard_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function capas(): HasMany
    {
        return $this->hasMany(Capa::class);
    }

    public function auditLabel(): string
    {
        return 'Temuan '.$this->number;
    }
}
