<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\AuditLog;
use App\Support\CurrentInstitution;
use Carbon\Carbon;
use Filament\Actions\Action;
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

    /** Nama tombol yang mengubah data; disembunyikan dari Pengamat (termasuk akun tamu demo). */
    private const WRITE_ACTIONS = [
        'create', 'edit', 'delete', 'forceDelete', 'restore', 'replicate', 'attach', 'detach', 'associate', 'dissociate', 'import',
        'add_capa', 'approve', 'assign', 'capa', 'check', 'check_all', 'compute', 'done', 'duplicate', 'freeze', 'generate',
        'lock_track', 'ratify', 'recolor', 'refresh', 'reject', 'reset_mfa', 'revise', 'rtl', 'state', 'submit', 'task',
        'to_capa', 'to_task', 'transition', 'unfreeze', 'upload', 'validate', 'verify', 'version',
    ];

    public function boot(): void
    {
        Carbon::setLocale('id');

        // Pengamat hanya melihat: halaman buat/ubah sudah 404, tombolnya pun tidak perlu tampil.
        // Dijalankan setelah setUp() agar aturan bawaan tombol (mis. Hapus pada data terhapus) tetap berlaku.
        Action::configureUsing(function (Action $action) {
            if (! in_array($action->getName(), self::WRITE_ACTIONS, true)) {
                return;
            }

            $hidden = (fn () => $this->isHidden)->call($action);
            // Tombol tabel disalin per baris, jadi aturan bawaan dievaluasi pada salinan yang sedang dirender.
            $action->hidden(fn (Action $current) => auth()->user()?->roleIn(filament()->getTenant()) === Role::Pengamat
                || (bool) $current->evaluate($hidden));
        }, isImportant: true);

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
