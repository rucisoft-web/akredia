<?php

namespace App\Filament\App\Resources\FinancialAudits\Pages;

use App\Filament\App\Resources\FinancialAudits\FinancialAuditResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFinancialAudits extends ManageRecords
{
    protected static string $resource = FinancialAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
