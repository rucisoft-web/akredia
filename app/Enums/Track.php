<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jalur status akreditasi. */
enum Track: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Accredited = 'accredited';
    case Excellent = 'excellent';

    public const META = [
        'accredited' => ['Terakreditasi', 'info'],
        'excellent' => ['Terakreditasi Unggul', 'success'],
    ];
}
