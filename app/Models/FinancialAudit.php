<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Audit keuangan eksternal (indikator 35). */
class FinancialAudit extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public const OPINIONS = ['WTP' => 'Wajar Tanpa Pengecualian', 'WDP' => 'Wajar Dengan Pengecualian', 'TW' => 'Tidak Wajar', 'TMP' => 'Tidak Memberikan Pendapat'];
}
