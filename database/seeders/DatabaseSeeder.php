<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\InstrumentImporter;
use App\Models\InstrumentVersion;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Instrumen IAPT 4.1, administrator platform dari .env, dan data contoh bila AKREDIA_DEMO=true. */
    public function run(): void
    {
        if (! InstrumentVersion::query()->where('code', 'IAPT-4.1')->exists()) {
            app(InstrumentImporter::class)->seed();
            $this->command?->info('Pustaka Instrumen IAPT 4.1 dimuat.');
        }

        if ($email = config('akredia.admin.email')) {
            User::query()->updateOrCreate(['email' => $email], [
                'name' => 'Administrator Akredia',
                'password' => config('akredia.admin.password') ?: str()->password(16),
                'is_platform_admin' => true,
                'is_active' => true,
            ]);
            $this->command?->info("Administrator platform: {$email}");
        }

        if (config('akredia.demo.enabled')) {
            $this->call([UnjDemoSeeder::class, BinusDemoSeeder::class, PolbanDemoSeeder::class]);
        }
    }
}
