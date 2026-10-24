<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use App\Support\CurrentInstitution;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Menyalin tenant Filament ke CurrentInstitution dan memeriksa ulang keanggotaan aktif. */
class SetCurrentInstitution
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Institution) {
            abort_unless($request->user()?->activeMembership($tenant), 403, 'Keanggotaan Anda di institusi ini tidak aktif.');
            app(CurrentInstitution::class)->set($tenant);
        }

        return $next($request);
    }
}
