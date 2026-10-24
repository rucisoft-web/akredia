<?php

namespace App\Filament\App\Pages;

use App\Filament\Concerns\GuidePage;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PanduanMengenalAkredia extends GuidePage
{
    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Mengenal Akredia';

    protected static ?string $title = 'Bagaimana Akredia Membantu Pimpinan';

    protected static ?string $slug = 'panduan/mengenal-akredia';

    protected string $view = 'filament.guide.mengenal-akredia';
}
