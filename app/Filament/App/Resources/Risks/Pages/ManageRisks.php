<?php

namespace App\Filament\App\Resources\Risks\Pages;

use App\Filament\App\Resources\Risks\RiskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRisks extends ManageRecords
{
    protected static string $resource = RiskResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
