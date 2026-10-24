<?php

namespace App\Filament\App\Resources\Tasks\Pages;

use App\Filament\App\Resources\Tasks\TaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTasks extends ManageRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make(), \Filament\Actions\Action::make('kanban')->label('Papan Kanban')->icon('heroicon-o-view-columns')->color('gray')->url(\App\Filament\App\Pages\PapanTugas::getUrl()), \Filament\Actions\Action::make('calendar')->label('Kalender')->icon('heroicon-o-calendar-days')->color('gray')->url(\App\Filament\App\Pages\KalenderTenggat::getUrl())];
    }
}
