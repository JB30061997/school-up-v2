<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CycleController extends Controller
{
    public function store(Request $request)
    {
        $environmentId = session('current_environment_id');

        abort_unless($environmentId, 403, 'Aucun établissement sélectionné.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cycles', 'code')
                    ->where('environment_id', $environmentId),
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        Cycle::create([
            'environment_id' => $environmentId,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => true,
        ]);

        return back()->with(
            'success',
            'Le cycle a été ajouté avec succès.'
        );
    }

    public function update(Request $request, Cycle $cycle)
    {
        $this->ensureCycleBelongsToCurrentEnvironment($cycle);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:50',

                Rule::unique('cycles', 'code')
                    ->where(
                        fn($query) => $query->where(
                            'environment_id',
                            $cycle->environment_id
                        )
                    )
                    ->ignore($cycle->id),
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $cycle->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => $validated['active'] ?? $cycle->active,
        ]);

        return back()->with(
            'success',
            'Le cycle a été modifié avec succès.'
        );
    }

    public function destroy(Cycle $cycle)
    {
        $this->ensureCycleBelongsToCurrentEnvironment($cycle);

        if ($cycle->levels()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer ce cycle car il contient des niveaux.'
            );
        }

        $cycle->delete();

        return back()->with(
            'success',
            'Le cycle a été supprimé.'
        );
    }

    public function toggleActive(Cycle $cycle)
    {
        $this->ensureCycleBelongsToCurrentEnvironment($cycle);

        $cycle->update([
            'active' => ! $cycle->active,
        ]);

        return back()->with(
            'success',
            $cycle->active
                ? 'Le cycle a été activé.'
                : 'Le cycle a été désactivé.'
        );
    }

    private function ensureCycleBelongsToCurrentEnvironment(
        Cycle $cycle
    ): void {
        $environmentId = session('current_environment_id');

        abort_unless(
            $environmentId &&
                (int) $cycle->environment_id === (int) $environmentId,
            403
        );
    }
}
