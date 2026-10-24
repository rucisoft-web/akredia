<?php

namespace App\Filament\App\Resources\Achievements;

use App\Filament\App\Resources\Achievements\Pages\ManageAchievements;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Achievement;
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

class AchievementResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Achievement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Prestasi mahasiswa';

    protected static ?string $pluralModelLabel = 'Prestasi Mahasiswa';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Nama kegiatan / prestasi')->required()->columnSpanFull(),
            Select::make('study_program_id')->label('Program studi')->options(fn () => \App\Models\StudyProgram::query()->orderBy('level')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->label()]))->searchable(),
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            Select::make('level')->label('Tingkat')->options(Level::class)->required(),
            Select::make('rank')->label('Capaian')->options(['1' => 'Juara 1', '2' => 'Juara 2', '3' => 'Juara 3', 'lainnya' => 'Lainnya / finalis'])->required(),
            Select::make('kind')->label('Bidang')->options(['akademik' => 'Akademik', 'nonakademik' => 'Non-akademik'])->default('akademik'),
            TextInput::make('student_count')->label('Jumlah mahasiswa')->numeric()->default(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('title')->label('Prestasi')->searchable()->wrap(),
                TextColumn::make('studyProgram.name')->label('Prodi')->toggleable(),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('level')->label('Tingkat')->badge(),
                TextColumn::make('rank')->label('Capaian')->formatStateUsing(fn ($state) => is_numeric($state) ? 'Juara '.$state : 'Lainnya'),
                TextColumn::make('student_count')->label('Mhs'),
            ])
            ->filters([
                SelectFilter::make('year')->label('Tahun')->options(fn () => \App\Models\Achievement::query()->distinct()->orderByDesc('year')->pluck('year', 'year')),
                SelectFilter::make('level')->label('Tingkat')->options(Level::class)->multiple(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAchievements::route('/')];
    }
}
