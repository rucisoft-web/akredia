<?php

namespace App\Filament\App\Resources\StudentCohorts;

use App\Filament\App\Resources\StudentCohorts\Pages\ManageStudentCohorts;
use App\Filament\Concerns\WriterPermissions;
use App\Models\StudentCohort;
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

class StudentCohortResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = StudentCohort::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 35;

    protected static ?string $modelLabel = 'Kohort';

    protected static ?string $pluralModelLabel = 'Kohort Mahasiswa';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('study_program_id')->label('Program studi')->options(fn () => \App\Models\StudyProgram::query()->orderBy('level')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->label()]))->searchable()->required(),
            TextInput::make('intake_year')->label('Tahun masuk')->numeric()->required(),
            TextInput::make('intake_count')->label('Jumlah masuk')->numeric()->required(),
            TextInput::make('graduated_on_time')->label('Lulus tepat masa tempuh')->numeric()->default(0),
            TextInput::make('graduated_within_double')->label('Lulus ≤ 2× masa tempuh')->numeric()->default(0),
            TextInput::make('still_active')->label('Masih aktif')->numeric()->default(0),
            TextInput::make('dropped_out')->label('Mengundurkan diri / DO')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('intake_year', 'desc')
            ->columns([
                TextColumn::make('studyProgram.name')->label('Program studi')->searchable()->description(fn ($record) => $record->studyProgram?->level),
                TextColumn::make('intake_year')->label('Angkatan')->sortable(),
                TextColumn::make('intake_count')->label('Masuk')->numeric(locale: 'id'),
                TextColumn::make('graduated_on_time')->label('Tepat waktu')->numeric(locale: 'id')
                    ->description(fn ($record) => $record->intake_count ? number_format($record->graduated_on_time / $record->intake_count * 100, 1, ',', '.').'%' : null),
                TextColumn::make('graduated_within_double')->label('≤ 2× masa tempuh')->numeric(locale: 'id')
                    ->description(fn ($record) => $record->intake_count ? number_format($record->graduated_within_double / $record->intake_count * 100, 1, ',', '.').'%' : null),
                TextColumn::make('still_active')->label('Aktif')->numeric(locale: 'id'),
                TextColumn::make('dropped_out')->label('DO')->numeric(locale: 'id'),
            ])
            ->filters([
                SelectFilter::make('intake_year')->label('Angkatan')->options(fn () => \App\Models\StudentCohort::query()->distinct()->orderByDesc('intake_year')->pluck('intake_year', 'intake_year')),
                SelectFilter::make('study_program_id')->label('Program studi')->options(fn () => \App\Models\StudyProgram::query()->orderBy('level')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->label()]))->searchable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageStudentCohorts::route('/')];
    }
}
