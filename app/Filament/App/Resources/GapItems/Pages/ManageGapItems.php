<?php

namespace App\Filament\App\Resources\GapItems\Pages;

use App\Filament\App\Resources\GapItems\GapItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGapItems extends ManageRecords
{
    protected static string $resource = GapItemResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
