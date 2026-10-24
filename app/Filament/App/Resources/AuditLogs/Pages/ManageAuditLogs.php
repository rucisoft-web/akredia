<?php

namespace App\Filament\App\Resources\AuditLogs\Pages;

use App\Filament\App\Resources\AuditLogs\AuditLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAuditLogs extends ManageRecords
{
    use \App\Filament\Concerns\InteractsWithTenant;

    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\Action::make('csv')->label('Ekspor CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')->url(fn () => route('laporan.audit', ['institution' => static::tenantInstitution()]))];
    }
}
