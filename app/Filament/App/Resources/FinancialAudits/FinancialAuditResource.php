<?php

namespace App\Filament\App\Resources\FinancialAudits;

use App\Filament\App\Resources\FinancialAudits\Pages\ManageFinancialAudits;
use App\Filament\Concerns\WriterPermissions;
use App\Models\FinancialAudit;
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

class FinancialAuditResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = FinancialAudit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 70;

    protected static ?string $modelLabel = 'Audit keuangan';

    protected static ?string $pluralModelLabel = 'Audit Keuangan Eksternal';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            TextInput::make('auditor')->label('Kantor Akuntan Publik')->required(),
            Select::make('opinion')->label('Opini')->options(\App\Models\FinancialAudit::OPINIONS)->required(),
            DatePicker::make('report_date')->label('Tanggal laporan'),
            Select::make('document_id')->label('Dokumen laporan (Pustaka Bukti)')->options(fn () => \App\Models\Document::query()->orderBy('title')->pluck('title', 'id'))->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('year')->label('Tahun')->sortable()->weight('bold'),
                TextColumn::make('auditor')->label('KAP'),
                TextColumn::make('opinion')->label('Opini')->badge()->color(fn ($state) => $state === 'WTP' ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state.' · '.(\App\Models\FinancialAudit::OPINIONS[$state] ?? '')),
                TextColumn::make('report_date')->label('Tanggal')->date('d/m/Y'),
                TextColumn::make('document.code')->label('Bukti')->placeholder('—'),
            ])
            ->filters([

            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFinancialAudits::route('/')];
    }
}
