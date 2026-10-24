<?php

namespace App\Filament\App\Resources\WorkItems\RelationManagers;

use App\Filament\App\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Models\Keyword;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Bukti terhubung ke butir kerja dan, idealnya, ke kata kunci yang dibuktikannya (FR-EVD-03). */
class EvidenceLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'evidenceLinks';

    protected static ?string $title = 'Bukti';

    public function isReadOnly(): bool
    {
        return auth()->user()?->roleIn(filament()->getTenant())?->canWrite() !== true;
    }

    protected function keywordOptions(): array
    {
        $item = $this->getOwnerRecord()->loadMissing('subIndicator.descriptors.keywords', 'project');

        return $item->subIndicator->descriptors
            ->sortBy(fn ($d) => $d->track === $item->project->track ? 0 : 1)
            ->flatMap(fn ($d) => $d->keywords->mapWithKeys(fn (Keyword $k) => [$k->id => '['.$d->track->getLabel().'] '.$k->text]))
            ->all();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('document_id')->label('Dokumen bukti')->required()->searchable()
                ->options(fn () => Document::query()->visibleTo(auth()->user(), filament()->getTenant())->orderByDesc('updated_at')->limit(500)->get()
                    ->mapWithKeys(fn ($d) => [$d->id => $d->code.' · '.$d->title.' ('.$d->status->getLabel().')']))
                ->helperText('Hanya bukti berstatus Valid yang dihitung mendukung kata kunci.'),
            Select::make('keyword_id')->label('Kata kunci yang dibuktikan')->options(fn () => $this->keywordOptions())->searchable(),
            TextInput::make('relevance_note')->label('Catatan relevansi')->maxLength(250)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['document', 'keyword']))
            ->columns([
                TextColumn::make('document.code')->label('Kode')->weight('bold'),
                TextColumn::make('document.title')->label('Dokumen')->wrap(),
                TextColumn::make('document.status')->label('Status')->badge(),
                TextColumn::make('document.valid_until')->label('Berlaku s.d.')->date('d/m/Y')->placeholder('—')
                    ->color(fn ($record) => $record->document?->valid_until?->isPast() ? 'danger' : null),
                TextColumn::make('keyword.text')->label('Kata kunci')->wrap()->placeholder('Belum dipetakan'),
                TextColumn::make('relevance_note')->label('Catatan')->wrap()->placeholder('—')->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tautkan bukti'),
                Action::make('upload')->label('Unggah bukti baru')->icon(Heroicon::OutlinedArrowUpTray)->color('gray')
                    ->url(fn () => DocumentResource::getUrl('create')),
            ])
            ->recordActions([
                Action::make('open')->label('Buka')->icon(Heroicon::OutlinedEye)->color('gray')
                    ->url(fn ($record) => DocumentResource::getUrl('view', ['record' => $record->document_id])),
                EditAction::make(), DeleteAction::make()->label('Lepas'),
            ]);
    }
}
