<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Dosen (NIDN/NUPTK, status, pendidikan, jabatan akademik). */
class Lecturer extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['study_program_id' => StudyProgram::class];

    protected function casts(): array
    {
        return [
            'academic_rank' => \App\Enums\AcademicRank::class,
            'is_certified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function recognitions(): HasMany
    {
        return $this->hasMany(Recognition::class);
    }
}
