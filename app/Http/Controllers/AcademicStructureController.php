<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicStructureController extends Controller
{
    public function index(Request $request): Response
    {
        $environmentId = session('current_environment_id');

        abort_unless(
            $environmentId,
            403,
            'Aucun établissement sélectionné.'
        );

        /*
        |--------------------------------------------------------------------------
        | School years
        |--------------------------------------------------------------------------
        */

        $schoolYears = SchoolYear::query()
            ->where('environment_id', $environmentId)
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get([
                'id',
                'name',
                'start_date',
                'end_date',
                'is_current',
                'active',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Selected year
        |--------------------------------------------------------------------------
        */

        $selectedYearId = $request->integer('school_year_id');

        $selectedYear = $schoolYears->firstWhere(
            'id',
            $selectedYearId
        );

        if (! $selectedYear) {
            $selectedYear = $schoolYears->firstWhere(
                'is_current',
                true
            );
        }

        if (! $selectedYear) {
            $selectedYear = $schoolYears->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Academic structure
        |--------------------------------------------------------------------------
        */

        $cycles = Cycle::query()
            ->where('environment_id', $environmentId)
            ->with([
                'levels' => function ($levelQuery) use ($selectedYear) {
                    $levelQuery
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->with([
                            'classes' => function ($classQuery) use ($selectedYear) {
                                if ($selectedYear) {
                                    $classQuery->where(
                                        'school_year_id',
                                        $selectedYear->id
                                    );
                                } else {
                                    $classQuery->whereRaw('1 = 0');
                                }

                                $classQuery
                                    ->orderBy('name');
                            },
                        ]);
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Cycle $cycle) {
                return [
                    'id' => $cycle->id,
                    'name' => $cycle->name,
                    'code' => $cycle->code,
                    'sort_order' => $cycle->sort_order,
                    'active' => $cycle->active,

                    'levels' => $cycle->levels
                        ->map(function ($level) {
                            return [
                                'id' => $level->id,
                                'name' => $level->name,
                                'code' => $level->code,
                                'sort_order' => $level->sort_order,
                                'active' => $level->active,

                                'classes' => $level->classes
                                    ->map(function ($schoolClass) {
                                        return [
                                            'id' => $schoolClass->id,
                                            'name' => $schoolClass->name,
                                            'code' => $schoolClass->code,
                                            'capacity' => $schoolClass->capacity,
                                            'active' => $schoolClass->active,
                                        ];
                                    })
                                    ->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return Inertia::render(
            'academic-structure/Index',
            [
                'schoolYears' => $schoolYears,
                'selectedYear' => $selectedYear,
                'cycles' => $cycles,
            ]
        );
    }
}
