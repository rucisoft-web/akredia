<?php

namespace App\Filament\App\Resources\Documents\Pages;

use App\Enums\DocumentStatus;
use App\Filament\App\Resources\Documents\DocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Unggah bukti')];
    }

    public function getTabs(): array
    {
        $tab = fn (string $label, ?DocumentStatus $s) => Tab::make($label)
            ->badge(fn () => DocumentResource::getEloquentQuery()->when($s, fn ($q) => $q->where('status', $s->value))->count())
            ->badgeColor($s?->getColor() ?? 'gray')
            ->modifyQueryUsing(fn ($query) => $s ? $query->where('status', $s->value) : $query);

        return [
            'semua' => $tab('Semua', null),
            'antre' => $tab('Menunggu validasi', DocumentStatus::Submitted),
            'valid' => $tab('Valid', DocumentStatus::Valid),
            'draf' => $tab('Draf', DocumentStatus::Draft),
            'ditolak' => $tab('Ditolak', DocumentStatus::Rejected),
            'kedaluwarsa' => $tab('Kedaluwarsa', DocumentStatus::Expired),
        ];
    }
}
