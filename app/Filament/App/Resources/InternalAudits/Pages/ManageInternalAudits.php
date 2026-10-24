<?php

namespace App\Filament\App\Resources\InternalAudits\Pages;

use App\Filament\App\Resources\InternalAudits\InternalAuditResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageInternalAudits extends ManageRecords
{
    protected static string $resource = InternalAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
