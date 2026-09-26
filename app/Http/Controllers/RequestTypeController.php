<?php

namespace App\Http\Controllers;

use App\Models\RequestType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RequestTypeController extends Controller
{
    public function index(): Response
    {
        $environmentId = session('current_environment_id');

        abort_unless(
            $environmentId,
            403,
            'Aucun établissement sélectionné.'
        );

        $requestTypes = RequestType::query()
            ->where('environment_id', $environmentId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('request-types/Index', [
            'requestTypes' => $requestTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $environmentId = session('current_environment_id');

        abort_unless(
            $environmentId,
            403,
            'Aucun établissement sélectionné.'
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('request_types', 'code')
                    ->where(
                        fn ($query) =>
                        $query->where('environment_id', $environmentId)
                    ),
            ],

            'description' => ['nullable', 'string'],

            'sla_minutes' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'requires_approval' => ['boolean'],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'active' => ['boolean'],
        ]);

        RequestType::create([
            'environment_id' => $environmentId,
            'name' => $validated['name'],
            'code' => strtoupper(trim($validated['code'])),
            'description' => $validated['description'] ?? null,
            'sla_minutes' => $validated['sla_minutes'] ?? null,
            'requires_approval' => $validated['requires_approval'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => $validated['active'] ?? true,
        ]);

        return back()->with(
            'success',
            'Type de demande ajouté avec succès.'
        );
    }

    public function update(
        Request $request,
        RequestType $requestType
    ): RedirectResponse {
        $environmentId = session('current_environment_id');

        abort_unless(
            $requestType->environment_id === $environmentId,
            403
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('request_types', 'code')
                    ->where(
                        fn ($query) =>
                        $query->where('environment_id', $environmentId)
                    )
                    ->ignore($requestType->id),
            ],

            'description' => ['nullable', 'string'],

            'sla_minutes' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'requires_approval' => ['boolean'],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'active' => ['boolean'],
        ]);

        $requestType->update([
            'name' => $validated['name'],
            'code' => strtoupper(trim($validated['code'])),
            'description' => $validated['description'] ?? null,
            'sla_minutes' => $validated['sla_minutes'] ?? null,
            'requires_approval' => $validated['requires_approval'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => $validated['active'] ?? true,
        ]);

        return back()->with(
            'success',
            'Type de demande modifié avec succès.'
        );
    }

    public function toggleActive(
        RequestType $requestType
    ): RedirectResponse {
        $environmentId = session('current_environment_id');

        abort_unless(
            $requestType->environment_id === $environmentId,
            403
        );

        $requestType->update([
            'active' => ! $requestType->active,
        ]);

        return back()->with(
            'success',
            'Statut modifié avec succès.'
        );
    }

    public function destroy(
        RequestType $requestType
    ): RedirectResponse {
        $environmentId = session('current_environment_id');

        abort_unless(
            $requestType->environment_id === $environmentId,
            403
        );

        if ($requestType->requests()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer ce type car il est utilisé par des demandes.'
            );
        }

        $requestType->delete();

        return back()->with(
            'success',
            'Type de demande supprimé avec succès.'
        );
    }
}