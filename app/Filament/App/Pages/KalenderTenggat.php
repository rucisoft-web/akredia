<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\Documents\DocumentResource;
use App\Filament\App\Resources\Tasks\TaskResource;
use App\Filament\App\Resources\WorkItems\WorkItemResource;
use App\Models\Document;
use App\Models\Task;
use App\Models\WorkItem;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Kalender tenggat tugas, butir kerja, dan masa berlaku bukti. */
class KalenderTenggat extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'Kalender Tenggat';

    protected static ?string $title = 'Kalender Tenggat';

    protected static ?string $slug = 'kalender-tenggat';

    protected string $view = 'filament.pages.kalender-tenggat';

    public string $month = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function shift(int $n): void
    {
        $this->month = CarbonImmutable::createFromFormat('Y-m-d', $this->month.'-01')->addMonths($n)->format('Y-m');
    }

    protected function getViewData(): array
    {
        $first = CarbonImmutable::createFromFormat('Y-m-d', $this->month.'-01')->startOfDay();
        $start = $first->startOfWeek(CarbonImmutable::MONDAY);
        $end = $first->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY);
        $events = collect();

        foreach (Task::query()->whereBetween('due_date', [$start, $end])->where('status', '!=', 'cancelled')->get() as $t) {
            $events->push(['date' => $t->due_date->toDateString(), 'label' => $t->title, 'class' => $t->status->value === 'done' ? 'done' : ($t->isOverdue() ? 'overdue' : ''), 'url' => TaskResource::getUrl('index', ['tableSearch' => $t->number])]);
        }

        $project = filament()->getTenant()?->activeProject();

        foreach (WorkItem::query()->where('accreditation_project_id', $project?->id ?? 0)->whereBetween('due_date', [$start, $end])->with('subIndicator')->get() as $w) {
            $events->push(['date' => $w->due_date->toDateString(), 'label' => 'Butir '.$w->subIndicator->code, 'class' => 'item', 'url' => WorkItemResource::getUrl('view', ['record' => $w])]);
        }

        foreach (Document::query()->whereBetween('valid_until', [$start, $end])->get() as $d) {
            $events->push(['date' => $d->valid_until->toDateString(), 'label' => 'Kedaluwarsa: '.$d->title, 'class' => 'doc', 'url' => DocumentResource::getUrl('view', ['record' => $d])]);
        }

        $days = [];

        for ($d = $start; $d->lte($end); $d = $d->addDay()) {
            $days[] = ['date' => $d, 'out' => $d->month !== $first->month, 'events' => $events->where('date', $d->toDateString())->values()];
        }

        return ['days' => $days, 'label' => $first->translatedFormat('F Y')];
    }
}
