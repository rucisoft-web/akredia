<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Keputusan pimpinan yang dapat menurunkan tugas. */
class Decision extends Model
{
    use BelongsToInstitution, Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'decision_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }

    public const FORUMS = ['rapat_pimpinan' => 'Rapat Pimpinan', 'senat' => 'Rapat Senat', 'rtm' => 'Rapat Tinjauan Manajemen', 'yayasan' => 'Badan Penyelenggara', 'taskforce' => 'Rapat Tim Taskforce'];

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'source_id')->where('source', 'decision');
    }

    protected static function booted(): void
    {
        static::creating(fn (Decision $d) => $d->number ??= \App\Support\Sequence::next('decision', $d->institution_id ?? app(\App\Support\CurrentInstitution::class)->id()));
    }
}
