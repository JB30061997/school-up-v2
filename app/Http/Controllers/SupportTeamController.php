<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\SupportTeam;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SupportTeamController extends Controller
{
    /**
     * Liste des équipes de support
     * de l'environnement courant.
     */
    public function index(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

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

        $teams = SupportTeam::query()
            ->where(
                'environment_id',
                $environment->id
            )
            ->with([
                'leader:id,name,first_name,last_name,email',
            ])
            ->withCount([
                'users',
                'tickets',
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
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                isset($filters['active']),
                fn ($query) => $query->where(
                    'active',
                    (bool) $filters['active']
                )
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render(
            'support-teams/Index',
            [
                'teams' => $teams,

                'filters' => [
                    'search' => $filters['search'] ?? null,

                    'active' => $filters['active'] ?? null,
                ],

                'currentEnvironment' => [
                    'id' => $environment->id,
                    'name' => $environment->name,
                    'code' => $environment->code,
                ],
            ]
        );
    }

    /**
     * Formulaire de création.
     */
    public function create(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

        return Inertia::render(
            'support-teams/Create',
            [
                'users' => $this->environmentUsers(
                    $environment->id
                ),

                'currentEnvironment' => [
                'id' => $environment->id,
                'name' => $environment->name,
                'code' => $environment->code,
                ],
            ]
        );
    }

    /**
     * Création d'une équipe.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $environment =
            $this->currentEnvironment($request);

        $validated = $request->validate(
            $this->rules(
                environmentId: $environment->id
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Vérifier les utilisateurs
        |--------------------------------------------------------------------------
        */

        $memberIds = collect(
            $validated['users'] ?? []
        )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $leaderId = isset(
            $validated['leader_id']
        )
            ? (int) $validated['leader_id']
            : null;

        $this->validateEnvironmentUsers(
            $environment->id,
            $memberIds->all(),
            $leaderId
        );

        $team = DB::transaction(
            function () use (
                $validated,
                $environment,
                $memberIds,
                $leaderId
            ) {
                $code = ! empty(
                    $validated['code']
                )
                    ? Str::upper(
                        trim(
                            $validated['code']
                        )
                    )
                    : Str::upper(
                        Str::slug(
                            $validated['name'],
                            '_'
                        )
                    );

                /*
                |--------------------------------------------------------------------------
                | Vérification supplémentaire
                |--------------------------------------------------------------------------
                */

                $exists =
                    SupportTeam::query()
                        ->where(
                            'environment_id',
                            $environment->id
                        )
                        ->where(
                            'code',
                            $code
                        )
                        ->exists();

                if ($exists) {
                    abort(
                        422,
                        'Une équipe avec ce code existe déjà dans cet environnement.'
                    );
                }

                $team = SupportTeam::create([
                    'environment_id' => $environment->id,

                    'name' => $validated['name'],

                    'code' => $code,

                    'description' => $validated[
                            'description'
                        ] ?? null,

                    'leader_id' => $leaderId,

                    'active' => $validated['active']
                            ?? true,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Le leader doit également être membre
                |--------------------------------------------------------------------------
                */

                if ($leaderId) {
                    $memberIds->push(
                        $leaderId
                    );
                }

                $team->users()->sync(
                    $memberIds
                        ->unique()
                        ->values()
                        ->all()
                );

                return $team;
            }
        );

        return redirect()
            ->route(
                'support-teams.edit',
                $team
            )
            ->with(
                'success',
                'L’équipe de support a été créée avec succès.'
            );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(
        Request $request,
        SupportTeam $supportTeam
    ): Response {
        $environment =
            $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $supportTeam,
            $environment->id
        );

        $supportTeam->load([
            'leader:id,name,first_name,last_name,email',
            'users:id,name,first_name,last_name,email',
        ]);

        $supportTeam->loadCount(
            'tickets'
        );

        return Inertia::render(
            'support-teams/Edit',
            [
                'team' => [
                    'id' => $supportTeam->id,

                    'name' => $supportTeam->name,

                    'code' => $supportTeam->code,

                    'description' => $supportTeam->description,

                    'leader_id' => $supportTeam->leader_id,

                    'active' => (bool) $supportTeam->active,

                    'users' => $supportTeam
                        ->users
                        ->pluck('id')
                        ->values(),

                    'tickets_count' => $supportTeam
                        ->tickets_count,
                ],

                'users' => $this->environmentUsers(
                    $environment->id
                ),

                'currentEnvironment' => [
                'id' => $environment->id,
                'name' => $environment->name,
                'code' => $environment->code,
                ],
            ]
        );
    }

    /**
     * Modification d'une équipe.
     */
    public function update(
        Request $request,
        SupportTeam $supportTeam
    ): RedirectResponse {
        $environment =
            $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $supportTeam,
            $environment->id
        );

        $validated = $request->validate(
            $this->rules(
                environmentId: $environment->id,

                supportTeam: $supportTeam
            )
        );

        $memberIds = collect(
            $validated['users'] ?? []
        )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $leaderId = isset(
            $validated['leader_id']
        )
            ? (int) $validated['leader_id']
            : null;

        $this->validateEnvironmentUsers(
            $environment->id,
            $memberIds->all(),
            $leaderId
        );

        $code = ! empty(
            $validated['code']
        )
            ? Str::upper(
                trim(
                    $validated['code']
                )
            )
            : Str::upper(
                Str::slug(
                    $validated['name'],
                    '_'
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Vérifier unicité du code
        |--------------------------------------------------------------------------
        */

        $exists =
            SupportTeam::query()
                ->where(
                    'environment_id',
                    $environment->id
                )
                ->where(
                    'code',
                    $code
                )
                ->whereKeyNot(
                    $supportTeam->id
                )
                ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'code' => 'Une équipe avec ce code existe déjà dans cet environnement.',
                ])
                ->withInput();
        }

        DB::transaction(
            function () use (
                $supportTeam,
                $validated,
                $memberIds,
                $leaderId,
                $code
            ) {
                $supportTeam->update([
                    'name' => $validated['name'],

                    'code' => $code,

                    'description' => $validated[
                            'description'
                        ] ?? null,

                    'leader_id' => $leaderId,

                    'active' => $validated['active'],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Toujours garder le leader dans l'équipe
                |--------------------------------------------------------------------------
                */

                if ($leaderId) {
                    $memberIds->push(
                        $leaderId
                    );
                }

                $supportTeam
                    ->users()
                    ->sync(
                        $memberIds
                            ->unique()
                            ->values()
                            ->all()
                    );
            }
        );

        return back()->with(
            'success',
            'L’équipe de support a été modifiée avec succès.'
        );
    }

    /**
     * Activation / désactivation.
     */
    public function toggleActive(
        Request $request,
        SupportTeam $supportTeam
    ): RedirectResponse {
        $environment =
            $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $supportTeam,
            $environment->id
        );

        $supportTeam->update([
            'active' => ! $supportTeam->active,
        ]);

        return back()->with(
            'success',
            $supportTeam
                ->fresh()
                ->active
                ? 'L’équipe de support a été activée.'
                : 'L’équipe de support a été désactivée.'
        );
    }

    /**
     * Suppression.
     */
    public function destroy(
        Request $request,
        SupportTeam $supportTeam
    ): RedirectResponse {
        $environment =
            $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $supportTeam,
            $environment->id
        );

        /*
        |--------------------------------------------------------------------------
        | Ne pas supprimer une équipe utilisée par des tickets
        |--------------------------------------------------------------------------
        */

        if (
            $supportTeam
                ->tickets()
                ->exists()
        ) {
            return back()->withErrors([
                'team' => 'Cette équipe est liée à des tickets et ne peut pas être supprimée. Vous pouvez la désactiver.',
            ]);
        }

        DB::transaction(
            function () use ($supportTeam) {
                $supportTeam
                    ->users()
                    ->detach();

                $supportTeam->delete();
            }
        );

        return redirect()
            ->route(
                'support-teams.index'
            )
            ->with(
                'success',
                'L’équipe de support a été supprimée avec succès.'
            );
    }

    /**
     * Règles de validation.
     */
    private function rules(
        int $environmentId,
        ?SupportTeam $supportTeam = null
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'code' => [
                'nullable',
                'string',
                'max:100',
                'alpha_dash',

                Rule::unique(
                    'support_teams',
                    'code'
                )
                    ->where(
                        fn ($query) => $query->where(
                            'environment_id',
                            $environmentId
                        )
                    )
                    ->ignore(
                        $supportTeam?->id
                    ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'leader_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'users' => [
                'nullable',
                'array',
            ],

            'users.*' => [
                'integer',
                'distinct',
                'exists:users,id',
            ],

            'active' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Utilisateurs actifs accessibles
     * dans l'environnement courant.
     */
    private function environmentUsers(
        int $environmentId
    ): array {
        return User::query()
            ->select([
                'users.id',
                'users.name',
                'users.first_name',
                'users.last_name',
                'users.email',
            ])
            ->join(
                'user_environments',
                'user_environments.user_id',
                '=',
                'users.id'
            )
            ->where(
                'user_environments.environment_id',
                $environmentId
            )
            ->where(
                'user_environments.active',
                true
            )
            ->where(
                'users.active',
                true
            )
            ->orderBy('users.name')
            ->get()
            ->map(
                fn (User $user) => [
                    'id' => $user->id,

                    'name' => $user->full_name,

                    'email' => $user->email,
                ]
            )
            ->values()
            ->all();
    }

    /**
     * Vérifier que le leader et les membres
     * appartiennent réellement à l'environnement.
     */
    private function validateEnvironmentUsers(
        int $environmentId,
        array $memberIds,
        ?int $leaderId
    ): void {
        $ids = collect(
            $memberIds
        )
            ->when(
                $leaderId,
                fn ($collection) => $collection->push(
                    $leaderId
                )
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $validIds = DB::table(
            'user_environments'
        )
            ->join(
                'users',
                'users.id',
                '=',
                'user_environments.user_id'
            )
            ->where(
                'user_environments.environment_id',
                $environmentId
            )
            ->where(
                'user_environments.active',
                true
            )
            ->where(
                'users.active',
                true
            )
            ->whereIn(
                'users.id',
                $ids
            )
            ->pluck(
                'users.id'
            )
            ->map(
                fn ($id) => (int) $id
            );

        if (
            $validIds->count()
            !== $ids->count()
        ) {
            abort(
                403,
                'Un ou plusieurs utilisateurs sélectionnés n’ont pas accès à cet environnement.'
            );
        }
    }

    /**
     * Protection multi-environnement.
     */
    private function ensureSameEnvironment(
        SupportTeam $supportTeam,
        int $environmentId
    ): void {
        abort_unless(
            (int)
            $supportTeam->environment_id
                === $environmentId,
            404
        );
    }

    /**
     * Récupérer l'environnement courant.
     */
    private function currentEnvironment(
        Request $request
    ): Environment {
        $environment =
            $request->attributes->get(
                'currentEnvironment'
            );

        abort_unless(
            $environment instanceof Environment,
            403,
            'Aucun environnement actif sélectionné.'
        );

        return $environment;
    }
}
