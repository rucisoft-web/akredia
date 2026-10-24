<?php

namespace App\Models;

use App\Enums\RiskState;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Risiko akreditasi 5×5. Skor = probabilitas × dampak. */
class Risk extends Model
{
    use Auditable, BelongsToInstitution;

    public const LEVELS = ['kritis' => ['Kritis', 'danger'], 'tinggi' => ['Tinggi', 'warning'], 'sedang' => ['Sedang', 'info'], 'rendah' => ['Rendah', 'success']];

    protected $guarded = ['id'];

    protected array $tracked = ['probability', 'impact', 'state'];

    protected function casts(): array
    {
        return ['state' => RiskState::class, 'target_date' => 'date'];
    }

    public static function levelFor(int $score): string
    {
        return match (true) {
            $score >= 20 => 'kritis',
            $score >= 12 => 'tinggi',
            $score >= 6 => 'sedang',
            default => 'rendah',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Risk $r) {
            $r->score = (int) $r->probability * (int) $r->impact;
            $r->level = static::levelFor($r->score);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
