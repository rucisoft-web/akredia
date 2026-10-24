<?php

namespace App\Filament\Platform\Widgets;

use App\Models\AccreditationProject;
use App\Models\Document;
use App\Models\Institution;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Institusi aktif', Institution::where('is_active', true)->count()),
            Stat::make('Pengguna', User::count()),
            Stat::make('Proyek akreditasi', AccreditationProject::withoutGlobalScopes()->count()),
            Stat::make('Dokumen bukti', Document::withoutGlobalScopes()->count()),
        ];
    }
}
