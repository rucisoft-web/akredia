<?php

namespace App\Filament\App\Resources\Findings\Pages;

use App\Filament\App\Resources\Findings\FindingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFindings extends ManageRecords
{
    protected static string $resource = FindingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
