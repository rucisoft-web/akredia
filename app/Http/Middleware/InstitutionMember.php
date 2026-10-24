<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use App\Support\CurrentInstitution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Rute di luar panel (laporan): parameter {institution} harus institusi tempat pengguna menjadi anggota aktif. */
class InstitutionMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $institution = $request->route('institution');
        $institution = $institution instanceof Institution ? $institution : Institution::where('slug', $institution)->firstOrFail();
        abort_unless($request->user()?->activeMembership($institution), 403);
        app(CurrentInstitution::class)->set($institution);
        $request->route()->setParameter('institution', $institution);

        return $next($request);
    }
}
