<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Program studi. */
class StudyProgram extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['unit_id' => Unit::class];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function accreditations(): HasMany
    {
        return $this->hasMany(ProgramAccreditation::class);
    }

    public function lecturers(): HasMany
    {
        return $this->hasMany(Lecturer::class);
    }

    public function yearStats(): HasMany
    {
        return $this->hasMany(ProgramYearStat::class);
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(StudentCohort::class);
    }

    public function currentAccreditation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProgramAccreditation::class)->where('is_current', true)->latestOfMany('valid_until');
    }

    public function label(): string
    {
        return $this->level.' '.$this->name;
    }
}
