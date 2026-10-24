<?php

namespace App\Filament\App\Resources\Lecturers;

use App\Filament\App\Resources\Lecturers\Pages\ManageLecturers;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Lecturer;
use App\Enums\AcademicRank;
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

class LecturerResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Lecturer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Dosen';

    protected static ?string $pluralModelLabel = 'Dosen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama dosen')->required()->maxLength(255),
            TextInput::make('nidn')->label('NIDN / NUPTK')->maxLength(20),
            Select::make('study_program_id')->label('Homebase')->options(fn () => \App\Models\StudyProgram::query()->orderBy('level')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->label()]))->searchable(),
            Select::make('employment')->label('Status')->options(['tetap' => 'Dosen tetap', 'tidak_tetap' => 'Dosen tidak tetap'])->default('tetap')->required(),
            Select::make('highest_degree')->label('Pendidikan tertinggi')->options(['S1' => 'S1', 'S2' => 'S2 / Magister', 'S3' => 'S3 / Doktor', 'Sp' => 'Spesialis'])->default('S2')->required(),
            Select::make('academic_rank')->label('Jabatan akademik')->options(AcademicRank::class)->default('none')->required(),
            Toggle::make('is_certified')->label('Bersertifikat pendidik'),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->weight('bold')->description(fn ($record) => $record->nidn),
                TextColumn::make('studyProgram.name')->label('Homebase')->searchable()->toggleable(),
                TextColumn::make('employment')->label('Status')->badge()->formatStateUsing(fn ($state) => $state === 'tetap' ? 'Tetap' : 'Tidak tetap')->color(fn ($state) => $state === 'tetap' ? 'primary' : 'gray'),
                TextColumn::make('highest_degree')->label('Pendidikan')->badge()->color(fn ($state) => $state === 'S3' ? 'success' : 'gray'),
                TextColumn::make('academic_rank')->label('Jabatan akademik')->badge(),
                IconColumn::make('is_certified')->label('Serdos')->boolean()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('study_program_id')->label('Homebase')->options(fn () => \App\Models\StudyProgram::query()->orderBy('level')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->label()]))->searchable(),
                SelectFilter::make('employment')->label('Status')->options(['tetap' => 'Dosen tetap', 'tidak_tetap' => 'Dosen tidak tetap']),
                SelectFilter::make('academic_rank')->label('Jabatan')->options(AcademicRank::class)->multiple(),
                SelectFilter::make('highest_degree')->label('Pendidikan')->options(['S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageLecturers::route('/')];
    }
}
