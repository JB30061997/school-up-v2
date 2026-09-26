<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\TicketCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TicketCategoryController extends Controller
{
    /**
     * Liste des catégories de tickets
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

        $categories = TicketCategory::query()
            ->where(
                'environment_id',
                $environment->id
            )
            ->withCount('tickets')
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
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render(
            'ticket-categories/Index',
            [
                'categories' => $categories,

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
    public function create(
        Request $request
    ): Response {
        $environment =
            $this->currentEnvironment($request);

        $nextSortOrder =
            (int) TicketCategory::query()
                ->where(
                    'environment_id',
                    $environment->id
                )
                ->max('sort_order') + 1;

        return Inertia::render(
            'ticket-categories/Create',
            [
                'currentEnvironment' => [
                    'id' => $environment->id,

                    'name' => $environment->name,

                    'code' => $environment->code,
                ],

                'nextSortOrder' => $nextSortOrder,
            ]
        );
    }

    /**
     * Création.
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

        $category = DB::transaction(
            function () use (
                $validated,
                $environment
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
                | Vérification supplémentaire du code
                |--------------------------------------------------------------------------
                */

                $exists =
                    TicketCategory::query()
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
                        'Une catégorie avec ce code existe déjà dans cet environnement.'
                    );
                }

                return TicketCategory::create([
                    'environment_id' => $environment->id,

                    'name' => $validated['name'],

                    'code' => $code,

                    'description' => $validated[
                            'description'
                        ] ?? null,

                    'sla_minutes' => $validated[
                            'sla_minutes'
                        ] ?? null,

                    'active' => $validated['active']
                            ?? true,

                    'sort_order' => $validated[
                            'sort_order'
                        ] ?? 0,
                ]);
            }
        );

        return redirect()
            ->route(
                'ticket-categories.edit',
                $category
            )
            ->with(
                'success',
                'La catégorie de ticket a été créée avec succès.'
            );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(
        Request $request,
        TicketCategory $ticketCategory
    ): Response {
        $environment =
            $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $ticketCategory,
            $environment->id
        );

        $ticketCategory->loadCount(
            'tickets'
        );

        return Inertia::render(
            'ticket-categories/Edit',
            [
                'category' => [
                    'id' => $ticketCategory->id,

                    'name' => $ticketCategory->name,

                    'code' => $ticketCategory->code,

                    'description' => $ticketCategory
                        ->description,

                    'sla_minutes' => $ticketCategory
                        ->sla_minutes,

                    'active' => (bool)
                        $ticketCategory->active,

                    'sort_order' => $ticketCategory
                        ->sort_order,

                    'tickets_count' => $ticketCategory
                        ->tickets_count,
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
     * Modification.
     */
    public function update(
        Request $request,
        TicketCategory $ticketCategory
    ): RedirectResponse {
        $environment =
            $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $ticketCategory,
            $environment->id
        );

        $validated = $request->validate(
            $this->rules(
                environmentId: $environment->id,

                ticketCategory: $ticketCategory
            )
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
        | Vérifier le code dans l'environnement courant
        |--------------------------------------------------------------------------
        */

        $exists =
            TicketCategory::query()
                ->where(
                    'environment_id',
                    $environment->id
                )
                ->where(
                    'code',
                    $code
                )
                ->whereKeyNot(
                    $ticketCategory->id
                )
                ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'code' => 'Une catégorie avec ce code existe déjà dans cet environnement.',
                ])
                ->withInput();
        }

        $ticketCategory->update([
            'name' => $validated['name'],

            'code' => $code,

            'description' => $validated[
                    'description'
                ] ?? null,

            'sla_minutes' => $validated[
                    'sla_minutes'
                ] ?? null,

            'active' => $validated['active'],

            'sort_order' => $validated[
                    'sort_order'
                ] ?? 0,
        ]);

        return back()->with(
            'success',
            'La catégorie de ticket a été modifiée avec succès.'
        );
    }

    /**
     * Activation / désactivation.
     */
    public function toggleActive(
        Request $request,
        TicketCategory $ticketCategory
    ): RedirectResponse {
        $environment =
            $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $ticketCategory,
            $environment->id
        );

        $ticketCategory->update([
            'active' => ! $ticketCategory->active,
        ]);

        return back()->with(
            'success',
            $ticketCategory
                ->fresh()
                ->active
                ? 'La catégorie a été activée.'
                : 'La catégorie a été désactivée.'
        );
    }

    /**
     * Suppression.
     */
    public function destroy(
        Request $request,
        TicketCategory $ticketCategory
    ): RedirectResponse {
        $environment =
            $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $ticketCategory,
            $environment->id
        );

        /*
        |--------------------------------------------------------------------------
        | Ne pas supprimer une catégorie utilisée
        |--------------------------------------------------------------------------
        */

        if (
            $ticketCategory
                ->tickets()
                ->exists()
        ) {
            return back()->withErrors([
                'category' => 'Cette catégorie est utilisée par des tickets et ne peut pas être supprimée. Vous pouvez la désactiver.',
            ]);
        }

        $ticketCategory->delete();

        return redirect()
            ->route(
                'ticket-categories.index'
            )
            ->with(
                'success',
                'La catégorie a été supprimée avec succès.'
            );
    }

    /**
     * Modifier l'ordre d'affichage.
     *
     * Format attendu :
     *
     * categories: [
     *     { id: 3, sort_order: 1 },
     *     { id: 1, sort_order: 2 },
     *     { id: 2, sort_order: 3 }
     * ]
     */
    public function reorder(
        Request $request
    ): RedirectResponse {
        $environment =
            $this->currentEnvironment($request);

        $validated =
            $request->validate([
                'categories' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'categories.*.id' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:ticket_categories,id',
                ],

                'categories.*.sort_order' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ]);

        $ids = collect(
            $validated['categories']
        )
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            );

        /*
        |--------------------------------------------------------------------------
        | Protection multi-environnement
        |--------------------------------------------------------------------------
        */

        $validCount =
            TicketCategory::query()
                ->where(
                    'environment_id',
                    $environment->id
                )
                ->whereIn(
                    'id',
                    $ids
                )
                ->count();

        abort_unless(
            $validCount === $ids->count(),
            403,
            'Une ou plusieurs catégories n’appartiennent pas à l’environnement courant.'
        );

        DB::transaction(
            function () use (
                $validated,
                $environment
            ) {
                foreach (
                    $validated['categories'] as $item
                ) {
                    TicketCategory::query()
                        ->where(
                            'environment_id',
                            $environment->id
                        )
                        ->whereKey(
                            $item['id']
                        )
                        ->update([
                            'sort_order' => $item[
                                    'sort_order'
                                ],
                        ]);
                }
            }
        );

        return back()->with(
            'success',
            'L’ordre des catégories a été mis à jour.'
        );
    }

    /**
     * Règles de validation.
     */
    private function rules(
        int $environmentId,
        ?TicketCategory $ticketCategory = null
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
                    'ticket_categories',
                    'code'
                )
                    ->where(
                        fn ($query) => $query->where(
                            'environment_id',
                            $environmentId
                        )
                    )
                    ->ignore(
                        $ticketCategory?->id
                    ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'sla_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:525600',
            ],

            'active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Protection multi-environnement.
     */
    private function ensureSameEnvironment(
        TicketCategory $ticketCategory,
        int $environmentId
    ): void {
        abort_unless(
            (int)
            $ticketCategory->environment_id
                === $environmentId,
            404
        );
    }

    /**
     * Environnement courant.
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
