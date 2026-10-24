<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Sasaran mutu indikator. */
enum QualityTarget: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Input = 'input';
    case Process = 'process';
    case Output = 'output';
    case Impact = 'impact';

    public const META = [
        'input' => ['Masukan', 'gray'],
        'process' => ['Proses', 'info'],
        'output' => ['Luaran', 'warning'],
        'impact' => ['Dampak', 'success'],
    ];
}
