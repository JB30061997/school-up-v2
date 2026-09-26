<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller
{
    public function store(Request $request)
    {
        $environmentId = session('current_environment_id');

        abort_unless($environmentId, 403);

        $validated = $request->validate([
            'level_id' => [
                'required',
                'integer',
            ],

            'school_year_id' => [
                'required',
                'integer',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
            ],

            'capacity' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $level = Level::query()
            ->where('id', $validated['level_id'])
            ->whereHas(
                'cycle',
                fn($query) => $query->where(
                    'environment_id',
                    $environmentId
                )
            )
            ->firstOrFail();

        $schoolYear = SchoolYear::query()
            ->where('id', $validated['school_year_id'])
            ->where('environment_id', $environmentId)
            ->firstOrFail();

        $request->validate([
            'code' => [
                Rule::unique('school_classes', 'code')
                    ->where('level_id', $level->id)
                    ->where('school_year_id', $schoolYear->id),
            ],
        ]);

        SchoolClass::create([
            'level_id' => $level->id,
            'school_year_id' => $schoolYear->id,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'capacity' => $validated['capacity'] ?? null,
            'active' => true,
        ]);

        return back()->with(
            'success',
            'La classe a été ajoutée avec succès.'
        );
    }

    public function update(
        Request $request,
        SchoolClass $schoolClass
    ) {
        $this->ensureClassBelongsToCurrentEnvironment(
            $schoolClass
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',

                Rule::unique('school_classes', 'code')
                    ->where(
                        'level_id',
                        $schoolClass->level_id
                    )
                    ->where(
                        'school_year_id',
                        $schoolClass->school_year_id
                    )
                    ->ignore($schoolClass->id),
            ],

            'capacity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $schoolClass->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'capacity' => $validated['capacity'] ?? null,
            'active' => $validated['active'] ?? $schoolClass->active,
        ]);

        return back()->with(
            'success',
            'La classe a été modifiée avec succès.'
        );
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $this->ensureClassBelongsToCurrentEnvironment(
            $schoolClass
        );

        $schoolClass->delete();

        return back()->with(
            'success',
            'La classe a été supprimée.'
        );
    }

    public function toggleActive(
        SchoolClass $schoolClass
    ) {
        $this->ensureClassBelongsToCurrentEnvironment(
            $schoolClass
        );

        $schoolClass->update([
            'active' => ! $schoolClass->active,
        ]);

        return back()->with(
            'success',
            $schoolClass->active
                ? 'La classe a été activée.'
                : 'La classe a été désactivée.'
        );
    }

    private function ensureClassBelongsToCurrentEnvironment(
        SchoolClass $schoolClass
    ): void {
        $environmentId = session('current_environment_id');

        $schoolClass->loadMissing([
            'level.cycle',
            'schoolYear',
        ]);

        $sameLevelEnvironment =
            (int) $schoolClass->level->cycle->environment_id
            === (int) $environmentId;

        $sameYearEnvironment =
            (int) $schoolClass->schoolYear->environment_id
            === (int) $environmentId;

        abort_unless(
            $environmentId &&
                $sameLevelEnvironment &&
                $sameYearEnvironment,
            403
        );
    }
}
