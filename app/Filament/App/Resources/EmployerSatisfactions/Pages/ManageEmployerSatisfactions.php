<?php

namespace App\Filament\App\Resources\EmployerSatisfactions\Pages;

use App\Filament\App\Resources\EmployerSatisfactions\EmployerSatisfactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEmployerSatisfactions extends ManageRecords
{
    protected static string $resource = EmployerSatisfactionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
