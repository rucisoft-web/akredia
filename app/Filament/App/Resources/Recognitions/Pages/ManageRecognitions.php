<?php

namespace App\Filament\App\Resources\Recognitions\Pages;

use App\Filament\App\Resources\Recognitions\RecognitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRecognitions extends ManageRecords
{
    protected static string $resource = RecognitionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
