<?php

namespace App\Models;

use App\Enums\ReconciliationState;
use App\Enums\Severity;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Selisih data internal dengan PD Dikti; keparahan dihitung dari toleransi. */
class Reconciliation extends Model
{
    use Auditable, BelongsToInstitution;

    protected $guarded = ['id'];

    protected array $tracked = ['state', 'severity', 'pddikti_value', 'internal_value'];

    protected function casts(): array
    {
        return [
            'severity' => Severity::class,
            'state' => ReconciliationState::class,
            'internal_value' => 'float',
            'pddikti_value' => 'float',
            'difference' => 'float',
            'difference_pct' => 'float',
            'tolerance_pct' => 'float',
        ];
    }

    /** Dalam toleransi; ≤ 2× toleransi rendah; ≤ 5× sedang; lebih dari itu tinggi. */
    public static function severityFor(float $diffPct, float $tolerance): Severity
    {
        $tolerance = max($tolerance, 0.01);

        return match (true) {
            $diffPct <= $tolerance => Severity::Toleransi,
            $diffPct <= 2 * $tolerance => Severity::Rendah,
            $diffPct <= 5 * $tolerance => Severity::Sedang,
            default => Severity::Tinggi,
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Reconciliation $r) {
            if ($r->internal_value !== null && $r->pddikti_value !== null) {
                $r->difference = round($r->internal_value - $r->pddikti_value, 4);
                $base = abs($r->pddikti_value) > 0 ? abs($r->pddikti_value) : max(abs($r->internal_value), 1);
                $r->difference_pct = round(abs($r->difference) / $base * 100, 2);
                $r->severity = static::severityFor($r->difference_pct, (float) $r->tolerance_pct);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }

    public function ownerUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'owner_unit_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOpen(): bool
    {
        return $this->state !== ReconciliationState::Verified;
    }

    public function auditLabel(): string
    {
        return 'Rekonsiliasi '.$this->metric_code.' '.$this->scope_label;
    }
}
