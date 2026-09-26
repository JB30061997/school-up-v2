<?php

namespace App\Services\Users;

use App\Models\Environment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    /**
     * Créer un utilisateur.
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => $data['name'],
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'username' => $data['username'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'active' => $data['active'] ?? true,
            ]);

            return $user;
        });
    }

    /**
     * Affecter un utilisateur à une école / environnement.
     */
    public function assignEnvironment(
        User $user,
        Environment $environment,
        Role $role,
        bool $isDefault = false
    ): User {
        return DB::transaction(function () use (
            $user,
            $environment,
            $role,
            $isDefault
        ) {
            if (! $user->active) {
                throw ValidationException::withMessages([
                    'user' => 'Cet utilisateur est désactivé.',
                ]);
            }

            if (! $environment->active) {
                throw ValidationException::withMessages([
                    'environment' => 'Cet environnement est désactivé.',
                ]);
            }

            /*
             * Si les rôles sont liés à un environnement,
             * on vérifie qu'on ne mélange pas deux écoles.
             */
            if (
                isset($role->environment_id) &&
                $role->environment_id !== null &&
                (int) $role->environment_id !== (int) $environment->id
            ) {
                throw ValidationException::withMessages([
                    'role' =>
                        'Ce rôle n’appartient pas à cet environnement.',
                ]);
            }

            if ($isDefault) {
                DB::table('user_environments')
                    ->where('user_id', $user->id)
                    ->update([
                        'is_default' => false,
                        'updated_at' => now(),
                    ]);
            }

            $existing = DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('environment_id', $environment->id)
                ->first();

            if ($existing) {
                DB::table('user_environments')
                    ->where('id', $existing->id)
                    ->update([
                        'role_id' => $role->id,
                        'is_default' => $isDefault,
                        'active' => true,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('user_environments')->insert([
                    'user_id' => $user->id,
                    'environment_id' => $environment->id,
                    'role_id' => $role->id,
                    'is_default' => $isDefault,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            /*
             * Premier environnement actif = default automatiquement.
             */
            $hasDefault = DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('active', true)
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                DB::table('user_environments')
                    ->where('user_id', $user->id)
                    ->where('environment_id', $environment->id)
                    ->update([
                        'is_default' => true,
                        'updated_at' => now(),
                    ]);
            }

            return $this->loadUser($user);
        });
    }

    /**
     * Changer le rôle d'un utilisateur dans une école.
     */
    public function changeRole(
        User $user,
        Environment $environment,
        Role $role
    ): User {
        return DB::transaction(function () use (
            $user,
            $environment,
            $role
        ) {
            $access = DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('environment_id', $environment->id)
                ->first();

            if (! $access) {
                throw ValidationException::withMessages([
                    'environment' =>
                        'Cet utilisateur n’est pas affecté à cet environnement.',
                ]);
            }

            if (
                isset($role->environment_id) &&
                $role->environment_id !== null &&
                (int) $role->environment_id !== (int) $environment->id
            ) {
                throw ValidationException::withMessages([
                    'role' =>
                        'Ce rôle n’appartient pas à cet environnement.',
                ]);
            }

            DB::table('user_environments')
                ->where('id', $access->id)
                ->update([
                    'role_id' => $role->id,
                    'updated_at' => now(),
                ]);

            return $this->loadUser($user);
        });
    }

    /**
     * Définir l'école par défaut.
     */
    public function setDefaultEnvironment(
        User $user,
        Environment $environment
    ): User {
        return DB::transaction(function () use (
            $user,
            $environment
        ) {
            $access = DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('environment_id', $environment->id)
                ->where('active', true)
                ->exists();

            if (! $access) {
                throw ValidationException::withMessages([
                    'environment' =>
                        'L’utilisateur n’a pas accès à cet environnement.',
                ]);
            }

            DB::table('user_environments')
                ->where('user_id', $user->id)
                ->update([
                    'is_default' => false,
                    'updated_at' => now(),
                ]);

            DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('environment_id', $environment->id)
                ->update([
                    'is_default' => true,
                    'updated_at' => now(),
                ]);

            return $this->loadUser($user);
        });
    }

    /**
     * Désactiver l'accès à une école.
     */
    public function disableEnvironment(
        User $user,
        Environment $environment
    ): User {
        return DB::transaction(function () use (
            $user,
            $environment
        ) {
            $access = DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('environment_id', $environment->id)
                ->first();

            if (! $access) {
                throw ValidationException::withMessages([
                    'environment' =>
                        'L’utilisateur n’est pas affecté à cet environnement.',
                ]);
            }

            DB::table('user_environments')
                ->where('id', $access->id)
                ->update([
                    'active' => false,
                    'is_default' => false,
                    'updated_at' => now(),
                ]);

            /*
             * S'il reste des écoles actives et aucune par défaut,
             * on choisit automatiquement la première.
             */
            $hasDefault = DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('active', true)
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                $next = DB::table('user_environments')
                    ->where('user_id', $user->id)
                    ->where('active', true)
                    ->orderBy('id')
                    ->first();

                if ($next) {
                    DB::table('user_environments')
                        ->where('id', $next->id)
                        ->update([
                            'is_default' => true,
                            'updated_at' => now(),
                        ]);
                }
            }

            return $this->loadUser($user);
        });
    }

    /**
     * Réactiver l'accès à une école.
     */
    public function enableEnvironment(
        User $user,
        Environment $environment
    ): User {
        return DB::transaction(function () use (
            $user,
            $environment
        ) {
            $access = DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('environment_id', $environment->id)
                ->first();

            if (! $access) {
                throw ValidationException::withMessages([
                    'environment' =>
                        'Aucune affectation existante pour cet environnement.',
                ]);
            }

            DB::table('user_environments')
                ->where('id', $access->id)
                ->update([
                    'active' => true,
                    'updated_at' => now(),
                ]);

            $hasDefault = DB::table('user_environments')
                ->where('user_id', $user->id)
                ->where('active', true)
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                DB::table('user_environments')
                    ->where('id', $access->id)
                    ->update([
                        'is_default' => true,
                        'updated_at' => now(),
                    ]);
            }

            return $this->loadUser($user);
        });
    }

    /**
     * Désactiver complètement un utilisateur.
     */
    public function disableUser(User $user): User
    {
        $user->update([
            'active' => false,
        ]);

        return $user->fresh();
    }

    /**
     * Réactiver complètement un utilisateur.
     */
    public function enableUser(User $user): User
    {
        $user->update([
            'active' => true,
        ]);

        return $user->fresh();
    }

    /**
     * Vérifier l'accès actif d'un utilisateur à une école.
     */
    public function hasEnvironmentAccess(
        User $user,
        Environment|int $environment
    ): bool {
        $environmentId = $environment instanceof Environment
            ? $environment->id
            : $environment;

        return $user->active &&
            $user->environments()
                ->where('environments.id', $environmentId)
                ->where('environments.active', true)
                ->wherePivot('active', true)
                ->exists();
    }

    private function loadUser(User $user): User
    {
        return $user->fresh([
            'environments',
        ]);
    }
}