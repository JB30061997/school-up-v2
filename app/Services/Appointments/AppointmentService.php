<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\AppointmentType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Créer un nouveau rendez-vous.
     */
    public function create(
        array $data,
        User $user
    ): Appointment {
        return DB::transaction(function () use ($data, $user) {

            $environmentId = (int) $data['environment_id'];

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'accès à l'environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $user,
                $environmentId
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le type de rendez-vous
            |--------------------------------------------------------------------------
            */

            $type = AppointmentType::query()
                ->where('id', $data['appointment_type_id'])
                ->where('environment_id', $environmentId)
                ->where('active', true)
                ->first();

            if (! $type) {
                throw ValidationException::withMessages([
                    'appointment_type_id' =>
                        'Le type de rendez-vous sélectionné est invalide.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Préparer les dates
            |--------------------------------------------------------------------------
            */

            $startsAt = now()->parse(
                $data['starts_at']
            );

            $endsAt = ! empty($data['ends_at'])
                ? now()->parse($data['ends_at'])
                : $startsAt
                    ->copy()
                    ->addMinutes($type->duration_minutes);

            /*
            |--------------------------------------------------------------------------
            | Vérifier les dates
            |--------------------------------------------------------------------------
            */

            if ($endsAt->lessThanOrEqualTo($startsAt)) {
                throw ValidationException::withMessages([
                    'ends_at' =>
                        'La date de fin doit être postérieure à la date de début.',
                ]);
            }

            if ($startsAt->isPast()) {
                throw ValidationException::withMessages([
                    'starts_at' =>
                        'Le rendez-vous doit être planifié dans le futur.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'utilisateur assigné
            |--------------------------------------------------------------------------
            */

            $assignedUser = null;

            if (! empty($data['assigned_to'])) {
                $assignedUser = User::query()
                    ->whereKey($data['assigned_to'])
                    ->where('active', true)
                    ->first();

                if (! $assignedUser) {
                    throw ValidationException::withMessages([
                        'assigned_to' =>
                            'L’utilisateur sélectionné est invalide ou désactivé.',
                    ]);
                }

                $this->ensureEnvironmentAccess(
                    $assignedUser,
                    $environmentId
                );

                /*
                |--------------------------------------------------------------------------
                | Vérifier les conflits
                |--------------------------------------------------------------------------
                */

                $this->ensureNoConflict(
                    $assignedUser->id,
                    $environmentId,
                    $startsAt,
                    $endsAt
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Créer le rendez-vous
            |--------------------------------------------------------------------------
            */

            $appointment = Appointment::create([
                'reference' =>
                    $this->generateReference(),

                'environment_id' =>
                    $environmentId,

                'appointment_type_id' =>
                    $type->id,

                'created_by' =>
                    $user->id,

                'assigned_to' =>
                    $assignedUser?->id,

                'title' =>
                    $data['title'],

                'description' =>
                    $data['description'] ?? null,

                'starts_at' =>
                    $startsAt,

                'ends_at' =>
                    $endsAt,

                'location' =>
                    $data['location'] ?? null,

                'status' =>
                    'REQUESTED',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $appointment,
                $user,
                'created',
                null,
                'REQUESTED',
                'Rendez-vous créé.'
            );

            return $this->loadRelations(
                $appointment
            );
        });
    }

    /**
     * Affecter un rendez-vous.
     */
    public function assign(
        Appointment $appointment,
        User $assignedUser,
        User $actor
    ): Appointment {
        return DB::transaction(function () use (
            $appointment,
            $assignedUser,
            $actor
        ) {

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'accès de l'acteur
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $actor,
                $appointment->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'utilisateur assigné
            |--------------------------------------------------------------------------
            */

            if (! $assignedUser->active) {
                throw ValidationException::withMessages([
                    'assigned_to' =>
                        'Cet utilisateur est désactivé.',
                ]);
            }

            $this->ensureEnvironmentAccess(
                $assignedUser,
                $appointment->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $appointment->status,
                    [
                        'REJECTED',
                        'CANCELLED',
                        'COMPLETED',
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Ce rendez-vous ne peut plus être affecté.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier les conflits
            |--------------------------------------------------------------------------
            */

            $this->ensureNoConflict(
                $assignedUser->id,
                $appointment->environment_id,
                $appointment->starts_at,
                $appointment->ends_at,
                $appointment->id
            );

            /*
            |--------------------------------------------------------------------------
            | Modifier l'affectation
            |--------------------------------------------------------------------------
            */

            $oldAssignedTo =
                $appointment->assigned_to;

            $appointment->update([
                'assigned_to' =>
                    $assignedUser->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $appointment,
                $actor,
                'assigned',
                $appointment->status,
                $appointment->status,
                'Rendez-vous affecté.',
                [
                    'old_assigned_to' =>
                        $oldAssignedTo,

                    'assigned_to' =>
                        $assignedUser->id,
                ]
            );

            return $this->loadRelations(
                $appointment
            );
        });
    }

    /**
     * Confirmer un rendez-vous.
     */
    public function confirm(
        Appointment $appointment,
        User $actor,
        ?string $note = null
    ): Appointment {
        return DB::transaction(function () use (
            $appointment,
            $actor,
            $note
        ) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $actor,
                $appointment->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            if ($appointment->status !== 'REQUESTED') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Seul un rendez-vous demandé peut être confirmé.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier les conflits
            |--------------------------------------------------------------------------
            */

            if ($appointment->assigned_to) {
                $this->ensureNoConflict(
                    $appointment->assigned_to,
                    $appointment->environment_id,
                    $appointment->starts_at,
                    $appointment->ends_at,
                    $appointment->id
                );
            }

            $fromStatus =
                $appointment->status;

            /*
            |--------------------------------------------------------------------------
            | Confirmation
            |--------------------------------------------------------------------------
            */

            $appointment->update([
                'status' =>
                    'CONFIRMED',

                'confirmation_note' =>
                    $note,

                'confirmed_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $appointment,
                $actor,
                'confirmed',
                $fromStatus,
                'CONFIRMED',
                $note ?? 'Rendez-vous confirmé.'
            );

            return $this->loadRelations(
                $appointment
            );
        });
    }

    /**
     * Refuser un rendez-vous.
     */
    public function reject(
        Appointment $appointment,
        User $actor,
        string $reason
    ): Appointment {
        return DB::transaction(function () use (
            $appointment,
            $actor,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $actor,
                $appointment->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le motif
            |--------------------------------------------------------------------------
            */

            $reason = trim($reason);

            if ($reason === '') {
                throw ValidationException::withMessages([
                    'reason' =>
                        'Le motif du refus est obligatoire.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            if ($appointment->status !== 'REQUESTED') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Seul un rendez-vous demandé peut être refusé.',
                ]);
            }

            $fromStatus =
                $appointment->status;

            /*
            |--------------------------------------------------------------------------
            | Refuser
            |--------------------------------------------------------------------------
            */

            $appointment->update([
                'status' =>
                    'REJECTED',

                'rejection_reason' =>
                    $reason,

                'rejected_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $appointment,
                $actor,
                'rejected',
                $fromStatus,
                'REJECTED',
                $reason
            );

            return $this->loadRelations(
                $appointment
            );
        });
    }

    /**
     * Annuler un rendez-vous.
     */
    public function cancel(
        Appointment $appointment,
        User $actor,
        string $reason
    ): Appointment {
        return DB::transaction(function () use (
            $appointment,
            $actor,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $actor,
                $appointment->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le motif
            |--------------------------------------------------------------------------
            */

            $reason = trim($reason);

            if ($reason === '') {
                throw ValidationException::withMessages([
                    'reason' =>
                        'Le motif d’annulation est obligatoire.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $appointment->status,
                    [
                        'REQUESTED',
                        'CONFIRMED',
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Ce rendez-vous ne peut pas être annulé.',
                ]);
            }

            $fromStatus =
                $appointment->status;

            /*
            |--------------------------------------------------------------------------
            | Annuler
            |--------------------------------------------------------------------------
            */

            $appointment->update([
                'status' =>
                    'CANCELLED',

                'cancellation_reason' =>
                    $reason,

                'cancelled_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $appointment,
                $actor,
                'cancelled',
                $fromStatus,
                'CANCELLED',
                $reason
            );

            return $this->loadRelations(
                $appointment
            );
        });
    }

    /**
     * Terminer un rendez-vous.
     */
    public function complete(
        Appointment $appointment,
        User $actor,
        ?string $note = null
    ): Appointment {
        return DB::transaction(function () use (
            $appointment,
            $actor,
            $note
        ) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $actor,
                $appointment->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            if ($appointment->status !== 'CONFIRMED') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Seul un rendez-vous confirmé peut être terminé.',
                ]);
            }

            $fromStatus =
                $appointment->status;

            /*
            |--------------------------------------------------------------------------
            | Terminer
            |--------------------------------------------------------------------------
            */

            $appointment->update([
                'status' =>
                    'COMPLETED',

                'completed_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $appointment,
                $actor,
                'completed',
                $fromStatus,
                'COMPLETED',
                $note ?? 'Rendez-vous terminé.'
            );

            return $this->loadRelations(
                $appointment
            );
        });
    }

    /**
     * Vérifier que l'utilisateur possède un accès actif
     * à l'environnement du rendez-vous.
     */
    private function ensureEnvironmentAccess(
        User $user,
        int $environmentId
    ): void {
        if (! $user->hasEnvironmentAccess($environmentId)) {
            throw ValidationException::withMessages([
                'environment_id' =>
                    'Vous n’avez pas accès à cet environnement.',
            ]);
        }
    }

    /**
     * Vérifier qu'un utilisateur n'a pas déjà
     * un rendez-vous sur le même créneau
     * dans le même environnement.
     */
    private function ensureNoConflict(
        int $userId,
        int $environmentId,
        $startsAt,
        $endsAt,
        ?int $ignoreAppointmentId = null
    ): void {
        $query = Appointment::query()
            ->where(
                'environment_id',
                $environmentId
            )
            ->where(
                'assigned_to',
                $userId
            )
            ->whereIn(
                'status',
                [
                    'REQUESTED',
                    'CONFIRMED',
                ]
            )
            ->where(
                'starts_at',
                '<',
                $endsAt
            )
            ->where(
                'ends_at',
                '>',
                $startsAt
            );

        if ($ignoreAppointmentId) {
            $query->where(
                'id',
                '!=',
                $ignoreAppointmentId
            );
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'starts_at' =>
                    'Cet utilisateur possède déjà un rendez-vous sur ce créneau.',
            ]);
        }
    }

    /**
     * Générer une référence unique.
     *
     * Exemple :
     * RDV-2026-000001
     */
    private function generateReference(): string
    {
        $year = now()->format('Y');

        $lastId = Appointment::query()
            ->lockForUpdate()
            ->max('id') ?? 0;

        return sprintf(
            'RDV-%s-%06d',
            $year,
            $lastId + 1
        );
    }

    /**
     * Ajouter une entrée dans l'historique.
     */
    private function history(
        Appointment $appointment,
        User $user,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $note = null,
        array $metadata = []
    ): void {
        AppointmentHistory::create([
            'appointment_id' =>
                $appointment->id,

            'user_id' =>
                $user->id,

            'action' =>
                $action,

            'from_status' =>
                $fromStatus,

            'to_status' =>
                $toStatus,

            'note' =>
                $note,

            'metadata' =>
                empty($metadata)
                    ? null
                    : $metadata,

            'created_at' =>
                now(),
        ]);
    }

    /**
     * Recharger le rendez-vous
     * avec ses relations principales.
     */
    private function loadRelations(
        Appointment $appointment
    ): Appointment {
        return $appointment->fresh([
            'environment',
            'type',
            'creator',
            'assignedTo',
        ]);
    }
}