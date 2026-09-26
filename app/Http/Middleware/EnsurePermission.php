<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $environment = $request->attributes->get(
            'currentEnvironment'
        );

        if (! $environment) {
            abort(
                403,
                'Aucun environnement actif sélectionné.'
            );
        }

        $userEnvironment = $user->userEnvironments()
            ->where(
                'environment_id',
                $environment->id
            )
            ->where('active', true)
            ->with('role.permissions')
            ->first();

        if (
            ! $userEnvironment ||
            ! $userEnvironment->role ||
            ! $userEnvironment->role->active
        ) {
            abort(
                403,
                'Vous n’avez pas accès à cet environnement.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if (
            $userEnvironment->role->code === 'super_admin'
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Permission du rôle dans l'environnement courant
        |--------------------------------------------------------------------------
        */

        $hasPermission = $userEnvironment
            ->role
            ->permissions
            ->contains(
                'code',
                $permission
            );

        if (! $hasPermission) {
            abort(
                403,
                'Vous n’avez pas la permission nécessaire pour effectuer cette action.'
            );
        }

        return $next($request);
    }
}