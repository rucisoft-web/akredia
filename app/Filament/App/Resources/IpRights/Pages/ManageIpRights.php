<?php

namespace App\Filament\App\Resources\IpRights\Pages;

use App\Filament\App\Resources\IpRights\IpRightResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageIpRights extends ManageRecords
{
    protected static string $resource = IpRightResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
