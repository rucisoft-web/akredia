<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Peran pengguna dalam satu institusi (Tabel 11 rancangan). */
enum Role: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Admin = 'admin';
    case Pimpinan = 'pimpinan';
    case Manajer = 'manajer';
    case Pic = 'pic';
    case PemilikData = 'pemilik_data';
    case Reviewer = 'reviewer';
    case Asesor = 'asesor';
    case Pengamat = 'pengamat';

    public const META = [
        'admin' => ['Administrator Sistem', 'danger'],
        'pimpinan' => ['Pimpinan', 'primary'],
        'manajer' => ['Manajer Akreditasi', 'primary'],
        'pic' => ['PIC Indikator', 'info'],
        'pemilik_data' => ['Pemilik Data', 'info'],
        'reviewer' => ['Reviewer', 'warning'],
        'asesor' => ['Asesor Internal', 'warning'],
        'pengamat' => ['Pengamat (baca saja)', 'gray'],
    ];

    /** Kelola proyek, PIC, gerbang kesiapan, konfigurasi, dan penghapusan. */
    public function canManage(): bool
    {
        return in_array($this, [self::Admin, self::Manajer], true);
    }

    /** Mencatat dan mengubah data kerja (bukti, LED, data, tugas). */
    public function canWrite(): bool
    {
        return ! in_array($this, [self::Pengamat, self::Pimpinan], true);
    }

    /** Mereviu bukti dan LED. */
    public function canReview(): bool
    {
        return in_array($this, [self::Admin, self::Manajer, self::Reviewer], true);
    }

    /** Penilaian independen asesor internal dan temuan AMI. */
    public function canAudit(): bool
    {
        return in_array($this, [self::Admin, self::Manajer, self::Asesor], true);
    }

    /** Keputusan pimpinan: mengunci jalur, mengesahkan LED. */
    public function canLead(): bool
    {
        return in_array($this, [self::Admin, self::Pimpinan], true);
    }

    /** Peran yang dibatasi cakupan unit untuk bukti rahasia. */
    public function isUnitScoped(): bool
    {
        return in_array($this, [self::Pic, self::PemilikData], true);
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Pengguna, peran, konfigurasi, impor instrumen, cadangan.',
            self::Pimpinan => 'Melihat semua; mengunci jalur, mengesahkan LED, keputusan pimpinan.',
            self::Manajer => 'Mengelola proyek, PIC, jadwal, gerbang kesiapan; membuka dan mengunci data.',
            self::Pic => 'Analisis kesenjangan, menulis LED, menautkan bukti pada indikator yang ditugaskan.',
            self::PemilikData => 'Mengunggah bukti dan data dalam cakupan unitnya; menanggapi rekonsiliasi.',
            self::Reviewer => 'Mereviu bukti dan LED; tidak boleh mereviu karyanya sendiri.',
            self::Asesor => 'Simulasi penilaian independen; membuat temuan.',
            self::Pengamat => 'Baca saja.',
        };
    }
}
