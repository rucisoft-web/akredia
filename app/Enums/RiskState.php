<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status risiko. */
enum RiskState: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Open = 'open';
    case Mitigating = 'mitigating';
    case Closed = 'closed';

    public const META = [
        'open' => ['Terbuka', 'danger'],
        'mitigating' => ['Dimitigasi', 'warning'],
        'closed' => ['Ditutup', 'success'],
    ];
}
