<?php

namespace App\Filament\App\Resources\Reconciliations;

use App\Filament\App\Resources\Reconciliations\Pages\ManageReconciliations;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Reconciliation;
use App\Enums\ReconciliationState;
use App\Enums\Severity;
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

class ReconciliationResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Reconciliation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = 'Rekonsiliasi';

    protected static ?string $pluralModelLabel = 'Rekonsiliasi PD Dikti';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('metric_code')->label('Kode data / metrik')->required(),
            TextInput::make('scope_label')->label('Cakupan')->placeholder('Institusi / nama prodi'),
            TextInput::make('internal_value')->label('Nilai internal')->numeric(),
            TextInput::make('pddikti_value')->label('Nilai PD Dikti')->numeric(),
            TextInput::make('tolerance_pct')->label('Toleransi (%)')->numeric()->default(2),
            Select::make('state')->label('Status')->options(ReconciliationState::class)->default('open')->required(),
            Select::make('owner_unit_id')->label('Unit pemilik data')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            Select::make('owner_id')->label('Penanggung jawab')->options(fn () => static::memberOptions())->searchable(),
            Select::make('fix_document_id')->label('Bukti perbaikan')->options(fn () => \App\Models\Document::query()->orderByDesc('id')->limit(300)->pluck('title', 'id'))->searchable(),
            Textarea::make('note')->label('Catatan investigasi')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('difference_pct', 'desc')
            ->columns([
                TextColumn::make('metric_code')->label('Data')->weight('bold')->searchable()->description(fn ($record) => $record->scope_label),
                TextColumn::make('internal_value')->label('Internal')->numeric(2, locale: 'id'),
                TextColumn::make('pddikti_value')->label('PD Dikti')->numeric(2, locale: 'id'),
                TextColumn::make('difference')->label('Selisih')->numeric(2, locale: 'id')->description(fn ($record) => $record->difference_pct !== null ? number_format($record->difference_pct, 1, ',', '.').'%' : null),
                TextColumn::make('severity')->label('Keparahan')->badge(),
                TextColumn::make('state')->label('Status')->badge(),
                TextColumn::make('ownerUnit.name')->label('Unit')->placeholder('—')->toggleable(),
                TextColumn::make('owner.name')->label('PJ')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('state')->label('Status')->options(ReconciliationState::class)->multiple(),
                SelectFilter::make('severity')->label('Keparahan')->options(Severity::class)->multiple(),
                SelectFilter::make('owner_unit_id')->label('Unit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id')),
            ])
            ->recordActions([Action::make('verify')->label('Verifikasi')->icon(Heroicon::OutlinedCheckBadge)->color('success')->visible(fn ($record) => static::canReview() && $record->state->value === 'fixed')->requiresConfirmation()->action(fn ($record) => $record->update(['state' => 'verified', 'verified_by' => auth()->id()])), Action::make('task')->label('Jadikan tugas')->icon(Heroicon::OutlinedClipboardDocumentList)->color('gray')->visible(fn ($record) => static::canWrite() && $record->isOpen())->action(function ($record) { \App\Models\Task::create(['accreditation_project_id' => $record->accreditation_project_id, 'title' => 'Perbaiki data '.$record->metric_code.' '.($record->scope_label ?? ''), 'description' => 'Selisih internal '.$record->internal_value.' vs PD Dikti '.$record->pddikti_value.'. Perbaiki di sumber data dan unggah bukti.', 'assignee_id' => $record->owner_id, 'unit_id' => $record->owner_unit_id, 'due_date' => now()->addDays(14), 'priority' => $record->severity->value === 'tinggi' ? 'tinggi' : 'sedang', 'source' => 'reconciliation', 'source_id' => $record->id]); \Filament\Notifications\Notification::make()->success()->title('Tugas perbaikan data dibuat')->send(); }), EditAction::make(), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['ownerUnit', 'owner']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageReconciliations::route('/')];
    }
}
