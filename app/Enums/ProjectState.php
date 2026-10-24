<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status proyek akreditasi (Tabel 14). */
enum ProjectState: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Draft = 'draft';
    case Preparation = 'preparation';
    case Drafting = 'drafting';
    case Finalization = 'finalization';
    case Ready = 'ready';
    case Submitted = 'submitted';
    case Assessment = 'assessment';
    case Done = 'done';

    public const META = [
        'draft' => ['Draf', 'gray'],
        'preparation' => ['Persiapan', 'info'],
        'drafting' => ['Penyusunan', 'info'],
        'finalization' => ['Finalisasi', 'warning'],
        'ready' => ['Siap Diajukan', 'success'],
        'submitted' => ['Diajukan', 'primary'],
        'assessment' => ['Asesmen', 'primary'],
        'done' => ['Selesai', 'success'],
    ];
}
