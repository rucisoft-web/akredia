<?php

namespace App\Filament\App\Resources\LedSections\Pages;

use App\Enums\LedState;
use App\Filament\App\Resources\LedSections\LedSectionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListLedSections extends ListRecords
{
    protected static string $resource = LedSectionResource::class;

    public function getSubheading(): ?string
    {
        return 'Satu bagian narasi per sub-indikator. Sitasi bukti ditulis [B:KODE], angka hidup ditulis {{KODE_METRIK}}.';
    }

    public function getTabs(): array
    {
        $tab = fn (string $label, array $states = []) => Tab::make($label)
            ->badge(fn () => LedSectionResource::getEloquentQuery()->when($states, fn ($q) => $q->whereIn('state', $states))->count())
            ->modifyQueryUsing(fn ($query) => $states ? $query->whereIn('state', $states) : $query);

        return [
            'semua' => $tab('Semua'),
            'draf' => $tab('Draf & revisi', [LedState::Draft->value, LedState::Revision->value]),
            'reviu' => $tab('Menunggu reviu', [LedState::Review->value]),
            'setuju' => $tab('Disetujui', [LedState::ReviewerApproved->value, LedState::Ratified->value]),
        ];
    }
}
