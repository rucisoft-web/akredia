<?php

namespace App\Filament\App\Resources\VisitRecommendations;

use App\Filament\App\Resources\VisitRecommendations\Pages\ManageVisitRecommendations;
use App\Filament\Concerns\WriterPermissions;
use App\Models\VisitRecommendation;
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

class VisitRecommendationResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = VisitRecommendation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Asesmen Lapangan';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'Rekomendasi asesor';

    protected static ?string $pluralModelLabel = 'Rekomendasi Asesor';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_visit_id')->label('Asesmen lapangan')->options(fn () => \App\Models\SiteVisit::query()->orderByDesc('starts_on')->pluck('title', 'id'))->required()->default(fn () => \App\Models\SiteVisit::query()->latest('starts_on')->value('id')),
            Select::make('indicator_id')->label('Indikator')->options(fn () => \App\Models\Indicator::query()->orderBy('number')->get()->mapWithKeys(fn ($i) => [$i->id => $i->label()]))->searchable(),
            Textarea::make('recommendation')->label('Rekomendasi')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('indicator.number')->label('Ind.')->placeholder('—'),
                TextColumn::make('recommendation')->label('Rekomendasi')->wrap()->weight('bold'),
                TextColumn::make('follow_up')->label('Tindak lanjut')->badge()->formatStateUsing(fn ($state) => ['task' => 'Tugas', 'capa' => 'CAPA'][$state] ?? $state)->placeholder('Belum'),
            ])
            ->filters([
                SelectFilter::make('site_visit_id')->label('Asesmen')->options(fn () => \App\Models\SiteVisit::query()->orderByDesc('starts_on')->pluck('title', 'id')),
                TernaryFilter::make('follow_up')->label('Sudah ditindaklanjuti')->queries(true: fn ($q) => $q->whereNotNull('follow_up'), false: fn ($q) => $q->whereNull('follow_up')),
            ])
            ->recordActions([Action::make('to_task')->label('Jadikan tugas')->icon(Heroicon::OutlinedClipboardDocumentList)->color('gray')->visible(fn ($record) => static::canWrite() && ! $record->follow_up)->action(function ($record) { $t = \App\Models\Task::create(['accreditation_project_id' => static::activeProject()?->id, 'title' => 'Tindak lanjut rekomendasi asesor'.($record->indicator ? ' indikator '.$record->indicator->number : ''), 'description' => $record->recommendation, 'due_date' => now()->addMonths(3), 'priority' => 'tinggi', 'source' => 'recommendation', 'source_id' => $record->id]); $record->update(['follow_up' => 'task', 'follow_up_id' => $t->id]); }), Action::make('to_capa')->label('Jadikan temuan + CAPA')->icon(Heroicon::OutlinedWrenchScrewdriver)->color('gray')->visible(fn ($record) => static::canWrite() && ! $record->follow_up)->action(function ($record) { $f = \App\Models\Finding::create(['category' => 'observasi', 'source' => 'asesmen', 'indicator_id' => $record->indicator_id, 'description' => $record->recommendation, 'state' => 'capa', 'due_date' => now()->addMonths(3)]); $f->capas()->create(['institution_id' => $f->institution_id, 'kind' => 'korektif', 'action' => 'Tindak lanjuti: '.$record->recommendation, 'due_date' => now()->addMonths(3)]); $record->update(['follow_up' => 'capa', 'follow_up_id' => $f->id]); }), EditAction::make(), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('indicator');
    }

    public static function getPages(): array
    {
        return ['index' => ManageVisitRecommendations::route('/')];
    }
}
