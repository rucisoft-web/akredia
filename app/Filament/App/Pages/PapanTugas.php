<?php

namespace App\Filament\App\Pages;

use App\Enums\TaskStatus;
use App\Filament\App\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\Unit;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Papan Kanban tugas & RTL: seret kartu antar kolom untuk mengubah status. */
class PapanTugas extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Papan Kanban';

    protected static ?string $title = 'Papan Kanban Tugas';

    protected static ?string $slug = 'papan-tugas';

    protected string $view = 'filament.pages.papan-tugas';

    public ?int $unit = null;

    public bool $mine = false;

    public function move(int $id, string $status): void
    {
        abort_unless(auth()->user()?->roleIn(filament()->getTenant())?->canWrite(), 403);
        $task = Task::query()->findOrFail($id);
        $task->update(['status' => TaskStatus::from($status)]);
    }

    protected function getViewData(): array
    {
        $tasks = Task::query()->with(['assignee', 'unit', 'workItem.subIndicator'])
            ->when($this->unit, fn ($q) => $q->where('unit_id', $this->unit))
            ->when($this->mine, fn ($q) => $q->where('assignee_id', auth()->id()))
            ->where(fn ($q) => $q->where('status', '!=', 'done')->orWhere('completed_at', '>=', now()->subDays(14)))
            ->where('status', '!=', 'cancelled')
            ->orderByRaw('due_date is null')->orderBy('due_date')->get();

        return [
            'columns' => [
                'todo' => ['Belum Dikerjakan', $tasks->where('status', TaskStatus::Todo)],
                'progress' => ['Dikerjakan', $tasks->where('status', TaskStatus::Progress)],
                'done' => ['Selesai (14 hari terakhir)', $tasks->where('status', TaskStatus::Done)],
            ],
            'units' => Unit::query()->orderBy('name')->pluck('name', 'id'),
            'listUrl' => TaskResource::getUrl('index'),
        ];
    }
}
