<?php

namespace App\Filament\App\Resources\AccreditationProjects\Pages;

use App\Filament\App\Resources\AccreditationProjects\AccreditationProjectResource;
use App\Services\ProjectWorkflow;
use App\Services\ReadinessCalculator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAccreditationProject extends CreateRecord
{
    protected static string $resource = AccreditationProjectResource::class;

    protected function afterCreate(): void
    {
        $count = app(ProjectWorkflow::class)->generate($this->record);
        app(ReadinessCalculator::class)->refresh($this->record);

        Notification::make()->success()->title("{$count} butir kerja sub-indikator dibangkitkan")
            ->body('Kertas kerja per kata kunci, kerangka LED, dan daftar eligibilitas sudah disiapkan. Tetapkan PIC dan reviewer di menu Butir Kerja.')
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->record]);
    }
}
