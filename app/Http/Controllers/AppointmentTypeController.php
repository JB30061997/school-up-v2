<?php

namespace App\Http\Controllers;

use App\Models\AppointmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentTypeController extends Controller
{
    public function index(): Response
    {
        $environmentId = session('current_environment_id');

        abort_unless(
            $environmentId,
            403,
            'Aucun établissement sélectionné.'
        );

        $appointmentTypes = AppointmentType::query()
            ->where('environment_id', $environmentId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('appointment-types/Index', [
            'appointmentTypes' => $appointmentTypes,
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('appointment_types', 'code')
                    ->where(
                        fn ($query) =>
                        $query->where('environment_id', $environmentId)
                    ),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'duration_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:1440',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'active' => [
                'boolean',
            ],
        ]);

        AppointmentType::create([
            'environment_id' => $environmentId,
            'name' => $validated['name'],
            'code' => strtoupper(trim($validated['code'])),
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => $validated['active'] ?? true,
        ]);

        return back()->with(
            'success',
            'Type de rendez-vous ajouté avec succès.'
        );
    }

    public function update(
        Request $request,
        AppointmentType $appointmentType
    ): RedirectResponse {
        $environmentId = session('current_environment_id');

        abort_unless(
            $appointmentType->environment_id === $environmentId,
            403
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
                Rule::unique('appointment_types', 'code')
                    ->where(
                        fn ($query) =>
                        $query->where('environment_id', $environmentId)
                    )
                    ->ignore($appointmentType->id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'duration_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:1440',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'active' => [
                'boolean',
            ],
        ]);

        $appointmentType->update([
            'name' => $validated['name'],
            'code' => strtoupper(trim($validated['code'])),
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => $validated['active'] ?? true,
        ]);

        return back()->with(
            'success',
            'Type de rendez-vous modifié avec succès.'
        );
    }

    public function toggleActive(
        AppointmentType $appointmentType
    ): RedirectResponse {
        $environmentId = session('current_environment_id');

        abort_unless(
            $appointmentType->environment_id === $environmentId,
            403
        );

        $appointmentType->update([
            'active' => ! $appointmentType->active,
        ]);

        return back()->with(
            'success',
            'Statut modifié avec succès.'
        );
    }

    public function destroy(
        AppointmentType $appointmentType
    ): RedirectResponse {
        $environmentId = session('current_environment_id');

        abort_unless(
            $appointmentType->environment_id === $environmentId,
            403
        );

        if ($appointmentType->appointments()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer ce type car il est utilisé par des rendez-vous.'
            );
        }

        $appointmentType->delete();

        return back()->with(
            'success',
            'Type de rendez-vous supprimé avec succès.'
        );
    }
}