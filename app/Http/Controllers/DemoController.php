<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Halaman publik "Akredia untuk Pimpinan Perguruan Tinggi" dan login tamu ke perguruan tinggi contoh.
 */
class DemoController extends Controller
{
    /** Urutan kartu di halaman login. */
    public const ORDER = ['unj', 'binus', 'polban'];

    public function pimpinan(): View
    {
        return view('pimpinan.page', [
            'guestUrl' => self::guestUrl(),
            'loginUrl' => self::loginUrl(),
            'samples' => self::samples(),
        ]);
    }

    public function guest(Request $request): RedirectResponse
    {
        $samples = self::samples();
        abort_if($samples->isEmpty(), 404);

        $institution = $samples->firstWhere('slug', $request->query('pt')) ?? $samples->first();

        Auth::guard(Filament::getPanel('app')->getAuthGuard())->login(self::guestUser());
        $request->session()->regenerate();

        return redirect()->to(url('/app/'.$institution->slug));
    }

    public static function loginUrl(): string
    {
        return url('/app/login');
    }

    /** Tautan "Masuk sebagai Tamu", atau null bila belum ada perguruan tinggi contoh untuk tamu. */
    public static function guestUrl(?string $slug = null): ?string
    {
        return self::samples()->isEmpty() ? null : route('demo.tamu', array_filter(['pt' => $slug]));
    }

    /** @return Collection<int, Institution> perguruan tinggi contoh yang dapat dibuka akun tamu */
    public static function samples(): Collection
    {
        if (! config('akredia.demo.guest') || ! $user = self::guestUser()) {
            return collect();
        }

        return $user->getTenants(Filament::getPanel('app'))->where('is_demo', true)
            ->sortBy(fn (Institution $i) => array_search($i->slug, self::ORDER, true) === false ? 99 : array_search($i->slug, self::ORDER, true))
            ->values();
    }

    /** Singkatan untuk pita tamu, misalnya "UNJ" atau "BINUS". */
    public static function shortName(Institution $institution): string
    {
        return mb_strtoupper($institution->slug);
    }

    protected static function guestUser(): ?User
    {
        return User::where('email', User::DEMO_GUEST_EMAIL)->where('is_active', true)->first();
    }
}
