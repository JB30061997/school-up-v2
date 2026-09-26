<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Liste des rôles.
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

        $roles = Role::query()
            ->withCount([
                'permissions',
                'userEnvironments',
            ])
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($query) use ($search) {
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
                    });
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

        return Inertia::render('roles/Index', [
            'roles' => $roles,

            'filters' => [
                'search' => $filters['search'] ?? null,

                'active' => $filters['active'] ?? null,
            ],
        ]);
    }

    /**
     * Formulaire de création.
     */
    public function create(): Response
    {
        return Inertia::render('roles/Create', [
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    /**
     * Création d'un rôle.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'code' => [
                'nullable',
                'string',
                'max:100',
                'alpha_dash',
                'unique:roles,code',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'active' => [
                'required',
                'boolean',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                'distinct',
                'exists:permissions,id',
            ],
        ]);

        $role = DB::transaction(
            function () use ($validated) {
                $code = ! empty($validated['code'])
                    ? Str::lower(
                        $validated['code']
                    )
                    : Str::slug(
                        $validated['name'],
                        '_'
                    );

                /*
                |--------------------------------------------------------------------------
                | Vérifier le code généré
                |--------------------------------------------------------------------------
                */

                if (
                    Role::query()
                        ->where('code', $code)
                        ->exists()
                ) {
                    abort(
                        422,
                        'Un rôle avec ce code existe déjà.'
                    );
                }

                $role = Role::create([
                    'name' => $validated['name'],

                    'code' => $code,

                    'description' => $validated['description']
                            ?? null,

                    'active' => $validated['active'],
                ]);

                $role->permissions()->sync(
                    $validated['permissions']
                        ?? []
                );

                return $role;
            }
        );

        return redirect()
            ->route('roles.edit', $role)
            ->with(
                'success',
                'Le rôle a été créé avec succès.'
            );
    }

    /**
     * Formulaire de modification.
     */
public function edit(Role $role): Response
{
    $role->load('permissions:id,name,code,module,description');

    return Inertia::render('roles/Edit', [
        'role' => [
            'id' => $role->id,
            'name' => $role->name,
            'code' => $role->code,
            'description' => $role->description,
            'active' => (bool) $role->active,

            // Vue محتاج IDs
            'permissions' => $role->permissions
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
        ],

        // IMPORTANT: flat array
        'permissions' => Permission::query()
            ->orderBy('module')
            ->orderBy('code')
            ->get([
                'id',
                'name',
                'code',
                'module',
                'description',
            ])
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'code' => $permission->code,
                'module' => $permission->module,
                'description' => $permission->description,
            ])
            ->values()
            ->all(),
    ]);
}

    /**
     * Modification d'un rôle.
     */
    public function update(
        Request $request,
        Role $role
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'code' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',

                Rule::unique(
                    'roles',
                    'code'
                )->ignore($role->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'active' => [
                'required',
                'boolean',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                'distinct',
                'exists:permissions,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Protection Super Admin
        |--------------------------------------------------------------------------
        |
        | On évite de désactiver le rôle super_admin.
        |
        */

        if (
            $role->code === 'super_admin'
            && ! $validated['active']
        ) {
            return back()
                ->withErrors([
                    'active' => 'Le rôle Super Admin ne peut pas être désactivé.',
                ]);
        }

        DB::transaction(
            function () use (
                $role,
                $validated
            ) {
                $role->update([
                    'name' => $validated['name'],

                    'code' => Str::lower(
                        $validated['code']
                    ),

                    'description' => $validated['description']
                            ?? null,

                    'active' => $validated['active'],
                ]);

                $role->permissions()->sync(
                    $validated['permissions']
                        ?? []
                );
            }
        );

        return back()->with(
            'success',
            'Le rôle a été modifié avec succès.'
        );
    }

    /**
     * Activation / désactivation d'un rôle.
     */
    public function toggleActive(
        Role $role
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Protection Super Admin
        |--------------------------------------------------------------------------
        */

        if (
            $role->code === 'super_admin'
            && $role->active
        ) {
            return back()->withErrors([
                'role' => 'Le rôle Super Admin ne peut pas être désactivé.',
            ]);
        }

        $role->update([
            'active' => ! $role->active,
        ]);

        return back()->with(
            'success',
            $role->fresh()->active
                ? 'Le rôle a été activé.'
                : 'Le rôle a été désactivé.'
        );
    }

    /**
     * Suppression d'un rôle.
     */
    public function destroy(
        Role $role
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Protection Super Admin
        |--------------------------------------------------------------------------
        */

        if ($role->code === 'super_admin') {
            return back()->withErrors([
                'role' => 'Le rôle Super Admin ne peut pas être supprimé.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Vérifier si le rôle est utilisé
        |--------------------------------------------------------------------------
        */

        if (
            $role->userEnvironments()
                ->exists()
        ) {
            return back()->withErrors([
                'role' => 'Ce rôle est actuellement affecté à un ou plusieurs utilisateurs. Il ne peut pas être supprimé.',
            ]);
        }

        DB::transaction(
            function () use ($role) {
                /*
                | Détacher les permissions.
                */
                $role->permissions()->detach();

                /*
                | Supprimer le rôle.
                */
                $role->delete();
            }
        );

        return redirect()
            ->route('roles.index')
            ->with(
                'success',
                'Le rôle a été supprimé avec succès.'
            );
    }

    /**
     * Permissions regroupées par module.
     *
     * Exemple :
     *
     * [
     *     "users" => [...],
     *     "tickets" => [...],
     *     "rdv" => [...]
     * ]
     */
    private function groupedPermissions(): array
    {
        return Permission::query()
            ->orderBy('module')
            ->orderBy('code')
            ->get([
                'id',
                'name',
                'code',
                'module',
                'description',
            ])
            ->groupBy(
                fn (Permission $permission) => $permission->module
                        ?: $this->moduleFromCode(
                            $permission->code
                        )
            )
            ->map(
                fn ($permissions) => $permissions
                    ->map(
                        fn (Permission $permission) => [
                            'id' => $permission->id,

                            'name' => $permission->name,

                            'code' => $permission->code,

                            'module' => $permission->module,

                            'description' => $permission->description,
                        ]
                    )
                    ->values()
                    ->all()
            )
            ->all();
    }

    /**
     * Déduire le module depuis le code
     * si le champ module est vide.
     *
     * users.create -> users
     * tickets.assign -> tickets
     * dashboard.view -> dashboard
     */
    private function moduleFromCode(
        string $code
    ): string {
        return Str::before(
            $code,
            '.'
        );
    }
}
