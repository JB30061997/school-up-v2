<?php

namespace App\Http\Controllers;

use App\Models\TicketStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TicketStatusController extends Controller
{
    public function index(): Response
    {
        $ticketStatuses = TicketStatus::query()
            ->withCount('tickets')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('ticket-statuses/Index', [
            'ticketStatuses' => $ticketStatuses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                'unique:ticket_statuses,code',
            ],

            'is_closed' => [
                'boolean',
            ],

            'is_default' => [
                'boolean',
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

        DB::transaction(function () use ($validated) {

            $isDefault = $validated['is_default'] ?? false;

            if ($isDefault) {
                TicketStatus::query()->update([
                    'is_default' => false,
                ]);
            }

            TicketStatus::create([
                'name' => $validated['name'],
                'code' => strtoupper(trim($validated['code'])),
                'is_closed' => $validated['is_closed'] ?? false,
                'is_default' => $isDefault,
                'sort_order' => $validated['sort_order'] ?? 0,
                'active' => $validated['active'] ?? true,
            ]);
        });

        return back()->with(
            'success',
            'Statut de ticket ajouté avec succès.'
        );
    }

    public function update(
        Request $request,
        TicketStatus $ticketStatus
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ticket_statuses', 'code')
                    ->ignore($ticketStatus->id),
            ],

            'is_closed' => [
                'boolean',
            ],

            'is_default' => [
                'boolean',
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

        DB::transaction(function () use (
            $validated,
            $ticketStatus
        ) {
            $isDefault = $validated['is_default'] ?? false;

            if ($isDefault) {
                TicketStatus::query()
                    ->whereKeyNot($ticketStatus->id)
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $ticketStatus->update([
                'name' => $validated['name'],
                'code' => strtoupper(trim($validated['code'])),
                'is_closed' => $validated['is_closed'] ?? false,
                'is_default' => $isDefault,
                'sort_order' => $validated['sort_order'] ?? 0,
                'active' => $validated['active'] ?? true,
            ]);
        });

        return back()->with(
            'success',
            'Statut de ticket modifié avec succès.'
        );
    }

    public function toggleActive(
        TicketStatus $ticketStatus
    ): RedirectResponse {
        if ($ticketStatus->is_default && $ticketStatus->active) {
            return back()->with(
                'error',
                'Le statut par défaut ne peut pas être désactivé.'
            );
        }

        $ticketStatus->update([
            'active' => ! $ticketStatus->active,
        ]);

        return back()->with(
            'success',
            'Statut modifié avec succès.'
        );
    }

    public function setDefault(
        TicketStatus $ticketStatus
    ): RedirectResponse {
        if (! $ticketStatus->active) {
            return back()->with(
                'error',
                'Un statut inactif ne peut pas devenir le statut par défaut.'
            );
        }

        DB::transaction(function () use ($ticketStatus) {

            TicketStatus::query()->update([
                'is_default' => false,
            ]);

            $ticketStatus->update([
                'is_default' => true,
            ]);
        });

        return back()->with(
            'success',
            'Statut par défaut modifié avec succès.'
        );
    }

    public function destroy(
        TicketStatus $ticketStatus
    ): RedirectResponse {
        if ($ticketStatus->is_default) {
            return back()->with(
                'error',
                'Impossible de supprimer le statut par défaut.'
            );
        }

        if ($ticketStatus->tickets()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer ce statut car il est utilisé par des tickets.'
            );
        }

        $ticketStatus->delete();

        return back()->with(
            'success',
            'Statut de ticket supprimé avec succès.'
        );
    }
}