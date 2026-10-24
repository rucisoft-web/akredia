<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Lembaga akreditasi prodi. */
enum AccreditationAgency: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Banpt = 'banpt';
    case LamInfokom = 'lam_infokom';
    case LamEmba = 'lam_emba';
    case LamPtkes = 'lam_ptkes';
    case LamTeknik = 'lam_teknik';
    case LamSama = 'lam_sama';
    case LamDik = 'lam_dik';
    case LamSpak = 'lam_spak';
    case Intl = 'intl';

    public const META = [
        'banpt' => ['BAN-PT', 'primary'],
        'lam_infokom' => ['LAM INFOKOM', 'info'],
        'lam_emba' => ['LAMEMBA', 'info'],
        'lam_ptkes' => ['LAM-PTKes', 'info'],
        'lam_teknik' => ['LAM Teknik', 'info'],
        'lam_sama' => ['LAMSAMA', 'info'],
        'lam_dik' => ['LAMDIK', 'info'],
        'lam_spak' => ['LAMSPAK', 'info'],
        'intl' => ['Internasional (ABET/ASIIN/AUN-QA/FIBAA)', 'success'],
    ];
}
