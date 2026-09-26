<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EnvironmentSelectionController extends Controller
{
    /**
     * Affiche les environnements accessibles à l'utilisateur.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        $environments = $user->activeEnvironments()
            ->select([
                'environments.id',
                'environments.name',
                'environments.code',
                'environments.app_name',
                'environments.logo',
                'environments.logo_dark',
                'environments.primary_color',
                'environments.secondary_color',
                'environments.sidebar_color',
                'environments.sidebar_text_color',
                'environments.accent_color',
                'environments.favicon',
                'environments.current_exercise',
            ])
            ->get()
            ->map(fn (Environment $environment) => [
                'id' => $environment->id,
                'name' => $environment->name,
                'code' => $environment->code,
                'app_name' => $environment->app_name,

                'logo' => $environment->logo,
                'logo_dark' => $environment->logo_dark,
                'primary_color' => $environment->primary_color,
                'secondary_color' => $environment->secondary_color,
                'sidebar_color' => $environment->sidebar_color,
                'sidebar_text_color' => $environment->sidebar_text_color,
                'accent_color' => $environment->accent_color,
                'favicon' => $environment->favicon,

                'current_exercise' => $environment->current_exercise,

                'is_default' => (bool) $environment->pivot->is_default,
            ]);

        if ($environments->isEmpty()) {
            abort(
                403,
                'Aucun environnement ne vous a été attribué.'
            );
        }

        return Inertia::render('environments/Select', [
            'environments' => $environments,
        ]);
    }

    /**
     * Sélectionne ou change l'environnement courant.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'environment_id' => [
                'required',
                'integer',
                'exists:environments,id',
            ],
        ]);

        $user = $request->user();

        $environmentId = (int) $validated['environment_id'];

        /*
        |--------------------------------------------------------------------------
        | Vérification de l'accès
        |--------------------------------------------------------------------------
        */

        if (! $user->hasEnvironmentAccess($environmentId)) {
            abort(
                403,
                'Vous n’avez pas accès à cet environnement.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Récupération du rôle dans cette école
        |--------------------------------------------------------------------------
        */

        $role = $user->roleInEnvironment($environmentId);

        if (! $role) {
            abort(
                403,
                'Aucun rôle actif ne vous a été attribué dans cet environnement.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Enregistrement du contexte courant
        |--------------------------------------------------------------------------
        */

        $request->session()->put([
            'current_environment_id' => $environmentId,
            'current_role_id' => $role->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Protection contre Session Fixation
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Quitte l'environnement courant.
     *
     * L'utilisateur pourra ensuite sélectionner une autre école.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'current_environment_id',
            'current_role_id',
        ]);

        return redirect()->route('environments.select');
    }
}