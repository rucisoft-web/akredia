<?php

namespace App\Support;

use App\Models\Institution;

/**
 * Institusi aktif untuk permintaan atau perintah yang sedang berjalan.
 *
 * Diisi middleware tenant Filament dan perintah artisan, lalu dipakai global scope BelongsToInstitution.
 */
class CurrentInstitution
{
    protected ?Institution $institution = null;

    public function set(?Institution $institution): void
    {
        $this->institution = $institution;
    }

    public function get(): ?Institution
    {
        return $this->institution;
    }

    public function id(): ?int
    {
        return $this->institution?->getKey();
    }

    public function runAs(Institution $institution, callable $callback): mixed
    {
        $previous = $this->institution;
        $this->institution = $institution;

        try {
            return $callback();
        } finally {
            $this->institution = $previous;
        }
    }
}
