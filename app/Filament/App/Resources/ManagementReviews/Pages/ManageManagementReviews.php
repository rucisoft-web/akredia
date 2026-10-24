<?php

namespace App\Filament\App\Resources\ManagementReviews\Pages;

use App\Filament\App\Resources\ManagementReviews\ManagementReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageManagementReviews extends ManageRecords
{
    protected static string $resource = ManagementReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
