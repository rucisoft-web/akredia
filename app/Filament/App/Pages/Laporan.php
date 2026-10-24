<?php

namespace App\Filament\App\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Laporan bawaan dan ekspor (M14). */
class Laporan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Ringkasan';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Laporan & Ekspor';

    protected static ?string $title = 'Laporan & Ekspor';

    protected static ?string $slug = 'laporan';

    protected string $view = 'filament.pages.laporan';

    protected function getViewData(): array
    {
        $i = filament()->getTenant();
        $r = fn (string $name) => route('laporan.'.$name, ['institution' => $i]);
        $role = auth()->user()?->roleIn($i);

        return ['reports' => array_filter([
            ['Laporan mingguan pimpinan', 'Ringkasan AKI, proyeksi, syarat perlu, gerbang, status per kriteria, tugas terlambat, dan prodi yang akreditasinya segera berakhir. Cetak atau simpan sebagai PDF.', $r('mingguan'), 'Buka & cetak'],
            ['Status syarat perlu (1 halaman)', 'Daftar sub-indikator syarat perlu beserta status kedua jalur dan tujuh syarat gerbang Siap Diajukan.', $r('syarat-perlu'), 'Buka & cetak'],
            ['LED lengkap (DOCX)', 'Seluruh narasi per kriteria, indikator, dan sub-indikator dengan sitasi bukti diganti judul dan URL publik, angka hidup diganti nilai.', $r('led-docx'), 'Unduh DOCX'],
            ['LED lengkap (cetak/PDF)', 'Versi tampilan untuk dibaca pimpinan atau disimpan sebagai PDF.', $r('led'), 'Buka & cetak'],
            ['Tabel LKPT (XLSX)', 'Dua belas tabel LKPT dari Data Kinerja untuk TS proyek.', $r('lkpt'), 'Unduh XLSX'],
            ['Kertas kerja PIC (XLSX)', 'Butir kerja dan kertas kerja per kata kunci beserta RTL dan tugas.', $r('kertas-kerja'), 'Unduh XLSX'],
            ['Daftar bukti publik (XLSX)', 'Bukti berstatus publik beserta URL untuk ditempel di LED SAPTO. Portal publik: '.route('bukti.index', $i), $r('bukti-publik'), 'Unduh XLSX'],
            ($role?->canManage() || $role?->canLead()) ? ['Log audit (CSV)', 'Seluruh jejak perubahan, login, ekspor, dan unduhan bukti rahasia.', $r('audit'), 'Unduh CSV'] : null,
        ])];
    }
}
