<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Unit;
use App\Models\User;

class DemoGuestTest extends DemoTestCase
{
    public function test_halaman_pimpinan_publik_tanpa_tamu(): void
    {
        $this->get('/')->assertOk()->assertSee('Akreditasi institusi yang terukur')->assertDontSee('/demo/tamu')->assertDontSee('id="data-contoh"', false);
        $this->get('/untuk-pimpinan')->assertOk()->assertSee('Akredia untuk Pimpinan Perguruan Tinggi')->assertDontSee('/demo/tamu');
        $this->get('/demo/tamu')->assertNotFound();
        $this->get('/app/login')->assertOk()->assertSee('Akredia untuk Pimpinan Perguruan Tinggi')->assertDontSee('Masuk sebagai Tamu');
    }

    public function test_tamu_masuk_ke_data_demo_dan_hanya_melihat(): void
    {
        $institution = $this->institution();
        $guest = User::create(['name' => 'Tamu Demo', 'email' => User::DEMO_GUEST_EMAIL, 'password' => 'rahasia-1234', 'is_active' => true]);
        $unit = Unit::withoutGlobalScopes()->where('institution_id', $institution->id)->firstOrFail();
        Membership::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'user_id' => $guest->id, 'unit_id' => $unit->id, 'role' => 'pengamat', 'position_title' => 'Tamu']);

        $this->get('/')->assertOk()->assertSee('id="data-contoh"', false)->assertSee($institution->name)->assertSee('demo/tamu?pt='.$institution->slug, false);
        $this->get('/app/login')->assertSee('Masuk sebagai Tamu')->assertSee('Pilih perguruan tinggi contoh')->assertSee($institution->name);
        $this->get('/demo/tamu?pt=tidak-ada')->assertRedirect('/app/'.$institution->slug);
        $this->get('/demo/tamu?pt='.$institution->slug)->assertRedirect('/app/'.$institution->slug);
        $this->assertAuthenticatedAs($guest);

        $this->get('/app/'.$institution->slug)->assertOk()->assertSee('DATA DEMO')->assertSee('Mode tamu')->assertSee('tamu (hanya lihat)', false);
        $this->get('/app/'.$institution->slug.'/panduan/untuk-pimpinan')->assertOk();
        $this->get('/app/profile')->assertRedirect();

        // Tombol pengubah data tidak tampil bagi tamu.
        $questions = '/app/'.$institution->slug.'/visit-questions';
        $page = $this->get($questions)->assertOk()->assertDontSee('Buat Pertanyaan');
        $this->assertDoesNotMatchRegularExpression('/(Ubah|Hapus)\s*<\/span>/', $page->getContent());
    }

    public function test_tombol_pengubah_data_tetap_tampil_bagi_manajer(): void
    {
        $page = $this->actingAs($this->user('manajer'))->get('/app/'.$this->institution()->slug.'/visit-questions')
            ->assertOk()->assertSee('Buat Pertanyaan');
        $this->assertMatchesRegularExpression('/Hapus\s*<\/span>/', $page->getContent());
    }
}
