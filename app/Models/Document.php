<?php

namespace App\Models;

use App\Enums\Confidentiality;
use App\Enums\DocType;
use App\Enums\DocumentStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/** Dokumen bukti berversi; satu dokumen dapat mendukung banyak sub-indikator. */
class Document extends Model
{
    use Auditable, BelongsToInstitution;

    protected $guarded = ['id'];

    protected array $tracked = ['status', 'valid_until', 'confidentiality', 'is_public', 'validated_by'];

    protected array $institutionParents = ['owner_unit_id' => Unit::class];

    protected function casts(): array
    {
        return [
            'doc_type' => DocType::class,
            'status' => DocumentStatus::class,
            'confidentiality' => Confidentiality::class,
            'is_public' => 'boolean',
            'doc_date' => 'date',
            'valid_until' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Document $doc) {
            $doc->code ??= \App\Support\Sequence::next('document', $doc->institution_id ?? app(\App\Support\CurrentInstitution::class)->id());
        });

        static::saving(function (Document $doc) {
            if ($doc->is_public) {
                $doc->confidentiality = Confidentiality::Public;
                $doc->public_slug ??= static::uniqueSlug($doc);
            }
        });
    }

    protected static function uniqueSlug(Document $doc): string
    {
        $base = Str::slug(Str::limit($doc->title, 60, '')) ?: 'bukti';
        $slug = $base;

        for ($i = 2; static::withoutGlobalScopes()->where('institution_id', $doc->institution_id)->where('public_slug', $slug)->where('id', '!=', $doc->id)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function ownerUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'owner_unit_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->latestOfMany('version');
    }

    public function links(): HasMany
    {
        return $this->hasMany(EvidenceLink::class);
    }

    public function workItems(): BelongsToMany
    {
        return $this->belongsToMany(WorkItem::class, 'evidence_links')->withPivot(['keyword_id', 'relevance_note'])->withTimestamps();
    }

    public function linkCheck(): HasOne
    {
        return $this->hasOne(LinkCheck::class)->latestOfMany();
    }

    /** Bukti yang dapat dihitung untuk kesiapan: valid, belum kedaluwarsa, tautannya tidak rusak. */
    public function isUsable(): bool
    {
        return $this->status === DocumentStatus::Valid
            && (! $this->valid_until || ! $this->valid_until->isPast())
            && ($this->relationLoaded('linkCheck') ? ($this->linkCheck?->is_ok ?? true) : true);
    }

    public function publicUrl(): ?string
    {
        return $this->is_public && $this->public_slug
            ? route('bukti.show', ['institution' => $this->institution?->slug ?? Institution::find($this->institution_id)?->slug, 'slug' => $this->public_slug])
            : null;
    }

    /**
     * Cakupan akses: PIC/Pemilik Data yang dibatasi unit tidak melihat bukti rahasia unit lain.
     */
    public function scopeVisibleTo(Builder $query, ?User $user, ?Institution $institution): Builder
    {
        $membership = $user?->activeMembership($institution);

        if (! $membership || ! $membership->role->isUnitScoped() || ! $membership->unit_id) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('confidentiality', '!=', Confidentiality::Secret->value)
            ->orWhere('owner_unit_id', $membership->unit_id)
            ->orWhere('uploaded_by', $user->id));
    }
}
