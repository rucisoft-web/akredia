<?php

namespace App\Filament\App\Resources\StudyPrograms;

use App\Filament\App\Resources\StudyPrograms\Pages\ManageStudyPrograms;
use App\Filament\Concerns\ManagerPermissions;
use App\Models\StudyProgram;
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

class StudyProgramResource extends Resource
{
    use ManagerPermissions;

    protected static ?string $model = StudyProgram::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Institusi';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Program studi';

    protected static ?string $pluralModelLabel = 'Program Studi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama program studi')->required(),
            Select::make('level')->label('Jenjang')->options(\App\Support\Labels::LEVELS)->required(),
            TextInput::make('code_pddikti')->label('Kode PD Dikti'),
            Select::make('unit_id')->label('Fakultas / unit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            TextInput::make('opened_year')->label('Tahun dibuka')->numeric(),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('code_pddikti')->label('Kode')->searchable()->toggleable(),
                TextColumn::make('name')->label('Program studi')->searchable()->weight('bold')->description(fn ($record) => $record->unit?->name),
                TextColumn::make('level')->label('Jenjang')->badge(),
                TextColumn::make('currentAccreditation.rank')->label('Akreditasi')->badge()->placeholder('Belum ada'),
                TextColumn::make('currentAccreditation.agency')->label('Lembaga')->toggleable(),
                TextColumn::make('currentAccreditation.valid_until')->label('Berlaku sampai')->date('d/m/Y')
                    ->color(fn ($record) => ($m = $record->currentAccreditation?->monthsLeft()) === null ? null : ($m <= 6 ? 'danger' : ($m <= 18 ? 'warning' : null))),
                TextColumn::make('lecturers_count')->label('Dosen tetap')->counts(['lecturers' => fn ($q) => $q->where('employment', 'tetap')->where('is_active', true)])
                    ->color(fn ($state) => $state < 5 ? 'danger' : null),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('level')->label('Jenjang')->options(\App\Support\Labels::LEVELS)->multiple(),
                SelectFilter::make('unit_id')->label('Fakultas')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id')),
                TernaryFilter::make('is_active')->label('Aktif')->default(true),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['currentAccreditation', 'unit']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageStudyPrograms::route('/')];
    }
}
