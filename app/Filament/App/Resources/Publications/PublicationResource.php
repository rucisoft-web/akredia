<?php

namespace App\Filament\App\Resources\Publications;

use App\Filament\App\Resources\Publications\Pages\ManagePublications;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Publication;
use App\Enums\PublicationType;
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

class PublicationResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Publication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'Publikasi';

    protected static ?string $pluralModelLabel = 'Publikasi Dosen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Judul')->required()->columnSpanFull(),
            Select::make('lecturer_id')->label('Dosen')->options(fn () => \App\Models\Lecturer::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            Select::make('type')->label('Jenis')->options(PublicationType::class)->required(),
            Select::make('category')->label('Sumber')->options(['penelitian' => 'Penelitian', 'pkm' => 'PkM'])->default('penelitian'),
            TextInput::make('venue')->label('Jurnal / seminar'),
            TextInput::make('citations')->label('Sitasi')->numeric()->default(0),
            TextInput::make('url')->label('URL')->url(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->wrap()->limit(90),
                TextColumn::make('lecturer.name')->label('Dosen')->searchable()->toggleable(),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('type')->label('Jenis')->badge(),
                TextColumn::make('citations')->label('Sitasi')->sortable(),
            ])
            ->filters([
                SelectFilter::make('year')->label('Tahun')->options(fn () => \App\Models\Publication::query()->distinct()->orderByDesc('year')->pluck('year', 'year')),
                SelectFilter::make('type')->label('Jenis')->options(PublicationType::class)->multiple(),
                SelectFilter::make('category')->label('Sumber')->options(['penelitian' => 'Penelitian', 'pkm' => 'PkM']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePublications::route('/')];
    }
}
