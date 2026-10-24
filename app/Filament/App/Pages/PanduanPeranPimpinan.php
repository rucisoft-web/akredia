<?php

namespace App\Filament\App\Pages;

use App\Filament\Concerns\GuidePage;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PanduanPeranPimpinan extends GuidePage
{
    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Peran Pimpinan';

    protected static ?string $title = 'Peran Pimpinan di Setiap Tahap';

    protected static ?string $slug = 'panduan/peran-pimpinan';

    protected string $view = 'filament.guide.peran-pimpinan';
}
