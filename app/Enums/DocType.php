<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Jenis dokumen bukti. */
enum DocType: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Kebijakan = 'kebijakan';
    case Peraturan = 'peraturan';
    case Sk = 'sk';
    case Pedoman = 'pedoman';
    case Renstra = 'renstra';
    case Laporan = 'laporan';
    case Notulen = 'notulen';
    case Data = 'data';
    case Sertifikat = 'sertifikat';
    case Kerjasama = 'kerjasama';
    case Laman = 'laman';
    case Lainnya = 'lainnya';

    public const META = [
        'kebijakan' => ['Kebijakan', 'primary'],
        'peraturan' => ['Peraturan / Statuta', 'primary'],
        'sk' => ['Surat Keputusan', 'info'],
        'pedoman' => ['Pedoman / Manual / SOP', 'info'],
        'renstra' => ['Renstra / Rencana', 'primary'],
        'laporan' => ['Laporan', 'warning'],
        'notulen' => ['Notulen / Berita Acara', 'gray'],
        'data' => ['Data / Rekap', 'gray'],
        'sertifikat' => ['Sertifikat / Penghargaan', 'success'],
        'kerjasama' => ['Dokumen Kerja Sama', 'success'],
        'laman' => ['Laman Web', 'info'],
        'lainnya' => ['Lainnya', 'gray'],
    ];
}
