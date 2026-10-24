<?php

namespace Tests\Feature;

use App\Enums\DocumentStatus;
use App\Enums\LedState;
use App\Models\Capa;
use App\Models\Document;
use App\Models\Finding;
use App\Models\LedSection;
use App\Models\WorkItem;
use App\Services\EvidenceService;
use App\Services\LedService;
use App\Services\ReadinessCalculator;
use LogicException;

class RulesTest extends DemoTestCase
{
    public function test_reviewer_tidak_boleh_sama_dengan_pic(): void
    {
        $this->actAsTenant();
        $item = WorkItem::first();

        $this->expectException(LogicException::class);
        $item->update(['reviewer_id' => $item->pic_id]);
    }

    public function test_pengunggah_tidak_boleh_memvalidasi_buktinya_sendiri(): void
    {
        $this->actAsTenant();
        $doc = Document::where('status', DocumentStatus::Submitted)->firstOrFail();

        $this->expectException(LogicException::class);
        app(EvidenceService::class)->validate($doc, $doc->uploader);
    }

    public function test_validator_berbeda_dapat_memvalidasi(): void
    {
        $this->actAsTenant();
        $doc = Document::where('status', DocumentStatus::Submitted)->firstOrFail();
        app(EvidenceService::class)->validate($doc, $this->user('manajer'));

        $this->assertSame(DocumentStatus::Valid, $doc->fresh()->status);
    }

    public function test_verifikator_capa_tidak_boleh_pelaksana(): void
    {
        $this->actAsTenant();
        $capa = Capa::whereNotNull('owner_id')->firstOrFail();

        $this->expectException(LogicException::class);
        $capa->update(['verifier_id' => $capa->owner_id]);
    }

    public function test_led_melebihi_batas_kata_tidak_dapat_disetujui(): void
    {
        $this->actAsTenant();
        $section = LedSection::where('word_count', '>', 1500)->firstOrFail();
        $section->update(['state' => LedState::Review]);
        $reviewer = $section->workItem->reviewer;

        $this->expectExceptionMessage('melewati batas');
        app(LedService::class)->approve($section->fresh(), $reviewer);
    }

    public function test_penulis_tidak_boleh_mereviu_narasinya_sendiri(): void
    {
        $this->actAsTenant();
        $section = LedSection::where('state', LedState::Review)->where('word_count', '<=', 1500)->firstOrFail();

        $this->expectException(LogicException::class);
        app(LedService::class)->approve($section, $section->workItem->pic);
    }

    public function test_gerbang_tertutup_bila_syarat_perlu_gagal(): void
    {
        $this->actAsTenant();
        $project = $this->project();
        $calc = app(ReadinessCalculator::class);
        $result = $calc->evaluate($project);

        $this->assertSame(['2A'], $result['failed_prerequisites']->map(fn ($w) => $w->subIndicator->code)->values()->all());
        $this->assertFalse($calc->gatePasses($project));
        $this->assertGreaterThan(80, $result['projection_accredited']);
    }

    public function test_temuan_dan_capa_diberi_nomor_otomatis(): void
    {
        $this->actAsTenant();
        $this->assertMatchesRegularExpression('#^TMN/\d{4}/\d{3}$#', Finding::first()->number);
    }
}
