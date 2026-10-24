<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Warna heatmap butir kerja, ditentukan aturan. */
enum HeatColor: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Green = 'green';
    case Yellow = 'yellow';
    case Red = 'red';
    case Gray = 'gray';

    public const META = [
        'green' => ['Hijau', 'success'],
        'yellow' => ['Kuning', 'warning'],
        'red' => ['Merah', 'danger'],
        'gray' => ['Abu-abu', 'gray'],
    ];
}
