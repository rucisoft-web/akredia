<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Support\CurrentInstitution;
use Carbon\Carbon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CurrentInstitution::class);
    }

    public function boot(): void
    {
        Carbon::setLocale('id');

        RateLimiter::for('public-survey', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Log audit login (FR-ADM-01) untuk setiap institusi tempat pengguna menjadi anggota.
        Event::listen(Login::class, function (Login $event) {
            $event->user->forceFill(['last_login_at' => now()])->saveQuietly();

            foreach ($event->user->memberships()->where('is_active', true)->pluck('institution_id') as $id) {
                AuditLog::record('login', null, ['email' => $event->user->email], $id);
            }
        });
    }
}
