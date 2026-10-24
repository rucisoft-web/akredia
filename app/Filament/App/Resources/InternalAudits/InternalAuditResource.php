<?php

namespace App\Filament\App\Resources\InternalAudits;

use App\Filament\App\Resources\InternalAudits\Pages\ManageInternalAudits;
use App\Filament\Concerns\WriterPermissions;
use App\Models\InternalAudit;
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

class InternalAuditResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = InternalAudit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Audit mutu internal';

    protected static ?string $pluralModelLabel = 'Audit Mutu Internal';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama audit')->required()->columnSpanFull(),
            TextInput::make('cycle_year')->label('Siklus (tahun)')->numeric()->default(now()->year)->required(),
            Select::make('unit_id')->label('Unit diaudit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            DatePicker::make('audit_date')->label('Tanggal audit'),
            TextInput::make('lead_auditor')->label('Ketua auditor'),
            Select::make('state')->label('Status')->options(\App\Models\InternalAudit::STATES)->default('rencana'),
            Textarea::make('summary')->label('Ringkasan hasil')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('audit_date', 'desc')
            ->columns([
                TextColumn::make('number')->label('Nomor')->searchable(),
                TextColumn::make('name')->label('Audit')->searchable()->wrap()->weight('bold'),
                TextColumn::make('unit.name')->label('Unit'),
                TextColumn::make('audit_date')->label('Tanggal')->date('d/m/Y')->sortable(),
                TextColumn::make('findings_count')->label('Temuan')->counts('findings'),
                TextColumn::make('state')->label('Status')->badge()->formatStateUsing(fn ($state) => \App\Models\InternalAudit::STATES[$state] ?? $state),
            ])
            ->filters([
                SelectFilter::make('cycle_year')->label('Siklus')->options(fn () => \App\Models\InternalAudit::query()->distinct()->orderByDesc('cycle_year')->pluck('cycle_year', 'cycle_year')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageInternalAudits::route('/')];
    }
}
