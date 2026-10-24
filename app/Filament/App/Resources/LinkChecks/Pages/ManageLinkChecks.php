<?php

namespace App\Filament\App\Resources\LinkChecks\Pages;

use App\Filament\App\Resources\LinkChecks\LinkCheckResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLinkChecks extends ManageRecords
{
    use \App\Filament\Concerns\InteractsWithTenant;

    protected static string $resource = LinkCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\Action::make('check_all')->label('Periksa semua tautan')->icon('heroicon-o-arrow-path')->action(function () { $r = app(\App\Services\EvidenceService::class)->checkAll(static::tenantInstitution()?->id); \Filament\Notifications\Notification::make()->success()->title('Pemeriksaan selesai')->body(($r['checked'] ?? 0).' tautan diperiksa, '.($r['broken'] ?? 0).' rusak.')->send(); })];
    }
}
