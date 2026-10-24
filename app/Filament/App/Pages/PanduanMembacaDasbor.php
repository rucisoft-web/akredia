<?php

namespace App\Filament\App\Pages;

use App\Filament\Concerns\GuidePage;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PanduanMembacaDasbor extends GuidePage
{
    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $navigationLabel = 'Membaca Dasbor';

    protected static ?string $title = 'Cara Membaca Dasbor Eksekutif';

    protected static ?string $slug = 'panduan/membaca-dasbor';

    protected string $view = 'filament.guide.membaca-dasbor';
}
