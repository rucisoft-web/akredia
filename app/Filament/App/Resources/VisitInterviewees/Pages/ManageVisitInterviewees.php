<?php

namespace App\Filament\App\Resources\VisitInterviewees\Pages;

use App\Filament\App\Resources\VisitInterviewees\VisitIntervieweeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVisitInterviewees extends ManageRecords
{
    protected static string $resource = VisitIntervieweeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
