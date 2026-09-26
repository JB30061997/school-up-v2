<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    public function store(Request $request)
    {
        $environmentId = session('current_environment_id');

        abort_unless($environmentId, 403);

        $validated = $request->validate([
            'cycle_id' => [
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

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $cycle = Cycle::query()
            ->where('id', $validated['cycle_id'])
            ->where('environment_id', $environmentId)
            ->firstOrFail();

        $request->validate([
            'code' => [
                Rule::unique('levels', 'code')
                    ->where('cycle_id', $cycle->id),
            ],
        ]);

        Level::create([
            'cycle_id' => $cycle->id,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => true,
        ]);

        return back()->with(
            'success',
            'Le niveau a été ajouté avec succès.'
        );
    }

    public function update(Request $request, Level $level)
    {
        $this->ensureLevelBelongsToCurrentEnvironment($level);

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

                Rule::unique('levels', 'code')
                    ->where('cycle_id', $level->cycle_id)
                    ->ignore($level->id),
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

        $level->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => $validated['active'] ?? $level->active,
        ]);

        return back()->with(
            'success',
            'Le niveau a été modifié avec succès.'
        );
    }

    public function destroy(Level $level)
    {
        $this->ensureLevelBelongsToCurrentEnvironment($level);

        if ($level->classes()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer ce niveau car il contient des classes.'
            );
        }

        $level->delete();

        return back()->with(
            'success',
            'Le niveau a été supprimé.'
        );
    }

    public function toggleActive(Level $level)
    {
        $this->ensureLevelBelongsToCurrentEnvironment($level);

        $level->update([
            'active' => ! $level->active,
        ]);

        return back()->with(
            'success',
            $level->active
                ? 'Le niveau a été activé.'
                : 'Le niveau a été désactivé.'
        );
    }

    private function ensureLevelBelongsToCurrentEnvironment(
        Level $level
    ): void {
        $environmentId = session('current_environment_id');

        $level->loadMissing('cycle');

        abort_unless(
            $environmentId &&
                (int) $level->cycle->environment_id === (int) $environmentId,
            403
        );
    }
}
