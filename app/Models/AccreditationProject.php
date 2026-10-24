<?php

namespace App\Models;

use App\Enums\ProjectState;
use App\Enums\Track;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Proyek akreditasi institusi: jalur, varian, TS, tanggal target, dan skor terhitung. */
class AccreditationProject extends Model
{
    use Auditable, BelongsToInstitution;

    public const DEFAULT_WEIGHTS = ['fulfilment' => 40, 'evidence' => 20, 'data' => 15, 'led' => 15, 'execution' => 10];

    public const WEIGHT_LABELS = [
        'fulfilment' => 'Pemenuhan',
        'evidence' => 'Kesiapan bukti',
        'data' => 'Kualitas data',
        'led' => 'Kematangan LED',
        'execution' => 'Eksekusi',
    ];

    protected $guarded = ['id'];

    protected array $tracked = ['state', 'track', 'target_date', 'frozen_at', 'track_locked_at'];

    protected function casts(): array
    {
        return [
            'track' => Track::class,
            'state' => ProjectState::class,
            'target_date' => 'date',
            'submitted_at' => 'date',
            'track_locked_at' => 'datetime',
            'frozen_at' => 'datetime',
            'computed_at' => 'datetime',
            'aki_weights' => 'array',
            'aki_components' => 'array',
            'aki' => 'float',
            'projection_accredited' => 'float',
            'projection_excellent' => 'float',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(InstrumentVariant::class, 'instrument_variant_id');
    }

    public function tsPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'ts_period_id');
    }

    public function workItems(): HasMany
    {
        return $this->hasMany(WorkItem::class);
    }

    public function eligibilityItems(): HasMany
    {
        return $this->hasMany(EligibilityItem::class)->orderBy('sort');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ReadinessSnapshot::class)->orderBy('taken_on');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(Reconciliation::class);
    }

    public function frozenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'frozen_by');
    }

    public function weights(): array
    {
        return array_merge(self::DEFAULT_WEIGHTS, $this->aki_weights ?? []);
    }

    public function isFrozen(): bool
    {
        return $this->frozen_at !== null;
    }

    /** Tahun TS (tahun akademik awal), mis. 2025 untuk 2025/2026. */
    public function tsYear(): int
    {
        return $this->tsPeriod?->year_start ?? (int) now()->subYear()->format('Y');
    }

    public function daysToTarget(): ?int
    {
        return $this->target_date ? (int) now()->startOfDay()->diffInDays($this->target_date, false) : null;
    }

    public function threshold(Track $track): float
    {
        return $track === Track::Excellent ? (float) $this->variant->threshold_excellent : (float) $this->variant->threshold_accredited;
    }
}
