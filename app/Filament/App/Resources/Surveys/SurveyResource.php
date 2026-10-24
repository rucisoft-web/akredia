<?php

namespace App\Filament\App\Resources\Surveys;

use App\Filament\App\Resources\Surveys\Pages\ManageSurveys;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Survey;
use App\Enums\Stakeholder;
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

class SurveyResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Survey::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Survei & Tracer';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Survei kepuasan';

    protected static ?string $pluralModelLabel = 'Survei Kepuasan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Judul survei')->required()->columnSpanFull(),
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            Select::make('stakeholder')->label('Kelompok')->options(Stakeholder::class)->required(),
            TextInput::make('population')->label('Populasi')->numeric()->default(0),
            TextInput::make('respondents')->label('Responden')->numeric()->default(0),
            TextInput::make('sb')->label('Jawaban Sangat Baik')->numeric()->default(0),
            TextInput::make('b')->label('Jawaban Baik')->numeric()->default(0),
            TextInput::make('c')->label('Jawaban Cukup')->numeric()->default(0),
            TextInput::make('kb')->label('Jawaban Kurang Baik')->numeric()->default(0),
            Toggle::make('is_open')->label('Kuesioner publik dibuka')->helperText('Tautan publik dapat dibagikan ke responden; jawaban otomatis masuk ke rekap.'),
            Textarea::make('follow_up')->label('Tindak lanjut hasil survei')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('stakeholder')->label('Kelompok')->badge(),
                TextColumn::make('title')->label('Survei')->wrap()->toggleable(),
                TextColumn::make('respondents')->label('Responden')->numeric(locale: 'id')->description(fn ($record) => $record->population ? $record->responseRate().'% populasi' : null),
                TextColumn::make('satisfaction')->label('SB + B')->state(fn ($record) => $record->satisfaction())->suffix('%')
                    ->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 60 ? 'warning' : 'danger'))->weight('bold'),
                TextColumn::make('index')->label('Indeks (1–4)')->state(fn ($record) => $record->index()),
                IconColumn::make('is_open')->label('Publik')->boolean(),
            ])
            ->filters([
                SelectFilter::make('year')->label('Tahun')->options(fn () => \App\Models\Survey::query()->distinct()->orderByDesc('year')->pluck('year', 'year')),
                SelectFilter::make('stakeholder')->label('Kelompok')->options(Stakeholder::class)->multiple(),
            ])
            ->recordActions([Action::make('link')->label('Tautan publik')->icon(Heroicon::OutlinedLink)->color('gray')->visible(fn ($record) => $record->is_open)->url(fn ($record) => route('survei.show', $record->public_token), shouldOpenInNewTab: true), EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSurveys::route('/')];
    }
}
