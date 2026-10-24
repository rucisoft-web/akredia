<?php

namespace App\Filament\App\Resources\Documents\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Riwayat Versi';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('version', 'desc')
            ->columns([
                TextColumn::make('version')->label('Versi')->prefix('v')->weight('bold'),
                TextColumn::make('original_name')->label('Berkas / tautan')->state(fn ($record) => $record->original_name ?? $record->url)->wrap(),
                TextColumn::make('size')->label('Ukuran')->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 0, ',', '.').' KB' : '—'),
                TextColumn::make('sha256')->label('SHA-256')->limit(16)->copyable()->tooltip(fn ($state) => $state)->placeholder('—')->fontFamily('mono'),
                TextColumn::make('note')->label('Catatan')->placeholder('—'),
                TextColumn::make('uploader.name')->label('Oleh')->placeholder('—'),
                TextColumn::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                Action::make('download')->label('Unduh')->icon(Heroicon::OutlinedArrowDownTray)->color('gray')
                    ->visible(fn ($record) => (bool) $record->file_path)
                    ->url(fn ($record) => URL::temporarySignedRoute('dokumen.unduh', now()->addMinutes(30), ['version' => $record->id]), true),
            ]);
    }
}
