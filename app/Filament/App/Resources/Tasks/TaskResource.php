<?php

namespace App\Filament\App\Resources\Tasks;

use App\Filament\App\Resources\Tasks\Pages\ManageTasks;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Task;
use App\Enums\Priority;
use App\Enums\TaskStatus;
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

class TaskResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Tugas';

    protected static ?string $pluralModelLabel = 'Tugas & RTL';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Judul tugas')->required()->columnSpanFull(),
            Select::make('assignee_id')->label('Penanggung jawab')->options(fn () => static::memberOptions())->searchable(),
            Select::make('unit_id')->label('Unit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            DatePicker::make('due_date')->label('Tenggat'),
            Select::make('priority')->label('Prioritas')->options(Priority::class)->default('sedang')->required(),
            Select::make('status')->label('Status')->options(TaskStatus::class)->default('todo')->required(),
            Select::make('work_item_id')->label('Butir kerja terkait')->searchable()
                ->options(fn () => \App\Models\WorkItem::query()->where('accreditation_project_id', static::activeProject()?->id ?? 0)->with('subIndicator')->get()->sortBy('subIndicator.sort')->mapWithKeys(fn ($w) => [$w->id => $w->subIndicator->code.' · '.\Illuminate\Support\Str::limit($w->subIndicator->statement, 70)])),
            Textarea::make('description')->label('Uraian')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date')
            ->columns([
                TextColumn::make('number')->label('No.')->searchable()->toggleable(),
                TextColumn::make('title')->label('Tugas')->searchable()->wrap()->weight('bold')
                    ->description(fn ($record) => collect([\App\Models\Task::SOURCES[$record->source] ?? null, $record->workItem?->subIndicator?->code ? 'Sub-indikator '.$record->workItem->subIndicator->code : null])->filter()->implode(' · ')),
                TextColumn::make('assignee.name')->label('PJ')->placeholder('—')->description(fn ($record) => $record->unit?->name),
                TextColumn::make('due_date')->label('Tenggat')->date('d/m/Y')->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null)
                    ->description(fn ($record) => $record->isOverdue() ? 'terlambat '.$record->due_date->diffInDays(today()).' hari' : null),
                TextColumn::make('priority')->label('Prioritas')->badge(),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(TaskStatus::class)->multiple()->default(['todo', 'progress']),
                SelectFilter::make('priority')->label('Prioritas')->options(Priority::class)->multiple(),
                SelectFilter::make('assignee_id')->label('PJ')->options(fn () => static::memberOptions())->searchable(),
                SelectFilter::make('unit_id')->label('Unit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
                SelectFilter::make('source')->label('Sumber')->options(\App\Models\Task::SOURCES),
                TernaryFilter::make('overdue')->label('Terlambat')->queries(true: fn ($q) => $q->overdue(), false: fn ($q) => $q),
                \Filament\Tables\Filters\Filter::make('mine')->label('Tugas saya')->toggle()->query(fn ($query) => $query->where('assignee_id', auth()->id())),
            ])
            ->recordActions([Action::make('done')->label('Selesai')->icon(Heroicon::OutlinedCheck)->color('success')->visible(fn ($record) => static::canWrite() && $record->status->value !== 'done')->action(fn ($record) => $record->update(['status' => 'done'])), EditAction::make(), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['assignee', 'unit', 'workItem.subIndicator']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTasks::route('/')];
    }
}
