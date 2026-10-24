<?php

namespace App\Filament\App\Resources\ResearchGrants;

use App\Filament\App\Resources\ResearchGrants\Pages\ManageResearchGrants;
use App\Filament\Concerns\WriterPermissions;
use App\Models\ResearchGrant;
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

class ResearchGrantResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = ResearchGrant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 65;

    protected static ?string $modelLabel = 'Dana penelitian/PkM';

    protected static ?string $pluralModelLabel = 'Dana Penelitian & PkM';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Judul / skema')->required()->columnSpanFull(),
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            Select::make('category')->label('Kategori')->options(['penelitian' => 'Penelitian', 'pkm' => 'PkM'])->required(),
            Select::make('source')->label('Sumber dana')->options(\App\Support\Labels::FUND_SOURCES)->required(),
            TextInput::make('amount')->label('Jumlah (Rp)')->numeric()->prefix('Rp')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->wrap(),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('category')->label('Kategori')->badge()->formatStateUsing(fn ($state) => $state === 'pkm' ? 'PkM' : 'Penelitian'),
                TextColumn::make('source')->label('Sumber')->formatStateUsing(fn ($state) => \App\Support\Labels::FUND_SOURCES[$state] ?? $state),
                TextColumn::make('amount')->label('Jumlah')->formatStateUsing(fn ($state) => \App\Support\Number::money($state))->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')->formatStateUsing(fn ($state) => \App\Support\Number::money($state))),
            ])
            ->filters([
                SelectFilter::make('year')->label('Tahun')->options(fn () => \App\Models\ResearchGrant::query()->distinct()->orderByDesc('year')->pluck('year', 'year')),
                SelectFilter::make('category')->label('Kategori')->options(['penelitian' => 'Penelitian', 'pkm' => 'PkM']),
                SelectFilter::make('source')->label('Sumber')->options(\App\Support\Labels::FUND_SOURCES),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageResearchGrants::route('/')];
    }
}
