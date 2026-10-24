<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use SensitiveParameter;

class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_active', 'is_platform_admin'];

    protected $attributes = ['is_active' => true, 'is_platform_admin' => false];

    protected $hidden = ['password', 'remember_token', 'app_authentication_secret', 'app_authentication_recovery_codes'];

    /** @var array<int, Membership|null> */
    protected array $membershipCache = [];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_platform_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class)->withoutGlobalScopes();
    }

    public function activeMembership(Institution|int|null $institution): ?Membership
    {
        $id = $institution instanceof Institution ? $institution->id : $institution;

        if ($id === null) {
            return null;
        }

        if (! array_key_exists($id, $this->membershipCache)) {
            $this->membershipCache[$id] = Membership::withoutGlobalScopes()
                ->where('institution_id', $id)
                ->where('user_id', $this->id)
                ->where('is_active', true)
                ->first();
        }

        return $this->membershipCache[$id];
    }

    public function roleIn(Institution|int|null $institution): ?Role
    {
        return $this->activeMembership($institution)?->role;
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }

    public const DEMO_GUEST_EMAIL = 'tamu@unj.akredia.test';

    /** Akun tamu bersama untuk melihat data demo UNJ; tidak boleh mengubah profil atau kata sandi. */
    public function isDemoGuest(): bool
    {
        return $this->email === self::DEMO_GUEST_EMAIL;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'platform' => $this->is_platform_admin,
            default => true,
        };
    }

    public function getTenants(Panel $panel): array|Collection
    {
        $ids = $this->memberships()->where('is_active', true)->pluck('institution_id');

        return Institution::query()->whereIn('id', $ids)->where('is_active', true)->orderBy('name')->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->is_active && $tenant->is_active && $this->activeMembership($tenant) !== null;
    }

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(#[SensitiveParameter] ?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    public function saveAppAuthenticationRecoveryCodes(#[SensitiveParameter] ?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }
}
