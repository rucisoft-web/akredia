<?php

namespace App\Filament\App\Pages;

use App\Services\LkptBuilder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Tabel LKPT siap salin ke SAPTO (M06). */
class TabelLkpt extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 70;

    protected static ?string $navigationLabel = 'Tabel LKPT';

    protected static ?string $title = 'Tabel Laporan Kinerja Perguruan Tinggi (LKPT)';

    protected static ?string $slug = 'tabel-lkpt';

    protected string $view = 'filament.pages.tabel-lkpt';

    public string $active = 'identitas';

    public function getSubheading(): ?string
    {
        $project = filament()->getTenant()?->activeProject();

        return 'Disusun otomatis dari Data Kinerja untuk TS '.($project?->tsYear() ?? now()->year - 1).'. Klik "Salin" lalu tempel ke tabel SAPTO, atau unduh semua tabel sebagai XLSX.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('xlsx')->label('Unduh XLSX')->icon(Heroicon::OutlinedArrowDownTray)
                ->url(fn () => route('laporan.lkpt', ['institution' => filament()->getTenant()])),
        ];
    }

    protected function getViewData(): array
    {
        $tenant = filament()->getTenant();

        return ['tables' => app(LkptBuilder::class)->tables($tenant, $tenant->activeProject())];
    }
}
