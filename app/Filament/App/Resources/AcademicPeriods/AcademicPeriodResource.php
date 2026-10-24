<?php

namespace App\Filament\App\Resources\AcademicPeriods;

use App\Filament\App\Resources\AcademicPeriods\Pages\ManageAcademicPeriods;
use App\Filament\Concerns\ManagerPermissions;
use App\Models\AcademicPeriod;
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

class AcademicPeriodResource extends Resource
{
    use ManagerPermissions;

    protected static ?string $model = AcademicPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Institusi';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Periode akademik';

    protected static ?string $pluralModelLabel = 'Periode Akademik';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama periode')->placeholder('2025/2026')->required()->maxLength(30),
            TextInput::make('year_start')->label('Tahun awal (TS)')->numeric()->required(),
            DatePicker::make('starts_at')->label('Mulai'),
            DatePicker::make('ends_at')->label('Selesai'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year_start', 'desc')
            ->columns([
                TextColumn::make('name')->label('Periode')->weight('bold'),
                TextColumn::make('year_start')->label('Tahun awal')->sortable(),
                TextColumn::make('starts_at')->label('Mulai')->date('d/m/Y'),
                TextColumn::make('ends_at')->label('Selesai')->date('d/m/Y'),
            ])
            ->filters([

            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAcademicPeriods::route('/')];
    }
}
