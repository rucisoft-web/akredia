<?php

namespace App\Filament\App\Resources\Findings;

use App\Filament\App\Resources\Findings\Pages\ManageFindings;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Finding;
use App\Enums\FindingCategory;
use App\Enums\FindingState;
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

class FindingResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Finding::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Temuan';

    protected static ?string $pluralModelLabel = 'Temuan AMI & Simulasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('internal_audit_id')->label('Audit')->options(fn () => \App\Models\InternalAudit::query()->orderByDesc('cycle_year')->pluck('name', 'id'))->searchable(),
            Select::make('source')->label('Sumber')->options(\App\Models\Finding::SOURCES)->default('ami')->required(),
            Select::make('category')->label('Kategori')->options(FindingCategory::class)->required(),
            Select::make('state')->label('Status')->options(FindingState::class)->default('open')->required(),
            Select::make('unit_id')->label('Unit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            Select::make('quality_standard_id')->label('Standar SPMI')->options(fn () => \App\Models\QualityStandard::query()->orderBy('code')->get()->mapWithKeys(fn ($s) => [$s->id => $s->code.' · '.$s->name]))->searchable(),
            Select::make('indicator_id')->label('Indikator IAPT terkait')->options(fn () => \App\Models\Indicator::query()->orderBy('number')->get()->mapWithKeys(fn ($i) => [$i->id => $i->label()]))->searchable(),
            DatePicker::make('due_date')->label('Tenggat penyelesaian'),
            Textarea::make('description')->label('Uraian temuan')->required()->columnSpanFull(),
            Textarea::make('root_cause')->label('Akar masalah')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('No.')->weight('bold')->searchable(),
                TextColumn::make('category')->label('Kategori')->badge(),
                TextColumn::make('description')->label('Temuan')->wrap()->lineClamp(2)->searchable()->description(fn ($record) => $record->audit?->name),
                TextColumn::make('unit.name')->label('Unit')->placeholder('—'),
                TextColumn::make('indicator.number')->label('Ind.')->placeholder('—'),
                TextColumn::make('capas_count')->label('CAPA')->counts('capas')->alignCenter(),
                TextColumn::make('due_date')->label('Tenggat')->date('d/m/Y')->color(fn ($record) => $record->state->value !== 'closed' && $record->due_date?->isPast() ? 'danger' : null),
                TextColumn::make('state')->label('Status')->badge(),
            ])
            ->filters([
                SelectFilter::make('state')->label('Status')->options(FindingState::class)->multiple(),
                SelectFilter::make('category')->label('Kategori')->options(FindingCategory::class)->multiple(),
                SelectFilter::make('source')->label('Sumber')->options(\App\Models\Finding::SOURCES),
                SelectFilter::make('unit_id')->label('Unit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id')),
                SelectFilter::make('internal_audit_id')->label('Audit')->relationship('audit', 'name'),
            ])
            ->recordActions([Action::make('capa')->label('CAPA')->icon(Heroicon::OutlinedWrenchScrewdriver)->color('gray')->url(fn ($record) => \App\Filament\App\Resources\Capas\CapaResource::getUrl('index', ['filters' => ['finding_id' => ['value' => $record->id], 'open' => ['value' => '']]])), Action::make('add_capa')->label('Tambah CAPA')->icon(Heroicon::OutlinedPlus)->color('gray')->visible(fn () => static::canWrite())->schema([Textarea::make('action')->label('Tindakan')->required(), Select::make('owner_id')->label('Penanggung jawab')->options(fn () => static::memberOptions())->searchable(), DatePicker::make('due_date')->label('Tenggat')])->action(function ($record, array $data) { $record->capas()->create($data + ['institution_id' => $record->institution_id, 'kind' => 'korektif']); if ($record->state->value === 'open') { $record->update(['state' => 'capa']); } }), EditAction::make(), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['audit', 'unit', 'indicator']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFindings::route('/')];
    }
}
