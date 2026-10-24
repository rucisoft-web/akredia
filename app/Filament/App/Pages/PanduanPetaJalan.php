<?php

namespace App\Filament\App\Pages;

use App\Filament\Concerns\GuidePage;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PanduanPetaJalan extends GuidePage
{
    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $navigationLabel = 'Peta Jalan Akreditasi';

    protected static ?string $title = 'Peta Jalan Persiapan Akreditasi Institusi';

    protected static ?string $slug = 'panduan/peta-jalan';

    protected string $view = 'filament.guide.peta-jalan';
}
