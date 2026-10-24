<?php

namespace App\Filament\App\Resources\TracerStudys\Pages;

use App\Filament\App\Resources\TracerStudys\TracerStudyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTracerStudys extends ManageRecords
{
    protected static string $resource = TracerStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
