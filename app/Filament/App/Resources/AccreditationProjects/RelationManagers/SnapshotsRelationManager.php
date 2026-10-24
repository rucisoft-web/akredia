<?php

namespace App\Filament\App\Resources\AccreditationProjects\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SnapshotsRelationManager extends RelationManager
{
    protected static string $relationship = 'snapshots';

    protected static ?string $title = 'Riwayat Kesiapan';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('taken_on', 'desc')
            ->columns([
                TextColumn::make('taken_on')->label('Tanggal')->date('d/m/Y'),
                TextColumn::make('aki')->label('AKI')->numeric(1, locale: 'id')->weight('bold'),
                TextColumn::make('projection_accredited')->label('Proyeksi Terakreditasi')->suffix('%')->numeric(1, locale: 'id'),
                TextColumn::make('projection_excellent')->label('Proyeksi Unggul')->suffix('%')->numeric(1, locale: 'id'),
                TextColumn::make('prerequisites_failed')->label('Syarat perlu gagal')->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
            ]);
    }
}
