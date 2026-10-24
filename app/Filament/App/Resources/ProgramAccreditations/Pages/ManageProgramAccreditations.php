<?php

namespace App\Filament\App\Resources\ProgramAccreditations\Pages;

use App\Filament\App\Resources\ProgramAccreditations\ProgramAccreditationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProgramAccreditations extends ManageRecords
{
    protected static string $resource = ProgramAccreditationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
