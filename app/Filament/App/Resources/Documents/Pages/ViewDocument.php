<?php

namespace App\Filament\App\Resources\Documents\Pages;

use App\Enums\DocumentStatus;
use App\Filament\App\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Services\EvidenceService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\URL;
use LogicException;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

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
            Notification::make()->danger()->title('Tidak dapat diproses')->body($e->getMessage())->send();
        }

        $this->record->refresh();
    }

    protected function getHeaderActions(): array
    {
        $svc = app(EvidenceService::class);

        return [
            Action::make('download')->label('Unduh versi terbaru')->icon(Heroicon::OutlinedArrowDownTray)->color('gray')
                ->visible(fn (Document $record) => (bool) $record->latestVersion?->file_path)
                ->url(fn (Document $record) => URL::temporarySignedRoute('dokumen.unduh', now()->addMinutes(30), ['version' => $record->latestVersion->id]), true),
            Action::make('open_url')->label('Buka tautan')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                ->visible(fn (Document $record) => (bool) $record->latestVersion?->url)
                ->url(fn (Document $record) => $record->latestVersion->url, true),
            Action::make('version')->label('Versi baru')->icon(Heroicon::OutlinedArrowUpTray)->color('gray')
                ->visible(fn () => (bool) $this->role()?->canWrite())
                ->modalDescription('Versi lama tetap tersimpan. Dokumen yang sudah Valid kembali ke antrean validasi.')
                ->schema([
                    FileUpload::make('upload')->label('Berkas')->disk(EvidenceService::DISK)->directory('bukti/unggah')->visibility('private')
                        ->maxSize(fn () => (int) config('akredia.upload_max_kb', 20480))->preserveFilenames(),
                    TextInput::make('url')->label('Atau tautan (URL)')->url(),
                    TextInput::make('note')->label('Catatan perubahan'),
                ])
                ->action(function (Document $record, array $data) use ($svc) {
                    $upload = is_array($data['upload'] ?? null) ? (array_values($data['upload'])[0] ?? null) : ($data['upload'] ?? null);

                    if (! $upload && blank($data['url'] ?? null)) {
                        Notification::make()->warning()->title('Unggah berkas atau isi tautan')->send();

                        return;
                    }

                    $this->attempt(fn () => $svc->addVersion($record, $upload, $data['url'] ?: null, auth()->user(), $data['note'] ?? null), 'Versi baru tersimpan');
                }),
            Action::make('submit')->label('Ajukan validasi')->icon(Heroicon::OutlinedPaperAirplane)
                ->visible(fn (Document $record) => $this->role()?->canWrite() && in_array($record->status, [DocumentStatus::Draft, DocumentStatus::Rejected], true))
                ->action(fn (Document $record) => $this->attempt(fn () => $svc->submit($record), 'Diajukan ke antrean validasi')),
            Action::make('validate')->label('Validasi')->icon(Heroicon::OutlinedCheckBadge)->color('success')
                ->visible(fn (Document $record) => $this->role()?->canReview() && $record->status === DocumentStatus::Submitted)
                ->requiresConfirmation()
                ->modalDescription('Validator tidak boleh orang yang mengunggah dokumen (prinsip empat mata).')
                ->action(fn (Document $record) => $this->attempt(fn () => $svc->validate($record, auth()->user()), 'Dokumen dinyatakan Valid')),
            Action::make('reject')->label('Tolak')->icon(Heroicon::OutlinedXCircle)->color('danger')
                ->visible(fn (Document $record) => $this->role()?->canReview() && $record->status === DocumentStatus::Submitted)
                ->schema([Textarea::make('reason')->label('Alasan penolakan')->required()])
                ->action(fn (Document $record, array $data) => $this->attempt(fn () => $svc->reject($record, auth()->user(), $data['reason']), 'Dokumen ditolak dan dikembalikan ke pengunggah')),
            EditAction::make(),
        ];
    }
}
