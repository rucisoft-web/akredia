<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jabatan akademik dosen. */
enum AcademicRank: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Gb = 'gb';
    case Lk = 'lk';
    case L = 'l';
    case Aa = 'aa';
    case None = 'none';

    public const META = [
        'gb' => ['Guru Besar', 'success'],
        'lk' => ['Lektor Kepala', 'primary'],
        'l' => ['Lektor', 'info'],
        'aa' => ['Asisten Ahli', 'warning'],
        'none' => ['Belum ber-JAFA', 'danger'],
    ];
}
