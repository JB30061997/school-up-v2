<?php

namespace App\Http\Controllers;

use App\Models\AppointmentType;
use App\Models\RequestType;
use App\Models\TicketStatus;
use Inertia\Inertia;
use Inertia\Response;

class ReferentialController extends Controller
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

        $appointmentTypes = AppointmentType::query()
            ->where('environment_id', $environmentId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $ticketStatuses = TicketStatus::query()
            ->withCount('tickets')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('referentials/Index', [
            'requestTypes' => $requestTypes,
            'appointmentTypes' => $appointmentTypes,
            'ticketStatuses' => $ticketStatuses,
        ]);
    }
}
