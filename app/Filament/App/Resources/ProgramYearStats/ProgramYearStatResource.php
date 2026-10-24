<?php

namespace App\Filament\App\Resources\ProgramYearStats;

use App\Filament\App\Resources\ProgramYearStats\Pages\ManageProgramYearStats;
use App\Filament\Concerns\WriterPermissions;
use App\Models\ProgramYearStat;
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

class ProgramYearStatResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = ProgramYearStat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Statistik mahasiswa';

    protected static ?string $pluralModelLabel = 'Statistik Mahasiswa';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('study_program_id')->label('Program studi')->options(fn () => \App\Models\StudyProgram::query()->orderBy('level')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->label()]))->searchable()->required(),
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            TextInput::make('applicants')->label('Pendaftar')->numeric()->default(0),
            TextInput::make('new_students')->label('Mahasiswa baru')->numeric()->default(0),
            TextInput::make('active_students')->label('Mahasiswa aktif')->numeric()->default(0),
            TextInput::make('graduates')->label('Lulusan')->numeric()->default(0),
            TextInput::make('students_outside_10sks')->label('Mahasiswa ≥ 10 sks di luar prodi')->numeric()->default(0),
            TextInput::make('avg_study_months')->label('Rerata masa studi (bulan)')->numeric(),
            TextInput::make('avg_gpa')->label('Rerata IPK')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('studyProgram.name')->label('Program studi')->searchable()->description(fn ($record) => $record->studyProgram?->level),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('applicants')->label('Pendaftar')->numeric(locale: 'id')->toggleable(),
                TextColumn::make('new_students')->label('Mhs baru')->numeric(locale: 'id')->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('')),
                TextColumn::make('active_students')->label('Aktif')->numeric(locale: 'id')->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('')),
                TextColumn::make('graduates')->label('Lulusan')->numeric(locale: 'id')->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('')),
                TextColumn::make('students_outside_10sks')->label('≥10 sks luar prodi')->numeric(locale: 'id')->toggleable(),
                TextColumn::make('avg_study_months')->label('Masa studi')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('year')->label('Tahun')->options(fn () => \App\Models\ProgramYearStat::query()->distinct()->orderByDesc('year')->pluck('year', 'year')),
                SelectFilter::make('study_program_id')->label('Program studi')->options(fn () => \App\Models\StudyProgram::query()->orderBy('level')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->label()]))->searchable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageProgramYearStats::route('/')];
    }
}
