<?php

namespace App\Filament\App\Resources\StudyPrograms\Pages;

use App\Filament\App\Resources\StudyPrograms\StudyProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStudyPrograms extends ManageRecords
{
    protected static string $resource = StudyProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
