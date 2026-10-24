<?php

namespace App\Support;

/** Daftar pilihan sederhana yang tidak perlu enum tersendiri. */
class Labels
{
    public const SCALE = [1 => '1 · Sangat rendah', 2 => '2 · Rendah', 3 => '3 · Sedang', 4 => '4 · Tinggi', 5 => '5 · Sangat tinggi'];

    public const UNIT_TYPES = [
        'rektorat' => 'Rektorat / Pimpinan',
        'fakultas' => 'Fakultas',
        'lembaga' => 'Lembaga (LPM, LPPM)',
        'biro' => 'Biro / Direktorat',
        'upt' => 'UPT / Pusat',
        'satgas' => 'Satgas / Komite',
    ];

    public const LEVELS = ['S3' => 'S3', 'S2' => 'S2', 'S1' => 'S1', 'D4' => 'D4 / Sarjana Terapan', 'D3' => 'D3', 'Profesi' => 'Profesi', 'Sp' => 'Spesialis'];

    public const RECOGNITIONS = [
        'narasumber' => 'Narasumber / pembicara kunci',
        'editor' => 'Editor / mitra bestari jurnal',
        'juri' => 'Juri / penilai',
        'penghargaan' => 'Penghargaan',
        'pakar' => 'Staf ahli / tenaga pakar',
        'visiting' => 'Visiting lecturer / professor',
    ];

    public const FUND_SOURCES = [
        'internal' => 'Internal PT',
        'nasional' => 'Nasional (kementerian, BRIN, LPDP)',
        'industri' => 'Industri / mitra',
        'internasional' => 'Internasional',
        'mandiri' => 'Mandiri',
    ];
}
