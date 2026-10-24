<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status bukti. */
enum DocumentStatus: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Draft = 'draft';
    case Submitted = 'submitted';
    case Valid = 'valid';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public const META = [
        'draft' => ['Draf', 'gray'],
        'submitted' => ['Diajukan', 'warning'],
        'valid' => ['Valid', 'success'],
        'rejected' => ['Ditolak', 'danger'],
        'expired' => ['Kedaluwarsa', 'danger'],
    ];
}
