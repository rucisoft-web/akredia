<?php

namespace App\Filament\App\Resources\Recognitions;

use App\Filament\App\Resources\Recognitions\Pages\ManageRecognitions;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Recognition;
use App\Enums\Level;
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

class RecognitionResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Recognition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = 'Rekognisi';

    protected static ?string $pluralModelLabel = 'Rekognisi Dosen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Rekognisi')->required()->columnSpanFull(),
            Select::make('lecturer_id')->label('Dosen')->options(fn () => \App\Models\Lecturer::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            Select::make('kind')->label('Jenis')->options(\App\Support\Labels::RECOGNITIONS)->required(),
            Select::make('level')->label('Tingkat')->options(Level::class)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('title')->label('Rekognisi')->searchable()->wrap(),
                TextColumn::make('lecturer.name')->label('Dosen')->searchable(),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('kind')->label('Jenis')->formatStateUsing(fn ($state) => \App\Support\Labels::RECOGNITIONS[$state] ?? $state),
                TextColumn::make('level')->label('Tingkat')->badge(),
            ])
            ->filters([
                SelectFilter::make('level')->label('Tingkat')->options(Level::class)->multiple(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRecognitions::route('/')];
    }
}
