<?php

namespace App\Filament\App\Resources\MetricValues\Pages;

use App\Filament\App\Resources\MetricValues\MetricValueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMetricValues extends ManageRecords
{
    use \App\Filament\Concerns\InteractsWithTenant;

    protected static string $resource = MetricValueResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\Action::make('compute')->label('Hitung ulang dari Data Kinerja')->icon('heroicon-o-arrow-path')->visible(fn () => static::canWrite() && static::activeProject())->action(function () { try { app(\App\Services\MetricEngine::class)->compute(static::activeProject(), auth()->id()); app(\App\Services\ReadinessCalculator::class)->refresh(static::activeProject()); \Filament\Notifications\Notification::make()->success()->title('Metrik dihitung ulang')->send(); } catch (\LogicException $e) { \Filament\Notifications\Notification::make()->danger()->title($e->getMessage())->send(); } })];
    }
}
