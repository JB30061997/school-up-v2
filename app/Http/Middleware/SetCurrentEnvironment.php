<?php

namespace App\Http\Middleware;

use App\Models\Environment;
use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentEnvironment
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Utilisateur non connecté
        |--------------------------------------------------------------------------
        */

        if (! $user) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Utilisateur désactivé
        |--------------------------------------------------------------------------
        */

        if (! $user->active) {
            $request->session()->forget([
                'current_environment_id',
                'current_role_id',
            ]);

            abort(403, 'Votre compte utilisateur est désactivé.');
        }

        /*
        |--------------------------------------------------------------------------
        | Environment actuellement stocké en session
        |--------------------------------------------------------------------------
        */

        $environmentId = $request->session()->get(
            'current_environment_id'
        );

        /*
        |--------------------------------------------------------------------------
        | Vérifier que l'accès existe toujours
        |--------------------------------------------------------------------------
        */

        if (
            $environmentId &&
            ! $user->hasEnvironmentAccess((int) $environmentId)
        ) {
            $request->session()->forget([
                'current_environment_id',
                'current_role_id',
            ]);

            $environmentId = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Aucun environnement sélectionné :
        | utiliser l'environnement par défaut
        |--------------------------------------------------------------------------
        */

        if (! $environmentId) {
            $defaultEnvironment = $user->defaultEnvironment();

            if ($defaultEnvironment) {
                $environmentId = $defaultEnvironment->id;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Charger Environment + Role
        |--------------------------------------------------------------------------
        */

        $currentEnvironment = null;
        $currentRole = null;

        if ($environmentId) {
            $currentEnvironment = Environment::query()
                ->whereKey($environmentId)
                ->where('active', true)
                ->first();

            if ($currentEnvironment) {
                $currentRole = $user->roleInEnvironment(
                    $currentEnvironment->id
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sécurité :
        | Environment présent mais rôle absent/inactif
        |--------------------------------------------------------------------------
        */

        if ($currentEnvironment && ! $currentRole) {
            $request->session()->forget([
                'current_environment_id',
                'current_role_id',
            ]);

            $currentEnvironment = null;
            $environmentId = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Synchroniser la session
        |--------------------------------------------------------------------------
        */

        if ($currentEnvironment && $currentRole) {
            $request->session()->put([
                'current_environment_id' => $currentEnvironment->id,
                'current_role_id' => $currentRole->id,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Partager le contexte avec toute l'application
        |--------------------------------------------------------------------------
        */

        app()->instance(
            'currentEnvironment',
            $currentEnvironment
        );

        app()->instance(
            'currentRole',
            $currentRole
        );

        $request->attributes->set(
            'currentEnvironment',
            $currentEnvironment
        );

        $request->attributes->set(
            'currentRole',
            $currentRole
        );

        return $next($request);
    }
}