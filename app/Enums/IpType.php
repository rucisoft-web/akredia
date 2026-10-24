<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jenis kekayaan intelektual. */
enum IpType: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Paten = 'paten';
    case PatenSederhana = 'paten_sederhana';
    case HakCipta = 'hak_cipta';
    case Merek = 'merek';
    case Desain = 'desain_industri';
    case Lainnya = 'lainnya';

    public const META = [
        'paten' => ['Paten', 'success'],
        'paten_sederhana' => ['Paten Sederhana', 'primary'],
        'hak_cipta' => ['Hak Cipta', 'info'],
        'merek' => ['Merek', 'warning'],
        'desain_industri' => ['Desain Industri', 'gray'],
        'lainnya' => ['Teknologi Tepat Guna / Lainnya', 'gray'],
    ];
}
