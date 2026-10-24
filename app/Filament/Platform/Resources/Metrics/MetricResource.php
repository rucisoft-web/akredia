<?php

namespace App\Filament\Platform\Resources\Metrics;

use App\Filament\Platform\Resources\Metrics\Pages\ManageMetrics;
use App\Models\Metric;
use App\Services\MetricEngine;
use App\Support\Formula;
use BackedEnum;
use Closure;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Rumus dan ambang metrik dapat diubah tanpa rilis kode. */
class MetricResource extends Resource
{
    protected static ?string $model = Metric::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Rumus Metrik';

    protected static ?string $modelLabel = 'Metrik';

    protected static ?string $pluralModelLabel = 'Rumus Metrik';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('code')->label('Kode')->required()->maxLength(20)->disabledOn('edit'),
                TextInput::make('name')->label('Nama')->required(),
                TextInput::make('formula')->label('Rumus')->required()->columnSpanFull()
                    ->helperText('Fungsi: pct(a, b), avg(...), decline(lama, baru), min, max. Variabel: '.implode(', ', array_keys(MetricEngine::VARIABLES)))
                    ->rule(fn () => function (string $attribute, $value, Closure $fail) {
                        if ($error = Formula::validate((string) $value, array_keys(MetricEngine::VARIABLES))) {
                            $fail($error);
                        }
                    }),
                TextInput::make('formula_text')->label('Rumus (teks)')->columnSpanFull(),
                TextInput::make('unit')->label('Satuan')->default('%'),
                TextInput::make('indicator_ref')->label('Indikator'),
            ]),
            Section::make('Ambang')->columns(4)->schema([
                TextInput::make('accredited_min')->label('Terakreditasi ≥')->numeric(),
                TextInput::make('accredited_max')->label('Terakreditasi ≤')->numeric(),
                TextInput::make('excellent_min')->label('Unggul ≥')->numeric(),
                TextInput::make('excellent_max')->label('Unggul ≤')->numeric(),
                TextInput::make('tolerance_pct')->label('Toleransi rekonsiliasi (%)')->numeric()->default(2),
                Toggle::make('is_prerequisite')->label('Syarat perlu'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('code')->label('Kode')->weight('bold')->searchable(),
                TextColumn::make('name')->label('Nama')->searchable()->wrap(),
                TextColumn::make('formula')->label('Rumus')->fontFamily('mono')->size('xs')->wrap(),
                TextColumn::make('accredited')->label('Terakreditasi')->state(fn (Metric $r) => MetricEngine::thresholdText($r, \App\Enums\Track::Accredited)),
                TextColumn::make('excellent')->label('Unggul')->state(fn (Metric $r) => MetricEngine::thresholdText($r, \App\Enums\Track::Excellent)),
                IconColumn::make('is_prerequisite')->label('Syarat perlu')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMetrics::route('/')];
    }
}
