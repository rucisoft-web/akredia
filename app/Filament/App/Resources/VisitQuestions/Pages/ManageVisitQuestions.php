<?php

namespace App\Filament\App\Resources\VisitQuestions\Pages;

use App\Filament\App\Resources\VisitQuestions\VisitQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVisitQuestions extends ManageRecords
{
    protected static string $resource = VisitQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
