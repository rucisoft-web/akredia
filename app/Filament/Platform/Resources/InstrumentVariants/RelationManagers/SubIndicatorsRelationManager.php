<?php

namespace App\Filament\Platform\Resources\InstrumentVariants\RelationManagers;

use App\Services\InstrumentImporter;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SubIndicatorsRelationManager extends RelationManager
{
    protected static string $relationship = 'subIndicators';

    protected static ?string $title = 'Sub-indikator & deskriptor';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('code')->label('Kode')->disabled(),
            TextInput::make('weight')->label('Bobot')->numeric()->required(),
            Textarea::make('statement')->label('Pernyataan')->rows(3)->required()->columnSpanFull(),
            Textarea::make('data_sources')->label('Sumber data')->rows(2)->columnSpanFull(),
            Toggle::make('is_prerequisite_accredited')->label('Syarat perlu Terakreditasi'),
            Toggle::make('is_prerequisite_excellent')->label('Syarat perlu Unggul'),
            Select::make('metric_code')->label('Metrik terkait')->options(fn () => \App\Models\Metric::query()->orderBy('sort')->pluck('code', 'code'))->searchable(),
            TextInput::make('lkpt_table')->label('Tabel LKPT'),
            Textarea::make('descriptor_accredited')->label('Deskriptor Terakreditasi (kata kunci dipisah titik koma)')->rows(4)->columnSpanFull()
                ->afterStateHydrated(fn ($component, ?Model $record) => $component->state($record?->descriptorFor('accredited')?->text)),
            Textarea::make('descriptor_excellent')->label('Deskriptor Unggul (kata kunci dipisah titik koma)')->rows(4)->columnSpanFull()
                ->afterStateHydrated(fn ($component, ?Model $record) => $component->state($record?->descriptorFor('excellent')?->text)),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['indicator.criterion', 'descriptors.keywords']))
            ->defaultSort('sort')
            ->paginated([25, 50, 100])
            ->columns([
                TextColumn::make('indicator.criterion.number')->label('K')->badge(),
                TextColumn::make('code')->label('Kode')->weight('bold')->searchable(),
                TextColumn::make('statement')->label('Pernyataan')->wrap()->lineClamp(2)->searchable(),
                TextColumn::make('keywords')->label('Kata kunci')->state(fn (Model $r) => $r->descriptorFor('accredited')?->keywords->count() ?? 0),
                IconColumn::make('is_prerequisite_accredited')->label('SP-T')->boolean(),
                IconColumn::make('is_prerequisite_excellent')->label('SP-U')->boolean(),
                TextColumn::make('metric_code')->label('Metrik')->placeholder('—'),
                TextColumn::make('weight')->label('Bobot'),
            ])
            ->filters([SelectFilter::make('criterion')->label('Kriteria')->relationship('indicator.criterion', 'name')])
            ->recordActions([
                EditAction::make()->using(function (Model $record, array $data) {
                    $acc = $data['descriptor_accredited'] ?? null;
                    $exc = $data['descriptor_excellent'] ?? null;
                    unset($data['descriptor_accredited'], $data['descriptor_excellent']);
                    $record->update($data);
                    $importer = app(InstrumentImporter::class);
                    $importer->syncDescriptor($record, 'accredited', $acc);
                    $importer->syncDescriptor($record, 'excellent', $exc);

                    return $record;
                }),
            ]);
    }
}
