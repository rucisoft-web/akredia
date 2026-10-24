<?php

namespace App\Filament\App\Resources\Documents\RelationManagers;

use App\Filament\App\Resources\WorkItems\WorkItemResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $title = 'Dipakai pada Butir Kerja';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['workItem.subIndicator', 'keyword']))
            ->columns([
                TextColumn::make('workItem.subIndicator.code')->label('Sub-indikator')->weight('bold'),
                TextColumn::make('workItem.subIndicator.statement')->label('Pernyataan')->wrap()->lineClamp(2),
                TextColumn::make('keyword.text')->label('Kata kunci')->wrap()->placeholder('—'),
                TextColumn::make('relevance_note')->label('Catatan')->placeholder('—'),
            ])
            ->recordUrl(fn ($record) => WorkItemResource::getUrl('view', ['record' => $record->work_item_id]));
    }
}
