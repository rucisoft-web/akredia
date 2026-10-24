<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Statistik tahunan prodi: pendaftar, mahasiswa baru, aktif, lulusan. */
class ProgramYearStat extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['study_program_id' => StudyProgram::class];

    protected function casts(): array
    {
        return [
            'avg_study_months' => 'float',
            'avg_gpa' => 'float',
        ];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }
}
