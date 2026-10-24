<?php

namespace App\Filament\App\Resources\AccreditationProjects\Pages;

use App\Filament\App\Resources\AccreditationProjects\AccreditationProjectResource;
use App\Services\ReadinessCalculator;
use Filament\Resources\Pages\EditRecord;

class EditAccreditationProject extends EditRecord
{
    protected static string $resource = AccreditationProjectResource::class;

    protected function afterSave(): void
    {
        app(ReadinessCalculator::class)->refresh($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->record]);
    }
}
