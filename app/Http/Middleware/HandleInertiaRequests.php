<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /*
        |--------------------------------------------------------------------------
        | Authenticated User
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Current Environment
        |--------------------------------------------------------------------------
        |
        | Ordre de résolution :
        |
        | 1. Attribut de la requête
        | 2. Session
        | 3. Environnement par défaut
        | 4. Premier environnement actif
        |
        */

        $currentEnvironment = $request->attributes->get(
            'currentEnvironment'
        );

        $currentEnvironmentId = $currentEnvironment?->id
            ?? $request->session()->get('current_environment_id');

        /*
        |--------------------------------------------------------------------------
        | Accessible Environments Collection
        |--------------------------------------------------------------------------
        */

        $activeEnvironments = collect();

        if ($user) {
            $activeEnvironments = $user
                ->activeEnvironments()
                ->select([
                    'environments.id',
                    'environments.name',
                    'environments.code',
                    'environments.app_name',
                    'environments.logo',
                    'environments.logo_dark',
                    'environments.current_exercise',
                    'environments.primary_color',
                    'environments.secondary_color',
                    'environments.sidebar_color',
                    'environments.sidebar_text_color',
                    'environments.accent_color',
                    'environments.favicon',
                ])
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Environment Automatically After Login
        |--------------------------------------------------------------------------
        |
        | Si aucun environnement n'est encore enregistré dans la session,
        | on choisit automatiquement :
        |
        | - l'environnement par défaut
        | - sinon le premier environnement actif
        |
        */

        if ($user && ! $currentEnvironmentId) {
            $defaultEnvironment = $activeEnvironments->first(
                fn ($environment) =>
                    (bool) ($environment->pivot->is_default ?? false)
            );

            $resolvedEnvironment = $defaultEnvironment
                ?? $activeEnvironments->first();

            if ($resolvedEnvironment) {
                $currentEnvironment = $resolvedEnvironment;
                $currentEnvironmentId = $resolvedEnvironment->id;

                /*
                |--------------------------------------------------------------
                | Save environment immediately in session
                |--------------------------------------------------------------
                */

                $request->session()->put(
                    'current_environment_id',
                    $currentEnvironmentId
                );

                /*
                |--------------------------------------------------------------
                | Make environment available during the SAME request
                |--------------------------------------------------------------
                */

                $request->attributes->set(
                    'currentEnvironment',
                    $currentEnvironment
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Environment Exists In Session But Not In Request Attributes
        |--------------------------------------------------------------------------
        */

        if (
            $user &&
            $currentEnvironmentId &&
            ! $currentEnvironment
        ) {
            $currentEnvironment = $activeEnvironments->first(
                fn ($environment) =>
                    (int) $environment->id ===
                    (int) $currentEnvironmentId
            );

            /*
            |--------------------------------------------------------------------------
            | Invalid / Deleted / Inaccessible Environment
            |--------------------------------------------------------------------------
            */

            if (! $currentEnvironment) {
                $fallbackEnvironment = $activeEnvironments->first(
                    fn ($environment) =>
                        (bool) ($environment->pivot->is_default ?? false)
                ) ?? $activeEnvironments->first();

                if ($fallbackEnvironment) {
                    $currentEnvironment = $fallbackEnvironment;
                    $currentEnvironmentId = $fallbackEnvironment->id;

                    $request->session()->put(
                        'current_environment_id',
                        $currentEnvironmentId
                    );
                } else {
                    $currentEnvironment = null;
                    $currentEnvironmentId = null;

                    $request->session()->forget(
                        'current_environment_id'
                    );
                }
            }

            if ($currentEnvironment) {
                $request->attributes->set(
                    'currentEnvironment',
                    $currentEnvironment
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Current User Environment / Role / Permissions
        |--------------------------------------------------------------------------
        */

        $userEnvironment = null;
        $currentRole = null;
        $permissions = [];

        if ($user && $currentEnvironmentId) {
            $userEnvironment = $user
                ->userEnvironments()
                ->where(
                    'environment_id',
                    $currentEnvironmentId
                )
                ->where('active', true)
                ->with('role')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Current Role
            |--------------------------------------------------------------------------
            */

            $currentRole = $userEnvironment?->role;

            /*
            |--------------------------------------------------------------------------
            | Current Permissions
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            | La table permissions ne possède PAS de colonne "active".
            |
            | Donc on récupère simplement les codes des permissions
            | liées au rôle.
            |
            */

            if (
                $currentRole &&
                (bool) $currentRole->active
            ) {
                $permissions = $currentRole
                    ->permissions()
                    ->pluck('permissions.code')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Accessible Environments
        |--------------------------------------------------------------------------
        */

        $environments = $activeEnvironments
            ->map(fn ($environment) => [
                'id' => $environment->id,

                'name' => $environment->name,

                'code' => $environment->code,

                'app_name' => $environment->app_name,

                'logo' => $environment->logo,

                'logo_dark' => $environment->logo_dark,

                'current_exercise' =>
                    $environment->current_exercise,

                'is_default' => (bool) (
                    $environment->pivot->is_default ?? false
                ),

                'is_current' =>
                    (int) $environment->id ===
                    (int) $currentEnvironmentId,
            ])
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Shared Props
        |--------------------------------------------------------------------------
        */

        return [
            ...parent::share($request),

            /*
            |--------------------------------------------------------------------------
            | Application
            |--------------------------------------------------------------------------
            */

            'name' => config('app.name'),

            /*
            |--------------------------------------------------------------------------
            | Authentication
            |--------------------------------------------------------------------------
            */

            'auth' => [
                /*
                |--------------------------------------------------------------------------
                | User
                |--------------------------------------------------------------------------
                */

                'user' => $user
                    ? [
                        'id' => $user->id,

                        'name' => $user->name,

                        'first_name' => $user->first_name,

                        'last_name' => $user->last_name,

                        'username' => $user->username,

                        'email' => $user->email,

                        'phone' => $user->phone,

                        'active' => (bool) $user->active,
                    ]
                    : null,

                /*
                |--------------------------------------------------------------------------
                | Role
                |--------------------------------------------------------------------------
                */

                'role' => $currentRole
                    ? [
                        'id' => $currentRole->id,

                        'name' => $currentRole->name,

                        'code' => $currentRole->code,
                    ]
                    : null,

                /*
                |--------------------------------------------------------------------------
                | Permissions
                |--------------------------------------------------------------------------
                */

                'permissions' => $permissions,

                /*
                |--------------------------------------------------------------------------
                | Environment IDs
                |--------------------------------------------------------------------------
                */

                'environment_id' =>
                    $currentEnvironmentId,

                'user_environment_id' =>
                    $userEnvironment?->id,
            ],

            /*
            |--------------------------------------------------------------------------
            | Current Environment
            |--------------------------------------------------------------------------
            */

            'currentEnvironment' => $currentEnvironment
                ? [
                    'id' =>
                        $currentEnvironment->id,

                    'name' =>
                        $currentEnvironment->name,

                    'code' =>
                        $currentEnvironment->code,

                    'app_name' =>
                        $currentEnvironment->app_name,

                    'current_exercise' =>
                        $currentEnvironment->current_exercise,

                    'logo' =>
                        $currentEnvironment->logo,

                    'logo_dark' =>
                        $currentEnvironment->logo_dark,

                    'primary_color' =>
                        $currentEnvironment->primary_color,

                    'secondary_color' =>
                        $currentEnvironment->secondary_color,

                    'sidebar_color' =>
                        $currentEnvironment->sidebar_color,

                    'sidebar_text_color' =>
                        $currentEnvironment->sidebar_text_color,

                    'accent_color' =>
                        $currentEnvironment->accent_color,

                    'favicon' =>
                        $currentEnvironment->favicon,
                ]
                : null,

            /*
            |--------------------------------------------------------------------------
            | Accessible Environments
            |--------------------------------------------------------------------------
            */

            'environments' => $environments,

            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            'notifications' => [
                'unread_count' => $user
                    ? $user
                        ->unreadNotifications()
                        ->count()
                    : 0,

                'latest' => $user
                    ? $user
                        ->notifications()
                        ->latest()
                        ->limit(5)
                        ->get()
                        ->map(
                            fn ($notification) => [
                                'id' =>
                                    $notification->id,

                                'type' =>
                                    class_basename(
                                        $notification->type
                                    ),

                                'data' =>
                                    $notification->data,

                                'read' =>
                                    $notification->read_at !== null,

                                'read_at' =>
                                    $notification
                                        ->read_at
                                        ?->toISOString(),

                                'created_at' =>
                                    $notification
                                        ->created_at
                                        ?->toISOString(),
                            ]
                        )
                        ->values()
                        ->all()
                    : [],
            ],

            /*
            |--------------------------------------------------------------------------
            | Flash Messages
            |--------------------------------------------------------------------------
            */

            'flash' => [
                'success' => fn () =>
                    $request
                        ->session()
                        ->get('success'),

                'error' => fn () =>
                    $request
                        ->session()
                        ->get('error'),

                'warning' => fn () =>
                    $request
                        ->session()
                        ->get('warning'),

                'info' => fn () =>
                    $request
                        ->session()
                        ->get('info'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Sidebar
            |--------------------------------------------------------------------------
            */

            'sidebarOpen' =>
                ! $request->hasCookie('sidebar_state')
                ||
                $request->cookie('sidebar_state') === 'true',
        ];
    }
}