<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Versi berkas bukti dengan hash SHA-256. */
class DocumentVersion extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    /** @var array<string, class-string> */
    protected array $institutionParents = ['document_id' => Document::class];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function auditLabel(): string
    {
        return $this->document?->title.' v'.$this->version;
    }

    public function sizeLabel(): string
    {
        return $this->size ? number_format($this->size / 1024, 0, ',', '.').' KB' : '-';
    }
}
