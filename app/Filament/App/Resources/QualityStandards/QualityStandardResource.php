<?php

namespace App\Filament\App\Resources\QualityStandards;

use App\Filament\App\Resources\QualityStandards\Pages\ManageQualityStandards;
use App\Filament\Concerns\WriterPermissions;
use App\Models\QualityStandard;
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

class QualityStandardResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = QualityStandard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Standar mutu';

    protected static ?string $pluralModelLabel = 'Standar Mutu';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kode')->required(),
            TextInput::make('name')->label('Nama standar')->required(),
            Select::make('category')->label('Bidang')->options(['akademik' => 'Akademik', 'nonakademik' => 'Non-akademik'])->default('akademik'),
            Select::make('owner_unit_id')->label('Unit pemilik')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            Select::make('ppepp_stage')->label('Tahap PPEPP saat ini')->options(\App\Models\QualityStandard::STAGES)->default('pelaksanaan'),
            DatePicker::make('approved_at')->label('Ditetapkan'),
            DatePicker::make('review_due')->label('Jadwal tinjau ulang'),
            Textarea::make('statement')->label('Pernyataan standar')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable(),
                TextColumn::make('name')->label('Standar')->searchable()->wrap()->weight('bold'),
                TextColumn::make('category')->label('Bidang')->badge()->formatStateUsing(fn ($state) => $state === 'akademik' ? 'Akademik' : 'Non-akademik'),
                TextColumn::make('ownerUnit.name')->label('Unit')->toggleable(),
                TextColumn::make('ppepp_stage')->label('PPEPP')->badge()->formatStateUsing(fn ($state) => \App\Models\QualityStandard::STAGES[$state] ?? $state),
                TextColumn::make('review_due')->label('Tinjau ulang')->date('d/m/Y')->color(fn ($record) => $record->review_due?->isPast() ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('category')->label('Bidang')->options(['akademik' => 'Akademik', 'nonakademik' => 'Non-akademik']),
                SelectFilter::make('ppepp_stage')->label('PPEPP')->options(\App\Models\QualityStandard::STAGES),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageQualityStandards::route('/')];
    }
}
