<?php

namespace App\Filament\App\Pages;

use App\Services\DataImporter;
use App\Services\ReconciliationEngine;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

/** Impor Data Kinerja dan rekap PD Dikti dari XLSX/CSV dengan templat kolom tetap. */
class ImporData extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpOnSquareStack;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 80;

    protected static ?string $navigationLabel = 'Impor Data';

    protected static ?string $title = 'Impor Data Kinerja & PD Dikti';

    protected static ?string $slug = 'impor-data';

    protected string $view = 'filament.pages.impor-data';

    public ?array $data = [];

    public ?array $result = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->roleIn(filament()->getTenant())?->canWrite();
    }

    public static function linkAction(string $type): Action
    {
        return Action::make('impor_'.$type)->label('Impor XLSX')->icon(Heroicon::OutlinedArrowUpTray)->color('gray')
            ->visible(fn () => static::canAccess())
            ->url(fn () => static::getUrl(['type' => $type]));
    }

    public function mount(): void
    {
        $type = request()->query('type');
        $this->form->fill(['type' => array_key_exists((string) $type, DataImporter::TYPES) ? $type : 'dosen']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Select::make('type')->label('Jenis data')->required()->live()
                ->options(collect(DataImporter::TYPES)->map(fn ($t) => $t[0]))
                ->helperText(fn ($get) => ($t = DataImporter::TYPES[$get('type')] ?? null) ? 'Kolom: '.implode(', ', $t[1]).'. '.$t[2] : null),
            FileUpload::make('file')->label('Berkas XLSX atau CSV')->required()->disk('local')->directory('impor')
                ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain', 'application/vnd.ms-excel'])
                ->maxSize(10240)->preserveFilenames(),
        ]);
    }

    public function import(): void
    {
        $state = $this->form->getState();
        $file = is_array($state['file']) ? array_values($state['file'])[0] : $state['file'];
        $path = Storage::disk('local')->path($file);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if ($state['type'] === 'pddikti') {
            $project = filament()->getTenant()->activeProject();

            if (! $project) {
                Notification::make()->danger()->title('Buat proyek akreditasi terlebih dahulu')->send();

                return;
            }

            $this->result = app(ReconciliationEngine::class)->import($project, $path, $ext);
        } else {
            $this->result = app(DataImporter::class)->import($state['type'], $path, $ext);
        }

        Storage::disk('local')->delete($file);
        Notification::make()->success()->title('Impor selesai')
            ->body("{$this->result['created']} baris baru, {$this->result['updated']} diperbarui, ".count($this->result['errors']).' galat.')->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('template')->label('Unduh templat')->icon(Heroicon::OutlinedDocumentArrowDown)->color('gray')
                ->action(function () {
                    $type = $this->data['type'] ?? 'dosen';
                    $path = tempnam(sys_get_temp_dir(), 'tpl').'.xlsx';
                    app(DataImporter::class)->template($type, $path);

                    return response()->download($path, 'templat-'.$type.'.xlsx')->deleteFileAfterSend();
                }),
        ];
    }
}
