<?php

namespace Tests\Feature;

use App\Models\AccreditationProject;
use App\Models\Institution;
use App\Models\User;
use App\Support\CurrentInstitution;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Basis uji: basis data dimigrasi dan diisi data contoh satu kali, lalu tiap uji dibungkus transaksi. */
abstract class DemoTestCase extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DemoTestSeeder::class;

    protected function beforeRefreshingDatabase(): void
    {
        config(['filesystems.disks.local.root' => sys_get_temp_dir().'/akredia-test-storage']);
    }

    protected function institution(): Institution
    {
        return Institution::where('slug', DemoSeeder::SLUG)->firstOrFail();
    }

    protected function project(): AccreditationProject
    {
        return AccreditationProject::withoutGlobalScopes()->where('institution_id', $this->institution()->id)->firstOrFail();
    }

    protected function user(string $local): User
    {
        return User::where('email', "{$local}@akredia.test")->firstOrFail();
    }

    protected function actAsTenant(): Institution
    {
        $institution = $this->institution();
        app(CurrentInstitution::class)->set($institution);

        return $institution;
    }
}
