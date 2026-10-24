<?php

namespace App\Filament\App\Resources\LedSections;

use App\Enums\LedState;
use App\Filament\App\Resources\LedSections\Pages\EditorLed;
use App\Filament\App\Resources\LedSections\Pages\ListLedSections;
use App\Filament\Concerns\InteractsWithTenant;
use App\Models\Criterion;
use App\Models\LedSection;
use App\Models\SubIndicator;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class LedSectionResource extends Resource
{
    use InteractsWithTenant;

    protected static ?string $model = LedSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Persiapan Akreditasi';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Bagian LED';

    protected static ?string $pluralModelLabel = 'Laporan Evaluasi Diri (LED)';

    protected static ?string $navigationLabel = 'Penyusunan LED';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('workItem', fn ($q) => $q->where('accreditation_project_id', static::activeProject()?->id ?? 0))
            ->with(['workItem.subIndicator.indicator.criterion', 'workItem.pic', 'workItem.reviewer', 'workItem.project'])
            ->withCount(['citations as doc_citations_count' => fn ($q) => $q->whereNotNull('document_id'), 'comments as open_comments_count' => fn ($q) => $q->where('is_resolved', false)]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(fn (Builder $query) => $query->orderBy(
                SubIndicator::select('sub_indicators.sort')->join('work_items', 'work_items.sub_indicator_id', '=', 'sub_indicators.id')->whereColumn('work_items.id', 'led_sections.work_item_id')
            ))
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('workItem.subIndicator.code')->label('Kode')->weight('bold')
                    ->description(fn ($record) => 'K'.$record->workItem->subIndicator->indicator->criterion->number),
                TextColumn::make('workItem.subIndicator.statement')->label('Sub-indikator')->wrap()->lineClamp(2),
                TextColumn::make('workItem.pic.name')->label('Penulis (PIC)')->placeholder('—'),
                TextColumn::make('state')->label('Status')->badge(),
                TextColumn::make('word_count')->label('Kata')->numeric(locale: 'id')
                    ->color(fn ($record) => $record->word_count > $record->workItem->project->word_limit ? 'danger' : null)
                    ->description(fn ($record) => 'batas '.number_format($record->workItem->project->word_limit, 0, ',', '.')),
                TextColumn::make('keyword_coverage')->label('Kata kunci')->suffix('%')
                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 70 ? 'warning' : 'danger')),
                TextColumn::make('doc_citations_count')->label('Sitasi')->alignCenter(),
                TextColumn::make('open_comments_count')->label('Komentar')->alignCenter()->color(fn ($state) => $state ? 'warning' : null),
                TextColumn::make('updated_at')->label('Diubah')->since()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('state')->label('Status')->options(LedState::class)->multiple(),
                SelectFilter::make('criterion')->label('Kriteria')
                    ->options(fn () => Criterion::query()->orderBy('number')->get()->mapWithKeys(fn ($c) => [$c->id => 'Kriteria '.$c->number]))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn ($q, $v) => $q->whereHas('workItem.subIndicator.indicator', fn ($q) => $q->where('criterion_id', $v)))),
                Filter::make('mine')->label('Saya tulis / reviu')->toggle()
                    ->query(fn (Builder $query) => $query->whereHas('workItem', fn ($q) => $q->where('pic_id', auth()->id())->orWhere('second_pic_id', auth()->id())->orWhere('reviewer_id', auth()->id()))),
                Filter::make('over')->label('Melewati batas kata')->toggle()
                    ->query(fn (Builder $query) => $query->whereRaw('word_count > (select word_limit from accreditation_projects ap join work_items wi on wi.accreditation_project_id = ap.id where wi.id = led_sections.work_item_id)')),
            ])
            ->recordUrl(fn ($record) => static::getUrl('editor', ['record' => $record]))
            ->recordActions([
                Action::make('open')->label('Tulis / reviu')->icon(Heroicon::OutlinedPencilSquare)->url(fn ($record) => static::getUrl('editor', ['record' => $record])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLedSections::route('/'),
            'editor' => EditorLed::route('/{record}'),
        ];
    }
}
