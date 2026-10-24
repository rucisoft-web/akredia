<?php

namespace App\Models\Concerns;

use App\Models\Institution;
use App\Support\CurrentInstitution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Isolasi data per institusi.
 *
 * - Global scope membatasi query ke institusi aktif.
 * - institution_id diisi otomatis saat membuat baris.
 * - Baris anak ditolak bila merujuk induk milik institusi lain ($institutionParents: kolom => kelas).
 */
trait BelongsToInstitution
{
    public static function bootBelongsToInstitution(): void
    {
        static::addGlobalScope('institution', function (Builder $builder) {
            $id = app(CurrentInstitution::class)->id();

            if ($id !== null) {
                $builder->where($builder->getModel()->qualifyColumn('institution_id'), $id);
            }
        });

        static::saving(function (Model $model) {
            if (! $model->institution_id) {
                $model->institution_id = app(CurrentInstitution::class)->id()
                    ?? throw new LogicException('institution_id wajib diisi untuk '.class_basename($model).'.');
            }

            foreach ($model->institutionParents ?? [] as $column => $class) {
                $parentId = $model->getAttribute($column);

                if ($parentId === null) {
                    continue;
                }

                $parent = $class::withoutGlobalScopes()->whereKey($parentId)->value('institution_id');

                if ((int) $parent !== (int) $model->institution_id) {
                    throw new LogicException(class_basename($model)." tidak boleh merujuk {$column} milik institusi lain.");
                }
            }
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
