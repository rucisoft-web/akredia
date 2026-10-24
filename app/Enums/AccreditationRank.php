<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status/peringkat akreditasi prodi. */
enum AccreditationRank: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Unggul = 'unggul';
    case BaikSekali = 'baik_sekali';
    case Baik = 'baik';
    case A = 'a';
    case B = 'b';
    case C = 'c';
    case Terakreditasi = 'terakreditasi';
    case Minimum = 'minimum';
    case Internasional = 'internasional';
    case Tidak = 'tidak';

    public const META = [
        'unggul' => ['Unggul', 'success'],
        'baik_sekali' => ['Baik Sekali', 'info'],
        'baik' => ['Baik', 'warning'],
        'a' => ['A', 'success'],
        'b' => ['B', 'info'],
        'c' => ['C', 'warning'],
        'terakreditasi' => ['Terakreditasi', 'info'],
        'minimum' => ['Terakreditasi Minimum', 'warning'],
        'internasional' => ['Internasional', 'success'],
        'tidak' => ['Tidak Terakreditasi', 'danger'],
    ];
}
