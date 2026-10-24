<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Prioritas. */
enum Priority: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Rendah = 'rendah';
    case Sedang = 'sedang';
    case Tinggi = 'tinggi';
    case Mendesak = 'mendesak';

    public const META = [
        'rendah' => ['Rendah', 'gray'],
        'sedang' => ['Sedang', 'info'],
        'tinggi' => ['Tinggi', 'warning'],
        'mendesak' => ['Mendesak', 'danger'],
    ];
}
