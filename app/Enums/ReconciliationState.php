<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Alur rekonsiliasi. */
enum ReconciliationState: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Open = 'open';
    case Investigation = 'investigation';
    case Fixed = 'fixed';
    case Verified = 'verified';

    public const META = [
        'open' => ['Terbuka', 'danger'],
        'investigation' => ['Investigasi', 'warning'],
        'fixed' => ['Diperbaiki di Sumber', 'info'],
        'verified' => ['Terverifikasi', 'success'],
    ];
}
