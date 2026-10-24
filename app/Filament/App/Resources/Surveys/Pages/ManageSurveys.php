<?php

namespace App\Filament\App\Resources\Surveys\Pages;

use App\Filament\App\Resources\Surveys\SurveyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSurveys extends ManageRecords
{
    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
