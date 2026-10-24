<?php

namespace Tests\Feature;

use App\Enums\Confidentiality;
use App\Models\Document;
use App\Models\Institution;
use App\Models\Unit;
use App\Support\CurrentInstitution;
use LogicException;

class TenancyTest extends DemoTestCase
{
    public function test_data_institusi_lain_tidak_terlihat(): void
    {
        $other = Institution::create(['name' => 'Institut Lain', 'type' => 'pts_akademik']);
        app(CurrentInstitution::class)->set($other);

        $this->assertSame(0, Document::count());
        $this->assertSame(0, Unit::count());

        app(CurrentInstitution::class)->set($this->institution());
        $this->assertGreaterThan(100, Document::count());
    }

    public function test_tidak_boleh_merujuk_induk_milik_institusi_lain(): void
    {
        $other = Institution::create(['name' => 'Institut Lain', 'type' => 'pts_akademik']);
        $foreignUnit = app(CurrentInstitution::class)->runAs($other, fn () => Unit::create(['name' => 'Unit asing']));
        $this->actAsTenant();

        $this->expectException(LogicException::class);
        Document::create(['title' => 'Uji', 'doc_type' => 'lainnya', 'owner_unit_id' => $foreignUnit->id]);
    }

    public function test_dokumen_rahasia_unit_lain_tersembunyi_bagi_pic(): void
    {
        $institution = $this->actAsTenant();
        $pic = $this->user('pic.keuangan');
        $secretElsewhere = Document::where('confidentiality', Confidentiality::Secret)
            ->where('owner_unit_id', '!=', $pic->activeMembership($institution)->unit_id)
            ->where('uploaded_by', '!=', $pic->id)->firstOrFail();

        $visible = Document::query()->visibleTo($pic, $institution)->pluck('id');
        $this->assertFalse($visible->contains($secretElsewhere->id));
        $this->assertTrue(Document::query()->visibleTo($this->user('manajer'), $institution)->pluck('id')->contains($secretElsewhere->id));
    }
}
