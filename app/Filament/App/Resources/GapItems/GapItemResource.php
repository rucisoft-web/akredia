<?php

namespace App\Filament\App\Resources\GapItems;

use App\Filament\App\Resources\GapItems\Pages\ManageGapItems;
use App\Filament\Concerns\WriterPermissions;
use App\Models\GapItem;
use App\Enums\GapStatus;
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

class GapItemResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = GapItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Persiapan Akreditasi';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'Baris kertas kerja';

    protected static ?string $pluralModelLabel = 'Kertas Kerja Gabungan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('keyword_text')->label('Kata kunci')->disabled()->columnSpanFull(),
            Select::make('status')->label('Status')->options(GapStatus::class)->required(),
            Select::make('owner_id')->label('Penanggung jawab')->options(fn () => static::memberOptions())->searchable(),
            DatePicker::make('due_date')->label('Tenggat'),
            Textarea::make('implementation_note')->label('Kondisi implementasi')->columnSpanFull(),
            Textarea::make('rtl')->label('Rencana tindak lanjut')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('workItem.subIndicator.code')->label('Sub-ind.')->weight('bold'),
                TextColumn::make('keyword_text')->label('Kata kunci')->wrap()->searchable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('implementation_note')->label('Kondisi')->wrap()->lineClamp(2)->placeholder('—')->toggleable(),
                TextColumn::make('rtl')->label('RTL')->wrap()->lineClamp(2)->placeholder('—'),
                TextColumn::make('owner.name')->label('PJ')->placeholder('—'),
                TextColumn::make('due_date')->label('Tenggat')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('task.number')->label('Tugas')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(GapStatus::class)->multiple()->default(['belum', 'sebagian']),
                SelectFilter::make('owner_id')->label('PJ')->options(fn () => static::memberOptions())->searchable(),
                TernaryFilter::make('no_task')->label('Belum jadi tugas')->queries(true: fn ($q) => $q->whereNull('task_id'), false: fn ($q) => $q->whereNotNull('task_id')),
            ])
            ->recordActions([Action::make('item')->label('Butir')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')->url(fn ($record) => \App\Filament\App\Resources\WorkItems\WorkItemResource::getUrl('view', ['record' => $record->work_item_id])), EditAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('workItem', fn ($q) => $q->where('accreditation_project_id', static::activeProject()?->id ?? 0))->with(['workItem.subIndicator', 'owner', 'task']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ManageGapItems::route('/')];
    }
}
