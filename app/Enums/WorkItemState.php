<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status butir kerja per sub-indikator. */
enum WorkItemState: string implements HasColor, HasLabel
{
    use EnumMeta;

    case NotAssessed = 'not_assessed';
    case GapAnalysis = 'gap_analysis';
    case Evidence = 'evidence';
    case Writing = 'writing';
    case Review = 'review';
    case Approved = 'approved';

    public const META = [
        'not_assessed' => ['Belum Dinilai', 'gray'],
        'gap_analysis' => ['Analisis Kesenjangan', 'info'],
        'evidence' => ['Pengumpulan Bukti', 'info'],
        'writing' => ['Penulisan LED', 'warning'],
        'review' => ['Reviu', 'warning'],
        'approved' => ['Disetujui', 'success'],
    ];
}
