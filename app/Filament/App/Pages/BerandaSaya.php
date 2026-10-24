<?php

namespace App\Filament\App\Pages;

use App\Enums\DocumentStatus;
use App\Enums\LedState;
use App\Filament\App\Resources\Documents\DocumentResource;
use App\Filament\App\Resources\LedSections\LedSectionResource;
use App\Filament\App\Resources\WorkItems\WorkItemResource;
use App\Models\Document;
use App\Models\LedSection;
use App\Models\Task;
use App\Models\WorkItem;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Beranda pribadi: butir yang saya pegang, tugas, LED yang menunggu reviu saya, bukti untuk divalidasi. */
class BerandaSaya extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Ringkasan';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Pekerjaan Saya';

    protected static ?string $title = 'Pekerjaan Saya';

    protected static ?string $slug = 'pekerjaan-saya';

    protected string $view = 'filament.pages.beranda-saya';

    protected function getViewData(): array
    {
        $uid = auth()->id();
        $project = filament()->getTenant()?->activeProject();
        $role = auth()->user()?->roleIn(filament()->getTenant());

        return [
            'role' => $role,
            'items' => WorkItem::query()->where('accreditation_project_id', $project?->id ?? 0)
                ->where(fn ($q) => $q->where('pic_id', $uid)->orWhere('second_pic_id', $uid))->with(['subIndicator', 'ledSection'])->get()->sortBy('subIndicator.sort'),
            'tasks' => Task::query()->open()->where('assignee_id', $uid)->orderByRaw('due_date is null')->orderBy('due_date')->limit(20)->get(),
            'reviews' => LedSection::query()->where('state', LedState::Review)->whereHas('workItem', fn ($q) => $q->where('reviewer_id', $uid)->where('accreditation_project_id', $project?->id ?? 0))->with('workItem.subIndicator')->get(),
            'validations' => $role?->canReview() ? Document::query()->where('status', DocumentStatus::Submitted)->where('uploaded_by', '!=', $uid)->latest()->limit(15)->get() : collect(),
            'urls' => [
                'item' => fn ($w) => WorkItemResource::getUrl('view', ['record' => $w]),
                'led' => fn ($s) => LedSectionResource::getUrl('editor', ['record' => $s]),
                'doc' => fn ($d) => DocumentResource::getUrl('view', ['record' => $d]),
                'tasks' => \App\Filament\App\Pages\PapanTugas::getUrl(),
            ],
        ];
    }
}
