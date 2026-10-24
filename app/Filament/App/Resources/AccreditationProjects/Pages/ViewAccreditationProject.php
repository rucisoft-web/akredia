<?php

namespace App\Filament\App\Resources\AccreditationProjects\Pages;

use App\Enums\ProjectState;
use App\Filament\App\Resources\AccreditationProjects\AccreditationProjectResource;
use App\Models\AccreditationProject;
use App\Models\AuditLog;
use App\Services\MetricEngine;
use App\Services\ProjectWorkflow;
use App\Services\ReadinessCalculator;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use LogicException;

class ViewAccreditationProject extends ViewRecord
{
    protected static string $resource = AccreditationProjectResource::class;

    protected function role()
    {
        return auth()->user()?->roleIn(filament()->getTenant());
    }

    protected function attempt(callable $fn, string $success): void
    {
        try {
            $fn();
            Notification::make()->success()->title($success)->send();
        } catch (LogicException $e) {
            Notification::make()->danger()->title('Tidak dapat diproses')->body($e->getMessage())->persistent()->send();
        }

        $this->record->refresh();
    }

    protected function getHeaderActions(): array
    {
        $manage = fn () => (bool) $this->role()?->canManage();

        return [
            Action::make('compute')->label('Hitung ulang')->icon(Heroicon::OutlinedArrowPath)->color('gray')
                ->visible(fn () => (bool) $this->role()?->canWrite())
                ->action(fn (AccreditationProject $record) => $this->attempt(function () use ($record) {
                    app(MetricEngine::class)->compute($record, auth()->id());
                    app(ReadinessCalculator::class)->refresh($record);
                }, 'Metrik, proyeksi, AKI, dan warna heatmap diperbarui')),
            Action::make('transition')->label('Ubah status')->icon(Heroicon::OutlinedArrowRightCircle)
                ->visible(fn (AccreditationProject $record) => $manage() && app(ProjectWorkflow::class)->nextStates($record) !== [])
                ->modalDescription('Status mengikuti siklus Tabel 14. Siap Diajukan hanya terbuka bila semua syarat gerbang terpenuhi.')
                ->schema(fn (AccreditationProject $record) => [
                    Select::make('to')->label('Status tujuan')->required()
                        ->options(collect(app(ProjectWorkflow::class)->nextStates($record))->mapWithKeys(fn ($s) => [$s => ProjectState::from($s)->getLabel()])),
                    Textarea::make('reason')->label('Alasan / catatan')->helperText('Wajib bila kembali ke Penyusunan.'),
                ])
                ->action(fn (AccreditationProject $record, array $data) => $this->attempt(
                    fn () => app(ProjectWorkflow::class)->transition($record, ProjectState::from($data['to']), $data['reason'] ?? null),
                    'Status proyek diperbarui',
                )),
            ActionGroup::make([
                Action::make('freeze')->label('Kunci data TS (freeze)')->icon(Heroicon::OutlinedLockClosed)
                    ->visible(fn (AccreditationProject $record) => ! $record->isFrozen())
                    ->requiresConfirmation()
                    ->modalDescription('Nilai metrik dikunci dan snapshot data, variabel, serta versi bukti disimpan. Perubahan data setelahnya tidak memengaruhi angka di LED.')
                    ->action(fn (AccreditationProject $record) => $this->attempt(
                        fn () => app(ProjectWorkflow::class)->freeze($record, auth()->user(), app(MetricEngine::class)),
                        'Data TS dikunci',
                    )),
                Action::make('unfreeze')->label('Buka kunci data')->icon(Heroicon::OutlinedLockOpen)->color('danger')
                    ->visible(fn (AccreditationProject $record) => $record->isFrozen())
                    ->schema([Textarea::make('reason')->label('Alasan membuka kunci')->required()])
                    ->action(fn (AccreditationProject $record, array $data) => $this->attempt(
                        fn () => app(ProjectWorkflow::class)->unfreeze($record, $data['reason']),
                        'Kunci data dibuka; alasan tercatat di log audit',
                    )),
                Action::make('lock_track')->label('Kunci jalur target')->icon(Heroicon::OutlinedFlag)
                    ->visible(fn (AccreditationProject $record) => ! $record->track_locked_at)
                    ->requiresConfirmation()
                    ->modalDescription(fn (AccreditationProject $record) => 'Jalur '.$record->track->getLabel().' ditetapkan pimpinan dan tidak dapat diubah lagi dari formulir proyek.')
                    ->action(fn (AccreditationProject $record) => $this->attempt(function () use ($record) {
                        $record->forceFill(['track_locked_at' => now(), 'track_locked_by' => auth()->id()])->save();
                        AuditLog::record('kunci_jalur', $record, ['jalur' => $record->track->value]);
                    }, 'Jalur target dikunci')),
                Action::make('generate')->label('Bangkitkan butir yang belum ada')->icon(Heroicon::OutlinedSparkles)
                    ->action(fn (AccreditationProject $record) => $this->attempt(
                        fn () => app(ProjectWorkflow::class)->generate($record),
                        'Butir kerja diperiksa dan dilengkapi',
                    )),
                Action::make('duplicate')->label('Salin untuk siklus berikutnya')->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->schema([
                        TextInput::make('name')->label('Nama proyek baru')->required()->default(fn (AccreditationProject $record) => $record->name.' (salinan)'),
                        DatePicker::make('target_date')->label('Target pengajuan'),
                    ])
                    ->action(function (AccreditationProject $record, array $data) {
                        $copy = app(ProjectWorkflow::class)->duplicate($record, $data);
                        Notification::make()->success()->title('Proyek disalin beserta PIC dan bukti yang masih berlaku')->send();
                        $this->redirect(static::getResource()::getUrl('view', ['record' => $copy]));
                    }),
            ])->label('Lainnya')->icon(Heroicon::OutlinedEllipsisVertical)->button()->color('gray')->visible($manage),
            EditAction::make(),
        ];
    }
}
