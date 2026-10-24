<?php

namespace App\Filament\App\Resources\Lecturers\Pages;

use App\Filament\App\Resources\Lecturers\LecturerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLecturers extends ManageRecords
{
    protected static string $resource = LecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [\App\Filament\App\Pages\ImporData::linkAction('dosen'), CreateAction::make()];
    }
}
