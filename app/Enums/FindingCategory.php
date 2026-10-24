<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Kategori temuan AMI. */
enum FindingCategory: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Mayor = 'mayor';
    case Minor = 'minor';
    case Observasi = 'observasi';
    case Peluang = 'peluang';

    public const META = [
        'mayor' => ['KTS Mayor', 'danger'],
        'minor' => ['KTS Minor', 'warning'],
        'observasi' => ['Observasi', 'info'],
        'peluang' => ['Peluang Peningkatan', 'success'],
    ];
}
