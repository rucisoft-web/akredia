<?php

namespace App\Filament\App\Pages;

use App\Filament\Concerns\GuidePage;
use App\Http\Controllers\DemoController;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PanduanUntukPimpinan extends GuidePage
{
    protected static ?int $navigationSort = 0;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $navigationLabel = 'Akredia untuk Pimpinan PT';

    protected static ?string $title = 'Akredia untuk Pimpinan Perguruan Tinggi';

    protected static ?string $slug = 'panduan/untuk-pimpinan';

    protected string $view = 'filament.guide.untuk-pimpinan';

    protected function getViewData(): array
    {
        return parent::getViewData() + ['standalone' => false, 'guestUrl' => null, 'loginUrl' => DemoController::loginUrl(), 'publicUrl' => route('pimpinan')];
    }
}
