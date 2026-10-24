<?php

namespace App\Filament\App\Resources\Units;

use App\Filament\App\Resources\Units\Pages\ManageUnits;
use App\Filament\Concerns\ManagerPermissions;
use App\Models\Unit;
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

class UnitResource extends Resource
{
    use ManagerPermissions;

    protected static ?string $model = Unit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Institusi';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Unit kerja';

    protected static ?string $pluralModelLabel = 'Unit Kerja';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama unit')->required()->maxLength(255),
            TextInput::make('code')->label('Kode')->maxLength(30),
            Select::make('type')->label('Jenis')->options(\App\Support\Labels::UNIT_TYPES)->default('biro')->required(),
            Select::make('parent_id')->label('Unit induk')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            TextInput::make('head_name')->label('Kepala unit')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable(),
                TextColumn::make('name')->label('Unit')->searchable()->weight('bold'),
                TextColumn::make('type')->label('Jenis')->badge()->formatStateUsing(fn ($state) => \App\Support\Labels::UNIT_TYPES[$state] ?? $state),
                TextColumn::make('parent.name')->label('Induk')->placeholder('—'),
                TextColumn::make('head_name')->label('Kepala')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis')->options(\App\Support\Labels::UNIT_TYPES),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUnits::route('/')];
    }
}
