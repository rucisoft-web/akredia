<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\AccreditationProjects\AccreditationProjectResource;
use App\Filament\App\Resources\Documents\DocumentResource;
use App\Filament\App\Resources\LedSections\LedSectionResource;
use App\Filament\App\Resources\MetricValues\MetricValueResource;
use App\Filament\App\Resources\Reconciliations\ReconciliationResource;
use App\Filament\App\Resources\Risks\RiskResource;
use App\Filament\App\Resources\StudyPrograms\StudyProgramResource;
use App\Filament\App\Resources\Tasks\TaskResource;
use App\Filament\App\Resources\WorkItems\WorkItemResource;
use App\Services\ExecutiveDashboard;
use App\Services\MetricEngine;
use App\Services\ReadinessCalculator;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Dasbor eksekutif pimpinan: kesiapan akreditasi institusi dalam satu layar, setiap grafik dapat ditelusuri. */
class DasborEksekutif extends Page
{
    protected static string $routePath = '/';

    public static function getRoutePath(Panel $panel): string
    {
        return static::$routePath;
    }

    protected static ?int $navigationSort = -10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Ringkasan';

    protected static ?string $navigationLabel = 'Dasbor Eksekutif';

    protected static ?string $title = 'Dasbor Eksekutif';

    protected string $view = 'filament.pages.dasbor-eksekutif';

    public function getHeading(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')->label('Perbarui angka')->icon(Heroicon::OutlinedArrowPath)->color('gray')
                ->visible(fn () => (bool) auth()->user()?->roleIn(filament()->getTenant())?->canWrite() && filament()->getTenant()?->activeProject())
                ->action(function () {
                    $project = filament()->getTenant()->activeProject();

                    try {
                        if (! $project->isFrozen()) {
                            app(MetricEngine::class)->compute($project, auth()->id());
                        }

                        app(ReadinessCalculator::class)->refresh($project);
                        Notification::make()->success()->title('Angka kesiapan diperbarui')->send();
                    } catch (\LogicException $e) {
                        Notification::make()->danger()->title($e->getMessage())->send();
                    }
                }),
        ];
    }

    public function getViewData(): array
    {
        $tenant = filament()->getTenant();
        $project = $tenant?->activeProject();
        $data = app(ExecutiveDashboard::class)->data($project);
        $wi = fn (array $filters = [], ?string $tab = null) => WorkItemResource::getUrl('index', array_filter(['tab' => $tab, 'filters' => $filters ?: null]));

        return [
            'd' => $data,
            'institution' => $tenant,
            'links' => [
                'project' => $project ? AccreditationProjectResource::getUrl('view', ['record' => $project]) : AccreditationProjectResource::getUrl('create'),
                'items' => $wi(),
                'red' => $wi([], 'merah'),
                'green' => $wi([], 'hijau'),
                'unassigned' => $wi(['unassigned' => ['value' => 1]]),
                'prerequisite' => $wi(['prerequisite' => ['value' => 1]]),
                'indicator' => fn (int $n) => $wi(['indicator' => ['value' => $n]]),
                'criterion' => fn (int $id) => $wi(['criterion' => ['value' => $id]]),
                'item' => fn ($item) => WorkItemResource::getUrl('view', ['record' => $item]),
                'documents' => DocumentResource::getUrl('index'),
                'doc_status' => fn (string $s) => DocumentResource::getUrl('index', ['filters' => ['status' => ['values' => [$s]]]]),
                'doc_queue' => DocumentResource::getUrl('index', ['tab' => 'antre']),
                'doc_expiring' => DocumentResource::getUrl('index', ['filters' => ['expiring' => ['value' => 1]]]),
                'led' => LedSectionResource::getUrl('index'),
                'led_state' => fn (string $s) => $s === 'empty' ? LedSectionResource::getUrl('index') : LedSectionResource::getUrl('index', ['filters' => ['state' => ['values' => [$s]]]]),
                'tasks' => TaskResource::getUrl('index'),
                'tasks_overdue' => TaskResource::getUrl('index', ['filters' => ['overdue' => ['value' => 1]]]),
                'tasks_unit' => fn ($unitId) => TaskResource::getUrl('index', ['filters' => array_filter(['unit_id' => $unitId ? ['value' => $unitId] : null])]),
                'risk' => fn (int $p, int $i) => RiskResource::getUrl('index', ['filters' => ['probability' => ['value' => $p], 'impact' => ['value' => $i], 'state' => ['values' => ['open', 'mitigating']]]]),
                'risks' => RiskResource::getUrl('index'),
                'recon' => ReconciliationResource::getUrl('index', ['filters' => ['state' => ['values' => ['open', 'investigation', 'fixed']]]]),
                'metrics' => MetricValueResource::getUrl('index'),
                'programs' => StudyProgramResource::getUrl('index'),
                'guide' => PanduanMengenalAkredia::getUrl(),
            ],
        ];
    }
}
