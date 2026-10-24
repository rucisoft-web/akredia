<?php

namespace App\Filament\App\Resources\WorkItems\Pages;

use App\Enums\HeatColor;
use App\Filament\App\Resources\WorkItems\WorkItemResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWorkItems extends ListRecords
{
    protected static string $resource = WorkItemResource::class;

    public function getSubheading(): ?string
    {
        $project = filament()->getTenant()?->activeProject();

        return $project ? $project->name.' · jalur '.$project->track->getLabel().' · '.$project->variant->name : 'Belum ada proyek akreditasi aktif.';
    }

    public function getTabs(): array
    {
        $tab = fn (string $label, ?HeatColor $color = null) => Tab::make($label)
            ->badge(fn () => WorkItemResource::getEloquentQuery()->when($color, fn ($q) => $q->where('color', $color->value))->count())
            ->badgeColor($color?->getColor() ?? 'gray')
            ->modifyQueryUsing(fn (Builder $query) => $color ? $query->where('color', $color->value) : $query);

        return [
            'semua' => $tab('Semua'),
            'merah' => $tab('Merah', HeatColor::Red),
            'kuning' => $tab('Kuning', HeatColor::Yellow),
            'abu' => $tab('Belum dinilai', HeatColor::Gray),
            'hijau' => $tab('Hijau', HeatColor::Green),
        ];
    }
}
