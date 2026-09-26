<?php

namespace App\Http\Controllers;

use App\Models\SupportTeam;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Models\User;
use App\Services\Tickets\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService
    ) {
    }

    /**
     * Liste des tickets de l'environnement courant.
     */
    public function index(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

        $tickets = Ticket::query()
            ->forEnvironment($environment->id)
            ->with([
                'category:id,name',
                'status:id,name,code',
                'supportTeam:id,name',
                'assignedTo:id,name,first_name,last_name',
                'creator:id,name,first_name,last_name',
            ])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('tickets/Index', [
            'tickets' => $tickets,

            'environment' => [
                'id' => $environment->id,
                'name' => $environment->name,
                'code' => $environment->code,
            ],
        ]);
    }

    /**
     * Formulaire de création.
     */
    public function create(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

        $categories = TicketCategory::query()
            ->where('environment_id', $environment->id)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'code',
                'sla_minutes',
            ]);

        $teams = SupportTeam::query()
            ->where('environment_id', $environment->id)
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        return Inertia::render('tickets/Create', [
            'categories' => $categories,
            'teams' => $teams,

            'environment' => [
                'id' => $environment->id,
                'name' => $environment->name,
                'code' => $environment->code,
            ],
        ]);
    }

    /**
     * Enregistrer un nouveau ticket.
     */
    public function store(Request $request): RedirectResponse
    {
        $environment = $this->currentEnvironment($request);

        $validated = $request->validate([
            'ticket_category_id' => [
                'required',
                'integer',
            ],

            'support_team_id' => [
                'nullable',
                'integer',
            ],

            'assigned_to' => [
                'nullable',
                'integer',
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'priority' => [
                'required',
                Rule::in([
                    'low',
                    'normal',
                    'high',
                    'urgent',
                ]),
            ],
        ]);

        $validated['environment_id'] =
            $environment->id;

        $ticket = $this->ticketService->create(
            $validated,
            $request->user()
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with(
                'success',
                'Ticket créé avec succès.'
            );
    }

    /**
     * Afficher un ticket.
     */
    public function show(
        Request $request,
        int $ticket
    ): Response {
        $environment = $this->currentEnvironment($request);

        /*
         * Important :
         * on ne fait PAS Ticket::findOrFail($ticket).
         *
         * Le ticket doit obligatoirement appartenir
         * à l'environnement courant.
         */
        $ticket = Ticket::query()
            ->forEnvironment($environment->id)
            ->with([
                'environment',
                'category',
                'status',
                'creator',
                'supportTeam',
                'assignedTo',

                'replies' => fn ($query) =>
                    $query->with('user')
                        ->orderBy('id'),

                'histories' => fn ($query) =>
                    $query->with('user')
                        ->orderByDesc('id'),
            ])
            ->findOrFail($ticket);

        $statuses = TicketStatus::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'code',
                'is_closed',
            ]);

        $teams = SupportTeam::query()
            ->where('environment_id', $environment->id)
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        $users = User::query()
            ->where('users.active', true)
            ->whereHas(
                'environments',
                fn ($query) =>
                    $query
                        ->where(
                            'environments.id',
                            $environment->id
                        )
                        ->where(
                            'user_environments.active',
                            true
                        )
            )
            ->orderBy('name')
            ->get([
                'users.id',
                'users.name',
                'users.first_name',
                'users.last_name',
            ]);

        return Inertia::render('tickets/Show', [
            'ticket' => $ticket,
            'statuses' => $statuses,
            'teams' => $teams,
            'users' => $users,
        ]);
    }

    /**
     * Affecter un ticket.
     */
    public function assign(
        Request $request,
        int $ticket
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $ticket = Ticket::query()
            ->forEnvironment($environment->id)
            ->findOrFail($ticket);

        $validated = $request->validate([
            'support_team_id' => [
                'nullable',
                'integer',
            ],

            'assigned_to' => [
                'nullable',
                'integer',
            ],
        ]);

        $team = null;

        if (! empty($validated['support_team_id'])) {
            $team = SupportTeam::query()
                ->whereKey(
                    $validated['support_team_id']
                )
                ->where(
                    'environment_id',
                    $environment->id
                )
                ->where('active', true)
                ->firstOrFail();
        }

        $assignee = null;

        if (! empty($validated['assigned_to'])) {
            $assignee = User::query()
                ->whereKey(
                    $validated['assigned_to']
                )
                ->where('active', true)
                ->firstOrFail();
        }

        $this->ticketService->assign(
            $ticket,
            $request->user(),
            $team,
            $assignee
        );

        return back()->with(
            'success',
            'Affectation mise à jour avec succès.'
        );
    }

    /**
     * Changer le statut d'un ticket.
     */
    public function changeStatus(
        Request $request,
        int $ticket
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $ticket = Ticket::query()
            ->forEnvironment($environment->id)
            ->findOrFail($ticket);

        $validated = $request->validate([
            'ticket_status_id' => [
                'required',
                'integer',
                'exists:ticket_statuses,id',
            ],

            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $status = TicketStatus::query()
            ->whereKey(
                $validated['ticket_status_id']
            )
            ->where('active', true)
            ->firstOrFail();

        $this->ticketService->changeStatus(
            $ticket,
            $status,
            $request->user(),
            $validated['note'] ?? null
        );

        return back()->with(
            'success',
            'Statut du ticket mis à jour.'
        );
    }

    /**
     * Ajouter une réponse.
     */
    public function reply(
        Request $request,
        int $ticket
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $ticket = Ticket::query()
            ->forEnvironment($environment->id)
            ->findOrFail($ticket);

        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:10000',
            ],

            'is_internal' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $this->ticketService->reply(
            $ticket,
            $request->user(),
            $validated['message'],
            (bool) ($validated['is_internal'] ?? false)
        );

        return back()->with(
            'success',
            'Réponse ajoutée avec succès.'
        );
    }

    /**
     * Retourne l'environnement courant.
     */
    private function currentEnvironment(
        Request $request
    ) {
        $environment = $request->attributes->get(
            'currentEnvironment'
        );

        abort_unless(
            $environment,
            403,
            'Aucun environnement actif sélectionné.'
        );

        return $environment;
    }
}