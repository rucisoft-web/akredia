<?php

namespace App\Filament\App\Resources\Capas;

use App\Filament\App\Resources\Capas\Pages\ManageCapas;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Capa;
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

class CapaResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Capa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'CAPA';

    protected static ?string $pluralModelLabel = 'Tindakan Korektif & Pencegahan (CAPA)';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('finding_id')->label('Temuan')->required()->searchable()->options(fn () => \App\Models\Finding::query()->orderByDesc('id')->get()->mapWithKeys(fn ($f) => [$f->id => $f->number.' · '.\Illuminate\Support\Str::limit($f->description, 80)])),
            Select::make('kind')->label('Jenis')->options(['korektif' => 'Korektif', 'pencegahan' => 'Pencegahan'])->default('korektif')->required(),
            Textarea::make('action')->label('Tindakan')->required()->columnSpanFull(),
            Select::make('owner_id')->label('Penanggung jawab')->options(fn () => static::memberOptions())->searchable(),
            DatePicker::make('due_date')->label('Tenggat'),
            DateTimePicker::make('completed_at')->label('Selesai pada'),
            Select::make('verifier_id')->label('Verifikator efektivitas')->options(fn () => static::memberOptions())->searchable()->different('owner_id'),
            Select::make('is_effective')->label('Efektif?')->options([1 => 'Efektif', 0 => 'Tidak efektif'])->placeholder('Belum diverifikasi'),
            Textarea::make('verification_note')->label('Catatan verifikasi')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date')
            ->columns([
                TextColumn::make('finding.number')->label('Temuan')->weight('bold')->searchable()->description(fn ($record) => \Illuminate\Support\Str::limit($record->finding?->description, 70)),
                TextColumn::make('kind')->label('Jenis')->badge()->color('gray'),
                TextColumn::make('action')->label('Tindakan')->wrap()->lineClamp(2),
                TextColumn::make('owner.name')->label('PJ')->placeholder('—'),
                TextColumn::make('due_date')->label('Tenggat')->date('d/m/Y')->color(fn ($record) => ! $record->completed_at && $record->due_date?->isPast() ? 'danger' : null),
                TextColumn::make('completed_at')->label('Selesai')->date('d/m/Y')->placeholder('—'),
                IconColumn::make('is_effective')->label('Efektif')->boolean()->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('open')->label('Belum selesai')->default(true)->queries(true: fn ($q) => $q->whereNull('completed_at'), false: fn ($q) => $q->whereNotNull('completed_at')),
                SelectFilter::make('finding_id')->label('Temuan')->relationship('finding', 'number'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['finding', 'owner']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCapas::route('/')];
    }
}
