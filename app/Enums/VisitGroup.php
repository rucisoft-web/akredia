<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Kelompok narasumber asesmen lapangan. */
enum VisitGroup: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Pimpinan = 'pimpinan';
    case Dosen = 'dosen';
    case Tendik = 'tendik';
    case Mahasiswa = 'mahasiswa';
    case Lulusan = 'lulusan';
    case Pengguna = 'pengguna';
    case Observasi = 'observasi';

    public const META = [
        'pimpinan' => ['Pimpinan', 'primary'],
        'dosen' => ['Dosen', 'info'],
        'tendik' => ['Tenaga Kependidikan', 'gray'],
        'mahasiswa' => ['Mahasiswa', 'warning'],
        'lulusan' => ['Lulusan', 'success'],
        'pengguna' => ['Pengguna Lulusan', 'danger'],
        'observasi' => ['Observasi Lapangan', 'gray'],
    ];
}
