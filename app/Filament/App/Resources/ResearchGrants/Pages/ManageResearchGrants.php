<?php

namespace App\Filament\App\Resources\ResearchGrants\Pages;

use App\Filament\App\Resources\ResearchGrants\ResearchGrantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageResearchGrants extends ManageRecords
{
    protected static string $resource = ResearchGrantResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
