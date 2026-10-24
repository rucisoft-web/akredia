<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

/** Kepuasan pengguna lulusan, tujuh aspek (Tabel II-1.D.2). */
class EmployerSatisfaction extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    public const ASPECTS = [
        'etika' => 'Etika',
        'keahlian' => 'Keahlian bidang ilmu (kompetensi utama)',
        'bahasa_asing' => 'Kemampuan berbahasa asing',
        'ti' => 'Penggunaan teknologi informasi',
        'komunikasi' => 'Kemampuan berkomunikasi',
        'kerja_sama' => 'Kerja sama tim',
        'pengembangan_diri' => 'Pengembangan diri',
    ];

    public function total(): int
    {
        return $this->sb + $this->b + $this->c + $this->kb;
    }
}
