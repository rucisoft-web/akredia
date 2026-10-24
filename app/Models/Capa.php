<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** Tindakan korektif/preventif; efektivitas diverifikasi pihak yang berbeda dari pelaksana. */
class Capa extends Model
{
    use Auditable, BelongsToInstitution;

    protected $guarded = ['id'];

    protected array $institutionParents = ['finding_id' => Finding::class];

    protected array $tracked = ['completed_at', 'verified_at', 'is_effective'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'completed_at' => 'datetime', 'verified_at' => 'datetime', 'is_effective' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Capa $c) {
            if ($c->verifier_id && $c->owner_id && (int) $c->verifier_id === (int) $c->owner_id) {
                throw new LogicException('Verifikator efektivitas tidak boleh sama dengan pelaksana CAPA.');
            }
        });
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    public function isOnTime(): bool
    {
        return $this->completed_at && (! $this->due_date || $this->completed_at->lte($this->due_date->endOfDay()));
    }

    public function auditLabel(): string
    {
        return 'CAPA '.$this->finding?->number;
    }
}
