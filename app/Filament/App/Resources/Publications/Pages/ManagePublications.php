<?php

namespace App\Filament\App\Resources\Publications\Pages;

use App\Filament\App\Resources\Publications\PublicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePublications extends ManageRecords
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
