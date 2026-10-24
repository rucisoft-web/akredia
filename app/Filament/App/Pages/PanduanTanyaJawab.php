<?php

namespace App\Filament\App\Pages;

use App\Filament\Concerns\GuidePage;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PanduanTanyaJawab extends GuidePage
{
    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Tanya Jawab';

    protected static ?string $title = 'Tanya Jawab Pimpinan';

    protected static ?string $slug = 'panduan/tanya-jawab';

    protected string $view = 'filament.guide.tanya-jawab';
}
