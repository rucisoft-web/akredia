<?php

namespace App\Filament\App\Resources\ProgramYearStats\Pages;

use App\Filament\App\Resources\ProgramYearStats\ProgramYearStatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProgramYearStats extends ManageRecords
{
    protected static string $resource = ProgramYearStatResource::class;

    protected function getHeaderActions(): array
    {
        return [\App\Filament\App\Pages\ImporData::linkAction('statistik'), CreateAction::make()];
    }
}
