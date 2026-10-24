<?php

namespace App\Filament\App\Resources\StudentCohorts\Pages;

use App\Filament\App\Resources\StudentCohorts\StudentCohortResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStudentCohorts extends ManageRecords
{
    protected static string $resource = StudentCohortResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
