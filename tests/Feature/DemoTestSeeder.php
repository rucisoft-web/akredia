<?php

namespace Tests\Feature;

use App\Services\InstrumentImporter;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\Seeder;

class DemoTestSeeder extends Seeder
{
    public function run(): void
    {
        app(InstrumentImporter::class)->seed();
        $this->call(DemoSeeder::class);
    }
}
