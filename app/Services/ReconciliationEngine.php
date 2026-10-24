<?php

namespace App\Services;

use App\Enums\ReconciliationState;
use App\Models\AccreditationProject;
use App\Models\Metric;
use App\Models\MetricValue;
use App\Models\Reconciliation;
use App\Models\StudyProgram;
use App\Support\Spreadsheet;

/**
 * Rekonsiliasi data internal dengan ekspor PD Dikti / Neo Feeder (M07).
 *
 * Berkas impor: kolom `kode` (kode metrik seperti DTJA atau nama variabel seperti dosen_tetap),
 * `prodi` (kode PD Dikti prodi, kosong untuk institusi), dan `nilai_pddikti`.
 */
class ReconciliationEngine
{
    public function __construct(protected MetricEngine $engine) {}

    /** @return array{created:int, updated:int, errors: array<int,string>} */
    public function import(AccreditationProject $project, string $path, ?string $extension = null): array
    {
        $rows = Spreadsheet::read($path, $extension);
        $result = ['created' => 0, 'updated' => 0, 'errors' => []];
        $programs = StudyProgram::withoutGlobalScopes()->where('institution_id', $project->institution_id)->get()->keyBy('code_pddikti');

        foreach ($rows as $i => $row) {
            $code = trim((string) ($row['kode'] ?? $row['metrik'] ?? ''));
            $value = $row['nilai_pddikti'] ?? $row['nilai'] ?? null;
            $programCode = trim((string) ($row['prodi'] ?? ''));

            if ($code === '' || ! is_numeric($value)) {
                $result['errors'][$i + 2] = 'kode atau nilai_pddikti kosong/bukan angka';

                continue;
            }

            $program = $programCode !== '' ? $programs->get($programCode) : null;

            if ($programCode !== '' && ! $program) {
                $result['errors'][$i + 2] = "prodi {$programCode} tidak dikenal";

                continue;
            }

            $internal = $this->internalValue($project, $code, $program);

            if ($internal === null) {
                $result['errors'][$i + 2] = "kode {$code} tidak dikenal";

                continue;
            }

            $metric = Metric::where('code', strtoupper($code))->first();
            $recon = Reconciliation::withoutGlobalScopes()->firstOrNew([
                'institution_id' => $project->institution_id,
                'accreditation_project_id' => $project->id,
                'metric_code' => $metric ? $metric->code : $code,
                'scope_label' => $program ? $program->label() : 'Institusi',
            ]);
            $isNew = ! $recon->exists;
            $recon->fill(['internal_value' => $internal, 'pddikti_value' => (float) $value, 'tolerance_pct' => $metric?->tolerance_pct ?? 2]);

            if ($recon->state === ReconciliationState::Verified && $recon->isDirty(['internal_value', 'pddikti_value'])) {
                $recon->state = ReconciliationState::Open;
            }

            $recon->state ??= ReconciliationState::Open;
            $recon->save();
            $isNew ? $result['created']++ : $result['updated']++;
        }

        return $result;
    }

    public function internalValue(AccreditationProject $project, string $code, ?StudyProgram $program = null): ?float
    {
        if (array_key_exists($code, MetricEngine::VARIABLES)) {
            return $this->engine->variables($project->institution_id, $project->tsYear(), $program)[$code] ?? null;
        }

        $value = MetricValue::withoutGlobalScopes()->where('accreditation_project_id', $project->id)
            ->where('metric_code', strtoupper($code))->where('study_program_id', $program?->id)->where('source', 'internal')
            ->latest('id')->value('value');

        return $value === null ? null : (float) $value;
    }
}
