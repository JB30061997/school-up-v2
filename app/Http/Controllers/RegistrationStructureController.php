<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Environment;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use Illuminate\Http\JsonResponse;

class RegistrationStructureController extends Controller
{
    /**
     * Années scolaires actives d'un établissement.
     */
    public function schoolYears(Environment $environment): JsonResponse
    {
        abort_unless($environment->active, 404);

        $schoolYears = SchoolYear::query()
            ->where('environment_id', $environment->id)
            ->where('active', true)
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get([
                'id',
                'name',
                'start_date',
                'end_date',
                'is_current',
            ]);

        return response()->json([
            'data' => $schoolYears,
        ]);
    }

    /**
     * Cycles actifs d'un établissement.
     */
    public function cycles(
        Environment $environment,
        SchoolYear $schoolYear
    ): JsonResponse {
        abort_unless($environment->active, 404);

        /*
         * On vérifie que l'année appartient bien
         * à l'établissement sélectionné.
         */
        abort_unless(
            $schoolYear->environment_id === $environment->id
                && $schoolYear->active,
            404
        );

        /*
         * Les cycles sont liés directement à l'environnement.
         *
         * On garde uniquement les cycles qui possèdent au moins
         * une classe active pour l'année scolaire sélectionnée.
         */
        $cycles = Cycle::query()
            ->where('environment_id', $environment->id)
            ->where('active', true)
            ->whereHas('levels.classes', function ($query) use ($schoolYear) {
                $query
                    ->where('school_year_id', $schoolYear->id)
                    ->where('active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'sort_order',
            ]);

        return response()->json([
            'data' => $cycles,
        ]);
    }

    /**
     * Niveaux actifs d'un cycle.
     */
    public function levels(
        Environment $environment,
        SchoolYear $schoolYear,
        Cycle $cycle
    ): JsonResponse {
        abort_unless($environment->active, 404);

        abort_unless(
            $schoolYear->environment_id === $environment->id
                && $schoolYear->active,
            404
        );

        /*
         * Sécurité :
         * impossible d'envoyer un cycle d'une autre école.
         */
        abort_unless(
            $cycle->environment_id === $environment->id
                && $cycle->active,
            404
        );

        $levels = Level::query()
            ->where('cycle_id', $cycle->id)
            ->where('active', true)
            ->whereHas('classes', function ($query) use ($schoolYear) {
                $query
                    ->where('school_year_id', $schoolYear->id)
                    ->where('active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'sort_order',
            ]);

        return response()->json([
            'data' => $levels,
        ]);
    }

    /**
     * Classes actives d'un niveau pour une année donnée.
     */
    public function classes(
        Environment $environment,
        SchoolYear $schoolYear,
        Cycle $cycle,
        Level $level
    ): JsonResponse {
        abort_unless($environment->active, 404);

        abort_unless(
            $schoolYear->environment_id === $environment->id
                && $schoolYear->active,
            404
        );

        abort_unless(
            $cycle->environment_id === $environment->id
                && $cycle->active,
            404
        );

        /*
         * Impossible également d'envoyer un niveau
         * appartenant à un autre cycle.
         */
        abort_unless(
            $level->cycle_id === $cycle->id
                && $level->active,
            404
        );

        $classes = SchoolClass::query()
            ->where('level_id', $level->id)
            ->where('school_year_id', $schoolYear->id)
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'capacity',
            ]);

        return response()->json([
            'data' => $classes,
        ]);
    }
}
