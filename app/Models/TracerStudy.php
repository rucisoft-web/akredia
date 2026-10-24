<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

/** Rekap tracer study per angkatan lulus. */
class TracerStudy extends Model
{
    use BelongsToInstitution;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'avg_wait_months' => 'float',
        ];
    }

    public function pllk(): float
    {
        return $this->graduates > 0 ? round($this->within_one_year / $this->graduates * 100, 2) : 0;
    }
}
