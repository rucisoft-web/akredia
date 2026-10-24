<?php

namespace Tests\Feature;

use App\Filament\App\Tenancy\DaftarInstitusi;
use App\Models\Institution;
use App\Models\User;
use Livewire\Livewire;

class TenantRegistrationTest extends DemoTestCase
{
    public function test_pengguna_tanpa_institusi_tidak_mendapat_404(): void
    {
        $user = User::create(['name' => 'Tanpa Institusi', 'email' => 'kosong@contoh.test', 'password' => 'rahasia-1234', 'is_active' => true]);

        $this->actingAs($user)->get('/app')->assertRedirect('/app/new');
        $this->actingAs($user)->get('/app/new')->assertOk()->assertSee('belum terdaftar sebagai anggota institusi');
    }

    public function test_admin_platform_tanpa_institusi_dapat_mendaftarkan_institusi(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@contoh.test', 'password' => 'rahasia-1234', 'is_active' => true, 'is_platform_admin' => true]);
        $this->actingAs($admin)->get('/app')->assertRedirect('/app/new');

        Livewire::actingAs($admin)->test(DaftarInstitusi::class)
            ->fillForm(['name' => 'Universitas Uji Coba', 'type' => 'pts_akademik'])
            ->call('register')
            ->assertHasNoFormErrors();

        $institution = Institution::where('name', 'Universitas Uji Coba')->firstOrFail();
        $this->assertTrue($admin->fresh()->canAccessTenant($institution));
        $this->actingAs($admin->fresh())->get('/app')->assertRedirect('/app/'.$institution->slug);
    }
}
