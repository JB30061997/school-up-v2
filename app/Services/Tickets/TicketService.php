<?php

namespace App\Services\Tickets;

use App\Models\SupportTeam;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketHistory;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketService
{
    /**
     * Créer un nouveau ticket.
     */
    public function create(array $data, User $user): Ticket
    {
        return DB::transaction(function () use ($data, $user) {

            /*
            |--------------------------------------------------------------------------
            | 1. Vérifier l'accès de l'utilisateur à l'environnement
            |--------------------------------------------------------------------------
            */

            $environmentId = (int) $data['environment_id'];

            if (! $user->hasEnvironmentAccess($environmentId)) {
                throw ValidationException::withMessages([
                    'environment_id' =>
                        'Vous n’avez pas accès à cet environnement.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Vérifier la catégorie
            |--------------------------------------------------------------------------
            */

            $category = TicketCategory::query()
                ->whereKey($data['ticket_category_id'])
                ->where('environment_id', $environmentId)
                ->where('active', true)
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | 3. Vérifier l'équipe de support
            |--------------------------------------------------------------------------
            */

            $supportTeam = null;

            if (! empty($data['support_team_id'])) {
                $supportTeam = SupportTeam::query()
                    ->whereKey($data['support_team_id'])
                    ->where('environment_id', $environmentId)
                    ->where('active', true)
                    ->first();

                if (! $supportTeam) {
                    throw ValidationException::withMessages([
                        'support_team_id' =>
                            'Cette équipe n’appartient pas à cet environnement.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Vérifier l'utilisateur assigné
            |--------------------------------------------------------------------------
            */

            $assignee = null;

            if (! empty($data['assigned_to'])) {
                $assignee = User::query()
                    ->whereKey($data['assigned_to'])
                    ->where('active', true)
                    ->first();

                if (
                    ! $assignee ||
                    ! $assignee->hasEnvironmentAccess($environmentId)
                ) {
                    throw ValidationException::withMessages([
                        'assigned_to' =>
                            'Cet utilisateur n’a pas accès à cet environnement.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Récupérer le statut par défaut
            |--------------------------------------------------------------------------
            */

            $status = TicketStatus::query()
                ->where('is_default', true)
                ->where('active', true)
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | 6. Créer le ticket
            |--------------------------------------------------------------------------
            */

            $ticket = Ticket::create([
                'reference' => $this->temporaryReference(),

                'environment_id' => $environmentId,

                'ticket_category_id' => $category->id,

                'ticket_status_id' => $status->id,

                'created_by' => $user->id,

                'support_team_id' => $supportTeam?->id,

                'assigned_to' => $assignee?->id,

                'subject' => $data['subject'],

                'description' => $data['description'],

                'priority' => $data['priority'] ?? 'normal',

                /*
                 * Calcul automatique du SLA.
                 */
                'due_at' => $category->sla_minutes
                    ? now()->addMinutes($category->sla_minutes)
                    : null,

                /*
                 * Date d'affectation.
                 */
                'assigned_at' => $assignee
                    ? now()
                    : null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 7. Générer la référence définitive
            |--------------------------------------------------------------------------
            */

            $ticket->update([
                'reference' => $this->generateReference($ticket),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 8. Enregistrer l'historique
            |--------------------------------------------------------------------------
            */

            TicketHistory::create([
                'ticket_id' => $ticket->id,

                'user_id' => $user->id,

                'action' => 'created',

                'to_status_id' => $status->id,

                'note' => 'Ticket créé.',

                'metadata' => [
                    'priority' => $ticket->priority,
                    'category_id' => $category->id,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | 9. Retourner le ticket avec ses relations
            |--------------------------------------------------------------------------
            */

            return $ticket->fresh([
                'environment',
                'category',
                'status',
                'creator',
                'supportTeam',
                'assignedTo',
            ]);
        });
    }

    /**
     * Affecter un ticket à une équipe
     * et/ou à un utilisateur.
     */
    public function assign(
        Ticket $ticket,
        User $actor,
        ?SupportTeam $team = null,
        ?User $assignee = null
    ): Ticket {
        return DB::transaction(function () use (
            $ticket,
            $actor,
            $team,
            $assignee
        ) {

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'accès de l'acteur au ticket
            |--------------------------------------------------------------------------
            */

            $this->ensureTicketAccess($ticket, $actor);

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'environnement de l'équipe
            |--------------------------------------------------------------------------
            */

            if (
                $team &&
                $team->environment_id !== $ticket->environment_id
            ) {
                throw ValidationException::withMessages([
                    'support_team_id' =>
                        'Cette équipe n’appartient pas à cet environnement.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier que l'équipe est active
            |--------------------------------------------------------------------------
            */

            if ($team && ! $team->active) {
                throw ValidationException::withMessages([
                    'support_team_id' =>
                        'Cette équipe de support est désactivée.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'accès de l'utilisateur assigné
            |--------------------------------------------------------------------------
            */

            if ($assignee) {
                if (! $assignee->active) {
                    throw ValidationException::withMessages([
                        'assigned_to' =>
                            'Cet utilisateur est désactivé.',
                    ]);
                }

                if (
                    ! $assignee->hasEnvironmentAccess(
                        $ticket->environment_id
                    )
                ) {
                    throw ValidationException::withMessages([
                        'assigned_to' =>
                            'Cet utilisateur n’a pas accès à cet environnement.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Sauvegarder l'ancienne affectation
            |--------------------------------------------------------------------------
            */

            $oldTeamId = $ticket->support_team_id;
            $oldAssigneeId = $ticket->assigned_to;

            /*
            |--------------------------------------------------------------------------
            | Modifier l'affectation
            |--------------------------------------------------------------------------
            */

            $ticket->update([
                'support_team_id' => $team?->id,

                'assigned_to' => $assignee?->id,

                'assigned_at' => ($team || $assignee)
                    ? now()
                    : null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            TicketHistory::create([
                'ticket_id' => $ticket->id,

                'user_id' => $actor->id,

                'action' => 'assigned',

                'note' =>
                    'Affectation du ticket modifiée.',

                'metadata' => [
                    'old_team_id' => $oldTeamId,
                    'new_team_id' => $team?->id,

                    'old_assignee_id' => $oldAssigneeId,
                    'new_assignee_id' => $assignee?->id,
                ],
            ]);

            return $ticket->fresh([
                'supportTeam',
                'assignedTo',
            ]);
        });
    }

    /**
     * Changer le statut d'un ticket.
     */
    public function changeStatus(
        Ticket $ticket,
        TicketStatus $status,
        User $actor,
        ?string $note = null
    ): Ticket {
        return DB::transaction(function () use (
            $ticket,
            $status,
            $actor,
            $note
        ) {

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'accès de l'acteur au ticket
            |--------------------------------------------------------------------------
            */

            $this->ensureTicketAccess($ticket, $actor);

            /*
            |--------------------------------------------------------------------------
            | Vérifier que le statut est actif
            |--------------------------------------------------------------------------
            */

            if (! $status->active) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Ce statut est désactivé.',
                ]);
            }

            $oldStatusId = $ticket->ticket_status_id;

            /*
             * Si le statut ne change pas,
             * on ne fait rien.
             */
            if ($oldStatusId === $status->id) {
                return $ticket;
            }

            /*
            |--------------------------------------------------------------------------
            | Gestion automatique des dates
            |--------------------------------------------------------------------------
            */

            $dates = [];

            switch ($status->code) {
                case 'IN_PROGRESS':
                    $dates['started_at'] =
                        $ticket->started_at ?? now();

                    break;

                case 'RESOLVED':
                    $dates['resolved_at'] = now();

                    break;

                case 'CLOSED':
                    $dates['closed_at'] = now();

                    break;
            }

            /*
            |--------------------------------------------------------------------------
            | Modifier le statut
            |--------------------------------------------------------------------------
            */

            $ticket->update([
                'ticket_status_id' => $status->id,

                ...$dates,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique du changement
            |--------------------------------------------------------------------------
            */

            TicketHistory::create([
                'ticket_id' => $ticket->id,

                'user_id' => $actor->id,

                'action' => 'status_changed',

                'from_status_id' => $oldStatusId,

                'to_status_id' => $status->id,

                'note' => $note,
            ]);

            return $ticket->fresh([
                'status',
            ]);
        });
    }

    /**
     * Ajouter une réponse à un ticket.
     */
    public function reply(
        Ticket $ticket,
        User $user,
        string $message,
        bool $isInternal = false
    ): TicketReply {
        return DB::transaction(function () use (
            $ticket,
            $user,
            $message,
            $isInternal
        ) {

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'accès de l'utilisateur au ticket
            |--------------------------------------------------------------------------
            */

            $this->ensureTicketAccess($ticket, $user);

            /*
            |--------------------------------------------------------------------------
            | Vérifier le message
            |--------------------------------------------------------------------------
            */

            $message = trim($message);

            if ($message === '') {
                throw ValidationException::withMessages([
                    'message' =>
                        'Le message ne peut pas être vide.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Créer la réponse
            |--------------------------------------------------------------------------
            */

            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,

                'user_id' => $user->id,

                'message' => $message,

                'is_internal' => $isInternal,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ajouter dans l'historique
            |--------------------------------------------------------------------------
            */

            TicketHistory::create([
                'ticket_id' => $ticket->id,

                'user_id' => $user->id,

                'action' => $isInternal
                    ? 'internal_note_added'
                    : 'reply_added',

                'note' => $isInternal
                    ? 'Note interne ajoutée.'
                    : 'Réponse ajoutée.',
            ]);

            return $reply->load([
                'user',
            ]);
        });
    }

    /**
     * Vérifie que l'utilisateur possède un accès actif
     * à l'environnement du ticket.
     */
    private function ensureTicketAccess(
        Ticket $ticket,
        User $user
    ): void {
        if (
            ! $user->hasEnvironmentAccess(
                $ticket->environment_id
            )
        ) {
            throw ValidationException::withMessages([
                'ticket' =>
                    'Vous n’avez pas accès à ce ticket.',
            ]);
        }
    }

    /**
     * Générer la référence définitive.
     *
     * Exemple :
     *
     * TCK-2026-000001
     */
    private function generateReference(
        Ticket $ticket
    ): string {
        return sprintf(
            'TCK-%s-%06d',
            $ticket->created_at->format('Y'),
            $ticket->id
        );
    }

    /**
     * Générer une référence temporaire unique
     * avant de connaître l'ID du ticket.
     */
    private function temporaryReference(): string
    {
        return 'TMP-' . bin2hex(
            random_bytes(16)
        );
    }
}