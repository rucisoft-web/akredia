<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status kata kunci pada kertas kerja. */
enum GapStatus: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Belum = 'belum';
    case Sebagian = 'sebagian';
    case Terbukti = 'terbukti';

    public const META = [
        'belum' => ['Belum terbukti', 'danger'],
        'sebagian' => ['Sebagian', 'warning'],
        'terbukti' => ['Terbukti', 'success'],
    ];
}
