<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Institution;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Services\EvidenceService;
use App\Support\CurrentInstitution;
use App\Support\Help;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Halaman tanpa login: portal bukti publik untuk asesor dan kuesioner kepuasan publik. */
class PublicController extends Controller
{
    public function evidenceIndex(Institution $institution)
    {
        $docs = Document::withoutGlobalScopes()->where('institution_id', $institution->id)->where('is_public', true)->where('status', 'valid')->orderBy('code')->get();

        return view('public.evidence-index', compact('institution', 'docs'));
    }

    public function evidence(Institution $institution, string $slug)
    {
        $doc = Document::withoutGlobalScopes()->where('institution_id', $institution->id)->where('public_slug', $slug)->where('is_public', true)->firstOrFail();
        $version = DocumentVersion::withoutGlobalScopes()->where('document_id', $doc->id)->orderByDesc('version')->first();

        if (request()->boolean('unduh') && $version?->file_path) {
            return Storage::disk(EvidenceService::DISK)->response($version->file_path, $version->original_name, [], 'inline');
        }

        return view('public.evidence', compact('institution', 'doc', 'version'));
    }

    /** Unduhan berkas internal melalui URL bertanda tangan; bukti rahasia dicatat di log audit. */
    public function download(Request $request, DocumentVersion $version)
    {
        $doc = Document::withoutGlobalScopes()->findOrFail($version->document_id);
        $institution = Institution::findOrFail($doc->institution_id);
        abort_unless($request->user()?->activeMembership($institution), 403);
        abort_unless(Document::withoutGlobalScopes()->whereKey($doc->id)->visibleTo($request->user(), $institution)->exists(), 403);

        app(CurrentInstitution::class)->runAs($institution, fn () => app(EvidenceService::class)->logDownload($version));
        abort_unless($version->file_path && Storage::disk(EvidenceService::DISK)->exists($version->file_path), 404);

        return Storage::disk(EvidenceService::DISK)->download($version->file_path, $version->original_name);
    }

    /** Rujukan resmi BAN-PT satu indikator untuk panel Bantuan (teks publik). */
    public function helpIndicator(int $number)
    {
        abort_unless($ind = Help::official($number), 404);

        return response()->view('help.indicator', ['ind' => $ind])->header('Cache-Control', 'public, max-age=86400');
    }

    public function survey(string $token)
    {
        $survey = Survey::withoutGlobalScopes()->where('public_token', $token)->firstOrFail();
        $institution = Institution::findOrFail($survey->institution_id);

        return view('public.survey', compact('survey', 'institution'));
    }

    public function submitSurvey(Request $request, string $token)
    {
        $survey = Survey::withoutGlobalScopes()->where('public_token', $token)->where('is_open', true)->firstOrFail();
        $rules = collect(Survey::QUESTIONS)->keys()->mapWithKeys(fn ($k) => ["answers.$k" => 'required|integer|between:1,4'])->all();
        $data = $request->validate($rules + ['comment' => 'nullable|string|max:2000']);
        $hash = hash('sha256', $request->ip().'|'.$survey->id.'|'.config('app.key'));

        if (SurveyResponse::withoutGlobalScopes()->where('survey_id', $survey->id)->where('ip_hash', $hash)->where('created_at', '>=', now()->subHour())->exists()) {
            return back()->with('status', 'Terima kasih, jawaban dari perangkat ini sudah kami terima.');
        }

        app(CurrentInstitution::class)->runAs(Institution::findOrFail($survey->institution_id), function () use ($survey, $data, $hash) {
            SurveyResponse::create(['survey_id' => $survey->id, 'answers' => $data['answers'], 'comment' => $data['comment'] ?? null, 'ip_hash' => $hash]);
            $survey->addResponse($data['answers']);
        });

        return redirect()->route('survei.show', $token)->with('status', 'Terima kasih, jawaban Anda sudah tersimpan.');
    }
}
