<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Tingkat kerahasiaan bukti. */
enum Confidentiality: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Public = 'publik';
    case Internal = 'internal';
    case Secret = 'rahasia';

    public const META = [
        'publik' => ['Publik', 'success'],
        'internal' => ['Internal', 'info'],
        'rahasia' => ['Rahasia', 'danger'],
    ];
}
