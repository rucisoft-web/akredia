<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Enam kelompok pemangku kepentingan (Tabel 24 LKPT). */
enum Stakeholder: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Mahasiswa = 'mahasiswa';
    case Dosen = 'dosen';
    case Tendik = 'tendik';
    case Mitra = 'mitra';
    case Lulusan = 'lulusan';
    case Pengguna = 'pengguna';

    public const META = [
        'mahasiswa' => ['Mahasiswa', 'info'],
        'dosen' => ['Dosen', 'primary'],
        'tendik' => ['Tenaga Kependidikan', 'gray'],
        'mitra' => ['Mitra', 'success'],
        'lulusan' => ['Lulusan', 'warning'],
        'pengguna' => ['Pengguna Lulusan', 'danger'],
    ];
}
