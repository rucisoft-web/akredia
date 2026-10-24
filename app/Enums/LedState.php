<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status bagian LED. */
enum LedState: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Draft = 'draft';
    case Review = 'review';
    case Revision = 'revision';
    case ReviewerApproved = 'reviewer_approved';
    case Ratified = 'ratified';

    public const META = [
        'draft' => ['Draf', 'gray'],
        'review' => ['Reviu', 'warning'],
        'revision' => ['Revisi', 'danger'],
        'reviewer_approved' => ['Disetujui Reviewer', 'info'],
        'ratified' => ['Disahkan Pimpinan', 'success'],
    ];
}
