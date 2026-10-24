<?php

namespace App\Filament\App\Pages;

use App\Enums\Track;
use App\Services\ReadinessCalculator;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Simulasi what-if (M09): "bila sub-indikator ini dipenuhi, berapa proyeksi kita?" */
class SimulasiPenilaian extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Persiapan Akreditasi';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Simulasi Penilaian';

    protected static ?string $title = 'Simulasi Penilaian (What-if)';

    protected static ?string $slug = 'simulasi-penilaian';

    protected string $view = 'filament.pages.simulasi-penilaian';

    /** @var array<int, int> id butir kerja yang dianggap memenuhi */
    public array $assume = [];

    public string $track = 'accredited';

    public bool $prereqOnly = false;

    public function mount(): void
    {
        $this->track = filament()->getTenant()?->activeProject()?->track->value ?? 'accredited';
    }

    public function getSubheading(): ?string
    {
        return 'Centang sub-indikator yang belum memenuhi untuk melihat dampaknya pada proyeksi kedua jalur dan syarat perlu. Simulasi tidak mengubah data.';
    }

    public function clear(): void
    {
        $this->assume = [];
    }

    public function selectPrerequisites(array $ids): void
    {
        $this->assume = array_values(array_unique(array_merge($this->assume, $ids)));
    }

    protected function getViewData(): array
    {
        $project = filament()->getTenant()?->activeProject();

        if (! $project) {
            return ['project' => null];
        }

        $calc = app(ReadinessCalculator::class);
        $items = $calc->items($project);
        $track = Track::from($this->track);
        $assume = array_map('intval', $this->assume);
        $notMet = $items->filter(fn ($w) => ! $calc->isMet($w, $track))
            ->when($this->prereqOnly, fn ($c) => $c->filter(fn ($w) => $w->subIndicator->isPrerequisiteFor($track)));
        $total = max(0.0001, $items->sum(fn ($w) => $w->subIndicator->weight));

        $result = fn (Track $t, array $a) => [
            'projection' => $calc->projection($items, $t, $a),
            'failed' => $calc->failedPrerequisites($items, $t, $a)->count(),
            'threshold' => $project->threshold($t),
        ];

        return [
            'project' => $project,
            'notMet' => $notMet,
            'gain' => fn ($w) => round($w->subIndicator->weight / $total * 100, 2),
            'trackEnum' => $track,
            'before' => ['accredited' => $result(Track::Accredited, []), 'excellent' => $result(Track::Excellent, [])],
            'after' => ['accredited' => $result(Track::Accredited, $assume), 'excellent' => $result(Track::Excellent, $assume)],
            'prereqIds' => $notMet->filter(fn ($w) => $w->subIndicator->isPrerequisiteFor($track))->pluck('id')->values()->all(),
        ];
    }
}
