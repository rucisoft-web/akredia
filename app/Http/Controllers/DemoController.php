<?php

namespace App\Http\Controllers;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Halaman publik "Akredia untuk Pimpinan Perguruan Tinggi" dan login tamu ke data demo UNJ.
 */
class DemoController extends Controller
{
    public function pimpinan(): View
    {
        return view('pimpinan.page', [
            'guestUrl' => self::guestUrl(),
            'loginUrl' => self::loginUrl(),
        ]);
    }

    public function guest(Request $request): RedirectResponse
    {
        $guest = self::guestUser();
        abort_unless($guest, 404);

        Auth::guard(Filament::getPanel('app')->getAuthGuard())->login($guest);
        $request->session()->regenerate();

        $institution = $guest->getTenants(Filament::getPanel('app'))->firstWhere('is_demo', true);

        return redirect()->to(url('/app/'.$institution->slug));
    }

    public static function loginUrl(): string
    {
        return url('/app/login');
    }

    /** Tautan "Masuk sebagai tamu", atau null bila akun tamu demo belum dibuat di server ini. */
    public static function guestUrl(): ?string
    {
        return self::guestUser() ? route('demo.tamu') : null;
    }

    protected static function guestUser(): ?User
    {
        if (! config('akredia.demo.guest')) {
            return null;
        }

        $user = User::where('email', User::DEMO_GUEST_EMAIL)->where('is_active', true)->first();

        return $user?->getTenants(Filament::getPanel('app'))->contains('is_demo', true) ? $user : null;
    }
}
