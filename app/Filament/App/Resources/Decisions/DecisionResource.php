<?php

namespace App\Filament\App\Resources\Decisions;

use App\Filament\App\Resources\Decisions\Pages\ManageDecisions;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Decision;
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

class DecisionResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Decision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Pengendalian';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Keputusan pimpinan';

    protected static ?string $pluralModelLabel = 'Keputusan Pimpinan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Keputusan')->required()->columnSpanFull(),
            DatePicker::make('decision_date')->label('Tanggal')->default(today())->required(),
            Select::make('forum')->label('Forum')->options(\App\Models\Decision::FORUMS)->default('rapat_pimpinan')->required(),
            TextInput::make('decided_by')->label('Diputuskan oleh'),
            Select::make('status')->label('Status')->options(['berlaku' => 'Berlaku', 'selesai' => 'Selesai dilaksanakan', 'dicabut' => 'Dicabut'])->default('berlaku'),
            Textarea::make('content')->label('Isi keputusan')->required()->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('decision_date', 'desc')
            ->columns([
                TextColumn::make('number')->label('Nomor')->searchable(),
                TextColumn::make('title')->label('Keputusan')->searchable()->wrap()->weight('bold'),
                TextColumn::make('decision_date')->label('Tanggal')->date('d/m/Y')->sortable(),
                TextColumn::make('forum')->label('Forum')->formatStateUsing(fn ($state) => \App\Models\Decision::FORUMS[$state] ?? $state),
                TextColumn::make('tasks_count')->label('Tugas turunan')->counts('tasks'),
                TextColumn::make('status')->label('Status')->badge()->color(fn ($state) => match ($state) { 'berlaku' => 'info', 'selesai' => 'success', default => 'gray' }),
            ])
            ->filters([

            ])
            ->recordActions([Action::make('task')->label('Turunkan tugas')->icon(Heroicon::OutlinedArrowDownTray)->visible(fn () => static::canWrite())
    ->schema([TextInput::make('title')->label('Tugas')->required(), Select::make('assignee_id')->label('Penanggung jawab')->options(fn () => static::memberOptions())->searchable(), DatePicker::make('due_date')->label('Tenggat')->required()])
    ->action(function ($record, array $data) { \App\Models\Task::create($data + ['accreditation_project_id' => $record->accreditation_project_id, 'source' => 'decision', 'source_id' => $record->id, 'priority' => 'tinggi', 'description' => 'Turunan keputusan '.$record->number.': '.$record->title]); \Filament\Notifications\Notification::make()->title('Tugas dibuat')->success()->send(); }),
EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDecisions::route('/')];
    }
}
