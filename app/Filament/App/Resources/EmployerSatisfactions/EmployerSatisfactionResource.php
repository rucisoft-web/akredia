<?php

namespace App\Filament\App\Resources\EmployerSatisfactions;

use App\Filament\App\Resources\EmployerSatisfactions\Pages\ManageEmployerSatisfactions;
use App\Filament\Concerns\WriterPermissions;
use App\Models\EmployerSatisfaction;
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

class EmployerSatisfactionResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = EmployerSatisfaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandThumbUp;

    protected static string|UnitEnum|null $navigationGroup = 'Survei & Tracer';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Kepuasan pengguna';

    protected static ?string $pluralModelLabel = 'Kepuasan Pengguna Lulusan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            Select::make('aspect')->label('Aspek')->options(\App\Models\EmployerSatisfaction::ASPECTS)->required(),
            TextInput::make('sb')->label('Sangat Baik')->numeric()->default(0),
            TextInput::make('b')->label('Baik')->numeric()->default(0),
            TextInput::make('c')->label('Cukup')->numeric()->default(0),
            TextInput::make('kb')->label('Kurang')->numeric()->default(0),
            Textarea::make('follow_up')->label('Rencana tindak lanjut')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('aspect')->label('Aspek')->formatStateUsing(fn ($state) => \App\Models\EmployerSatisfaction::ASPECTS[$state] ?? $state)->wrap(),
                TextColumn::make('sb')->label('SB'),
                TextColumn::make('b')->label('B'),
                TextColumn::make('c')->label('C'),
                TextColumn::make('kb')->label('KB'),
                TextColumn::make('pct')->label('SB + B')->state(fn ($record) => $record->total() ? round(($record->sb + $record->b) / $record->total() * 100, 1) : 0)->suffix('%')->weight('bold'),
            ])
            ->filters([
                SelectFilter::make('year')->label('Tahun')->options(fn () => \App\Models\EmployerSatisfaction::query()->distinct()->orderByDesc('year')->pluck('year', 'year')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEmployerSatisfactions::route('/')];
    }
}
