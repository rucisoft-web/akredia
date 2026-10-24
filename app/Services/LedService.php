<?php

namespace App\Services;

use App\Enums\LedState;
use App\Enums\WorkItemState;
use App\Models\Document;
use App\Models\LedCitation;
use App\Models\LedRevision;
use App\Models\LedSection;
use App\Models\User;
use App\Models\WorkItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use LogicException;

/**
 * Penyusunan LED (M08). Narasi berupa teks dengan penanda:
 *   [B:BKT/2026/001]  sitasi bukti dari Pustaka Bukti
 *   {{DTJA}}          angka hidup dari Data Kinerja (berubah bila data berubah sebelum freeze)
 */
class LedService
{
    public const CITATION = '/\[B:([A-Z0-9\/\-]+)\]/';

    public const METRIC = '/\{\{([A-Z_0-9]+)\}\}/';

    public const STOPWORDS = ['yang', 'dan', 'atau', 'dengan', 'untuk', 'dalam', 'pada', 'dari', 'oleh', 'sesuai', 'secara', 'setiap', 'serta', 'minimal', 'paling', 'banyak', 'tersedia', 'terdokumentasi', 'ditetapkan', 'dilaksanakan'];

    public const TEMPLATE = <<<'TXT'
Penetapan
Tuliskan standar, kebijakan, atau target yang ditetapkan untuk sub-indikator ini beserta dokumen penetapannya [B:...].

Pelaksanaan
Uraikan bagaimana standar dilaksanakan dalam tiga tahun terakhir (TS-2 s.d. TS) beserta bukti pelaksanaan.

Evaluasi
Jelaskan cara dan hasil evaluasi (AMI, survei, monev). Sisipkan angka dari Data Kinerja, misalnya {{DTJA}}.

Pengendalian
Uraikan tindakan koreksi atas hasil evaluasi (RTM, CAPA).

Peningkatan
Tunjukkan peningkatan standar atau capaian sebagai hasil siklus PPEPP.

Akar masalah, faktor pendukung, dan faktor penghambat
Analisis akar masalah dari kesenjangan yang masih ada, faktor pendukung, dan faktor penghambat.

Tindak lanjut
Rencana tindak lanjut beserta penanggung jawab dan tenggat.
TXT;

    public function __construct(protected EvidenceService $evidence) {}

    /** Simpan narasi sebagai revisi baru; hitung kata, cakupan kata kunci, dan sitasi. */
    public function save(LedSection $section, ?string $body, ?User $user, ?string $note = null): LedSection
    {
        if ($section->workItem->project->isFrozen() && in_array($section->state, [LedState::Ratified], true)) {
            throw new LogicException('Narasi yang sudah disahkan tidak dapat diubah.');
        }

        $body = (string) $body;

        if ($body === (string) $section->body) {
            return $section;
        }

        [$coverage, $missing] = $this->coverage($section->workItem, $body);
        $section->fill([
            'body' => $body,
            'word_count' => LedSection::countWords($this->plain($body, false)),
            'keyword_coverage' => $coverage,
            'missing_keywords' => $missing,
            'updated_by' => $user?->id,
        ]);

        if (in_array($section->state, [LedState::ReviewerApproved, LedState::Ratified], true)) {
            // Perubahan setelah disetujui membuka reviu ulang.
            $section->state = LedState::Revision;
        }

        $section->save();

        LedRevision::create([
            'institution_id' => $section->institution_id,
            'led_section_id' => $section->id,
            'revision' => (int) $section->revisions()->max('revision') + 1,
            'body' => $body,
            'word_count' => $section->word_count,
            'state' => $section->state->value,
            'note' => $note,
            'user_id' => $user?->id,
        ]);

        $this->syncCitations($section);
        $this->evidence->registerLedLinks($section);

        if ($section->workItem->state === WorkItemState::NotAssessed || $section->workItem->state === WorkItemState::Evidence || $section->workItem->state === WorkItemState::GapAnalysis) {
            $section->workItem->update(['state' => WorkItemState::Writing]);
        }

        return $section;
    }

    /** @return array{0: float, 1: array<int, string>} persentase kata kunci tercakup dan daftar yang belum disebut. */
    public function coverage(WorkItem $item, string $body): array
    {
        $keywords = $item->subIndicator->descriptorFor($item->project->track)?->keywords ?? collect();
        $text = ' '.Str::lower($this->plain($body, false)).' ';
        $missing = [];

        foreach ($keywords as $kw) {
            $words = collect(preg_split('/[^\p{L}\p{N}]+/u', Str::lower($kw->text)))
                ->filter(fn ($w) => mb_strlen($w) >= 4 && ! in_array($w, self::STOPWORDS, true))->unique();

            if ($words->isEmpty()) {
                continue;
            }

            $hits = $words->filter(fn ($w) => str_contains($text, mb_substr($w, 0, max(4, mb_strlen($w) - 2))))->count();

            if ($hits / $words->count() < 0.5) {
                $missing[] = $kw->text;
            }
        }

        $total = $keywords->count();

        return [$total ? round(($total - count($missing)) / $total * 100, 1) : 100, $missing];
    }

    public function syncCitations(LedSection $section): void
    {
        preg_match_all(self::CITATION, (string) $section->body, $docs);
        preg_match_all(self::METRIC, (string) $section->body, $metrics);
        LedCitation::withoutGlobalScopes()->where('led_section_id', $section->id)->delete();

        $found = Document::withoutGlobalScopes()->where('institution_id', $section->institution_id)->whereIn('code', array_unique($docs[1]))->with('latestVersion')->get();

        foreach ($found as $doc) {
            LedCitation::create(['institution_id' => $section->institution_id, 'led_section_id' => $section->id, 'document_id' => $doc->id, 'document_version_id' => $doc->latestVersion?->id]);
        }

        foreach (array_unique($metrics[1]) as $code) {
            LedCitation::create(['institution_id' => $section->institution_id, 'led_section_id' => $section->id, 'metric_code' => $code]);
        }
    }

    /** Sitasi rusak: kode bukti tidak ditemukan, kedaluwarsa, atau tautannya gagal. @return array<int, string> */
    public function brokenCitations(LedSection $section): array
    {
        preg_match_all(self::CITATION, (string) $section->body, $m);
        $codes = array_unique($m[1]);
        $docs = Document::withoutGlobalScopes()->where('institution_id', $section->institution_id)->whereIn('code', $codes)->with('linkCheck')->get()->keyBy('code');
        $broken = [];

        foreach ($codes as $code) {
            $doc = $docs->get($code);

            if (! $doc) {
                $broken[] = "{$code} (tidak ditemukan)";
            } elseif ($doc->valid_until && $doc->valid_until->isPast()) {
                $broken[] = "{$code} (kedaluwarsa)";
            } elseif ($doc->linkCheck && ! $doc->linkCheck->is_ok) {
                $broken[] = "{$code} (tautan rusak)";
            }
        }

        return $broken;
    }

    public function submitForReview(LedSection $section): void
    {
        if (! in_array($section->state, [LedState::Draft, LedState::Revision], true)) {
            throw new LogicException('Hanya narasi Draf atau Revisi yang dapat diajukan untuk reviu.');
        }

        if ($section->word_count === 0) {
            throw new LogicException('Narasi masih kosong.');
        }

        $section->update(['state' => LedState::Review]);
        $section->workItem->update(['state' => WorkItemState::Review]);
    }

    public function requestRevision(LedSection $section, User $reviewer, string $note): void
    {
        if ($section->state !== LedState::Review) {
            throw new LogicException('Narasi tidak sedang direviu.');
        }

        $this->assertNotAuthor($section, $reviewer);
        $section->update(['state' => LedState::Revision, 'review_note' => $note]);
    }

    /** Disetujui reviewer: bukan penulis, tidak melewati batas kata, dan tanpa sitasi rusak. */
    public function approve(LedSection $section, User $reviewer): void
    {
        if ($section->state !== LedState::Review) {
            throw new LogicException('Hanya narasi yang sedang direviu yang dapat disetujui.');
        }

        $this->assertNotAuthor($section, $reviewer);
        $limit = $section->workItem->project->word_limit;

        if ($section->word_count > $limit) {
            throw new LogicException("Narasi {$section->word_count} kata melewati batas {$limit} kata.");
        }

        if ($broken = $this->brokenCitations($section)) {
            throw new LogicException('Ada sitasi rusak: '.implode(', ', $broken).'.');
        }

        $section->update(['state' => LedState::ReviewerApproved, 'approved_by' => $reviewer->id, 'approved_at' => now()]);
    }

    public function ratify(LedSection $section, User $leader): void
    {
        if ($section->state !== LedState::ReviewerApproved) {
            throw new LogicException('Pengesahan pimpinan hanya untuk narasi yang sudah disetujui reviewer.');
        }

        $section->update(['state' => LedState::Ratified, 'ratified_by' => $leader->id, 'ratified_at' => now()]);
    }

    protected function assertNotAuthor(LedSection $section, User $reviewer): void
    {
        $item = $section->workItem;

        if ((int) $item->pic_id === (int) $reviewer->id || (int) $section->updated_by === (int) $reviewer->id) {
            throw new LogicException('Reviewer tidak boleh mereviu narasinya sendiri.');
        }
    }

    /** @return array<string, float|null> nilai metrik proyek untuk angka hidup. */
    public function metricValues(WorkItem $item): array
    {
        return collect(app(MetricEngine::class)->current($item->project))->map(fn ($v) => $v->value)->all();
    }

    /** Ubah penanda menjadi teks bersih untuk SAPTO (dengan URL) atau untuk hitung kata. */
    public function plain(string $body, bool $withLinks = true, ?WorkItem $item = null): string
    {
        $values = $item ? $this->metricValues($item) : [];
        $docs = $withLinks && $item
            ? Document::withoutGlobalScopes()->where('institution_id', $item->institution_id)->get()->keyBy('code')
            : collect();

        $text = preg_replace_callback(self::CITATION, function ($m) use ($withLinks, $docs) {
            if (! $withLinks) {
                return '';
            }

            $doc = $docs->get($m[1]);

            return $doc ? '('.$doc->title.($doc->publicUrl() ? ', '.$doc->publicUrl() : '').')' : '';
        }, $body);

        return preg_replace_callback(self::METRIC, fn ($m) => isset($values[$m[1]]) ? number_format((float) $values[$m[1]], 2, ',', '.') : ($withLinks ? '[data '.$m[1].']' : $m[1]), $text);
    }

    /** HTML tampilan: sitasi menjadi tautan, angka hidup menjadi nilai bertanda. */
    public function render(LedSection $section, ?Collection $docs = null): string
    {
        $item = $section->workItem;
        $values = $this->metricValues($item);
        $docs ??= Document::withoutGlobalScopes()->where('institution_id', $section->institution_id)->with('latestVersion')->get()->keyBy('code');
        $html = e((string) $section->body);

        $html = preg_replace_callback(self::CITATION, function ($m) use ($docs) {
            $doc = $docs->get($m[1]);

            if (! $doc) {
                return '<span class="ak-cite broken" title="Bukti tidak ditemukan">['.e($m[1]).' ?]</span>';
            }

            $url = $doc->publicUrl() ?? ($doc->latestVersion ? URL::temporarySignedRoute('dokumen.unduh', now()->addHour(), ['version' => $doc->latestVersion->id]) : null);
            $class = $doc->isUsable() ? 'ak-cite' : 'ak-cite warn';

            return '<a class="'.$class.'" target="_blank" href="'.e($url ?? '#').'" title="'.e($doc->title).'">['.e($doc->code).']</a>';
        }, $html);

        $html = preg_replace_callback(self::METRIC, fn ($m) => '<span class="ak-metric" title="Angka hidup '.e($m[1]).'">'.(isset($values[$m[1]]) ? number_format((float) $values[$m[1]], 2, ',', '.') : e($m[1]).'?').'</span>', $html);
        $html = preg_replace('~(?<!["=])(https?://[^\s<]+)~', '<a href="$1" target="_blank">$1</a>', $html);

        return nl2br($html);
    }
}
