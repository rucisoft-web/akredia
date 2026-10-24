<?php

namespace App\Services;

use App\Enums\AcademicRank;
use App\Enums\Track;
use App\Models\AccreditationProject;
use App\Models\Achievement;
use App\Models\EmployerSatisfaction;
use App\Models\FinancialAudit;
use App\Models\Institution;
use App\Models\Lecturer;
use App\Models\Metric;
use App\Models\MetricValue;
use App\Models\ProgramYearStat;
use App\Models\StudentCohort;
use App\Models\StudyProgram;
use App\Models\Survey;
use App\Models\TracerStudy;
use App\Support\Formula;
use Illuminate\Support\Collection;
use LogicException;

/**
 * Menghitung rasio IAPT 4.1 dari dataset kinerja (M06).
 *
 * Variabel dihitung dari tabel sumber; rumus dan ambang diambil dari tabel metrics
 * sehingga dapat diubah tanpa rilis kode.
 */
class MetricEngine
{
    public const MAIN_LEVELS = ['S1', 'D4', 'D3'];

    /** Metrik yang juga dihitung per prodi. */
    public const PROGRAM_METRICS = ['RMDT', 'PDTT', 'PDOK', 'DTJA', 'PKMTK', 'PK2MTK'];

    public const VARIABLES = [
        'mhs_aktif_utama' => 'Mahasiswa aktif program utama (S1, D4, D3) saat TS',
        'mhs_aktif' => 'Mahasiswa aktif seluruh jenjang saat TS',
        'dosen_tetap' => 'Dosen tetap aktif',
        'dosen_tidak_tetap' => 'Dosen tidak tetap aktif',
        'dosen_doktor' => 'Dosen tetap bergelar doktor',
        'dosen_gb' => 'Dosen tetap Guru Besar',
        'dosen_lk' => 'Dosen tetap Lektor Kepala',
        'dosen_l' => 'Dosen tetap Lektor',
        'dosen_aa' => 'Dosen tetap Asisten Ahli',
        'min_dosen_prodi' => 'Dosen tetap pada prodi dengan dosen tetap paling sedikit',
        'lulusan_ts' => 'Lulusan program utama TS',
        'lulusan_ts1' => 'Lulusan program utama TS-1',
        'lulusan_ts2' => 'Lulusan program utama TS-2',
        'mhs_baru_ts' => 'Mahasiswa baru program utama TS',
        'mhs_baru_ts1' => 'Mahasiswa baru program utama TS-1',
        'mhs_baru_ts2' => 'Mahasiswa baru program utama TS-2',
        'kohort_tepat_masuk' => 'Mahasiswa masuk kohort tepat waktu (S1/D4 TS-3, D3 TS-2)',
        'kohort_tepat_lulus' => 'Lulus tepat masa tempuh dari kohort tersebut',
        'kohort_dua_masuk' => 'Mahasiswa masuk kohort dua kali masa tempuh (S1/D4 TS-6, D3 TS-4)',
        'kohort_dua_lulus' => 'Lulus dalam dua kali masa tempuh dari kohort tersebut',
        'mhs_berprestasi' => 'Mahasiswa juara 1-3 tingkat provinsi/nasional/internasional TS',
        'lulusan_terserap' => 'Lulusan terserap ≤ 1 tahun (tracer TS-1)',
        'lulusan_tracer' => 'Lulusan pada tracer TS-1',
        'prodi_aktif' => 'Prodi aktif',
        'prodi_terakreditasi' => 'Prodi dengan akreditasi berlaku',
        'prodi_unggul' => 'Prodi Unggul, A, atau internasional',
        'mhs_luar_prodi' => 'Mahasiswa aktif ≥ 10 sks di luar prodi',
        'pengguna_sb_b' => 'Jawaban SB + B survei pengguna lulusan',
        'pengguna_total' => 'Seluruh jawaban survei pengguna lulusan',
        'survei_sb_b' => 'Jawaban SB + B survei pemangku kepentingan TS',
        'survei_total' => 'Seluruh jawaban survei pemangku kepentingan TS',
        'audit_tahun' => 'Tahun TS-2 s.d. TS yang diaudit KAP',
    ];

    /** @return array<string, float> */
    public function variables(int $institutionId, int $ts, ?StudyProgram $program = null): array
    {
        $programs = StudyProgram::withoutGlobalScopes()->where('institution_id', $institutionId)->where('is_active', true)
            ->when($program, fn ($q) => $q->whereKey($program->id))
            ->with('currentAccreditation')->get();
        $programIds = $programs->pluck('id');
        $mainIds = $programs->whereIn('level', self::MAIN_LEVELS)->pluck('id');

        $stats = ProgramYearStat::withoutGlobalScopes()->whereIn('study_program_id', $programIds)
            ->whereBetween('year', [$ts - 2, $ts])->get();
        $sum = fn (string $col, int $year, ?Collection $ids = null) => (float) $stats
            ->where('year', $year)->whereIn('study_program_id', $ids ?? $mainIds)->sum($col);

        $lecturers = Lecturer::withoutGlobalScopes()->where('institution_id', $institutionId)->where('is_active', true)
            ->when($program, fn ($q) => $q->where('study_program_id', $program->id))->get();
        $permanent = $lecturers->where('employment', 'tetap');
        $rank = fn (AcademicRank $r) => (float) $permanent->filter(fn ($l) => $l->academic_rank === $r)->count();

        $cohorts = StudentCohort::withoutGlobalScopes()->whereIn('study_program_id', $programIds)->get();
        $levelOf = $programs->pluck('level', 'id');
        $cohortSum = function (array $offsets, string $col) use ($cohorts, $levelOf, $ts) {
            return (float) $cohorts->filter(function ($c) use ($offsets, $levelOf, $ts) {
                $level = $levelOf[$c->study_program_id] ?? null;

                return isset($offsets[$level]) && $c->intake_year === $ts - $offsets[$level];
            })->sum($col);
        };
        $onTime = ['S1' => 3, 'D4' => 3, 'D3' => 2];
        $double = ['S1' => 6, 'D4' => 6, 'D3' => 4];

        $vars = [
            'mhs_aktif_utama' => $sum('active_students', $ts),
            'mhs_aktif' => $sum('active_students', $ts, $programIds),
            'dosen_tetap' => (float) $permanent->count(),
            'dosen_tidak_tetap' => (float) $lecturers->where('employment', 'tidak_tetap')->count(),
            'dosen_doktor' => (float) $permanent->where('highest_degree', 'S3')->count(),
            'dosen_gb' => $rank(AcademicRank::Gb),
            'dosen_lk' => $rank(AcademicRank::Lk),
            'dosen_l' => $rank(AcademicRank::L),
            'dosen_aa' => $rank(AcademicRank::Aa),
            'min_dosen_prodi' => (float) ($programs->map(fn ($p) => $permanent->where('study_program_id', $p->id)->count())->min() ?? 0),
            'lulusan_ts' => $sum('graduates', $ts),
            'lulusan_ts1' => $sum('graduates', $ts - 1),
            'lulusan_ts2' => $sum('graduates', $ts - 2),
            'mhs_baru_ts' => $sum('new_students', $ts),
            'mhs_baru_ts1' => $sum('new_students', $ts - 1),
            'mhs_baru_ts2' => $sum('new_students', $ts - 2),
            'kohort_tepat_masuk' => $cohortSum($onTime, 'intake_count'),
            'kohort_tepat_lulus' => $cohortSum($onTime, 'graduated_on_time'),
            'kohort_dua_masuk' => $cohortSum($double, 'intake_count'),
            'kohort_dua_lulus' => $cohortSum($double, 'graduated_within_double'),
            'mhs_luar_prodi' => $sum('students_outside_10sks', $ts, $programIds),
            'prodi_aktif' => (float) $programs->count(),
            'prodi_terakreditasi' => (float) $programs->filter(fn ($p) => $p->currentAccreditation?->isAccredited())->count(),
            'prodi_unggul' => (float) $programs->filter(fn ($p) => $p->currentAccreditation?->isExcellent())->count(),
        ];

        if ($program === null) {
            $vars['mhs_berprestasi'] = (float) Achievement::withoutGlobalScopes()->where('institution_id', $institutionId)
                ->where('year', $ts)->whereIn('rank', ['1', '2', '3'])->whereIn('level', ['provinsi', 'nasional', 'internasional'])->sum('student_count');

            $tracer = TracerStudy::withoutGlobalScopes()->where('institution_id', $institutionId)
                ->where('graduation_year', '<=', $ts - 1)->orderByDesc('graduation_year')->first();
            $vars['lulusan_terserap'] = (float) ($tracer?->within_one_year ?? 0);
            $vars['lulusan_tracer'] = (float) ($tracer?->graduates ?? 0);

            $employer = EmployerSatisfaction::withoutGlobalScopes()->where('institution_id', $institutionId)->where('year', '<=', $ts)->get();
            $employer = $employer->where('year', $employer->max('year'));
            $vars['pengguna_sb_b'] = (float) ($employer->sum('sb') + $employer->sum('b'));
            $vars['pengguna_total'] = (float) $employer->sum(fn ($e) => $e->total());

            $surveys = Survey::withoutGlobalScopes()->where('institution_id', $institutionId)->where('year', $ts)->get();
            $vars['survei_sb_b'] = (float) ($surveys->sum('sb') + $surveys->sum('b'));
            $vars['survei_total'] = (float) $surveys->sum(fn ($s) => $s->totalAnswers());

            $vars['audit_tahun'] = (float) FinancialAudit::withoutGlobalScopes()->where('institution_id', $institutionId)
                ->whereBetween('year', [$ts - 2, $ts])->distinct()->count('year');
        }

        return $vars;
    }

    /** @return Collection<int, Metric> */
    public function metrics(?int $variantId = null): Collection
    {
        return Metric::query()->when($variantId, fn ($q) => $q->where(fn ($w) => $w->where('instrument_variant_id', $variantId)->orWhereNull('instrument_variant_id')))
            ->orderBy('sort')->get()->unique('code');
    }

    public function evaluate(Metric $metric, array $vars): ?float
    {
        foreach (Formula::variables($metric->formula) as $name) {
            if (! array_key_exists($name, $vars)) {
                return null;
            }
        }

        return Formula::evaluate($metric->formula, $vars);
    }

    /**
     * Status terhadap ambang jalur: true memenuhi, false tidak, null tidak ada ambang.
     */
    public function meets(Metric $metric, ?float $value, Track $track): ?bool
    {
        [$min, $max] = $track === Track::Excellent
            ? [$metric->excellent_min, $metric->excellent_max]
            : [$metric->accredited_min, $metric->accredited_max];

        if ($value === null || ($min === null && $max === null)) {
            return null;
        }

        return ($min === null || $value >= $min) && ($max === null || $value <= $max);
    }

    public static function thresholdText(Metric $metric, Track $track): string
    {
        [$min, $max] = $track === Track::Excellent ? [$metric->excellent_min, $metric->excellent_max] : [$metric->accredited_min, $metric->accredited_max];
        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');

        return match (true) {
            $min !== null && $max !== null => $fmt($min).' – '.$fmt($max),
            $min !== null => '≥ '.$fmt($min),
            $max !== null => '≤ '.$fmt($max),
            default => '-',
        };
    }

    /**
     * Hitung ulang dan simpan nilai metrik proyek (institusi dan per prodi) untuk tahun TS.
     *
     * @return Collection<int, MetricValue>
     */
    public function compute(AccreditationProject $project, ?int $userId = null): Collection
    {
        if ($project->isFrozen()) {
            throw new LogicException('Data TS sudah dikunci. Buka kunci data terlebih dahulu untuk menghitung ulang.');
        }

        $ts = $project->tsYear();
        $metrics = $this->metrics($project->instrument_variant_id);
        $vars = $this->variables($project->institution_id, $ts);
        $saved = collect();

        foreach ($metrics as $metric) {
            $saved->push($this->store($project, $metric, null, $ts, $this->evaluate($metric, $vars), $vars, $userId));
        }

        $programs = StudyProgram::withoutGlobalScopes()->where('institution_id', $project->institution_id)->where('is_active', true)->get();

        foreach ($programs as $program) {
            $pvars = $this->variables($project->institution_id, $ts, $program);

            foreach ($metrics->whereIn('code', self::PROGRAM_METRICS) as $metric) {
                $this->store($project, $metric, $program->id, $ts, $this->evaluate($metric, $pvars), $pvars, $userId);
            }
        }

        return $saved;
    }

    protected function store(AccreditationProject $project, Metric $metric, ?int $programId, int $ts, ?float $value, array $vars, ?int $userId): MetricValue
    {
        $inputs = array_intersect_key($vars, array_flip(Formula::variables($metric->formula)));
        $existing = MetricValue::withoutGlobalScopes()->firstOrNew([
            'institution_id' => $project->institution_id,
            'accreditation_project_id' => $project->id,
            'metric_code' => $metric->code,
            'study_program_id' => $programId,
            'year' => $ts,
            'source' => 'internal',
        ]);

        $changed = ! $existing->exists || round((float) $existing->value, 4) !== round((float) $value, 4);
        $existing->fill(['value' => $value, 'inputs' => $inputs, 'computed_by' => $userId]);

        if ($changed) {
            // Nilai berubah: verifikasi lama gugur.
            $existing->status = 'draft';
            $existing->verified_by = null;
            $existing->verified_at = null;
        }

        $existing->save();

        return $existing;
    }

    /** Nilai institusi terkini per kode metrik untuk proyek. @return array<string, MetricValue> */
    public function current(AccreditationProject $project): array
    {
        return MetricValue::withoutGlobalScopes()
            ->where('accreditation_project_id', $project->id)->whereNull('study_program_id')
            ->where('source', 'internal')->where('year', $project->tsYear())
            ->get()->keyBy('metric_code')->all();
    }
}
