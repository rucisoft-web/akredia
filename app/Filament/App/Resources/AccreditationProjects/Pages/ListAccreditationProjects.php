<?php

namespace App\Filament\App\Resources\AccreditationProjects\Pages;

use App\Filament\App\Resources\AccreditationProjects\AccreditationProjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccreditationProjects extends ListRecords
{
    protected static string $resource = AccreditationProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Proyek baru')];
    }
}
