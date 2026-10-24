<?php

namespace App\Services;

use App\Models\Criterion;
use App\Models\Descriptor;
use App\Models\Indicator;
use App\Models\IndicatorGroup;
use App\Models\InstrumentVariant;
use App\Models\InstrumentVersion;
use App\Models\Keyword;
use App\Models\Metric;
use App\Models\SubIndicator;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

/**
 * Pustaka Instrumen sebagai data (M02): memuat data awal, mengimpor dan mengekspor templat XLSX.
 * Satu baris XLSX = satu sub-indikator dalam satu varian.
 */
class InstrumentImporter
{
    public const COLUMNS = [
        'versi', 'varian', 'kriteria_no', 'kriteria', 'kelompok_kode', 'kelompok', 'indikator_no', 'sasaran', 'indikator',
        'sub_kode', 'sub_pernyataan', 'sumber_data', 'bobot', 'sp_terakreditasi', 'sp_unggul', 'metrik', 'tabel_lkpt',
        'deskriptor_terakreditasi', 'deskriptor_unggul', 'pic_usulan', 'bukti_utama', 'kriteria_iapt3',
    ];

    public const TARGETS = ['input' => 'Masukan', 'process' => 'Proses', 'output' => 'Luaran', 'impact' => 'Dampak'];

    /** Muat data awal IAPT 4.1 dari database/data/iapt41.php; varian lain disalin dari varian acuan. */
    public function seed(?array $data = null): InstrumentVersion
    {
        $data ??= require database_path('data/iapt41.php');

        return DB::transaction(function () use ($data) {
            $version = InstrumentVersion::updateOrCreate(['code' => $data['version']['code']], $data['version']);
            $variants = [];

            foreach ($data['variants'] as [$code, $name, $type]) {
                $variants[$code] = InstrumentVariant::updateOrCreate(
                    ['instrument_version_id' => $version->id, 'code' => $code],
                    ['name' => $name, 'institution_type' => $type, 'source_note' => $code === $data['seed_variant'] ? 'Data awal Lampiran '.$code : 'Disalin dari Lampiran '.$data['seed_variant'].'; cocokkan dengan Lampiran '.$code.' resmi'],
                );
            }

            $groups = [];

            foreach ($data['criteria'] as $number => [$name, $description, $groupRows]) {
                $criterion = Criterion::updateOrCreate(['instrument_version_id' => $version->id, 'number' => $number], ['name' => $name, 'description' => $description]);

                foreach ($groupRows as $i => [$gcode, $gname]) {
                    $groups[$gcode] = IndicatorGroup::updateOrCreate(['criterion_id' => $criterion->id, 'code' => $gcode], ['name' => $gname, 'sort' => $i]);
                }
            }

            foreach ($data['indicators'] as $number => [$gcode, $target, $statement, $evidence, $pic, $legacy, $subs]) {
                $group = $groups[$gcode];
                $indicator = Indicator::updateOrCreate(['instrument_version_id' => $version->id, 'number' => $number], [
                    'criterion_id' => $group->criterion_id, 'indicator_group_id' => $group->id, 'quality_target' => $target,
                    'statement' => $statement, 'evidence_hint' => $evidence, 'pic_suggestion' => $pic, 'legacy_criteria' => $legacy,
                ]);

                foreach ($variants as $variant) {
                    foreach ($subs as $sort => $sub) {
                        $this->upsertSub($indicator, $variant, $sub, $sort);
                    }
                }
            }

            foreach ($data['metrics'] as $sort => $m) {
                foreach ([null] as $variantId) {
                    Metric::updateOrCreate(['code' => $m[0], 'instrument_variant_id' => $variantId], [
                        'name' => $m[1], 'formula' => $m[2], 'formula_text' => $m[3], 'unit' => $m[4], 'indicator_ref' => $m[5],
                        'accredited_min' => $m[6], 'accredited_max' => $m[7], 'excellent_min' => $m[8], 'excellent_max' => $m[9],
                        'tolerance_pct' => $m[10], 'is_prerequisite' => in_array($m[0], ['DTJA', 'PRODI_AKR', 'MIN_DT', 'PDTT'], true), 'sort' => $sort,
                    ]);
                }
            }

            return $version;
        });
    }

    /** @param array{0:string,1:string,2:?string,3:bool,4:bool,5:?string,6:?string,7:string,8:string} $sub */
    protected function upsertSub(Indicator $indicator, InstrumentVariant $variant, array $sub, int $sort, float $weight = 1): SubIndicator
    {
        [$code, $statement, $sources, $spAcc, $spExc, $metric, $lkpt, $descAcc, $descExc] = $sub;

        $model = SubIndicator::updateOrCreate(['instrument_variant_id' => $variant->id, 'code' => $code], [
            'indicator_id' => $indicator->id, 'statement' => $statement, 'data_sources' => $sources,
            'is_prerequisite_accredited' => $spAcc, 'is_prerequisite_excellent' => $spExc,
            'metric_code' => $metric ?: null, 'lkpt_table' => $lkpt ?: null, 'weight' => $weight,
            'sort' => $indicator->number * 10 + max(0, ord(strtoupper(substr($code, -1))) - 64),
            'new_pt_rule' => in_array($code, ['1B'], true) ? 'Terakreditasi pertama: implementasi SPMI minimal 2 tahun' : null,
        ]);

        $this->syncDescriptor($model, 'accredited', $descAcc);
        $this->syncDescriptor($model, 'excellent', $descExc);

        return $model;
    }

    /** Simpan deskriptor dan urai menjadi kata kunci (FR-ITM-03); kata kunci yang ada dipertahankan bila teksnya sama. */
    public function syncDescriptor(SubIndicator $sub, string $track, ?string $text): void
    {
        if (blank($text)) {
            return;
        }

        $descriptor = Descriptor::updateOrCreate(['sub_indicator_id' => $sub->id, 'track' => $track], ['text' => $text]);
        $parts = self::keywordsFrom($text);
        $existing = $descriptor->keywords()->get()->keyBy('text');

        foreach ($parts as $i => $part) {
            if ($kw = $existing->pull($part)) {
                $kw->update(['sequence' => $i + 1]);
            } else {
                Keyword::create(['descriptor_id' => $descriptor->id, 'text' => $part, 'sequence' => $i + 1]);
            }
        }

        $existing->each->delete();
    }

    /** @return array<int, string> */
    public static function keywordsFrom(string $text): array
    {
        return collect(preg_split('/;\s*/u', $text))->map(fn ($p) => trim($p, " .\t\n"))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)).mb_substr($p, 1))->values()->all();
    }

    public function export(InstrumentVariant $variant, string $path): void
    {
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Instrumen');
        $writer->addRow(Row::fromValues(self::COLUMNS));

        $subs = SubIndicator::where('instrument_variant_id', $variant->id)
            ->with(['indicator.criterion', 'indicator.group', 'descriptors'])->orderBy('sort')->get();

        foreach ($subs as $s) {
            $i = $s->indicator;
            $writer->addRow(Row::fromValues([
                $variant->version->code, $variant->code, $i->criterion->number, $i->criterion->name, $i->group->code, $i->group->name,
                $i->number, $i->quality_target->value, $i->statement, $s->code, $s->statement, $s->data_sources, $s->weight,
                $s->is_prerequisite_accredited ? 'Ya' : 'Tidak', $s->is_prerequisite_excellent ? 'Ya' : 'Tidak', $s->metric_code, $s->lkpt_table,
                $s->descriptorFor('accredited')?->text, $s->descriptorFor('excellent')?->text, $i->pic_suggestion, $i->evidence_hint, $i->legacy_criteria,
            ]));
        }

        $writer->close();
    }

    /**
     * Impor templat XLSX. Versi dan varian dibuat bila belum ada.
     *
     * @return array{rows:int, errors: array<int, string>}
     */
    public function import(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);
        $header = null;
        $rows = 0;
        $errors = [];
        $records = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $n => $row) {
                $values = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row->toArray());

                if ($header === null) {
                    $header = array_map(fn ($h) => strtolower(trim((string) $h)), $values);
                    $missing = array_diff(['versi', 'varian', 'indikator_no', 'sub_kode', 'deskriptor_terakreditasi'], $header);

                    if ($missing) {
                        throw new RuntimeException('Kolom wajib tidak ada: '.implode(', ', $missing));
                    }

                    continue;
                }

                $r = array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), null));

                if (blank($r['sub_kode'] ?? null)) {
                    continue;
                }

                if (! is_numeric($r['indikator_no'])) {
                    $errors[$n] = 'indikator_no bukan angka';

                    continue;
                }

                $records[] = $r;
            }
            break;
        }

        $reader->close();

        DB::transaction(function () use ($records, &$rows) {
            foreach ($records as $r) {
                $version = InstrumentVersion::firstOrCreate(['code' => $r['versi']], ['name' => 'Instrumen '.$r['versi']]);
                $variant = InstrumentVariant::firstOrCreate(['instrument_version_id' => $version->id, 'code' => $r['varian']], [
                    'name' => 'Varian '.$r['varian'], 'institution_type' => 'pts_akademik',
                ]);
                $criterion = Criterion::firstOrCreate(['instrument_version_id' => $version->id, 'number' => (int) ($r['kriteria_no'] ?? 1)], ['name' => $r['kriteria'] ?? 'Kriteria']);
                $group = IndicatorGroup::firstOrCreate(['criterion_id' => $criterion->id, 'code' => $r['kelompok_kode'] ?: 'K'.$criterion->number], ['name' => $r['kelompok'] ?? $criterion->name]);
                $indicator = Indicator::updateOrCreate(['instrument_version_id' => $version->id, 'number' => (int) $r['indikator_no']], array_filter([
                    'criterion_id' => $criterion->id, 'indicator_group_id' => $group->id,
                    'quality_target' => array_key_exists($r['sasaran'] ?? '', self::TARGETS) ? $r['sasaran'] : (array_search($r['sasaran'] ?? '', self::TARGETS, true) ?: 'input'),
                    'statement' => $r['indikator'] ?? null, 'pic_suggestion' => $r['pic_usulan'] ?? null,
                    'evidence_hint' => $r['bukti_utama'] ?? null, 'legacy_criteria' => $r['kriteria_iapt3'] ?? null,
                ], fn ($v) => $v !== null && $v !== ''));

                $yes = fn ($v) => in_array(strtolower((string) $v), ['ya', 'y', '1', 'true', 'yes'], true);
                $this->upsertSub($indicator, $variant, [
                    (string) $r['sub_kode'], (string) ($r['sub_pernyataan'] ?? $r['sub_kode']), $r['sumber_data'] ?? null,
                    $yes($r['sp_terakreditasi'] ?? null), $yes($r['sp_unggul'] ?? null), $r['metrik'] ?? null, $r['tabel_lkpt'] ?? null,
                    (string) $r['deskriptor_terakreditasi'], (string) ($r['deskriptor_unggul'] ?? $r['deskriptor_terakreditasi']),
                ], 0, (float) ($r['bobot'] ?: 1));
                $rows++;
            }
        });

        return ['rows' => $rows, 'errors' => $errors];
    }
}
