<?php

namespace App\Filament\App\Resources\VisitSessions\Pages;

use App\Filament\App\Resources\VisitSessions\VisitSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVisitSessions extends ManageRecords
{
    protected static string $resource = VisitSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
