<?php

namespace App\Filament\App\Resources\VisitRecommendations\Pages;

use App\Filament\App\Resources\VisitRecommendations\VisitRecommendationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVisitRecommendations extends ManageRecords
{
    protected static string $resource = VisitRecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
