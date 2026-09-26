<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | User Environments
    |--------------------------------------------------------------------------
    |
    | Un rôle est global.
    | Son affectation à un utilisateur dans une école précise
    | passe par la table user_environments.
    |
    */

    public function userEnvironments(): HasMany
    {
        return $this->hasMany(UserEnvironment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role'
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifie si le rôle possède une permission donnée.
     *
     * Exemple :
     * $role->hasPermission('tickets.assign');
     */
    public function hasPermission(string $permissionCode): bool
    {
        if (! $this->active) {
            return false;
        }

        return $this->permissions()
            ->where('permissions.code', $permissionCode)
            ->exists();
    }

    /**
     * Vérifie si le rôle possède au moins une permission
     * parmi la liste fournie.
     *
     * Exemple :
     * $role->hasAnyPermission([
     *     'tickets.view',
     *     'tickets.assign',
     * ]);
     */
    public function hasAnyPermission(array $permissionCodes): bool
    {
        if (! $this->active || empty($permissionCodes)) {
            return false;
        }

        return $this->permissions()
            ->whereIn('permissions.code', $permissionCodes)
            ->exists();
    }

    /**
     * Vérifie si le rôle possède toutes les permissions fournies.
     *
     * Exemple :
     * $role->hasAllPermissions([
     *     'tickets.view',
     *     'tickets.assign',
     * ]);
     */
    public function hasAllPermissions(array $permissionCodes): bool
    {
        if (! $this->active || empty($permissionCodes)) {
            return false;
        }

        $permissionCodes = array_values(
            array_unique($permissionCodes)
        );

        $count = $this->permissions()
            ->whereIn('permissions.code', $permissionCodes)
            ->count();

        return $count === count($permissionCodes);
    }
}
