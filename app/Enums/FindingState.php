<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Alur temuan dan CAPA. */
enum FindingState: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Open = 'open';
    case Capa = 'capa';
    case Verification = 'verification';
    case Closed = 'closed';

    public const META = [
        'open' => ['Terbuka', 'danger'],
        'capa' => ['CAPA', 'warning'],
        'verification' => ['Verifikasi Efektivitas', 'info'],
        'closed' => ['Ditutup', 'success'],
    ];
}
