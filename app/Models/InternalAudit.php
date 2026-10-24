<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Audit Mutu Internal per unit. */
class InternalAudit extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'audit_date' => 'date',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }

    public const STATES = ['rencana' => 'Rencana', 'pelaksanaan' => 'Pelaksanaan', 'pelaporan' => 'Pelaporan', 'selesai' => 'Selesai'];

    protected static function booted(): void
    {
        static::creating(fn (InternalAudit $a) => $a->number ??= \App\Support\Sequence::next('audit', $a->institution_id ?? app(\App\Support\CurrentInstitution::class)->id(), $a->cycle_year));
    }
}
