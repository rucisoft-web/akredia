<?php

namespace App\Services;

use Anthropic\Client;
use App\Models\LedSection;
use Throwable;

/**
 * Asisten narasi LED (M16, opsional).
 *
 * Pemeriksaan lokal selalu tersedia: cakupan kata kunci deskriptor, unsur PPEPP, analisis akar masalah,
 * sitasi bukti, angka, dan batas kata. Bila AKREDIA_AI_KEY diisi dan institusi mengizinkan, Claude
 * memberi saran perbaikan. Saran tidak pernah disimpan otomatis; PIC yang memutuskan.
 */
class NarrativeAssistant
{
    public const PPEPP = ['penetapan', 'pelaksanaan', 'evaluasi', 'pengendalian', 'peningkatan'];

    public const ANALYSIS = ['akar masalah', 'faktor pendukung', 'faktor penghambat', 'tindak lanjut'];

    public function __construct(protected LedService $led) {}

    public static function aiAvailable(?\App\Models\Institution $institution): bool
    {
        return filled(config('akredia.ai.key')) && ($institution?->ai_enabled ?? false);
    }

    /** @return array{score:int, checks: array<int, array{label:string, ok:bool, detail:string}>, suggestions: array<int,string>} */
    public function check(LedSection $section, ?string $body = null): array
    {
        $body ??= (string) $section->body;
        $item = $section->workItem;
        $text = mb_strtolower($body);
        $limit = $item->project->word_limit;
        $words = LedSection::countWords($this->led->plain($body, false));
        [$coverage, $missing] = $this->led->coverage($item, $body);
        $ppeppMissing = array_values(array_filter(self::PPEPP, fn ($p) => ! str_contains($text, $p)));
        $analysisMissing = array_values(array_filter(self::ANALYSIS, fn ($p) => ! str_contains($text, $p)));
        preg_match_all(LedService::CITATION, $body, $cites);
        preg_match_all(LedService::METRIC, $body, $metrics);
        $broken = $this->led->brokenCitations(new LedSection(['body' => $body, 'institution_id' => $section->institution_id]));

        $checks = [
            ['label' => 'Panjang narasi', 'ok' => $words > 150 && $words <= $limit, 'detail' => "{$words} dari batas {$limit} kata".($words <= 150 ? '; terlalu singkat untuk menunjukkan pemenuhan.' : '')],
            ['label' => 'Cakupan kata kunci deskriptor', 'ok' => $missing === [], 'detail' => $missing === [] ? 'Semua kata kunci disebut.' : 'Belum disebut: '.implode('; ', $missing)],
            ['label' => 'Alur PPEPP', 'ok' => $ppeppMissing === [], 'detail' => $ppeppMissing === [] ? 'Kelima tahap PPEPP ada.' : 'Tahap belum tampak: '.implode(', ', $ppeppMissing)],
            ['label' => 'Analisis akar masalah dan tindak lanjut', 'ok' => count($analysisMissing) <= 1, 'detail' => $analysisMissing === [] ? 'Lengkap.' : 'Belum tampak: '.implode(', ', $analysisMissing)],
            ['label' => 'Sitasi bukti', 'ok' => count(array_unique($cites[1])) >= 2 && $broken === [], 'detail' => count(array_unique($cites[1])).' bukti dikutip'.($broken ? '; rusak: '.implode(', ', $broken) : '')],
            ['label' => 'Data kuantitatif', 'ok' => $item->subIndicator->metric_code === null || count($metrics[1]) > 0, 'detail' => $item->subIndicator->metric_code ? 'Sub-indikator ini terkait metrik '.$item->subIndicator->metric_code.'; '.(count($metrics[1]) ? 'angka hidup sudah disisipkan.' : 'sisipkan {{'.$item->subIndicator->metric_code.'}}.') : 'Tidak wajib.'],
        ];

        $suggestions = [];

        foreach ($missing as $kw) {
            $suggestions[] = "Tambahkan uraian dan bukti untuk kata kunci \"{$kw}\".";
        }

        if ($ppeppMissing) {
            $suggestions[] = 'Susun narasi mengikuti PPEPP; tambahkan bagian '.implode(', ', $ppeppMissing).'.';
        }

        if (in_array('akar masalah', $analysisMissing, true)) {
            $suggestions[] = 'Tambahkan analisis akar masalah atas kesenjangan yang masih ada, termasuk faktor pendukung dan penghambat.';
        }

        if (count(array_unique($cites[1])) < 2) {
            $suggestions[] = 'Kutip minimal dua bukti dari Pustaka Bukti dengan penanda [B:KODE] agar asesor dapat membuka dokumen.';
        }

        if ($words > $limit) {
            $suggestions[] = 'Ringkas narasi hingga di bawah '.$limit.' kata; pindahkan rincian ke lampiran bukti.';
        }

        return [
            'score' => (int) round(collect($checks)->where('ok', true)->count() / count($checks) * 100),
            'coverage' => $coverage,
            'checks' => $checks,
            'suggestions' => $suggestions,
        ];
    }

    /** Saran perbaikan dari Claude; mengembalikan teks saran atau pesan kesalahan yang ramah. */
    public function aiReview(LedSection $section, ?string $body = null): array
    {
        $body ??= (string) $section->body;
        $item = $section->workItem;
        $descriptor = $item->subIndicator->descriptorFor($item->project->track);
        $system = 'Anda reviewer LED akreditasi institusi perguruan tinggi Indonesia (IAPT 4.1, BAN-PT). '
            .'Nilai apakah narasi menunjukkan pemenuhan deskriptor dengan alur PPEPP, analisis akar masalah, dan bukti. '
            .'Jawab dalam bahasa Indonesia, ringkas: (1) kekuatan, (2) kata kunci deskriptor yang belum dibuktikan, '
            .'(3) maksimal lima saran perbaikan konkret. Jangan menulis ulang seluruh narasi dan jangan mengarang data.';
        $prompt = "Sub-indikator {$item->subIndicator->code}: {$item->subIndicator->statement}\n"
            ."Jalur: {$item->project->track->getLabel()}\nDeskriptor: {$descriptor?->text}\n"
            .'Kata kunci: '.($descriptor?->keywords->pluck('text')->implode('; ') ?? '-')."\n\nNarasi:\n".$this->led->plain($body, false, $item);

        try {
            $client = new Client(apiKey: config('akredia.ai.key'));
            $message = $client->beta->messages->create(
                model: config('akredia.ai.model'),
                maxTokens: 4000,
                system: $system,
                messages: [['role' => 'user', 'content' => $prompt]],
                outputConfig: ['effort' => 'low'],
                betas: ['server-side-fallback-2026-07-01'],
                fallbacks: 'default',
            );

            if ($message->stopReason === 'refusal') {
                return ['ok' => false, 'text' => 'Asisten AI menolak memproses narasi ini. Gunakan hasil pemeriksaan lokal.'];
            }

            $text = collect($message->content)->filter(fn ($b) => $b->type === 'text')->map(fn ($b) => $b->text)->implode("\n");

            return ['ok' => true, 'text' => trim($text)];
        } catch (Throwable $e) {
            report($e);

            return ['ok' => false, 'text' => 'Asisten AI tidak dapat dihubungi saat ini. Pemeriksaan lokal tetap tersedia.'];
        }
    }
}
