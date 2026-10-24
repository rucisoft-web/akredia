<?php

namespace App\Filament\App\Resources\Risks;

use App\Filament\App\Resources\Risks\Pages\ManageRisks;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Risk;
use App\Enums\RiskState;
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

class RiskResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Risk::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Risiko';

    protected static ?string $pluralModelLabel = 'Register Risiko';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Risiko')->required()->columnSpanFull(),
            Select::make('indicator_id')->label('Indikator terdampak')->options(fn () => \App\Models\Indicator::query()->orderBy('number')->get()->mapWithKeys(fn ($i) => [$i->id => $i->label()]))->searchable(),
            Select::make('owner_id')->label('Pemilik risiko')->options(fn () => static::memberOptions())->searchable(),
            Select::make('probability')->label('Probabilitas (1–5)')->options(\App\Support\Labels::SCALE)->default(3)->required(),
            Select::make('impact')->label('Dampak (1–5)')->options(\App\Support\Labels::SCALE)->default(3)->required(),
            Select::make('state')->label('Status')->options(RiskState::class)->default('open')->required(),
            DatePicker::make('target_date')->label('Target mitigasi'),
            Textarea::make('description')->label('Uraian')->columnSpanFull(),
            Textarea::make('mitigation')->label('Mitigasi')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('score', 'desc')
            ->columns([
                TextColumn::make('title')->label('Risiko')->searchable()->wrap()->weight('bold'),
                TextColumn::make('indicator.number')->label('Ind.')->placeholder('—'),
                TextColumn::make('probability')->label('P'),
                TextColumn::make('impact')->label('D'),
                TextColumn::make('score')->label('Skor')->sortable()->weight('bold'),
                TextColumn::make('level')->label('Tingkat')->badge()->formatStateUsing(fn ($state) => \App\Models\Risk::LEVELS[$state][0] ?? $state)->color(fn ($state) => \App\Models\Risk::LEVELS[$state][1] ?? 'gray'),
                TextColumn::make('owner.name')->label('Pemilik')->placeholder('—')->toggleable(),
                TextColumn::make('target_date')->label('Target')->date('d/m/Y')->toggleable(),
                TextColumn::make('state')->label('Status')->badge(),
            ])
            ->filters([
                SelectFilter::make('state')->label('Status')->options(RiskState::class)->multiple(),
                SelectFilter::make('level')->label('Tingkat')->options(collect(\App\Models\Risk::LEVELS)->map(fn ($l) => $l[0])->all())->multiple(),
                SelectFilter::make('probability')->label('Probabilitas')->options(array_combine(range(1, 5), range(1, 5))),
                SelectFilter::make('impact')->label('Dampak')->options(array_combine(range(1, 5), range(1, 5))),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRisks::route('/')];
    }
}
