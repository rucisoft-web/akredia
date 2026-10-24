<?php

namespace App\Filament\App\Pages;

use App\Filament\Concerns\GuidePage;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PanduanIapt extends GuidePage
{
    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Memahami IAPT 4.1';

    protected static ?string $title = 'Memahami IAPT 4.1: 4 Kriteria, 39 Indikator';

    protected static ?string $slug = 'panduan/memahami-iapt';

    protected string $view = 'filament.guide.memahami-iapt';
}
