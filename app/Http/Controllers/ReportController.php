<?php

namespace App\Http\Controllers;

use App\Enums\Track;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\GapItem;
use App\Models\Institution;
use App\Services\ExecutiveDashboard;
use App\Services\LedService;
use App\Services\LkptBuilder;
use App\Services\ReadinessCalculator;
use App\Support\Spreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Laporan bawaan (bagian 7.3 rancangan): LED, LKPT, kertas kerja, syarat perlu, mingguan, bukti publik, log audit. */
class ReportController extends Controller
{
    protected function project(Institution $institution)
    {
        return $institution->activeProject() ?? abort(404, 'Belum ada proyek akreditasi.');
    }

    protected function xlsx(string $name, array $sheets): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'ak').'.xlsx';
        Spreadsheet::write($path, $sheets);
        AuditLog::record('ekspor', null, ['berkas' => $name]);

        return response()->download($path, $name)->deleteFileAfterSend();
    }

    public function lkpt(Institution $institution): BinaryFileResponse
    {
        $sheets = [];

        foreach (app(LkptBuilder::class)->tables($institution, $institution->activeProject()) as $t) {
            $sheets[Str::limit(Str::before($t['title'], ' ('), 28, '')] = [$t['header'], $t['rows']];
        }

        return $this->xlsx('LKPT-'.$institution->slug.'.xlsx', $sheets);
    }

    public function workpapers(Institution $institution): BinaryFileResponse
    {
        $project = $this->project($institution);
        $items = app(ReadinessCalculator::class)->items($project);
        $gaps = GapItem::query()->whereIn('work_item_id', $items->pluck('id'))->with(['owner', 'task'])->get()->groupBy('work_item_id');

        $summary = $items->map(fn ($w) => [$w->subIndicator->code, $w->subIndicator->statement, $w->pic?->name, $w->reviewer?->name, $w->state->getLabel(), $w->color->getLabel(), $w->color_reason, $w->due_date?->format('d/m/Y'), $w->ledSection?->state->getLabel(), $w->ledSection?->word_count]);
        $rows = $items->flatMap(fn ($w) => ($gaps[$w->id] ?? collect())->map(fn ($g) => [$w->subIndicator->code, $w->pic?->name, $g->keyword_text, $g->status->getLabel(), $g->implementation_note, $g->rtl, $g->owner?->name, $g->due_date?->format('d/m/Y'), $g->task?->number]));

        return $this->xlsx('Kertas-Kerja-'.$institution->slug.'.xlsx', [
            'Butir kerja' => [['Kode', 'Sub-indikator', 'PIC', 'Reviewer', 'Status', 'Warna', 'Alasan warna', 'Tenggat', 'Status LED', 'Kata LED'], $summary],
            'Kertas kerja' => [['Kode', 'PIC', 'Kata kunci', 'Status', 'Kondisi', 'RTL', 'PJ', 'Tenggat', 'Tugas'], $rows],
        ]);
    }

    public function publicEvidence(Institution $institution): BinaryFileResponse
    {
        $docs = Document::query()->where('is_public', true)->orderBy('code')->get();

        return $this->xlsx('Bukti-Publik-'.$institution->slug.'.xlsx', [
            'Bukti publik' => [['Kode', 'Judul', 'Jenis', 'Nomor', 'Tanggal', 'Status', 'Berlaku sampai', 'URL publik'],
                $docs->map(fn ($d) => [$d->code, $d->title, $d->doc_type, $d->number, $d->doc_date?->format('d/m/Y'), $d->status, $d->valid_until?->format('d/m/Y'), $d->publicUrl()])],
        ]);
    }

    public function ledDocx(Institution $institution): BinaryFileResponse
    {
        $project = $this->project($institution);
        $items = app(ReadinessCalculator::class)->items($project);
        $led = app(LedService::class);

        $word = new PhpWord;
        $word->setDefaultFontName('Calibri');
        $word->setDefaultFontSize(11);
        $word->addTitleStyle(1, ['size' => 16, 'bold' => true, 'color' => '17406F']);
        $word->addTitleStyle(2, ['size' => 13, 'bold' => true, 'color' => '1F528C']);
        $word->addTitleStyle(3, ['size' => 11, 'bold' => true]);
        $section = $word->addSection(['marginTop' => Converter::cmToTwip(2.5), 'marginBottom' => Converter::cmToTwip(2.5), 'marginLeft' => Converter::cmToTwip(2.5), 'marginRight' => Converter::cmToTwip(2.5)]);
        $section->addText('LAPORAN EVALUASI DIRI', ['size' => 22, 'bold' => true, 'color' => '17406F'], ['alignment' => 'center', 'spaceBefore' => 2400]);
        $section->addText('Akreditasi Institusi · IAPT 4.1', ['size' => 14], ['alignment' => 'center']);
        $section->addText($institution->name, ['size' => 16, 'bold' => true], ['alignment' => 'center', 'spaceBefore' => 600]);
        $section->addText($project->name.' · Jalur '.$project->track->getLabel(), ['size' => 11], ['alignment' => 'center']);
        $section->addText('Dicetak '.now()->translatedFormat('d F Y H:i').' dari Akredia', ['size' => 9, 'color' => '64748B'], ['alignment' => 'center', 'spaceBefore' => 2400]);
        $section->addPageBreak();

        foreach ($items->groupBy(fn ($w) => $w->subIndicator->indicator->criterion->number) as $num => $group) {
            $criterion = $group->first()->subIndicator->indicator->criterion;
            $section->addTitle('Kriteria '.$num.' · '.$criterion->name, 1);

            foreach ($group->groupBy(fn ($w) => $w->subIndicator->indicator->number) as $indNum => $subs) {
                $section->addTitle('Indikator '.$indNum, 2);
                $section->addText($subs->first()->subIndicator->indicator->statement, ['italic' => true, 'color' => '475569']);

                foreach ($subs as $w) {
                    $section->addTitle($w->subIndicator->code.'. '.$w->subIndicator->statement, 3);
                    $text = $w->ledSection?->body ? $led->plain($w->ledSection->body, true, $w) : '(Narasi belum ditulis.)';

                    foreach (preg_split("/\n\s*\n/", trim($text)) as $para) {
                        $run = $section->addTextRun(['spaceAfter' => 120]);
                        $lines = explode("\n", $para);

                        foreach ($lines as $i => $line) {
                            $run->addText(htmlspecialchars($line, ENT_NOQUOTES));

                            if ($i < count($lines) - 1) {
                                $run->addTextBreak();
                            }
                        }
                    }

                    $section->addText(($w->ledSection?->word_count ?? 0).' kata · status '.($w->ledSection?->state->getLabel() ?? '—'), ['size' => 8, 'color' => '94A3B8']);
                }
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'led').'.docx';
        $word->save($path, 'Word2007');
        AuditLog::record('ekspor', $project, ['berkas' => 'LED DOCX']);

        return response()->download($path, 'LED-'.$institution->slug.'.docx')->deleteFileAfterSend();
    }

    public function ledPrint(Institution $institution)
    {
        $project = $this->project($institution);

        return view('reports.led', ['institution' => $institution, 'project' => $project, 'items' => app(ReadinessCalculator::class)->items($project), 'led' => app(LedService::class)]);
    }

    public function prerequisites(Institution $institution)
    {
        $project = $this->project($institution);
        $calc = app(ReadinessCalculator::class);
        $items = $calc->items($project)->filter(fn ($w) => $w->subIndicator->is_prerequisite_accredited || $w->subIndicator->is_prerequisite_excellent);

        return view('reports.prerequisites', ['institution' => $institution, 'project' => $project, 'items' => $items, 'calc' => $calc, 'gate' => $calc->gate($project)]);
    }

    public function weekly(Institution $institution)
    {
        $project = $institution->activeProject();

        return view('reports.weekly', ['institution' => $institution, 'd' => app(ExecutiveDashboard::class)->data($project), 'gate' => $project ? app(ReadinessCalculator::class)->gate($project) : []]);
    }

    public function audit(Institution $institution, Request $request): StreamedResponse
    {
        abort_unless($request->user()->roleIn($institution)?->canManage() || $request->user()->roleIn($institution)?->canLead(), 403);

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['waktu', 'pengguna', 'peristiwa', 'objek', 'label', 'perubahan', 'ip']);

            AuditLog::query()->with('user')->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [$r->created_at?->format('Y-m-d H:i:s'), $r->user?->email, $r->event, $r->typeLabel(), $r->label, json_encode($r->changes, JSON_UNESCAPED_UNICODE), $r->ip_address]);
                }
            });
            fclose($out);
        }, 'log-audit-'.$institution->slug.'-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }
}
