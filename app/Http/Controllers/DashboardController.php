<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\InternalRequest;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Dashboard de l'environnement courant.
     */
    public function index(Request $request): Response
    {
        $environment = $request->attributes->get(
            'currentEnvironment'
        );

        abort_unless(
            $environment,
            403,
            'Aucun environnement actif sélectionné.'
        );

        $environmentId = $environment->id;

        /*
        |--------------------------------------------------------------------------
        | Tickets
        |--------------------------------------------------------------------------
        */

        $ticketStatusIds = TicketStatus::query()
            ->pluck('id', 'code');

        $ticketsBase = Ticket::query()
            ->where('environment_id', $environmentId);

        $ticketsTotal = (clone $ticketsBase)->count();

        $ticketsOpen = isset($ticketStatusIds['OPEN'])
            ? (clone $ticketsBase)
                ->where(
                    'ticket_status_id',
                    $ticketStatusIds['OPEN']
                )
                ->count()
            : 0;

        $ticketsInProgress = isset(
            $ticketStatusIds['IN_PROGRESS']
        )
            ? (clone $ticketsBase)
                ->where(
                    'ticket_status_id',
                    $ticketStatusIds['IN_PROGRESS']
                )
                ->count()
            : 0;

        $ticketsPending = isset(
            $ticketStatusIds['PENDING']
        )
            ? (clone $ticketsBase)
                ->where(
                    'ticket_status_id',
                    $ticketStatusIds['PENDING']
                )
                ->count()
            : 0;

        $ticketsResolved = isset(
            $ticketStatusIds['RESOLVED']
        )
            ? (clone $ticketsBase)
                ->where(
                    'ticket_status_id',
                    $ticketStatusIds['RESOLVED']
                )
                ->count()
            : 0;

        $ticketsClosed = isset(
            $ticketStatusIds['CLOSED']
        )
            ? (clone $ticketsBase)
                ->where(
                    'ticket_status_id',
                    $ticketStatusIds['CLOSED']
                )
                ->count()
            : 0;

        $ticketsCancelled = isset(
            $ticketStatusIds['CANCELLED']
        )
            ? (clone $ticketsBase)
                ->where(
                    'ticket_status_id',
                    $ticketStatusIds['CANCELLED']
                )
                ->count()
            : 0;

        $ticketsOverdue = (clone $ticketsBase)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNull('resolved_at')
            ->whereNull('closed_at')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Demandes internes
        |--------------------------------------------------------------------------
        */

        $requestsBase = InternalRequest::query()
            ->where('environment_id', $environmentId);

        $requestsTotal = (clone $requestsBase)->count();

        $requestsDraft = (clone $requestsBase)
            ->where('status', 'DRAFT')
            ->count();

        $requestsSubmitted = (clone $requestsBase)
            ->where('status', 'SUBMITTED')
            ->count();

        $requestsInProgress = (clone $requestsBase)
            ->where('status', 'IN_PROGRESS')
            ->count();

        $requestsCompleted = (clone $requestsBase)
            ->where('status', 'COMPLETED')
            ->count();

        $requestsRejected = (clone $requestsBase)
            ->where('status', 'REJECTED')
            ->count();

        $requestsOverdue = (clone $requestsBase)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', [
                'COMPLETED',
                'REJECTED',
                'CANCELLED',
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Rendez-vous
        |--------------------------------------------------------------------------
        */

        $appointmentsBase = Appointment::query()
            ->where('environment_id', $environmentId);

        $appointmentsTotal =
            (clone $appointmentsBase)->count();

        $appointmentsRequested =
            (clone $appointmentsBase)
                ->where('status', 'REQUESTED')
                ->count();

        $appointmentsConfirmed =
            (clone $appointmentsBase)
                ->where('status', 'CONFIRMED')
                ->count();

        $appointmentsCompleted =
            (clone $appointmentsBase)
                ->where('status', 'COMPLETED')
                ->count();

        $appointmentsRejected =
            (clone $appointmentsBase)
                ->where('status', 'REJECTED')
                ->count();

        $appointmentsCancelled =
            (clone $appointmentsBase)
                ->where('status', 'CANCELLED')
                ->count();

        $appointmentsUpcoming =
            (clone $appointmentsBase)
                ->where('starts_at', '>=', now())
                ->whereIn('status', [
                    'REQUESTED',
                    'CONFIRMED',
                ])
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Derniers tickets
        |--------------------------------------------------------------------------
        */

        $recentTickets = Ticket::query()
            ->where('environment_id', $environmentId)
            ->with([
                'category:id,name',
                'status:id,name,code',
                'creator:id,name,first_name,last_name',
                'assignedTo:id,name,first_name,last_name',
            ])
            ->latest('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dernières demandes internes
        |--------------------------------------------------------------------------
        */

        $recentRequests = InternalRequest::query()
            ->where('environment_id', $environmentId)
            ->with([
                'type:id,name,code',
                'creator:id,name,first_name,last_name',
                'assignedTo:id,name,first_name,last_name',
            ])
            ->latest('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Prochains rendez-vous
        |--------------------------------------------------------------------------
        */

        $upcomingAppointments = Appointment::query()
            ->where('environment_id', $environmentId)
            ->where('starts_at', '>=', now())
            ->whereIn('status', [
                'REQUESTED',
                'CONFIRMED',
            ])
            ->with([
                'type:id,name,code',
                'creator:id,name,first_name,last_name',
                'assignedTo:id,name,first_name,last_name',
            ])
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Réponse Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Dashboard', [
            'environment' => [
                'id' => $environment->id,
                'name' => $environment->name,
                'code' => $environment->code,
                'current_exercise' => $environment->current_exercise,
            ],

            'stats' => [
                'tickets' => [
                    'total' => $ticketsTotal,
                    'open' => $ticketsOpen,
                    'in_progress' => $ticketsInProgress,
                    'pending' => $ticketsPending,
                    'resolved' => $ticketsResolved,
                    'closed' => $ticketsClosed,
                    'cancelled' => $ticketsCancelled,
                    'overdue' => $ticketsOverdue,
                ],

                'requests' => [
                    'total' => $requestsTotal,
                    'draft' => $requestsDraft,
                    'submitted' => $requestsSubmitted,
                    'in_progress' => $requestsInProgress,
                    'completed' => $requestsCompleted,
                    'rejected' => $requestsRejected,
                    'overdue' => $requestsOverdue,
                ],

                'appointments' => [
                    'total' => $appointmentsTotal,
                    'requested' => $appointmentsRequested,
                    'confirmed' => $appointmentsConfirmed,
                    'completed' => $appointmentsCompleted,
                    'rejected' => $appointmentsRejected,
                    'cancelled' => $appointmentsCancelled,
                    'upcoming' => $appointmentsUpcoming,
                ],
            ],

            'recentTickets' => $recentTickets,

            'recentRequests' => $recentRequests,

            'upcomingAppointments' => $upcomingAppointments,
        ]);
    }
}
