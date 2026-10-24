<?php

namespace App\Filament\App\Resources\TracerStudys;

use App\Filament\App\Resources\TracerStudys\Pages\ManageTracerStudys;
use App\Filament\Concerns\WriterPermissions;
use App\Models\TracerStudy;
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

class TracerStudyResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = TracerStudy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Survei & Tracer';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Tracer study';

    protected static ?string $pluralModelLabel = 'Tracer Study';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('graduation_year')->label('Tahun lulus')->numeric()->required(),
            TextInput::make('graduates')->label('Jumlah lulusan')->numeric()->required(),
            TextInput::make('respondents')->label('Responden')->numeric()->required(),
            TextInput::make('employed')->label('Bekerja')->numeric()->default(0),
            TextInput::make('further_study')->label('Studi lanjut')->numeric()->default(0),
            TextInput::make('entrepreneur')->label('Berwirausaha')->numeric()->default(0),
            TextInput::make('within_one_year')->label('Terserap ≤ 1 tahun')->numeric()->default(0),
            TextInput::make('avg_wait_months')->label('Rerata waktu tunggu (bulan)')->numeric(),
            TextInput::make('global_work')->label('Bekerja di tingkat global')->numeric()->default(0),
            TextInput::make('certified')->label('Bersertifikat kompetensi')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('graduation_year', 'desc')
            ->columns([
                TextColumn::make('graduation_year')->label('Tahun lulus')->sortable()->weight('bold'),
                TextColumn::make('graduates')->label('Lulusan')->numeric(locale: 'id'),
                TextColumn::make('respondents')->label('Responden')->numeric(locale: 'id')->description(fn ($record) => $record->graduates ? round($record->respondents / $record->graduates * 100).'%' : null),
                TextColumn::make('employed')->label('Bekerja'),
                TextColumn::make('further_study')->label('Studi lanjut'),
                TextColumn::make('entrepreneur')->label('Wirausaha'),
                TextColumn::make('pllk')->label('PLLK')->state(fn ($record) => $record->pllk())->suffix('%')->weight('bold')->color(fn ($state) => $state >= 20 ? 'success' : 'danger'),
                TextColumn::make('avg_wait_months')->label('Waktu tunggu')->suffix(' bln'),
            ])
            ->filters([

            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTracerStudys::route('/')];
    }
}
