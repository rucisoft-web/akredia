<?php

namespace App\Filament\Platform\Resources\InstrumentVariants\Pages;

use App\Filament\Platform\Resources\InstrumentVariants\InstrumentVariantResource;
use App\Services\InstrumentImporter;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;

class ManageInstrumentVariants extends ManageRecords
{
    protected static string $resource = InstrumentVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')->label('Impor templat XLSX')->icon('heroicon-o-arrow-up-tray')
                ->modalDescription('Gunakan berkas hasil "Ekspor XLSX" sebagai templat. Versi/varian baru dibuat otomatis; sub-indikator yang sudah ada diperbarui.')
                ->schema([FileUpload::make('file')->label('Berkas XLSX')->disk('local')->directory('impor')->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->required()])
                ->action(function (array $data) {
                    try {
                        $result = app(InstrumentImporter::class)->import(Storage::disk('local')->path($data['file']));
                        Notification::make()->success()->title("{$result['rows']} baris diimpor")
                            ->body($result['errors'] ? count($result['errors']).' baris dilewati: '.collect($result['errors'])->map(fn ($e, $n) => "baris {$n}: {$e}")->take(5)->implode('; ') : null)->send();
                    } catch (\Throwable $e) {
                        Notification::make()->danger()->title('Impor gagal')->body($e->getMessage())->send();
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }
                }),
        ];
    }
}
