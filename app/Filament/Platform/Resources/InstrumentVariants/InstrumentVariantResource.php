<?php

namespace App\Filament\Platform\Resources\InstrumentVariants;

use App\Filament\Platform\Resources\InstrumentVariants\Pages\ManageInstrumentVariants;
use App\Filament\Platform\Resources\InstrumentVariants\Pages\ViewInstrumentVariant;
use App\Filament\Platform\Resources\InstrumentVariants\RelationManagers\SubIndicatorsRelationManager;
use App\Models\InstrumentVariant;
use App\Services\InstrumentImporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Pustaka Instrumen: Buku 3 IAPT per varian jenis PT. */
class InstrumentVariantResource extends Resource
{
    protected static ?string $model = InstrumentVariant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Pustaka Instrumen';

    protected static ?string $modelLabel = 'Varian instrumen';

    protected static ?string $pluralModelLabel = 'Pustaka Instrumen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama varian')->required(),
            TextInput::make('threshold_accredited')->label('Ambang skor Terakreditasi')->numeric()->required(),
            TextInput::make('threshold_excellent')->label('Ambang skor Unggul')->numeric()->required(),
            TextInput::make('source_note')->label('Catatan sumber')->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(4)->components([
            TextEntry::make('version.code')->label('Versi'),
            TextEntry::make('code')->label('Kode varian'),
            TextEntry::make('name')->label('Jenis PT'),
            TextEntry::make('sub_indicators_count')->label('Sub-indikator')->state(fn (InstrumentVariant $r) => $r->subIndicators()->count()),
            TextEntry::make('threshold_accredited')->label('Ambang Terakreditasi'),
            TextEntry::make('threshold_excellent')->label('Ambang Unggul'),
            TextEntry::make('version.regulation')->label('Dasar hukum')->columnSpan(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->columns([
                TextColumn::make('version.code')->label('Versi')->badge(),
                TextColumn::make('code')->label('Kode')->weight('bold'),
                TextColumn::make('name')->label('Jenis PT')->searchable(),
                TextColumn::make('sub_indicators_count')->label('Sub-indikator')->counts('subIndicators'),
                TextColumn::make('threshold_accredited')->label('Ambang Terakreditasi'),
                TextColumn::make('threshold_excellent')->label('Ambang Unggul'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('export')->label('Ekspor XLSX')->icon(Heroicon::OutlinedArrowDownTray)->color('gray')
                    ->action(function (InstrumentVariant $record) {
                        $path = storage_path('app/private/instrumen-'.$record->code.'.xlsx');
                        @mkdir(dirname($path), 0775, true);
                        app(InstrumentImporter::class)->export($record, $path);

                        return response()->download($path, 'Instrumen-IAPT-'.$record->code.'.xlsx')->deleteFileAfterSend();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [SubIndicatorsRelationManager::class];
    }

    public static function getPages(): array
    {
        return ['index' => ManageInstrumentVariants::route('/'), 'view' => ViewInstrumentVariant::route('/{record}')];
    }
}
