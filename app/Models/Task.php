<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToInstitution;
use App\Support\Sequence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tugas dan RTL; bisa berasal dari kertas kerja, keputusan pimpinan, temuan, atau rekomendasi asesor. */
class Task extends Model
{
    use Auditable, BelongsToInstitution;

    public const SOURCES = [
        'manual' => 'Manual',
        'rtl' => 'RTL kertas kerja',
        'decision' => 'Keputusan pimpinan',
        'finding' => 'Temuan AMI',
        'recommendation' => 'Rekomendasi asesor',
        'reconciliation' => 'Rekonsiliasi data',
    ];

    protected $guarded = ['id'];

    protected array $tracked = ['status', 'assignee_id', 'due_date'];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => Priority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'escalated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Task $t) => $t->number ??= Sequence::next('task', $t->institution_id ?? app(\App\Support\CurrentInstitution::class)->id()));

        static::saving(function (Task $t) {
            if ($t->isDirty('status')) {
                $t->completed_at = $t->status === TaskStatus::Done ? ($t->completed_at ?? now()) : null;
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccreditationProject::class, 'accreditation_project_id');
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! $this->due_date->isToday()
            && in_array($this->status, [TaskStatus::Todo, TaskStatus::Progress], true);
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', [TaskStatus::Todo->value, TaskStatus::Progress->value]);
    }

    public function scopeOverdue(Builder $q): Builder
    {
        return $q->open()->whereDate('due_date', '<', today());
    }
}
