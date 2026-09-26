<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\Role;
use App\Models\User;
use App\Models\UserEnvironment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs.
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
            'role_id' => [
                'nullable',
                'integer',
                'exists:roles,id',
            ],
        ]);

        $users = User::query()
            ->whereHas(
                'userEnvironments',
                fn ($query) => $query->where(
                    'environment_id',
                    $environment->id
                )
            )
            ->with([
                'userEnvironments' => fn ($query) => $query
                    ->where(
                        'environment_id',
                        $environment->id
                    )
                    ->with([
                        'role:id,name,code',
                        'environment:id,name,code',
                    ]),
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
                                    'first_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'last_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'username',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'phone',
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
            ->when(
                $filters['role_id'] ?? null,
                fn ($query, $roleId) => $query->whereHas(
                    'userEnvironments',
                    fn ($pivotQuery) => $pivotQuery
                        ->where(
                            'environment_id',
                            $environment->id
                        )
                        ->where(
                            'role_id',
                            $roleId
                        )
                )
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::query()
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        return Inertia::render('users/Index', [
            'users' => $users,

            'roles' => $roles,

            'filters' => [
                'search' => $filters['search'] ?? null,

                'active' => $filters['active'] ?? null,

                'role_id' => $filters['role_id'] ?? null,
            ],
        ]);
    }

    /**
     * Formulaire de création.
     */
    public function create(Request $request): Response
    {
        $this->currentEnvironment($request);

        return Inertia::render('users/Create', [
            'environments' => $this->availableEnvironments(),

            'roles' => $this->availableRoles(),
        ]);
    }

    /**
     * Création d'un utilisateur.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $this->currentEnvironment($request);

        $validated = $request->validate(
            $this->userRules()
        );

        $assignments = $this->validateAssignments(
            $request
        );

        $user = DB::transaction(
            function () use (
                $validated,
                $assignments
            ) {
                $user = User::create([
                    'name' => $this->buildName(
                        $validated[
                            'first_name'
                        ] ?? null,
                        $validated[
                            'last_name'
                        ] ?? null,
                        $validated['name'] ?? null
                    ),

                    'first_name' => $validated[
                            'first_name'
                        ] ?? null,

                    'last_name' => $validated[
                            'last_name'
                        ] ?? null,

                    'username' => $validated['username'],

                    'email' => $validated['email'],

                    'phone' => $validated['phone'] ?? null,

                    'password' => Hash::make(
                        $validated['password']
                    ),

                    'active' => $validated['active']
                            ?? true,
                ]);

                $this->syncAssignments(
                    $user,
                    $assignments
                );

                return $user;
            }
        );

        return redirect()
            ->route('users.show', $user)
            ->with(
                'success',
                'Utilisateur créé avec succès.'
            );
    }

    /**
     * Détail d'un utilisateur.
     */
    public function show(
        Request $request,
        User $user
    ): Response {
        $environment = $this->currentEnvironment(
            $request
        );

        $this->ensureVisibleInCurrentEnvironment(
            $user,
            $environment->id
        );

        $user->load([
            'userEnvironments' => fn ($query) => $query
                ->with([
                    'environment:id,name,code,active,current_exercise',
                    'role:id,name,code',
                ])
                ->orderByDesc('is_default')
                ->orderBy('environment_id'),
        ]);

        return Inertia::render('users/Show', [
            'managedUser' => $user,
        ]);
    }

    /**
     * Formulaire de modification.
     */
    public function edit(
        Request $request,
        User $user
    ): Response {
        $environment = $this->currentEnvironment(
            $request
        );

        $this->ensureVisibleInCurrentEnvironment(
            $user,
            $environment->id
        );

        $user->load([
            'userEnvironments' => fn ($query) => $query
                ->with([
                    'environment:id,name,code',
                    'role:id,name,code',
                ])
                ->orderByDesc('is_default')
                ->orderBy('environment_id'),
        ]);

        return Inertia::render('users/Edit', [
            'managedUser' => $user,

            'environments' => $this->availableEnvironments(),

            'roles' => $this->availableRoles(),
        ]);
    }

    /**
     * Modification d'un utilisateur.
     */
    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $environment = $this->currentEnvironment(
            $request
        );

        $this->ensureVisibleInCurrentEnvironment(
            $user,
            $environment->id
        );

        $validated = $request->validate(
            $this->userRules($user)
        );

        $assignments = $this->validateAssignments(
            $request
        );

        DB::transaction(
            function () use (
                $user,
                $validated,
                $assignments
            ) {
                $data = [
                    'name' => $this->buildName(
                        $validated[
                            'first_name'
                        ] ?? null,
                        $validated[
                            'last_name'
                        ] ?? null,
                        $validated['name'] ?? null
                    ),

                    'first_name' => $validated[
                            'first_name'
                        ] ?? null,

                    'last_name' => $validated[
                            'last_name'
                        ] ?? null,

                    'username' => $validated['username'],

                    'email' => $validated['email'],

                    'phone' => $validated['phone'] ?? null,

                    'active' => $validated['active']
                            ?? $user->active,
                ];

                if (
                    ! empty(
                        $validated['password']
                    )
                ) {
                    $data['password'] =
                        Hash::make(
                            $validated['password']
                        );
                }

                $user->update($data);

                $this->syncAssignments(
                    $user,
                    $assignments
                );
            }
        );

        return redirect()
            ->route('users.show', $user)
            ->with(
                'success',
                'Utilisateur modifié avec succès.'
            );
    }

    /**
     * Activation / désactivation globale.
     */
    public function toggleActive(
        Request $request,
        User $user
    ): RedirectResponse {
        $environment = $this->currentEnvironment(
            $request
        );

        $this->ensureVisibleInCurrentEnvironment(
            $user,
            $environment->id
        );

        /*
        | Éviter qu'un utilisateur se désactive
        | lui-même par erreur.
        */
        abort_if(
            $request->user()->id === $user->id
                && $user->active,
            422,
            'Vous ne pouvez pas désactiver votre propre compte.'
        );

        DB::transaction(
            function () use ($user) {
                $newState = ! $user->active;

                $user->update([
                    'active' => $newState,
                ]);

                /*
                | Si le compte est désactivé,
                | tous ses accès sont désactivés.
                */
                if (! $newState) {
                    UserEnvironment::query()
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->update([
                            'active' => false,
                        ]);
                }
            }
        );

        return back()->with(
            'success',
            $user->fresh()->active
                ? 'Utilisateur activé avec succès.'
                : 'Utilisateur désactivé avec succès.'
        );
    }

    /**
     * Active / désactive l'accès à une école.
     */
    public function toggleEnvironment(
        Request $request,
        User $user,
        Environment $environment
    ): RedirectResponse {
        $this->currentEnvironment($request);

        $assignment = UserEnvironment::query()
            ->where('user_id', $user->id)
            ->where(
                'environment_id',
                $environment->id
            )
            ->firstOrFail();

        abort_if(
            ! $user->active
                && ! $assignment->active,
            422,
            'Le compte utilisateur est désactivé.'
        );

        DB::transaction(
            function () use (
                $assignment,
                $user
            ) {
                $newState =
                    ! $assignment->active;

                /*
                | On ne peut pas désactiver
                | l'environnement par défaut.
                */
                abort_if(
                    ! $newState
                        && $assignment->is_default,
                    422,
                    'Définissez d’abord un autre environnement par défaut.'
                );

                $assignment->update([
                    'active' => $newState,
                ]);

                /*
                | S'il n'existe aucun environnement
                | par défaut actif, celui-ci le devient.
                */
                if ($newState) {
                    $hasDefault =
                        UserEnvironment::query()
                            ->where(
                                'user_id',
                                $user->id
                            )
                            ->where(
                                'active',
                                true
                            )
                            ->where(
                                'is_default',
                                true
                            )
                            ->exists();

                    if (! $hasDefault) {
                        $assignment->update([
                            'is_default' => true,
                        ]);
                    }
                }
            }
        );

        return back()->with(
            'success',
            'Accès à l’environnement mis à jour.'
        );
    }

    /**
     * Définit l'environnement par défaut.
     */
    public function setDefaultEnvironment(
        Request $request,
        User $user,
        Environment $environment
    ): RedirectResponse {
        $this->currentEnvironment($request);

        $assignment = UserEnvironment::query()
            ->where('user_id', $user->id)
            ->where(
                'environment_id',
                $environment->id
            )
            ->where('active', true)
            ->first();

        abort_unless(
            $assignment,
            422,
            'Cet environnement n’est pas actif pour cet utilisateur.'
        );

        DB::transaction(
            function () use (
                $user,
                $assignment
            ) {
                UserEnvironment::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->update([
                        'is_default' => false,
                    ]);

                $assignment->update([
                    'is_default' => true,
                ]);
            }
        );

        return back()->with(
            'success',
            'Environnement par défaut mis à jour.'
        );
    }

    /**
     * Règles utilisateur.
     */
    private function userRules(
        ?User $user = null
    ): array {
        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'first_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'username' => [
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'users',
                    'username'
                )->ignore($user?->id),
            ],

            'email' => [
                'required',
                'email',
                'max:255',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($user?->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'password' => [
                $user
                    ? 'nullable'
                    : 'required',

                'confirmed',

                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Validation des affectations.
     *
     * Format attendu :
     *
     * environments: [
     *   {
     *     environment_id: 1,
     *     role_id: 2,
     *     active: true,
     *     is_default: true
     *   }
     * ]
     */
    private function validateAssignments(
        Request $request
    ): array {
        $validated = $request->validate([
            'environments' => [
                'required',
                'array',
                'min:1',
            ],

            'environments.*.environment_id' => [
                'required',
                'integer',
                'distinct',

                Rule::exists(
                    'environments',
                    'id'
                )->where(
                    fn ($query) => $query->where(
                        'active',
                        true
                    )
                ),
            ],

            'environments.*.role_id' => [
                'required',
                'integer',

                Rule::exists(
                    'roles',
                    'id'
                )->where(
                    fn ($query) => $query->where(
                        'active',
                        true
                    )
                ),
            ],

            'environments.*.active' => [
                'required',
                'boolean',
            ],

            'environments.*.is_default' => [
                'required',
                'boolean',
            ],
        ]);

        $assignments =
            $validated['environments'];

        $activeAssignments = collect(
            $assignments
        )->filter(
            fn ($assignment) => (bool) $assignment['active']
        );

        if ($activeAssignments->isEmpty()) {
            abort(
                422,
                'Au moins un environnement doit être actif.'
            );
        }

        $defaults = $activeAssignments
            ->filter(
                fn ($assignment) => (bool) $assignment[
                        'is_default'
                    ]
            );

        if ($defaults->count() !== 1) {
            abort(
                422,
                'Un seul environnement actif doit être défini par défaut.'
            );
        }

        /*
        | Un environnement désactivé
        | ne peut pas être par défaut.
        */
        foreach ($assignments as $assignment) {
            if (
                ! $assignment['active']
                && $assignment['is_default']
            ) {
                abort(
                    422,
                    'Un environnement désactivé ne peut pas être défini par défaut.'
                );
            }
        }

        return $assignments;
    }

    /**
     * Synchronisation des écoles/rôles.
     */
    private function syncAssignments(
        User $user,
        array $assignments
    ): void {
        $environmentIds = collect(
            $assignments
        )
            ->pluck('environment_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->all();

        /*
        | Supprimer les affectations
        | qui ne sont plus envoyées.
        */
        UserEnvironment::query()
            ->where(
                'user_id',
                $user->id
            )
            ->whereNotIn(
                'environment_id',
                $environmentIds
            )
            ->delete();

        foreach (
            $assignments as $assignment
        ) {
            UserEnvironment::updateOrCreate(
                [
                    'user_id' => $user->id,

                    'environment_id' => $assignment[
                            'environment_id'
                        ],
                ],
                [
                    'role_id' => $assignment[
                            'role_id'
                        ],

                    'active' => (bool) $assignment[
                            'active'
                        ],

                    'is_default' => (bool) $assignment[
                            'is_default'
                        ],
                ]
            );
        }
    }

    /**
     * Environnements disponibles.
     */
    private function availableEnvironments()
    {
        return Environment::query()
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'current_exercise',
                'primary_color',
            ]);
    }

    /**
     * Rôles disponibles.
     */
    private function availableRoles()
    {
        return Role::query()
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'description',
            ]);
    }

    /**
     * Protection :
     * l'utilisateur doit appartenir
     * à l'environnement courant.
     */
    private function ensureVisibleInCurrentEnvironment(
        User $user,
        int $environmentId
    ): void {
        $exists = UserEnvironment::query()
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'environment_id',
                $environmentId
            )
            ->exists();

        abort_unless(
            $exists,
            404
        );
    }

    /**
     * Construit le nom complet.
     */
    private function buildName(
        ?string $firstName,
        ?string $lastName,
        ?string $fallback
    ): string {
        $fullName = trim(
            trim($firstName ?? '')
            .' '
            .trim($lastName ?? '')
        );

        if ($fullName !== '') {
            return $fullName;
        }

        return trim(
            $fallback ?: 'Utilisateur'
        );
    }

    /**
     * Environnement courant.
     */
    private function currentEnvironment(
        Request $request
    ) {
        $environment =
            $request->attributes->get(
                'currentEnvironment'
            );

        abort_unless(
            $environment,
            403,
            'Aucun environnement actif sélectionné.'
        );

        return $environment;
    }
}
