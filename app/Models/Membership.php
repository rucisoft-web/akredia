<?php

namespace App\Models;

use App\Enums\Role;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Keanggotaan pengguna di satu institusi: peran (apa) dan cakupan unit (di mana). */
class Membership extends Model
{
    use Auditable, BelongsToInstitution;

    protected $attributes = ['is_active' => true];

    protected $guarded = ['id'];

    protected array $tracked = ['role', 'is_active', 'unit_id'];

    protected function casts(): array
    {
        return ['role' => Role::class, 'is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function auditLabel(): string
    {
        return 'Akses '.$this->user?->name;
    }
}
