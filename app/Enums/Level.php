<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Tingkat prestasi/rekognisi. */
enum Level: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Lokal = 'lokal';
    case Provinsi = 'provinsi';
    case Nasional = 'nasional';
    case Internasional = 'internasional';

    public const META = [
        'lokal' => ['Lokal / Wilayah', 'gray'],
        'provinsi' => ['Provinsi', 'info'],
        'nasional' => ['Nasional', 'primary'],
        'internasional' => ['Internasional', 'success'],
    ];
}
