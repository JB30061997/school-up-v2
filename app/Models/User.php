<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $username
 * @property string $email
 * @property string|null $phone
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property bool $active
 * @property Carbon|null $last_login_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'first_name',
    'last_name',
    'username',
    'email',
    'phone',
    'password',
    'active',
    'last_login_at',
])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory,
        Notifiable,
        PasskeyAuthenticatable,
        TwoFactorAuthenticatable;

    /**
     * Attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    */

    /**
     * Tous les environnements auxquels l'utilisateur est affecté.
     */
    public function environments(): BelongsToMany
    {
        return $this->belongsToMany(
            Environment::class,
            'user_environments'
        )
            ->withPivot([
                'id',
                'role_id',
                'is_default',
                'active',
            ])
            ->withTimestamps();
    }

    /**
     * Affectations User / Environment / Role.
     */
    public function userEnvironments(): HasMany
    {
        return $this->hasMany(UserEnvironment::class);
    }

    /**
     * Environnements actifs accessibles par l'utilisateur.
     */
    public function activeEnvironments(): BelongsToMany
    {
        return $this->environments()
            ->where('environments.active', true)
            ->wherePivot('active', true);
    }

    /**
     * Environnement par défaut de l'utilisateur.
     */
    public function defaultEnvironment(): ?Environment
    {
        if (! $this->active) {
            return null;
        }

        return $this->environments()
            ->where('environments.active', true)
            ->wherePivot('active', true)
            ->wherePivot('is_default', true)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Environment Access
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifie si l'utilisateur possède un accès actif
     * à l'environnement.
     */
    public function hasEnvironment(int $environmentId): bool
    {
        if (! $this->active) {
            return false;
        }

        return $this->environments()
            ->where('environments.id', $environmentId)
            ->where('environments.active', true)
            ->wherePivot('active', true)
            ->exists();
    }

    /**
     * Alias explicite pour les contrôles d'autorisation.
     */
    public function hasEnvironmentAccess(int $environmentId): bool
    {
        return $this->hasEnvironment($environmentId);
    }

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne le rôle actif de l'utilisateur
     * dans un environnement donné.
     */
    public function roleInEnvironment(int $environmentId): ?Role
    {
        if (! $this->hasEnvironmentAccess($environmentId)) {
            return null;
        }

        $assignment = $this->userEnvironments()
            ->where('environment_id', $environmentId)
            ->where('active', true)
            ->first();

        if (! $assignment || ! $assignment->role_id) {
            return null;
        }

        return Role::query()
            ->whereKey($assignment->role_id)
            ->where('active', true)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifie si l'utilisateur possède une permission
     * dans un environnement donné.
     *
     * Exemple :
     *
     * $user->canInEnvironment('tickets.assign', 1);
     */
    public function canInEnvironment(
        string $permissionCode,
        int $environmentId
    ): bool {
        if (! $this->active) {
            return false;
        }

        $role = $this->roleInEnvironment($environmentId);

        if (! $role) {
            return false;
        }

        /*
         * Super Admin possède tous les droits
         * dans les environnements auxquels il a accès.
         */
        if ($role->code === 'super_admin') {
            return true;
        }

        return $role->hasPermission($permissionCode);
    }

    /**
     * Vérifie si l'utilisateur possède au moins une
     * des permissions dans l'environnement.
     */
    public function canAnyInEnvironment(
        array $permissionCodes,
        int $environmentId
    ): bool {
        if (! $this->active || empty($permissionCodes)) {
            return false;
        }

        $role = $this->roleInEnvironment($environmentId);

        if (! $role) {
            return false;
        }

        if ($role->code === 'super_admin') {
            return true;
        }

        return $role->hasAnyPermission($permissionCodes);
    }

    /**
     * Vérifie si l'utilisateur possède toutes les
     * permissions dans l'environnement.
     */
    public function canAllInEnvironment(
        array $permissionCodes,
        int $environmentId
    ): bool {
        if (! $this->active || empty($permissionCodes)) {
            return false;
        }

        $role = $this->roleInEnvironment($environmentId);

        if (! $role) {
            return false;
        }

        if ($role->code === 'super_admin') {
            return true;
        }

        return $role->hasAllPermissions($permissionCodes);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Nom complet de l'utilisateur.
     */
    public function getFullNameAttribute(): string
    {
        $fullName = trim(
            ($this->first_name ?? '') . ' ' . ($this->last_name ?? '')
        );

        return $fullName !== ''
            ? $fullName
            : $this->name;
    }
}