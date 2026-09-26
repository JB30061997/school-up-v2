<?php

namespace App\Http\Controllers;

use App\Models\InternalRequest;
use App\Models\InternalRequestComment;
use App\Models\InternalRequestHistory;
use App\Models\RequestType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InternalRequestController extends Controller
{
    /**
     * Liste des demandes internes de l'environnement courant.
     */
    public function index(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'string', 'max:50'],
            'request_type_id' => [
                'nullable',
                'integer',
                'exists:request_types,id',
            ],
        ]);

        $requests = InternalRequest::query()
            ->where('environment_id', $environment->id)
            ->with([
                'type:id,name,code',
                'creator:id,name,first_name,last_name',
                'assignedTo:id,name,first_name,last_name',
                'approvedBy:id,name,first_name,last_name',
            ])
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('reference', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['priority'] ?? null,
                fn ($query, $priority) => $query->where('priority', $priority)
            )
            ->when(
                $filters['request_type_id'] ?? null,
                fn ($query, $requestTypeId) => $query->where(
                    'request_type_id',
                    $requestTypeId
                )
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $types = RequestType::query()
            ->where('environment_id', $environment->id)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'code',
            ]);

        return Inertia::render('requests/Index', [
            'requests' => $requests,

            'types' => $types,

            'filters' => [
                'search' => $filters['search'] ?? null,
                'status' => $filters['status'] ?? null,
                'priority' => $filters['priority'] ?? null,
                'request_type_id' => $filters['request_type_id'] ?? null,
            ],

            'statuses' => $this->statuses(),

            'priorities' => $this->priorities(),
        ]);
    }

    /**
     * Formulaire de création.
     */
    public function create(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

        $types = RequestType::query()
            ->where('environment_id', $environment->id)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'code',
            ]);

        return Inertia::render('requests/Create', [
            'types' => $types,
            'priorities' => $this->priorities(),
        ]);
    }

    /**
     * Création d'une demande interne.
     */
    public function store(Request $request): RedirectResponse
    {
        $environment = $this->currentEnvironment($request);

        $validated = $request->validate([
            'request_type_id' => [
                'required',
                'integer',
                Rule::exists('request_types', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where(
                                'environment_id',
                                $environment->id
                            )
                            ->where('active', true)
                    ),
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
                Rule::in(array_keys($this->priorities())),
            ],

            'due_at' => [
                'nullable',
                'date',
            ],
        ]);

        $internalRequest = DB::transaction(
            function () use (
                $validated,
                $environment,
                $request
            ) {
                $internalRequest = InternalRequest::create([
                    'reference' => $this->generateReference(),

                    'environment_id' => $environment->id,

                    'request_type_id' => $validated['request_type_id'],

                    'created_by' => $request->user()->id,

                    'subject' => $validated['subject'],

                    'description' => $validated['description'],

                    'priority' => $validated['priority'],

                    'status' => 'SUBMITTED',

                    'submitted_at' => now(),

                    'due_at' => $validated['due_at'] ?? null,
                ]);

                InternalRequestHistory::create([
                    'internal_request_id' => $internalRequest->id,

                    'user_id' => $request->user()->id,

                    'action' => 'created',

                    'from_status' => null,

                    'to_status' => 'SUBMITTED',

                    'note' => 'Demande interne créée.',
                ]);

                return $internalRequest;
            }
        );

        return redirect()
            ->route('requests.show', $internalRequest)
            ->with(
                'success',
                'La demande interne a été créée avec succès.'
            );
    }

    /**
     * Détail d'une demande.
     */
    public function show(
        Request $request,
        InternalRequest $internalRequest
    ): Response {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $internalRequest,
            $environment->id
        );

        $internalRequest->load([
            'type:id,name,code',
            'creator:id,name,first_name,last_name,email',
            'assignedTo:id,name,first_name,last_name,email',
            'approvedBy:id,name,first_name,last_name,email',

            'comments' => fn ($query) => $query
                ->with([
                    'user:id,name,first_name,last_name',
                ])
                ->orderBy('id'),

            'histories' => fn ($query) => $query
                ->with([
                    'user:id,name,first_name,last_name',
                ])
                ->orderBy('id'),
        ]);

        $users = $this->environmentUsers(
            $environment->id
        );

        return Inertia::render('requests/Show', [
            'internalRequest' => $internalRequest,
            'users' => $users,
            'statuses' => $this->statuses(),
            'priorities' => $this->priorities(),
        ]);
    }

    /**
     * Affecter la demande à un utilisateur.
     */
    public function assign(
        Request $request,
        InternalRequest $internalRequest
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $internalRequest,
            $environment->id
        );

        $validated = $request->validate([
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where(function ($query) use ($environment) {
                        $query->whereExists(
                            function ($subQuery) use ($environment) {
                                $subQuery
                                    ->selectRaw('1')
                                    ->from('user_environments')
                                    ->whereColumn(
                                        'user_environments.user_id',
                                        'users.id'
                                    )
                                    ->where(
                                        'user_environments.environment_id',
                                        $environment->id
                                    )
                                    ->where(
                                        'user_environments.active',
                                        true
                                    );
                            }
                        );
                    }),
            ],
        ]);

        DB::transaction(
            function () use (
                $internalRequest,
                $validated,
                $request
            ) {
                $internalRequest->update([
                    'assigned_to' => $validated['assigned_to'],

                    'assigned_at' => now(),
                ]);

                InternalRequestHistory::create([
                    'internal_request_id' => $internalRequest->id,

                    'user_id' => $request->user()->id,

                    'action' => 'assigned',

                    'from_status' => $internalRequest->status,

                    'to_status' => $internalRequest->status,

                    'note' => 'Demande affectée à un utilisateur.',
                ]);
            }
        );

        return back()->with(
            'success',
            'La demande a été affectée avec succès.'
        );
    }

    /**
     * Changer le statut de la demande.
     */
    public function changeStatus(
        Request $request,
        InternalRequest $internalRequest
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $internalRequest,
            $environment->id
        );

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(array_keys($this->statuses())),
            ],

            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $oldStatus = $internalRequest->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with(
                'info',
                'La demande possède déjà ce statut.'
            );
        }

        DB::transaction(
            function () use (
                $internalRequest,
                $oldStatus,
                $newStatus,
                $validated,
                $request
            ) {
                $updates = [
                    'status' => $newStatus,
                ];

                if ($newStatus === 'SUBMITTED') {
                    $updates['submitted_at'] =
                        $internalRequest->submitted_at ?? now();
                }

                if ($newStatus === 'IN_PROGRESS') {
                    $updates['started_at'] =
                        $internalRequest->started_at ?? now();
                }

                if ($newStatus === 'APPROVED') {
                    $updates['approved_by'] =
                        $request->user()->id;

                    $updates['approved_at'] = now();

                    $updates['rejected_at'] = null;
                }

                if ($newStatus === 'REJECTED') {
                    $updates['rejected_at'] = now();

                    $updates['approved_at'] = null;
                    $updates['approved_by'] = null;
                }

                if ($newStatus === 'COMPLETED') {
                    $updates['completed_at'] = now();
                }

                $internalRequest->update($updates);

                InternalRequestHistory::create([
                    'internal_request_id' => $internalRequest->id,

                    'user_id' => $request->user()->id,

                    'action' => 'status_changed',

                    'from_status' => $oldStatus,

                    'to_status' => $newStatus,

                    'note' => $validated['note'] ?? null,
                ]);
            }
        );

        return back()->with(
            'success',
            'Le statut de la demande a été mis à jour.'
        );
    }

    /**
     * Ajouter un commentaire.
     */
    public function comment(
        Request $request,
        InternalRequest $internalRequest
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $internalRequest,
            $environment->id
        );

        $validated = $request->validate([
            'comment' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(
            function () use (
                $internalRequest,
                $validated,
                $request
            ) {
                InternalRequestComment::create([
                    'internal_request_id' => $internalRequest->id,

                    'user_id' => $request->user()->id,

                    'comment' => $validated['comment'],
                ]);

                InternalRequestHistory::create([
                    'internal_request_id' => $internalRequest->id,

                    'user_id' => $request->user()->id,

                    'action' => 'commented',

                    'from_status' => $internalRequest->status,

                    'to_status' => $internalRequest->status,

                    'note' => 'Commentaire ajouté.',
                ]);
            }
        );

        return back()->with(
            'success',
            'Commentaire ajouté avec succès.'
        );
    }

    /**
     * Environnement courant.
     */
    private function currentEnvironment(Request $request)
    {
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

    /**
     * Empêche l'accès à une demande appartenant
     * à un autre environnement.
     */
    private function ensureSameEnvironment(
        InternalRequest $internalRequest,
        int $environmentId
    ): void {
        abort_unless(
            (int) $internalRequest->environment_id
                === $environmentId,
            404
        );
    }

    /**
     * Utilisateurs actifs de l'environnement.
     */
    private function environmentUsers(int $environmentId)
    {
        return User::query()
            ->where('active', true)
            ->whereHas(
                'environments',
                fn ($query) => $query
                    ->where(
                        'environments.id',
                        $environmentId
                    )
                    ->where('user_environments.active', true)
            )
            ->orderBy('name')
            ->get([
                'users.id',
                'users.name',
                'users.first_name',
                'users.last_name',
                'users.email',
            ]);
    }

    /**
     * Génération de la référence.
     *
     * Exemple :
     * DEM-2026-000001
     */
    private function generateReference(): string
    {
        $year = now()->format('Y');

        $prefix = "DEM-{$year}-";

        $lastReference = InternalRequest::query()
            ->where(
                'reference',
                'like',
                "{$prefix}%"
            )
            ->orderByDesc('id')
            ->value('reference');

        $number = 1;

        if ($lastReference) {
            $lastNumber = (int) substr(
                $lastReference,
                strlen($prefix)
            );

            $number = $lastNumber + 1;
        }

        return $prefix
            .str_pad(
                (string) $number,
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    /**
     * Statuts disponibles.
     */
    private function statuses(): array
    {
        return [
            'DRAFT' => 'Brouillon',
            'SUBMITTED' => 'Soumise',
            'ASSIGNED' => 'Affectée',
            'IN_PROGRESS' => 'En cours',
            'APPROVED' => 'Approuvée',
            'REJECTED' => 'Rejetée',
            'COMPLETED' => 'Terminée',
            'CANCELLED' => 'Annulée',
        ];
    }

    /**
     * Priorités disponibles.
     */
    private function priorities(): array
    {
        return [
            'LOW' => 'Faible',
            'NORMAL' => 'Normale',
            'HIGH' => 'Haute',
            'URGENT' => 'Urgente',
        ];
    }
}
