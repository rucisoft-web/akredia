<?php

namespace App\Filament\App\Resources\MetricValues;

use App\Filament\App\Resources\MetricValues\Pages\ManageMetricValues;
use App\Filament\Concerns\InteractsWithTenant;
use App\Models\MetricValue;
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

class MetricValueResource extends Resource
{
    use InteractsWithTenant;

    protected static ?string $model = MetricValue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'Nilai metrik';

    protected static ?string $pluralModelLabel = 'Nilai Metrik & Rasio';

    protected static ?string $navigationLabel = 'Nilai Metrik';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('value')->label('Nilai')->numeric()->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('metric_code')
            ->columns([
                TextColumn::make('metric_code')->label('Metrik')->weight('bold')->searchable()
                    ->description(fn ($record) => \App\Models\Metric::query()->where('code', $record->metric_code)->value('name')),
                TextColumn::make('studyProgram.name')->label('Cakupan')->placeholder('Institusi'),
                TextColumn::make('year')->label('TS')->sortable(),
                TextColumn::make('value')->label('Nilai')->numeric(2, locale: 'id')->weight('bold')->placeholder('—'),
                TextColumn::make('inputs')->label('Masukan rumus')->formatStateUsing(fn ($state) => collect(is_array($state) ? $state : [])->map(fn ($v, $k) => $k.'='.(is_numeric($v) ? number_format((float) $v, 2, ',', '.') : $v))->implode(' · '))->wrap()->toggleable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn ($state) => ['draft' => 'Draf', 'verified' => 'Terverifikasi', 'locked' => 'Dikunci'][$state] ?? $state)->color(fn ($state) => ['draft' => 'gray', 'verified' => 'success', 'locked' => 'primary'][$state] ?? 'gray'),
                TextColumn::make('verifier.name')->label('Verifikator')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(['draft' => 'Draf', 'verified' => 'Terverifikasi', 'locked' => 'Dikunci']),
                TernaryFilter::make('institution_level')->label('Tingkat institusi')->default(true)->queries(true: fn ($q) => $q->whereNull('study_program_id'), false: fn ($q) => $q->whereNotNull('study_program_id')),
                SelectFilter::make('study_program_id')->label('Program studi')->relationship('studyProgram', 'name')->searchable(),
            ])
            ->recordActions([Action::make('verify')->label('Verifikasi')->icon(Heroicon::OutlinedCheckBadge)->color('success')->visible(fn ($record) => static::canReview() && $record->status === 'draft')->action(function ($record) { if ((int) $record->computed_by === (int) auth()->id()) { \Filament\Notifications\Notification::make()->danger()->title('Verifikator tidak boleh orang yang menghitung nilai (prinsip empat mata).')->send(); return; } $record->update(['status' => 'verified', 'verified_by' => auth()->id(), 'verified_at' => now()]); })]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('accreditation_project_id', static::activeProject()?->id ?? 0)->with(['studyProgram', 'verifier']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ManageMetricValues::route('/')];
    }
}
