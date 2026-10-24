<?php

namespace App\Filament\App\Resources\WorkItems\Pages;

use App\Enums\WorkItemState;
use App\Filament\App\Resources\LedSections\LedSectionResource;
use App\Filament\App\Resources\WorkItems\WorkItemResource;
use App\Models\WorkItem;
use App\Services\ProjectWorkflow;
use App\Services\ReadinessCalculator;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use LogicException;

class ViewWorkItem extends ViewRecord
{
    protected static string $resource = WorkItemResource::class;

    public function getTitle(): string
    {
        return 'Sub-indikator '.$this->record->subIndicator->code;
    }

    public function getSubheading(): ?string
    {
        return 'Indikator '.$this->record->subIndicator->indicator->number.' · Kriteria '.$this->record->subIndicator->indicator->criterion->number.' '.$this->record->subIndicator->indicator->criterion->name;
    }


    protected function getHeaderActions(): array
    {
        $role = auth()->user()?->roleIn(filament()->getTenant());

        return [
            Action::make('led')->label('Buka editor LED')->icon(Heroicon::OutlinedPencilSquare)
                ->visible(fn (WorkItem $record) => (bool) $record->ledSection)
                ->url(fn (WorkItem $record) => LedSectionResource::getUrl('editor', ['record' => $record->ledSection])),
            Action::make('state')->label('Ubah status')->icon(Heroicon::OutlinedArrowRightCircle)->color('gray')
                ->visible(fn () => (bool) $role?->canWrite())
                ->schema([Select::make('state')->label('Status butir')->options(WorkItemState::class)->required()->default(fn (WorkItem $record) => $record->state->value)])
                ->action(function (WorkItem $record, array $data) {
                    try {
                        app(ProjectWorkflow::class)->setItemState($record, WorkItemState::from($data['state']));
                        Notification::make()->success()->title('Status butir diperbarui')->send();
                    } catch (LogicException $e) {
                        Notification::make()->danger()->title('Belum dapat disetujui')->body($e->getMessage())->send();
                    }
                }),
            Action::make('rtl')->label('Jadikan tugas RTL')->icon(Heroicon::OutlinedClipboardDocumentList)->color('gray')
                ->visible(fn () => (bool) $role?->canWrite())
                ->requiresConfirmation()
                ->modalDescription('Setiap kata kunci yang belum terbukti dan belum punya tugas akan menjadi tugas RTL untuk PIC kertas kerja.')
                ->action(function (WorkItem $record) {
                    $n = app(ProjectWorkflow::class)->createRtlTasks($record);
                    Notification::make()->success()->title($n ? "{$n} tugas RTL dibuat" : 'Tidak ada kata kunci yang perlu dijadikan tugas')->send();
                }),
            Action::make('recolor')->label('Perbarui warna')->icon(Heroicon::OutlinedArrowPath)->color('gray')
                ->action(function (WorkItem $record) {
                    app(ReadinessCalculator::class)->refresh($record->project);
                    $record->refresh();
                    Notification::make()->success()->title('Warna: '.$record->color->getLabel())->body($record->color_reason)->send();
                }),
            EditAction::make()->label('Tugaskan')->modalWidth('2xl')
                ->using(function (WorkItem $record, array $data) {
                    try {
                        $record->update($data);
                    } catch (LogicException $e) {
                        Notification::make()->danger()->title($e->getMessage())->send();
                    }

                    return $record;
                }),
        ];
    }
}
