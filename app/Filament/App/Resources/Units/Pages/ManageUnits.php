<?php

namespace App\Filament\App\Resources\Units\Pages;

use App\Filament\App\Resources\Units\UnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUnits extends ManageRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
