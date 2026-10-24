<?php

namespace App\Filament\App\Resources\Decisions\Pages;

use App\Filament\App\Resources\Decisions\DecisionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDecisions extends ManageRecords
{
    protected static string $resource = DecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
