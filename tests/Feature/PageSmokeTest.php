<?php

namespace Tests\Feature;

use App\Models\Survey;
use PHPUnit\Framework\Attributes\DataProvider;

class PageSmokeTest extends DemoTestCase
{
    public static function pages(): array
    {
        return [
            ['manajer', ''], ['manajer', '/work-items'], ['manajer', '/documents'], ['manajer', '/led-sections'],
            ['manajer', '/simulasi-penilaian'], ['manajer', '/tabel-lkpt'], ['manajer', '/papan-tugas'], ['manajer', '/kalender-tenggat'],
            ['manajer', '/metric-values'], ['manajer', '/reconciliations'], ['manajer', '/audit-logs'], ['manajer', '/laporan'],
            ['rektor', ''], ['rektor', '/panduan/mengenal-akredia'], ['rektor', '/panduan/memahami-iapt'], ['rektor', '/panduan/membaca-dasbor'],
            ['pic.lpm', '/pekerjaan-saya'], ['pengamat', ''], ['asesor', '/peta-instrumen'],
        ];
    }

    #[DataProvider('pages')]
    public function test_halaman_terbuka(string $user, string $path): void
    {
        $this->actingAs($this->user($user))->get('/app/unm'.$path)->assertOk();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/app/unm')->assertRedirect();
    }

    public function test_pengguna_bukan_anggota_ditolak(): void
    {
        $outsider = \App\Models\User::create(['name' => 'Luar', 'email' => 'luar@example.test', 'password' => 'Rahasia#12345']);
        $this->actingAs($outsider)->get('/app/unm/work-items')->assertNotFound();
        $this->actingAs($outsider)->get('/laporan/'.$this->institution()->id.'/led')->assertForbidden();
    }

    public function test_ekspor_dan_laporan(): void
    {
        $id = $this->institution()->id;
        $this->actingAs($this->user('manajer'));

        $this->get("/laporan/{$id}/lkpt.xlsx")->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->get("/laporan/{$id}/led.docx")->assertOk();
        $this->get("/laporan/{$id}/syarat-perlu")->assertOk()->assertSee('2A');
        $this->get("/laporan/{$id}/log-audit.csv")->assertOk();
    }

    public function test_portal_bukti_dan_survei_publik(): void
    {
        $this->get('/bukti/unm')->assertOk();

        $survey = Survey::withoutGlobalScopes()->where('is_open', true)->firstOrFail();
        $this->get('/survei/'.$survey->public_token)->assertOk();
        $answers = array_fill(0, count(Survey::QUESTIONS), 4);
        $this->post('/survei/'.$survey->public_token, ['answers' => $answers])->assertSessionHasNoErrors();
        $this->assertSame(1, $survey->fresh()->respondents);
    }

    public function test_panel_platform_hanya_untuk_admin_platform(): void
    {
        $this->actingAs($this->user('manajer'))->get('/platform')->assertForbidden();
        $admin = \App\Models\User::create(['name' => 'Admin', 'email' => 'root@example.test', 'password' => 'Rahasia#12345', 'is_platform_admin' => true]);
        $this->actingAs($admin)->get('/platform/instrument-variants')->assertOk();
        $this->actingAs($admin)->get('/platform/metrics')->assertOk();
    }
}
