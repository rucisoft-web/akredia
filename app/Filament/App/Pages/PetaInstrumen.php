<?php

namespace App\Filament\App\Pages;

use App\Models\Criterion;
use App\Models\InstrumentVariant;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Penjelajah Pustaka Instrumen (M02): kriteria → indikator → sub-indikator → deskriptor kedua jalur dan kata kunci. */
class PetaInstrumen extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Persiapan Akreditasi';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Peta Instrumen IAPT 4.1';

    protected static ?string $title = 'Peta Instrumen IAPT 4.1';

    protected static ?string $slug = 'peta-instrumen';

    protected string $view = 'filament.pages.peta-instrumen';

    public ?int $variant = null;

    public string $search = '';

    public function mount(): void
    {
        $tenant = filament()->getTenant();
        $this->variant = $tenant?->activeProject()?->instrument_variant_id
            ?? InstrumentVariant::query()->where('institution_type', $tenant?->type?->value)->value('id')
            ?? InstrumentVariant::query()->value('id');
    }

    protected function getViewData(): array
    {
        $variant = InstrumentVariant::query()->with('version')->find($this->variant);
        $term = mb_strtolower(trim($this->search));
        $criteria = Criterion::query()->where('instrument_version_id', $variant?->instrument_version_id)->orderBy('number')
            ->with(['indicators' => fn ($q) => $q->orderBy('number')->with(['subIndicators' => fn ($q) => $q->where('instrument_variant_id', $this->variant)->orderBy('sort')->with('descriptors.keywords')])])
            ->get();

        if ($term !== '') {
            foreach ($criteria as $c) {
                $c->setRelation('indicators', $c->indicators->filter(fn ($i) => str_contains(mb_strtolower($i->statement.' '.$i->subIndicators->pluck('statement')->implode(' ').' '.$i->subIndicators->pluck('code')->implode(' ')), $term) || (string) $i->number === $term));
            }
        }

        return [
            'variants' => InstrumentVariant::query()->with('version')->orderBy('code')->get(),
            'variantModel' => $variant,
            'criteria' => $criteria,
            'targets' => \App\Services\InstrumentImporter::TARGETS,
        ];
    }
}
