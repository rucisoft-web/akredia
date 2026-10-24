<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Tingkat keparahan selisih rekonsiliasi. */
enum Severity: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Tinggi = 'tinggi';
    case Sedang = 'sedang';
    case Rendah = 'rendah';
    case Toleransi = 'toleransi';

    public const META = [
        'tinggi' => ['Tinggi', 'danger'],
        'sedang' => ['Sedang', 'warning'],
        'rendah' => ['Rendah', 'info'],
        'toleransi' => ['Dalam toleransi', 'success'],
    ];
}
