<?php

namespace App\Filament\App\Resources\WorkItems;

use App\Enums\HeatColor;
use App\Enums\LedState;
use App\Enums\WorkItemState;
use App\Filament\App\Resources\LedSections\LedSectionResource;
use App\Filament\App\Resources\WorkItems\Pages\ListWorkItems;
use App\Filament\App\Resources\WorkItems\Pages\ViewWorkItem;
use App\Filament\App\Resources\WorkItems\RelationManagers\AssessmentsRelationManager;
use App\Filament\App\Resources\WorkItems\RelationManagers\EvidenceLinksRelationManager;
use App\Filament\App\Resources\WorkItems\RelationManagers\GapItemsRelationManager;
use App\Filament\App\Resources\WorkItems\RelationManagers\TasksRelationManager;
use App\Filament\Concerns\InteractsWithTenant;
use App\Models\Criterion;
use App\Models\SubIndicator;
use App\Models\Unit;
use App\Models\WorkItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class WorkItemResource extends Resource
{
    use InteractsWithTenant;

    protected static ?string $model = WorkItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Persiapan Akreditasi';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Butir kerja';

    protected static ?string $pluralModelLabel = 'Butir Kerja Sub-indikator';

    protected static ?string $navigationLabel = 'Butir Kerja';

    protected static ?string $recordTitleAttribute = 'id';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManage() || (static::canWrite() && in_array(auth()->id(), [$record->pic_id, $record->second_pic_id], true));
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getRecordTitle(?Model $record): string
    {
        return $record ? 'Sub-indikator '.$record->subIndicator->code : 'Butir kerja';
    }

    public static function getEloquentQuery(): Builder
    {
        $project = static::activeProject();

        return parent::getEloquentQuery()
            ->where('accreditation_project_id', $project?->id ?? 0)
            ->with(['subIndicator.indicator.criterion', 'pic', 'reviewer', 'ledSection', 'unit'])
            ->withCount(['evidenceLinks', 'gapItems as gaps_open_count' => fn ($q) => $q->where('status', '!=', 'terbukti')]);
    }

    public static function form(Schema $schema): Schema
    {
        $managerOnly = fn () => ! static::canManage();

        return $schema->components([
            Select::make('pic_id')->label('PIC utama')->options(fn () => static::memberOptions())->searchable()->disabled($managerOnly),
            Select::make('second_pic_id')->label('PIC pendamping')->options(fn () => static::memberOptions())->searchable()->disabled($managerOnly),
            Select::make('reviewer_id')->label('Reviewer')->options(fn () => static::memberOptions())->searchable()->disabled($managerOnly)
                ->different('pic_id')->validationMessages(['different' => 'Reviewer tidak boleh sama dengan PIC (prinsip empat mata).']),
            Select::make('unit_id')->label('Unit penanggung jawab')->options(fn () => Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable()->disabled($managerOnly),
            DatePicker::make('due_date')->label('Tenggat')->disabled($managerOnly),
            Toggle::make('is_blocked')->label('Terhambat')->helperText('Tandai bila butir menunggu keputusan atau data dari pihak lain.'),
            Textarea::make('current_condition')->label('Kondisi saat ini (analisis kesenjangan)')->rows(5)->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.work-item.summary')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(fn (Builder $query) => $query->orderBy(SubIndicator::select('sort')->whereColumn('sub_indicators.id', 'work_items.sub_indicator_id')))
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->emptyStateHeading('Belum ada butir kerja')
            ->emptyStateDescription('Buat proyek akreditasi terlebih dahulu; butir kerja dibangkitkan otomatis dari varian instrumen.')
            ->columns([
                TextColumn::make('color')->label('')->badge()->formatStateUsing(fn () => '●')->tooltip(fn ($record) => $record->color_reason),
                TextColumn::make('subIndicator.code')->label('Kode')->weight('bold')
                    ->description(fn ($record) => 'K'.$record->subIndicator->indicator->criterion->number)
                    ->searchable(),
                TextColumn::make('subIndicator.statement')->label('Sub-indikator')->wrap()->lineClamp(2)->width('34%')->searchable()
                    ->description(fn ($record) => collect([
                        $record->subIndicator->is_prerequisite_accredited ? 'Syarat perlu Terakreditasi' : null,
                        $record->subIndicator->is_prerequisite_excellent ? 'Syarat perlu Unggul' : null,
                        $record->is_blocked ? 'Terhambat' : null,
                    ])->filter()->implode(' · ') ?: null),
                TextColumn::make('pic.name')->label('PIC')->placeholder('Belum ditetapkan')->description(fn ($record) => $record->unit?->code)->wrap(),
                TextColumn::make('reviewer.name')->label('Reviewer')->placeholder('—')->toggleable(),
                TextColumn::make('state')->label('Status')->badge(),
                TextColumn::make('evidence_links_count')->label('Bukti')->alignCenter(),
                TextColumn::make('gaps_open_count')->label('Gap terbuka')->alignCenter()->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                TextColumn::make('ledSection.state')->label('LED')->badge()->placeholder('—')
                    ->description(fn ($record) => $record->ledSection ? number_format($record->ledSection->word_count, 0, ',', '.').' kata' : null),
                TextColumn::make('due_date')->label('Tenggat')->date('d/m/Y')->toggleable()
                    ->color(fn ($record) => $record->due_date?->isPast() && $record->state !== WorkItemState::Approved ? 'danger' : null),
            ])
            ->filters([
                Filter::make('mine')->label('Butir saya (PIC/reviewer)')->toggle()
                    ->query(fn (Builder $query) => $query->where(fn ($q) => $q->where('pic_id', auth()->id())->orWhere('second_pic_id', auth()->id())->orWhere('reviewer_id', auth()->id()))),
                SelectFilter::make('criterion')->label('Kriteria')
                    ->options(fn () => Criterion::query()->orderBy('number')->get()->mapWithKeys(fn ($c) => [$c->id => 'Kriteria '.$c->number.' · '.$c->name]))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn ($q, $v) => $q->whereHas('subIndicator.indicator', fn ($q) => $q->where('criterion_id', $v)))),
                SelectFilter::make('indicator')->label('Indikator')
                    ->options(array_combine(range(1, 39), array_map(fn ($n) => 'Indikator '.$n, range(1, 39))))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn ($q, $v) => $q->whereHas('subIndicator.indicator', fn ($q) => $q->where('number', $v)))),
                SelectFilter::make('color')->label('Warna')->options(HeatColor::class)->multiple(),
                SelectFilter::make('state')->label('Status')->options(WorkItemState::class)->multiple(),
                SelectFilter::make('pic_id')->label('PIC')->options(fn () => static::memberOptions())->searchable(),
                SelectFilter::make('unit_id')->label('Unit')->options(fn () => Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
                SelectFilter::make('led')->label('Status LED')->options(LedState::class)
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn ($q, $v) => $q->whereHas('ledSection', fn ($q) => $q->where('state', $v)))),
                TernaryFilter::make('prerequisite')->label('Syarat perlu')
                    ->queries(
                        true: fn ($q) => $q->whereHas('subIndicator', fn ($q) => $q->where('is_prerequisite_accredited', true)->orWhere('is_prerequisite_excellent', true)),
                        false: fn ($q) => $q->whereHas('subIndicator', fn ($q) => $q->where('is_prerequisite_accredited', false)->where('is_prerequisite_excellent', false)),
                    ),
                TernaryFilter::make('unassigned')->label('PIC belum ditetapkan')
                    ->queries(true: fn ($q) => $q->whereNull('pic_id'), false: fn ($q) => $q->whereNotNull('pic_id')),
                TernaryFilter::make('overdue')->label('Lewat tenggat')
                    ->queries(true: fn ($q) => $q->whereDate('due_date', '<', today())->where('state', '!=', 'approved'), false: fn ($q) => $q),
            ])
            ->filtersFormColumns(3)
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('led')->label('LED')->icon(Heroicon::OutlinedPencilSquare)->color('gray')
                    ->url(fn ($record) => $record->ledSection ? LedSectionResource::getUrl('editor', ['record' => $record->ledSection]) : null)
                    ->visible(fn ($record) => (bool) $record->ledSection),
                ViewAction::make(),
                EditAction::make()->label('Tugaskan'),
            ])
            ->toolbarActions([
                BulkAction::make('assign')->label('Tetapkan PIC / reviewer')->icon(Heroicon::OutlinedUserPlus)
                    ->visible(fn () => static::canManage())
                    ->schema([
                        Select::make('pic_id')->label('PIC utama')->options(fn () => static::memberOptions())->searchable(),
                        Select::make('reviewer_id')->label('Reviewer')->options(fn () => static::memberOptions())->searchable(),
                        Select::make('unit_id')->label('Unit')->options(fn () => Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
                        DatePicker::make('due_date')->label('Tenggat'),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $data = array_filter($data);

                        foreach ($records as $record) {
                            $next = $data + $record->only(['pic_id', 'reviewer_id']);

                            if ($next['pic_id'] && $next['pic_id'] === $next['reviewer_id']) {
                                continue;
                            }

                            $record->update($data);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [GapItemsRelationManager::class, EvidenceLinksRelationManager::class, AssessmentsRelationManager::class, TasksRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkItems::route('/'),
            'view' => ViewWorkItem::route('/{record}'),
        ];
    }
}
