<?php

namespace App\Models;

use App\Enums\Stakeholder;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Survei kepuasan per kelompok pemangku kepentingan dan tahun (Tabel 24 LKPT). */
class Survey extends Model
{
    use Auditable, BelongsToInstitution;

    /** Butir kuesioner kepuasan terhadap tata pamong (skala SB/B/C/KB). */
    public const QUESTIONS = [
        'Kejelasan visi, misi, dan arah pengembangan institusi',
        'Transparansi dan akuntabilitas pengambilan keputusan',
        'Kualitas layanan administrasi dan akademik',
        'Ketersediaan dan kemudahan akses sarana prasarana serta TIK',
        'Kecepatan tanggapan terhadap keluhan dan masukan',
        'Keadilan, keamanan, dan bebas kekerasan di lingkungan kampus',
    ];

    public const SCALE = ['sb' => 'Sangat Baik', 'b' => 'Baik', 'c' => 'Cukup', 'kb' => 'Kurang Baik'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['stakeholder' => Stakeholder::class, 'is_open' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(fn (Survey $s) => $s->public_token ??= Str::random(32));
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function totalAnswers(): int
    {
        return $this->sb + $this->b + $this->c + $this->kb;
    }

    /** Persentase jawaban Sangat Baik + Baik. */
    public function satisfaction(): float
    {
        $total = $this->totalAnswers();

        return $total > 0 ? round(($this->sb + $this->b) / $total * 100, 1) : 0;
    }

    /** Indeks 1–4 (SB=4 … KB=1). */
    public function index(): float
    {
        $total = $this->totalAnswers();

        return $total > 0 ? round((4 * $this->sb + 3 * $this->b + 2 * $this->c + $this->kb) / $total, 2) : 0;
    }

    /** Tambahkan satu jawaban kuesioner publik ke rekap (4 = SB … 1 = KB). */
    public function addResponse(array $answers): void
    {
        $map = [4 => 'sb', 3 => 'b', 2 => 'c', 1 => 'kb'];

        foreach ($answers as $value) {
            $this->{$map[(int) $value] ?? 'c'}++;
        }

        $this->respondents++;
        $this->save();
    }

    public function responseRate(): float
    {
        return $this->population > 0 ? round($this->respondents / $this->population * 100, 1) : 0;
    }
}
