<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\SchoolYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SchoolYearController extends Controller
{
    /**
     * Liste des années scolaires
     * de l'environnement courant.
     */
    public function index(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'active' => [
                'nullable',
                Rule::in(['0', '1']),
            ],
        ]);

        $schoolYears = SchoolYear::query()
            ->where(
                'environment_id',
                $environment->id
            )
            ->withCount('classes')
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) => $query->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
            )
            ->when(
                isset($filters['active']),
                fn ($query) => $query->where(
                    'active',
                    (bool) $filters['active']
                )
            )
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render(
            'school-years/Index',
            [
                'schoolYears' => $schoolYears,

                'filters' => [
                    'search' => $filters['search'] ?? null,

                    'active' => $filters['active'] ?? null,
                ],

                'currentEnvironment' => [
                    'id' => $environment->id,
                    'name' => $environment->name,
                    'code' => $environment->code,
                    'current_exercise' => $environment->current_exercise,
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
            'school-years/Create',
            [
                'currentEnvironment' => [
                    'id' => $environment->id,
                    'name' => $environment->name,
                    'code' => $environment->code,
                ],
            ]
        );
    }

    /**
     * Création d'une année scolaire.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $validated = $request->validate(
            $this->rules(
                environmentId: $environment->id
            )
        );

        $schoolYear = DB::transaction(
            function () use (
                $validated,
                $environment
            ) {
                $isCurrent =
                    (bool) ($validated['is_current'] ?? false);

                /*
                |--------------------------------------------------------------------------
                | Une seule année courante par environnement
                |--------------------------------------------------------------------------
                */

                if ($isCurrent) {
                    SchoolYear::query()
                        ->where(
                            'environment_id',
                            $environment->id
                        )
                        ->update([
                            'is_current' => false,
                        ]);
                }

                $schoolYear = SchoolYear::create([
                    'environment_id' => $environment->id,

                    'name' => $validated['name'],

                    'start_date' => $validated['start_date'],

                    'end_date' => $validated['end_date'],

                    'is_current' => $isCurrent,

                    'active' => $validated['active']
                            ?? true,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Synchroniser current_exercise de l'environnement
                |--------------------------------------------------------------------------
                */

                if ($isCurrent) {
                    $environment->update([
                        'current_exercise' => $schoolYear->name,
                    ]);
                }

                return $schoolYear;
            }
        );

        return redirect()
            ->route(
                'school-years.edit',
                $schoolYear
            )
            ->with(
                'success',
                'L’année scolaire a été créée avec succès.'
            );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(
        Request $request,
        SchoolYear $schoolYear
    ): Response {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $schoolYear,
            $environment->id
        );

        $schoolYear->loadCount('classes');

        return Inertia::render(
            'school-years/Edit',
            [
                'schoolYear' => [
                    'id' => $schoolYear->id,

                    'name' => $schoolYear->name,

                    'start_date' => $schoolYear->start_date
                        ?->format('Y-m-d'),

                    'end_date' => $schoolYear->end_date
                        ?->format('Y-m-d'),

                    'is_current' => (bool) $schoolYear->is_current,

                    'active' => (bool) $schoolYear->active,

                    'classes_count' => $schoolYear->classes_count,
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
     * Modification d'une année scolaire.
     */
    public function update(
        Request $request,
        SchoolYear $schoolYear
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $schoolYear,
            $environment->id
        );

        $validated = $request->validate(
            $this->rules(
                environmentId: $environment->id,
                schoolYear: $schoolYear
            )
        );

        DB::transaction(
            function () use (
                $validated,
                $environment,
                $schoolYear
            ) {
                $isCurrent =
                    (bool) $validated['is_current'];

                /*
                |--------------------------------------------------------------------------
                | Si elle devient courante,
                | retirer le statut des autres années
                |--------------------------------------------------------------------------
                */

                if ($isCurrent) {
                    SchoolYear::query()
                        ->where(
                            'environment_id',
                            $environment->id
                        )
                        ->whereKeyNot(
                            $schoolYear->id
                        )
                        ->update([
                            'is_current' => false,
                        ]);
                }

                $schoolYear->update([
                    'name' => $validated['name'],

                    'start_date' => $validated['start_date'],

                    'end_date' => $validated['end_date'],

                    'is_current' => $isCurrent,

                    'active' => $validated['active'],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Synchronisation avec Environment
                |--------------------------------------------------------------------------
                */

                if ($isCurrent) {
                    $environment->update([
                        'current_exercise' => $schoolYear->name,
                    ]);
                } elseif (
                    $environment->current_exercise
                    === $schoolYear->name
                ) {
                    $currentSchoolYear =
                        SchoolYear::query()
                            ->where(
                                'environment_id',
                                $environment->id
                            )
                            ->where(
                                'is_current',
                                true
                            )
                            ->where(
                                'active',
                                true
                            )
                            ->first();

                    $environment->update([
                        'current_exercise' => $currentSchoolYear?->name,
                    ]);
                }
            }
        );

        return back()->with(
            'success',
            'L’année scolaire a été modifiée avec succès.'
        );
    }

    /**
     * Définir une année comme année courante.
     */
    public function setCurrent(
        Request $request,
        SchoolYear $schoolYear
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $schoolYear,
            $environment->id
        );

        if (! $schoolYear->active) {
            return back()->withErrors([
                'school_year' => 'Une année scolaire inactive ne peut pas devenir l’année courante.',
            ]);
        }

        DB::transaction(
            function () use (
                $environment,
                $schoolYear
            ) {
                SchoolYear::query()
                    ->where(
                        'environment_id',
                        $environment->id
                    )
                    ->whereKeyNot(
                        $schoolYear->id
                    )
                    ->update([
                        'is_current' => false,
                    ]);

                $schoolYear->update([
                    'is_current' => true,
                ]);

                $environment->update([
                    'current_exercise' => $schoolYear->name,
                ]);
            }
        );

        return back()->with(
            'success',
            'L’année scolaire courante a été mise à jour.'
        );
    }

    /**
     * Activation / désactivation.
     */
    public function toggleActive(
        Request $request,
        SchoolYear $schoolYear
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $schoolYear,
            $environment->id
        );

        /*
        |--------------------------------------------------------------------------
        | Interdire la désactivation de l'année courante
        |--------------------------------------------------------------------------
        */

        if (
            $schoolYear->active
            && $schoolYear->is_current
        ) {
            return back()->withErrors([
                'school_year' => 'L’année scolaire courante ne peut pas être désactivée. Définissez d’abord une autre année comme courante.',
            ]);
        }

        $schoolYear->update([
            'active' => ! $schoolYear->active,
        ]);

        return back()->with(
            'success',
            $schoolYear->fresh()->active
                ? 'L’année scolaire a été activée.'
                : 'L’année scolaire a été désactivée.'
        );
    }

    /**
     * Suppression.
     */
    public function destroy(
        Request $request,
        SchoolYear $schoolYear
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $schoolYear,
            $environment->id
        );

        /*
        |--------------------------------------------------------------------------
        | Protection année courante
        |--------------------------------------------------------------------------
        */

        if ($schoolYear->is_current) {
            return back()->withErrors([
                'school_year' => 'L’année scolaire courante ne peut pas être supprimée.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Protection si des classes utilisent cette année
        |--------------------------------------------------------------------------
        */

        if ($schoolYear->classes()->exists()) {
            return back()->withErrors([
                'school_year' => 'Cette année scolaire contient des classes et ne peut pas être supprimée.',
            ]);
        }

        $schoolYear->delete();

        return redirect()
            ->route('school-years.index')
            ->with(
                'success',
                'L’année scolaire a été supprimée avec succès.'
            );
    }

    /**
     * Validation.
     */
    private function rules(
        int $environmentId,
        ?SchoolYear $schoolYear = null
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:20',

                Rule::unique(
                    'school_years',
                    'name'
                )
                    ->where(
                        fn ($query) => $query->where(
                            'environment_id',
                            $environmentId
                        )
                    )
                    ->ignore(
                        $schoolYear?->id
                    ),

                'regex:/^\d{4}-\d{4}$/',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],

            'is_current' => [
                'required',
                'boolean',
            ],

            'active' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Protection multi-environnement.
     */
    private function ensureSameEnvironment(
        SchoolYear $schoolYear,
        int $environmentId
    ): void {
        abort_unless(
            (int) $schoolYear->environment_id
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
