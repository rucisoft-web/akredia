<?php

namespace App\Filament\App\Resources\SiteVisits\Pages;

use App\Filament\App\Resources\SiteVisits\SiteVisitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSiteVisits extends ManageRecords
{
    protected static string $resource = SiteVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
