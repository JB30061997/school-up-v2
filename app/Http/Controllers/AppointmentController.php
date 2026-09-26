<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\AppointmentType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    /**
     * Liste des rendez-vous de l'environnement courant.
     */
    public function index(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],

            'status' => [
                'nullable',
                Rule::in(array_keys($this->statuses())),
            ],

            'appointment_type_id' => [
                'nullable',
                'integer',
                'exists:appointment_types,id',
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],

            /*
            |--------------------------------------------------------------------------
            | Calendar period
            |--------------------------------------------------------------------------
            |
            | Ces deux champs servent uniquement à charger la période visible
            | dans l'agenda. La liste paginée reste indépendante.
            |
            */

            'calendar_from' => [
                'nullable',
                'date',
            ],

            'calendar_to' => [
                'nullable',
                'date',
                'after_or_equal:calendar_from',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Query de base
        |--------------------------------------------------------------------------
        */

        $baseQuery = function () use ($environment, $filters) {
            return Appointment::query()
                ->where('environment_id', $environment->id)

                ->when(
                    $filters['search'] ?? null,
                    function ($query, $search) {
                        $query->where(function ($query) use ($search) {
                            $query
                                ->where('reference', 'like', "%{$search}%")
                                ->orWhere('title', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%")
                                ->orWhere('location', 'like', "%{$search}%");
                        });
                    }
                )

                ->when(
                    $filters['status'] ?? null,
                    fn ($query, $status) => $query->where('status', $status)
                )

                ->when(
                    $filters['appointment_type_id'] ?? null,
                    fn ($query, $typeId) => $query->where('appointment_type_id', $typeId)
                );
        };

        /*
        |--------------------------------------------------------------------------
        | Liste paginée
        |--------------------------------------------------------------------------
        */

        $appointments = $baseQuery()
            ->with([
                'type:id,name,code,duration_minutes',
                'creator:id,name,first_name,last_name',
                'assignedTo:id,name,first_name,last_name',
            ])

            ->when(
                $filters['date_from'] ?? null,
                fn ($query, $date) => $query->whereDate('starts_at', '>=', $date)
            )

            ->when(
                $filters['date_to'] ?? null,
                fn ($query, $date) => $query->whereDate('starts_at', '<=', $date)
            )

            ->orderByDesc('starts_at')

            ->paginate(15)

            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Agenda
        |--------------------------------------------------------------------------
        |
        | Par défaut on charge une fenêtre suffisamment large autour
        | d'aujourd'hui. Le frontend pourra ensuite envoyer calendar_from
        | et calendar_to lors de la navigation.
        |
        */

        $calendarFrom =
            $filters['calendar_from']
            ?? now()->startOfWeek()->subWeeks(4)->toDateString();

        $calendarTo =
            $filters['calendar_to']
            ?? now()->endOfWeek()->addWeeks(4)->toDateString();

        $calendarAppointments = $baseQuery()
            ->with([
                'type:id,name,code,duration_minutes',

                'creator:id,name,first_name,last_name',

                'assignedTo:id,name,first_name,last_name',
            ])

            ->whereDate(
                'starts_at',
                '>=',
                $calendarFrom
            )

            ->whereDate(
                'starts_at',
                '<=',
                $calendarTo
            )

            ->orderBy('starts_at')

            ->get([
                'id',
                'reference',
                'appointment_type_id',
                'created_by',
                'assigned_to',
                'title',
                'description',
                'starts_at',
                'ends_at',
                'location',
                'status',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Types
        |--------------------------------------------------------------------------
        */

        $types = AppointmentType::query()
            ->where(
                'environment_id',
                $environment->id
            )

            ->where(
                'active',
                true
            )

            ->orderBy(
                'sort_order'
            )

            ->get([
                'id',
                'name',
                'code',
                'duration_minutes',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'appointments/Index',
            [
                'appointments' => $appointments,

                'calendarAppointments' => $calendarAppointments,

                'types' => $types,

                'filters' => [
                    'search' => $filters['search'] ?? null,

                    'status' => $filters['status'] ?? null,

                    'appointment_type_id' => $filters['appointment_type_id'] ?? null,

                    'date_from' => $filters['date_from'] ?? null,

                    'date_to' => $filters['date_to'] ?? null,

                    'calendar_from' => $calendarFrom,

                    'calendar_to' => $calendarTo,
                ],

                'statuses' => $this->statuses(),
            ]
        );
    }

    /**
     * Formulaire de création.
     */
    public function create(Request $request): Response
    {
        $environment = $this->currentEnvironment($request);

        $types = AppointmentType::query()
            ->where('environment_id', $environment->id)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'code',
                'duration_minutes',
            ]);

        $users = $this->environmentUsers(
            $environment->id
        );

        return Inertia::render('appointments/Create', [
            'types' => $types,
            'users' => $users,
        ]);
    }

    /**
     * Création d'un rendez-vous.
     */
    public function store(Request $request): RedirectResponse
    {
        $environment = $this->currentEnvironment($request);

        $validated = $request->validate([
            'appointment_type_id' => [
                'required',
                'integer',

                Rule::exists(
                    'appointment_types',
                    'id'
                )->where(
                    fn ($query) => $query
                        ->where(
                            'environment_id',
                            $environment->id
                        )
                        ->where('active', true)
                ),
            ],

            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'starts_at' => [
                'required',
                'date',
            ],

            'ends_at' => [
                'required',
                'date',
                'after:starts_at',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vérifier l'utilisateur affecté
        |--------------------------------------------------------------------------
        */

        if (! empty($validated['assigned_to'])) {
            $this->ensureUserBelongsToEnvironment(
                (int) $validated['assigned_to'],
                $environment->id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Vérifier les conflits de planning
        |--------------------------------------------------------------------------
        */

        if (! empty($validated['assigned_to'])) {
            $hasConflict = Appointment::query()
                ->where(
                    'environment_id',
                    $environment->id
                )
                ->where(
                    'assigned_to',
                    $validated['assigned_to']
                )
                ->whereNotIn('status', [
                    'REJECTED',
                    'CANCELLED',
                ])
                ->where(function ($query) use ($validated) {
                    $query
                        ->where(
                            'starts_at',
                            '<',
                            $validated['ends_at']
                        )
                        ->where(
                            'ends_at',
                            '>',
                            $validated['starts_at']
                        );
                })
                ->exists();

            if ($hasConflict) {
                return back()
                    ->withErrors([
                        'starts_at' => 'Cet utilisateur possède déjà un rendez-vous sur ce créneau.',
                    ])
                    ->withInput();
            }
        }

        $appointment = DB::transaction(
            function () use (
                $validated,
                $environment,
                $request
            ) {
                $appointment = Appointment::create([
                    'reference' => $this->generateReference(),

                    'environment_id' => $environment->id,

                    'appointment_type_id' => $validated['appointment_type_id'],

                    'created_by' => $request->user()->id,

                    'assigned_to' => $validated['assigned_to'] ?? null,

                    'title' => $validated['title'],

                    'description' => $validated['description'] ?? null,

                    'starts_at' => $validated['starts_at'],

                    'ends_at' => $validated['ends_at'],

                    'location' => $validated['location'] ?? null,

                    'status' => 'REQUESTED',
                ]);

                AppointmentHistory::create([
                    'appointment_id' => $appointment->id,

                    'user_id' => $request->user()->id,

                    'action' => 'created',

                    'from_status' => null,

                    'to_status' => 'REQUESTED',

                    'note' => 'Rendez-vous créé.',
                ]);

                return $appointment;
            }
        );

        return redirect()
            ->route(
                'appointments.show',
                $appointment
            )
            ->with(
                'success',
                'Le rendez-vous a été créé avec succès.'
            );
    }

    /**
     * Détail du rendez-vous.
     */
    public function show(
        Request $request,
        Appointment $appointment
    ): Response {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $appointment,
            $environment->id
        );

        $appointment->load([
            'type:id,name,code,duration_minutes',

            'creator:id,name,first_name,last_name,email',

            'assignedTo:id,name,first_name,last_name,email',

            'histories' => fn ($query) => $query
                ->with([
                    'user:id,name,first_name,last_name',
                ])
                ->orderBy('id'),
        ]);

        $users = $this->environmentUsers(
            $environment->id
        );

        return Inertia::render('appointments/Show', [
            'appointment' => $appointment,

            'users' => $users,

            'statuses' => $this->statuses(),

            'availableActions' => $this->availableActions(
                $appointment->status
            ),
        ]);
    }

    /**
     * Affecter le rendez-vous.
     */
    public function assign(
        Request $request,
        Appointment $appointment
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $appointment,
            $environment->id
        );

        $validated = $request->validate([
            'assigned_to' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $this->ensureUserBelongsToEnvironment(
            (int) $validated['assigned_to'],
            $environment->id
        );

        /*
        |--------------------------------------------------------------------------
        | Vérifier conflit de planning
        |--------------------------------------------------------------------------
        */

        $hasConflict = Appointment::query()
            ->where(
                'environment_id',
                $environment->id
            )
            ->where(
                'assigned_to',
                $validated['assigned_to']
            )
            ->whereKeyNot(
                $appointment->id
            )
            ->whereNotIn('status', [
                'REJECTED',
                'CANCELLED',
            ])
            ->where(function ($query) use ($appointment) {
                $query
                    ->where(
                        'starts_at',
                        '<',
                        $appointment->ends_at
                    )
                    ->where(
                        'ends_at',
                        '>',
                        $appointment->starts_at
                    );
            })
            ->exists();

        if ($hasConflict) {
            return back()->withErrors([
                'assigned_to' => 'Cet utilisateur possède déjà un rendez-vous sur ce créneau.',
            ]);
        }

        $appointment->update([
            'assigned_to' => $validated['assigned_to'],
        ]);

        AppointmentHistory::create([
            'appointment_id' => $appointment->id,

            'user_id' => $request->user()->id,

            'action' => 'assigned',

            'from_status' => $appointment->status,

            'to_status' => $appointment->status,

            'note' => 'Rendez-vous affecté à un utilisateur.',
        ]);

        return back()->with(
            'success',
            'Le rendez-vous a été affecté avec succès.'
        );
    }

    /**
     * Confirmer un rendez-vous.
     */
    public function confirm(
        Request $request,
        Appointment $appointment
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $appointment,
            $environment->id
        );

        abort_unless(
            $appointment->status === 'REQUESTED',
            422,
            'Ce rendez-vous ne peut pas être confirmé.'
        );

        $validated = $request->validate([
            'confirmation_note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $this->transition(
            appointment: $appointment,
            userId: $request->user()->id,
            newStatus: 'CONFIRMED',
            action: 'confirmed',
            note: $validated['confirmation_note']
                ?? 'Rendez-vous confirmé.',
            additionalData: [
                'confirmation_note' => $validated['confirmation_note'] ?? null,

                'confirmed_at' => now(),

                'rejected_at' => null,

                'rejection_reason' => null,

                'cancelled_at' => null,

                'cancellation_reason' => null,
            ],
        );

        return back()->with(
            'success',
            'Le rendez-vous a été confirmé.'
        );
    }

    /**
     * Rejeter un rendez-vous.
     */
    public function reject(
        Request $request,
        Appointment $appointment
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $appointment,
            $environment->id
        );

        abort_unless(
            $appointment->status === 'REQUESTED',
            422,
            'Ce rendez-vous ne peut pas être rejeté.'
        );

        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $this->transition(
            appointment: $appointment,
            userId: $request->user()->id,
            newStatus: 'REJECTED',
            action: 'rejected',
            note: $validated['rejection_reason'],
            additionalData: [
                'rejection_reason' => $validated['rejection_reason'],

                'rejected_at' => now(),
            ],
        );

        return back()->with(
            'success',
            'Le rendez-vous a été rejeté.'
        );
    }

    /**
     * Annuler un rendez-vous.
     */
    public function cancel(
        Request $request,
        Appointment $appointment
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $appointment,
            $environment->id
        );

        abort_unless(
            in_array(
                $appointment->status,
                [
                    'REQUESTED',
                    'CONFIRMED',
                ],
                true
            ),
            422,
            'Ce rendez-vous ne peut pas être annulé.'
        );

        $validated = $request->validate([
            'cancellation_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $this->transition(
            appointment: $appointment,
            userId: $request->user()->id,
            newStatus: 'CANCELLED',
            action: 'cancelled',
            note: $validated['cancellation_reason'],
            additionalData: [
                'cancellation_reason' => $validated['cancellation_reason'],

                'cancelled_at' => now(),
            ],
        );

        return back()->with(
            'success',
            'Le rendez-vous a été annulé.'
        );
    }

    /**
     * Marquer un rendez-vous comme effectué.
     */
    public function complete(
        Request $request,
        Appointment $appointment
    ): RedirectResponse {
        $environment = $this->currentEnvironment($request);

        $this->ensureSameEnvironment(
            $appointment,
            $environment->id
        );

        abort_unless(
            $appointment->status === 'CONFIRMED',
            422,
            'Seul un rendez-vous confirmé peut être terminé.'
        );

        $validated = $request->validate([
            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $this->transition(
            appointment: $appointment,
            userId: $request->user()->id,
            newStatus: 'COMPLETED',
            action: 'completed',
            note: $validated['note']
                ?? 'Rendez-vous effectué avec succès.',
            additionalData: [
                'completed_at' => now(),
            ],
        );

        return back()->with(
            'success',
            'Le rendez-vous a été marqué comme effectué.'
        );
    }

    /**
     * Transition de statut avec historique.
     */
    private function transition(
        Appointment $appointment,
        int $userId,
        string $newStatus,
        string $action,
        ?string $note = null,
        array $additionalData = []
    ): void {
        DB::transaction(
            function () use (
                $appointment,
                $userId,
                $newStatus,
                $action,
                $note,
                $additionalData
            ) {
                $oldStatus =
                    $appointment->status;

                $appointment->update([
                    ...$additionalData,
                    'status' => $newStatus,
                ]);

                AppointmentHistory::create([
                    'appointment_id' => $appointment->id,

                    'user_id' => $userId,

                    'action' => $action,

                    'from_status' => $oldStatus,

                    'to_status' => $newStatus,

                    'note' => $note,
                ]);
            }
        );
    }

    /**
     * Environnement courant.
     */
    private function currentEnvironment(
        Request $request
    ) {
        $environment =
            $request->attributes->get(
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
     * Protection multi-environnement.
     */
    private function ensureSameEnvironment(
        Appointment $appointment,
        int $environmentId
    ): void {
        abort_unless(
            (int) $appointment->environment_id
                === $environmentId,
            404
        );
    }

    /**
     * Vérifier qu'un utilisateur appartient
     * à l'environnement courant.
     */
    private function ensureUserBelongsToEnvironment(
        int $userId,
        int $environmentId
    ): void {
        $exists = User::query()
            ->whereKey($userId)
            ->where('users.active', true)
            ->whereHas(
                'environments',
                function ($query) use ($environmentId) {
                    $query
                        ->where(
                            'environments.id',
                            $environmentId
                        )
                        ->where(
                            'user_environments.active',
                            true
                        );
                }
            )
            ->exists();

        abort_unless(
            $exists,
            422,
            'Cet utilisateur n’est pas actif dans cet environnement.'
        );
    }

    /**
     * Utilisateurs actifs de l'environnement.
     */
    private function environmentUsers(
        int $environmentId
    ) {
        return User::query()
            ->where('users.active', true)
            ->whereHas(
                'environments',
                function ($query) use ($environmentId) {
                    $query
                        ->where(
                            'environments.id',
                            $environmentId
                        )
                        ->where(
                            'user_environments.active',
                            true
                        );
                }
            )
            ->orderBy('users.name')
            ->get([
                'users.id',
                'users.name',
                'users.first_name',
                'users.last_name',
                'users.email',
            ]);
    }

    /**
     * Génération de référence.
     *
     * Exemple :
     * RDV-2026-000001
     */
    private function generateReference(): string
    {
        $year = now()->format('Y');

        $prefix = "RDV-{$year}-";

        $lastReference = Appointment::query()
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
     * Liste des statuts.
     */
    private function statuses(): array
    {
        return [
            'REQUESTED' => 'Demandé',
            'CONFIRMED' => 'Confirmé',
            'REJECTED' => 'Rejeté',
            'CANCELLED' => 'Annulé',
            'COMPLETED' => 'Effectué',
        ];
    }

    /**
     * Actions disponibles selon le statut.
     */
    private function availableActions(
        string $status
    ): array {
        return match ($status) {
            'REQUESTED' => [
                'confirm',
                'reject',
                'cancel',
            ],

            'CONFIRMED' => [
                'complete',
                'cancel',
            ],

            default => [],
        };
    }
}
