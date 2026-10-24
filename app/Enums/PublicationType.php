<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jenis publikasi (Tabel II-2.C.2). */
enum PublicationType: string implements HasColor, HasLabel
{
    use EnumMeta;

    case JIB = 'jurnal_intl_bereputasi';
    case JI = 'jurnal_intl';
    case JNT = 'jurnal_nas_terakreditasi';
    case JN = 'jurnal_nas';
    case SI = 'seminar_intl';
    case SN = 'seminar_nas';
    case MM = 'media_massa';

    public const META = [
        'jurnal_intl_bereputasi' => ['Jurnal internasional bereputasi', 'success'],
        'jurnal_intl' => ['Jurnal internasional', 'primary'],
        'jurnal_nas_terakreditasi' => ['Jurnal nasional terakreditasi', 'info'],
        'jurnal_nas' => ['Jurnal nasional', 'gray'],
        'seminar_intl' => ['Seminar internasional', 'warning'],
        'seminar_nas' => ['Seminar nasional', 'gray'],
        'media_massa' => ['Media massa', 'gray'],
    ];
}
