<?php

namespace App\Filament\App\Resources\Reconciliations\Pages;

use App\Filament\App\Resources\Reconciliations\ReconciliationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageReconciliations extends ManageRecords
{
    protected static string $resource = ReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make(), \App\Filament\App\Pages\ImporData::linkAction('pddikti')];
    }
}
