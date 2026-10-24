<?php

namespace App\Filament\App\Resources\LinkChecks;

use App\Filament\App\Resources\LinkChecks\Pages\ManageLinkChecks;
use App\Filament\Concerns\ManagerPermissions;
use App\Models\LinkCheck;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LinkCheckResource extends Resource
{
    use ManagerPermissions;

    protected static ?string $model = LinkCheck::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|UnitEnum|null $navigationGroup = 'Pustaka Bukti';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Pemeriksaan tautan';

    protected static ?string $pluralModelLabel = 'Pemeriksa Tautan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('url')->label('URL')->url()->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('is_ok')
            ->columns([
                IconColumn::make('is_ok')->label('Status')->boolean(),
                TextColumn::make('url')->label('Tautan')->wrap()->url(fn ($record) => $record->url, true)->searchable(),
                TextColumn::make('document.code')->label('Bukti')->placeholder('—')->description(fn ($record) => $record->document?->title),
                TextColumn::make('last_status')->label('HTTP')->placeholder('—'),
                TextColumn::make('failures')->label('Gagal beruntun')->color(fn ($state) => $state > 0 ? 'danger' : null),
                TextColumn::make('last_error')->label('Galat')->wrap()->placeholder('—')->toggleable(),
                TextColumn::make('last_checked_at')->label('Diperiksa')->since()->placeholder('Belum'),
            ])
            ->filters([
                TernaryFilter::make('is_ok')->label('Tautan baik'),
            ])
            ->recordActions([Action::make('check')->label('Periksa')->icon(Heroicon::OutlinedArrowPath)->color('gray')->action(fn ($record) => app(\App\Services\EvidenceService::class)->check($record)), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('document');
    }

    public static function getPages(): array
    {
        return ['index' => ManageLinkChecks::route('/')];
    }
}
