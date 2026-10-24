<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Unit;
use App\Models\User;

class DemoGuestTest extends DemoTestCase
{
    public function test_halaman_pimpinan_publik_tanpa_tamu(): void
    {
        $this->get('/untuk-pimpinan')->assertOk()->assertSee('Akredia untuk Pimpinan Perguruan Tinggi')->assertDontSee('/demo/tamu');
        $this->get('/demo/tamu')->assertNotFound();
        $this->get('/app/login')->assertOk()->assertSee('Akredia untuk Pimpinan Perguruan Tinggi')->assertDontSee('Masuk sebagai tamu');
    }

    public function test_tamu_masuk_ke_data_demo_dan_hanya_melihat(): void
    {
        $institution = $this->institution();
        $guest = User::create(['name' => 'Tamu Demo', 'email' => User::DEMO_GUEST_EMAIL, 'password' => 'rahasia-1234', 'is_active' => true]);
        $unit = Unit::withoutGlobalScopes()->where('institution_id', $institution->id)->firstOrFail();
        Membership::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'user_id' => $guest->id, 'unit_id' => $unit->id, 'role' => 'pengamat', 'position_title' => 'Tamu']);

        $this->get('/app/login')->assertSee('Masuk sebagai tamu');
        $this->get('/demo/tamu')->assertRedirect('/app/'.$institution->slug);
        $this->assertAuthenticatedAs($guest);

        $this->get('/app/'.$institution->slug)->assertOk()->assertSee('DATA DEMO')->assertSee('tamu (hanya lihat)', false);
        $this->get('/app/'.$institution->slug.'/panduan/untuk-pimpinan')->assertOk();
        $this->get('/app/profile')->assertRedirect();
    }
}
