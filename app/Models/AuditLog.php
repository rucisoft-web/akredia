<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

/** Log audit: hanya bisa ditambah, tidak bisa diubah atau dihapus dari aplikasi. */
class AuditLog extends Model
{
    use BelongsToInstitution;

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    /** Dimatikan saat memuat data contoh. */
    public static bool $enabled = true;

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Log audit tidak dapat diubah.'));
        static::deleting(fn () => throw new LogicException('Log audit tidak dapat dihapus.'));
    }

    protected function casts(): array
    {
        return ['changes' => 'array', 'created_at' => 'datetime'];
    }

    public static function record(string $event, ?Model $model = null, ?array $changes = null, ?int $institutionId = null): void
    {
        $institutionId ??= $model?->institution_id ?? app(\App\Support\CurrentInstitution::class)->id();

        if (! static::$enabled || ! $institutionId) {
            return;
        }

        static::withoutGlobalScopes()->create([
            'institution_id' => $institutionId,
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'label' => $model && method_exists($model, 'auditLabel') ? mb_substr($model->auditLabel(), 0, 250) : null,
            'changes' => $changes,
            'ip_address' => request()?->ip(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function typeLabel(): string
    {
        return match (class_basename((string) $this->auditable_type)) {
            'Document' => 'Bukti',
            'DocumentVersion' => 'Versi Bukti',
            'LedSection' => 'Bagian LED',
            'WorkItem' => 'Butir Kerja',
            'AccreditationProject' => 'Proyek',
            'Assessment' => 'Penilaian',
            'Task' => 'Tugas',
            'Risk' => 'Risiko',
            'Decision' => 'Keputusan',
            'Finding' => 'Temuan',
            'Capa' => 'CAPA',
            'Reconciliation' => 'Rekonsiliasi',
            'MetricValue' => 'Nilai Metrik',
            'Membership' => 'Akses Pengguna',
            '' => 'Sistem',
            default => class_basename((string) $this->auditable_type),
        };
    }
}
