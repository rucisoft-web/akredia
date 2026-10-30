<?php

namespace Tests\Feature;

use App\Models\WorkItem;
use App\Support\Help;

class HelpTest extends DemoTestCase
{
    public function test_rujukan_resmi_memuat_39_indikator_dan_syarat_perlu(): void
    {
        $official = Help::official();

        $this->assertCount(39, $official);
        $this->assertSame('Budaya Mutu', $official[1]['kriteria']);
        $this->assertSame(['A', 'B', 'C'], array_column($official[7]['terakreditasi'], 'label'));
        $this->assertTrue($official[7]['terakreditasi'][0]['syarat_perlu']);
        $this->assertStringContainsString('RM/DT ≤ 40', $official[6]['terakreditasi'][2]['deskripsi']);
        $this->assertNotEmpty($official[23]['panduan_led']);
        $this->assertNotEmpty($official[35]['bukti_kriteria']);
    }

    public function test_kunci_bantuan_dari_nama_rute(): void
    {
        $this->assertSame('documents', Help::keyFor('filament.app.resources.documents.view'));
        $this->assertSame('tracer-studys', Help::keyFor('filament.app.resources.tracer-studys.tracer-studies.index'));
        $this->assertSame('tabel-lkpt', Help::keyFor('filament.app.pages.tabel-lkpt'));
        $this->assertSame('panduan.memahami-iapt', Help::keyFor('filament.app.pages.panduan.memahami-iapt'));
        $this->assertSame('panduan', Help::keyFor('filament.app.pages.panduan.tanya-jawab'));
        $this->assertSame('_umum', Help::keyFor(null));

        foreach (Help::pages() as $key => $page) {
            $this->assertNotEmpty($page['judul'], $key);
            $this->assertNotEmpty($page['ringkas'], $key);
            foreach ($page['indikator'] ?? [] as $n) {
                $this->assertNotNull(Help::official($n), "$key → indikator $n");
            }
        }
    }

    public function test_tombol_bantuan_tampil_dengan_isi_sesuai_halaman(): void
    {
        $slug = $this->institution()->slug;
        $this->actingAs($this->user('manajer'));

        $this->get("/app/$slug/lecturers")->assertOk()
            ->assertSee('ak-help-btn', false)->assertSee('data-help-key="lecturers"', false)
            ->assertSee('Penjelasan isian dan contoh')->assertSee('PDTT ≤ 40%');

        $this->get("/app/$slug/tabel-lkpt")->assertOk()->assertSee('data-help-key="tabel-lkpt"', false)->assertSee('Tabel II-1.A.1');

        $this->actAsTenant();
        $item = WorkItem::query()->with('subIndicator.indicator')->firstOrFail();
        $this->get("/app/$slug/work-items/{$item->id}")->assertOk()
            ->assertSee('Sub-indikator '.$item->subIndicator->code.' di Akredia')
            ->assertSee('Indikator '.$item->subIndicator->indicator->number);
    }

    public function test_rujukan_indikator_dapat_dimuat_tanpa_login(): void
    {
        $this->get('/bantuan/indikator/11')->assertOk()->assertSee('DTJA')->assertSee('Petunjuk Panduan LED');
        $this->get('/bantuan/indikator/40')->assertNotFound();
    }
}
