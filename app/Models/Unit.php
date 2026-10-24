<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Unit kerja berjenjang (rektorat, fakultas, lembaga, biro). */
class Unit extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['parent_id' => Unit::class];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }
}
