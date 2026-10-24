<?php

namespace App\Enums;

use App\Enums\Concerns\EnumMeta;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Status tugas. */
enum TaskStatus: string implements HasColor, HasLabel
{
    use EnumMeta;

    case Todo = 'todo';
    case Progress = 'progress';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public const META = [
        'todo' => ['Belum Dikerjakan', 'gray'],
        'progress' => ['Dikerjakan', 'info'],
        'done' => ['Selesai', 'success'],
        'cancelled' => ['Dibatalkan', 'danger'],
    ];
}
