<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EnvironmentController extends Controller
{
    /**
     * Liste des environnements.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'active' => [
                'nullable',
                Rule::in(['0', '1']),
            ],
        ]);

        $environments = Environment::query()
            ->withCount([
                'users',
                'activeUsers',
                'schoolYears',
            ])
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'app_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'current_exercise',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                isset($filters['active']),
                fn ($query) =>
                    $query->where(
                        'active',
                        (bool) $filters['active']
                    )
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render(
            'environments/Index',
            [
                'environments' => $environments,

                'filters' => [
                    'search' =>
                        $filters['search'] ?? null,

                    'active' =>
                        $filters['active'] ?? null,
                ],
            ]
        );
    }

    /**
     * Formulaire de création.
     */
    public function create(): Response
    {
        return Inertia::render(
            'environments/Create',
            [
                'defaultBranding' => [
                    'primary_color' => '#8B1E2D',
                    'secondary_color' => '#641520',
                    'sidebar_color' => '#111827',
                    'sidebar_text_color' => '#FFFFFF',
                    'accent_color' => '#D4AF37',
                ],
            ]
        );
    }

    /**
     * Création d'un environnement.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate(
            $this->rules()
        );

        $environment = DB::transaction(
            function () use (
                $validated,
                $request
            ) {
                $logo = null;
                $logoDark = null;
                $favicon = null;

                if (
                    $request->hasFile('logo')
                ) {
                    $logo = $request
                        ->file('logo')
                        ->store(
                            'environments/logos',
                            'public'
                        );
                }

                if (
                    $request->hasFile('logo_dark')
                ) {
                    $logoDark = $request
                        ->file('logo_dark')
                        ->store(
                            'environments/logos',
                            'public'
                        );
                }

                if (
                    $request->hasFile('favicon')
                ) {
                    $favicon = $request
                        ->file('favicon')
                        ->store(
                            'environments/favicons',
                            'public'
                        );
                }

                return Environment::create([
                    'name' =>
                        $validated['name'],

                    'code' =>
                        strtoupper(
                            $validated['code']
                        ),

                    'app_name' =>
                        $validated['app_name']
                            ?? 'School Up',

                    'url' =>
                        $validated['url']
                            ?? null,

                    'logo' => $logo,

                    'logo_dark' => $logoDark,

                    'favicon' => $favicon,

                    'primary_color' =>
                        $validated[
                            'primary_color'
                        ] ?? '#8B1E2D',

                    'secondary_color' =>
                        $validated[
                            'secondary_color'
                        ] ?? '#641520',

                    'sidebar_color' =>
                        $validated[
                            'sidebar_color'
                        ] ?? '#111827',

                    'sidebar_text_color' =>
                        $validated[
                            'sidebar_text_color'
                        ] ?? '#FFFFFF',

                    'accent_color' =>
                        $validated[
                            'accent_color'
                        ] ?? '#D4AF37',

                    'current_exercise' =>
                        $validated[
                            'current_exercise'
                        ] ?? null,

                    'active' =>
                        $validated['active']
                            ?? true,
                ]);
            }
        );

        return redirect()
            ->route(
                'environments.edit',
                $environment
            )
            ->with(
                'success',
                'L’environnement a été créé avec succès.'
            );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(
        Environment $environment
    ): Response {
        $environment->loadCount([
            'users',
            'activeUsers',
            'schoolYears',
        ]);

        $environment->load([
            'currentSchoolYear',
        ]);

        return Inertia::render(
            'environments/Edit',
            [
                'environment' => [
                    'id' =>
                        $environment->id,

                    'name' =>
                        $environment->name,

                    'code' =>
                        $environment->code,

                    'app_name' =>
                        $environment->app_name,

                    'url' =>
                        $environment->url,

                    'logo' =>
                        $environment->logo,

                    'logo_url' =>
                        $this->storageUrl(
                            $environment->logo
                        ),

                    'logo_dark' =>
                        $environment->logo_dark,

                    'logo_dark_url' =>
                        $this->storageUrl(
                            $environment->logo_dark
                        ),

                    'favicon' =>
                        $environment->favicon,

                    'favicon_url' =>
                        $this->storageUrl(
                            $environment->favicon
                        ),

                    'primary_color' =>
                        $environment->primary_color,

                    'secondary_color' =>
                        $environment->secondary_color,

                    'sidebar_color' =>
                        $environment->sidebar_color,

                    'sidebar_text_color' =>
                        $environment
                            ->sidebar_text_color,

                    'accent_color' =>
                        $environment->accent_color,

                    'current_exercise' =>
                        $environment
                            ->current_exercise,

                    'active' =>
                        (bool) $environment->active,

                    'users_count' =>
                        $environment->users_count,

                    'active_users_count' =>
                        $environment
                            ->active_users_count,

                    'school_years_count' =>
                        $environment
                            ->school_years_count,

                    'current_school_year' =>
                        $environment
                            ->currentSchoolYear,
                ],
            ]
        );
    }

    /**
     * Modification d'un environnement.
     */
    public function update(
        Request $request,
        Environment $environment
    ): RedirectResponse {
        $validated = $request->validate(
            $this->rules($environment)
        );

        DB::transaction(
            function () use (
                $request,
                $validated,
                $environment
            ) {
                $data = [
                    'name' =>
                        $validated['name'],

                    'code' =>
                        strtoupper(
                            $validated['code']
                        ),

                    'app_name' =>
                        $validated['app_name']
                            ?? 'School Up',

                    'url' =>
                        $validated['url']
                            ?? null,

                    'primary_color' =>
                        $validated[
                            'primary_color'
                        ] ?? null,

                    'secondary_color' =>
                        $validated[
                            'secondary_color'
                        ] ?? null,

                    'sidebar_color' =>
                        $validated[
                            'sidebar_color'
                        ] ?? null,

                    'sidebar_text_color' =>
                        $validated[
                            'sidebar_text_color'
                        ] ?? null,

                    'accent_color' =>
                        $validated[
                            'accent_color'
                        ] ?? null,

                    'current_exercise' =>
                        $validated[
                            'current_exercise'
                        ] ?? null,

                    'active' =>
                        $validated['active'],
                ];

                /*
                |--------------------------------------------------------------------------
                | Logo
                |--------------------------------------------------------------------------
                */

                if (
                    $request->hasFile('logo')
                ) {
                    $this->deletePublicFile(
                        $environment->logo
                    );

                    $data['logo'] = $request
                        ->file('logo')
                        ->store(
                            'environments/logos',
                            'public'
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | Logo Dark
                |--------------------------------------------------------------------------
                */

                if (
                    $request->hasFile('logo_dark')
                ) {
                    $this->deletePublicFile(
                        $environment->logo_dark
                    );

                    $data['logo_dark'] = $request
                        ->file('logo_dark')
                        ->store(
                            'environments/logos',
                            'public'
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | Favicon
                |--------------------------------------------------------------------------
                */

                if (
                    $request->hasFile('favicon')
                ) {
                    $this->deletePublicFile(
                        $environment->favicon
                    );

                    $data['favicon'] = $request
                        ->file('favicon')
                        ->store(
                            'environments/favicons',
                            'public'
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | Suppression explicite des fichiers
                |--------------------------------------------------------------------------
                */

                if (
                    $request->boolean(
                        'remove_logo'
                    )
                ) {
                    $this->deletePublicFile(
                        $environment->logo
                    );

                    $data['logo'] = null;
                }

                if (
                    $request->boolean(
                        'remove_logo_dark'
                    )
                ) {
                    $this->deletePublicFile(
                        $environment->logo_dark
                    );

                    $data['logo_dark'] = null;
                }

                if (
                    $request->boolean(
                        'remove_favicon'
                    )
                ) {
                    $this->deletePublicFile(
                        $environment->favicon
                    );

                    $data['favicon'] = null;
                }

                $environment->update($data);
            }
        );

        return back()->with(
            'success',
            'L’environnement a été modifié avec succès.'
        );
    }

    /**
     * Activation / désactivation.
     */
    public function toggleActive(
        Request $request,
        Environment $environment
    ): RedirectResponse {
        $newState =
            ! $environment->active;

        /*
        |--------------------------------------------------------------------------
        | Protection environnement courant
        |--------------------------------------------------------------------------
        |
        | On évite qu'un administrateur désactive
        | l'école dans laquelle il travaille actuellement.
        |
        */

        $currentEnvironment =
            $request->attributes->get(
                'currentEnvironment'
            );

        if (
            ! $newState
            && $currentEnvironment
            && (int) $currentEnvironment->id
                === (int) $environment->id
        ) {
            return back()->withErrors([
                'environment' =>
                    'Vous ne pouvez pas désactiver l’environnement actuellement sélectionné.',
            ]);
        }

        DB::transaction(
            function () use (
                $environment,
                $newState
            ) {
                $environment->update([
                    'active' => $newState,
                ]);

                /*
                | Lorsqu'une école est désactivée,
                | les accès utilisateurs sont désactivés.
                */
                if (! $newState) {
                    $environment
                        ->userEnvironments()
                        ->update([
                            'active' => false,
                            'is_default' => false,
                        ]);
                }
            }
        );

        return back()->with(
            'success',
            $newState
                ? 'L’environnement a été activé.'
                : 'L’environnement a été désactivé.'
        );
    }

    /**
     * Supprimer le logo principal.
     */
    public function removeLogo(
        Environment $environment
    ): RedirectResponse {
        $this->deletePublicFile(
            $environment->logo
        );

        $environment->update([
            'logo' => null,
        ]);

        return back()->with(
            'success',
            'Le logo a été supprimé.'
        );
    }

    /**
     * Supprimer le logo dark.
     */
    public function removeLogoDark(
        Environment $environment
    ): RedirectResponse {
        $this->deletePublicFile(
            $environment->logo_dark
        );

        $environment->update([
            'logo_dark' => null,
        ]);

        return back()->with(
            'success',
            'Le logo sombre a été supprimé.'
        );
    }

    /**
     * Supprimer le favicon.
     */
    public function removeFavicon(
        Environment $environment
    ): RedirectResponse {
        $this->deletePublicFile(
            $environment->favicon
        );

        $environment->update([
            'favicon' => null,
        ]);

        return back()->with(
            'success',
            'Le favicon a été supprimé.'
        );
    }

    /**
     * Règles de validation.
     */
    private function rules(
        ?Environment $environment = null
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',

                Rule::unique(
                    'environments',
                    'code'
                )->ignore(
                    $environment?->id
                ),
            ],

            'app_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'url' => [
                'nullable',
                'url',
                'max:500',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp,svg',
                'max:4096',
            ],

            'logo_dark' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp,svg',
                'max:4096',
            ],

            'favicon' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg,webp,ico',
                'max:2048',
            ],

            'primary_color' =>
                $this->colorRules(),

            'secondary_color' =>
                $this->colorRules(),

            'sidebar_color' =>
                $this->colorRules(),

            'sidebar_text_color' =>
                $this->colorRules(),

            'accent_color' =>
                $this->colorRules(),

            'current_exercise' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/',
            ],

            'active' => [
                'required',
                'boolean',
            ],

            'remove_logo' => [
                'sometimes',
                'boolean',
            ],

            'remove_logo_dark' => [
                'sometimes',
                'boolean',
            ],

            'remove_favicon' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Validation couleur HEX.
     *
     * Exemples :
     * #8B1E2D
     * #FFFFFF
     */
    private function colorRules(): array
    {
        return [
            'nullable',
            'string',
            'max:7',
            'regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    /**
     * URL publique d'un fichier.
     */
    private function storageUrl(
        ?string $path
    ): ?string {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')
            ->url($path);
    }

    /**
     * Suppression sécurisée d'un fichier.
     */
    private function deletePublicFile(
        ?string $path
    ): void {
        if (! $path) {
            return;
        }

        if (
            Storage::disk('public')
                ->exists($path)
        ) {
            Storage::disk('public')
                ->delete($path);
        }
    }
}