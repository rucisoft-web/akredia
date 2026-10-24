<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Kohort mahasiswa per angkatan untuk kelulusan tepat waktu. */
class StudentCohort extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['study_program_id' => StudyProgram::class];

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }
}
