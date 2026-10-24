<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jenis PT yang menentukan varian Buku 3 IAPT 4.1. */
enum InstitutionType: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Ptnbh = 'ptnbh';
    case PtnAkademik = 'ptn_akademik';
    case PtsAkademik = 'pts_akademik';
    case Pjj = 'pjj';
    case PtnVokasi = 'ptn_vokasi';
    case PtsVokasi = 'pts_vokasi';
    case Ptkl = 'ptkl';
    case Akom = 'akom';

    public const META = [
        'ptnbh' => ['PTN Badan Hukum', 'primary'],
        'ptn_akademik' => ['PTN Akademik', 'primary'],
        'pts_akademik' => ['PTS Akademik', 'info'],
        'pjj' => ['PT Akademik PJJ', 'info'],
        'ptn_vokasi' => ['PTN Vokasi', 'success'],
        'pts_vokasi' => ['PTS Vokasi', 'success'],
        'ptkl' => ['PT Kementerian Lain / LPNK', 'warning'],
        'akom' => ['Akademi Komunitas', 'gray'],
    ];
}
