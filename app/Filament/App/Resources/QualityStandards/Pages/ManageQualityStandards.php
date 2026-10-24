<?php

namespace App\Filament\App\Resources\QualityStandards\Pages;

use App\Filament\App\Resources\QualityStandards\QualityStandardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageQualityStandards extends ManageRecords
{
    protected static string $resource = QualityStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
