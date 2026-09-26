<?php

namespace App\Services\Requests;

use App\Models\InternalRequest;
use App\Models\InternalRequestComment;
use App\Models\InternalRequestHistory;
use App\Models\RequestType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InternalRequestService
{
    /**
     * Créer une demande en brouillon.
     */
    public function create(array $data, User $user): InternalRequest
    {
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
            | Vérifier le type de demande
            |--------------------------------------------------------------------------
            */

            $type = RequestType::query()
                ->whereKey($data['request_type_id'])
                ->where('environment_id', $environmentId)
                ->where('active', true)
                ->first();

            if (! $type) {
                throw ValidationException::withMessages([
                    'request_type_id' => 'Ce type de demande n’est pas disponible dans cet environnement.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Créer la demande
            |--------------------------------------------------------------------------
            */

            $request = InternalRequest::create([
                'reference' => $this->temporaryReference(),

                'environment_id' => $environmentId,

                'request_type_id' => $type->id,

                'created_by' => $user->id,

                'assigned_to' => null,

                'approved_by' => null,

                'subject' => $data['subject'],

                'description' => $data['description'] ?? null,

                'status' => 'DRAFT',

                'priority' => $data['priority'] ?? 'normal',

                'due_at' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Générer la référence définitive
            |--------------------------------------------------------------------------
            */

            $request->update([
                'reference' => $this->generateReference($request),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $request,
                $user,
                'created',
                null,
                'DRAFT',
                'Demande créée en brouillon.'
            );

            return $this->freshRequest($request);
        });
    }

    /**
     * Soumettre une demande.
     */
    public function submit(
        InternalRequest $request,
        User $user
    ): InternalRequest {
        return DB::transaction(function () use ($request, $user) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $user,
                $request->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Seul le créateur peut soumettre
            |--------------------------------------------------------------------------
            */

            if ($request->created_by !== $user->id) {
                throw ValidationException::withMessages([
                    'request' => 'Seul le créateur peut soumettre cette demande.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            $this->ensureStatus(
                $request,
                ['DRAFT'],
                'Cette demande ne peut pas être soumise.'
            );

            $type = $request->type;

            /*
            |--------------------------------------------------------------------------
            | Soumettre
            |--------------------------------------------------------------------------
            */

            $request->update([
                'status' => 'SUBMITTED',

                'submitted_at' => now(),

                'due_at' => $type->sla_minutes
                    ? now()->addMinutes($type->sla_minutes)
                    : null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $request,
                $user,
                'submitted',
                'DRAFT',
                'SUBMITTED',
                'Demande soumise.'
            );

            return $this->freshRequest($request);
        });
    }

    /**
     * Affecter une demande.
     */
    public function assign(
        InternalRequest $request,
        User $actor,
        User $assignee
    ): InternalRequest {
        return DB::transaction(function () use (
            $request,
            $actor,
            $assignee
        ) {

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'accès de l'acteur
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $actor,
                $request->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'accès de l'utilisateur assigné
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $assignee,
                $request->environment_id
            );

            if (! $assignee->active) {
                throw ValidationException::withMessages([
                    'assigned_to' => 'Cet utilisateur est désactivé.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            $this->ensureStatus(
                $request,
                ['SUBMITTED', 'IN_PROGRESS'],
                'Cette demande ne peut pas être affectée dans son état actuel.'
            );

            $oldAssignee = $request->assigned_to;

            /*
            |--------------------------------------------------------------------------
            | Affectation
            |--------------------------------------------------------------------------
            */

            $request->update([
                'assigned_to' => $assignee->id,
                'assigned_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $request,
                $actor,
                'assigned',
                $request->status,
                $request->status,
                'Affectation de la demande modifiée.',
                [
                    'old_assignee_id' => $oldAssignee,
                    'new_assignee_id' => $assignee->id,
                ]
            );

            return $this->freshRequest($request);
        });
    }

    /**
     * Commencer le traitement.
     */
    public function start(
        InternalRequest $request,
        User $user
    ): InternalRequest {
        return DB::transaction(function () use ($request, $user) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $user,
                $request->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            $this->ensureStatus(
                $request,
                ['SUBMITTED'],
                'Cette demande ne peut pas être mise en cours.'
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier l'affectation
            |--------------------------------------------------------------------------
            */

            if (
                $request->assigned_to &&
                $request->assigned_to !== $user->id
            ) {
                throw ValidationException::withMessages([
                    'request' => 'Cette demande est affectée à un autre utilisateur.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Démarrer
            |--------------------------------------------------------------------------
            */

            $request->update([
                'status' => 'IN_PROGRESS',
                'started_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $request,
                $user,
                'started',
                'SUBMITTED',
                'IN_PROGRESS',
                'Traitement de la demande commencé.'
            );

            return $this->freshRequest($request);
        });
    }

    /**
     * Approuver une demande.
     */
    public function approve(
        InternalRequest $request,
        User $user,
        ?string $note = null
    ): InternalRequest {
        return DB::transaction(function () use (
            $request,
            $user,
            $note
        ) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $user,
                $request->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            $this->ensureStatus(
                $request,
                ['IN_PROGRESS'],
                'Cette demande ne peut pas être approuvée.'
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier si approbation nécessaire
            |--------------------------------------------------------------------------
            */

            if (! $request->type->requires_approval) {
                throw ValidationException::withMessages([
                    'request' => 'Ce type de demande ne nécessite pas d’approbation.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Approuver
            |--------------------------------------------------------------------------
            */

            $request->update([
                'status' => 'APPROVED',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $request,
                $user,
                'approved',
                'IN_PROGRESS',
                'APPROVED',
                $note ?? 'Demande approuvée.'
            );

            return $this->freshRequest($request);
        });
    }

    /**
     * Refuser une demande.
     */
    public function reject(
        InternalRequest $request,
        User $user,
        ?string $note = null
    ): InternalRequest {
        return DB::transaction(function () use (
            $request,
            $user,
            $note
        ) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $user,
                $request->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            $this->ensureStatus(
                $request,
                ['IN_PROGRESS'],
                'Cette demande ne peut pas être refusée.'
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier si approbation nécessaire
            |--------------------------------------------------------------------------
            */

            if (! $request->type->requires_approval) {
                throw ValidationException::withMessages([
                    'request' => 'Ce type de demande ne nécessite pas d’approbation.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Refuser
            |--------------------------------------------------------------------------
            */

            $request->update([
                'status' => 'REJECTED',
                'rejected_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $request,
                $user,
                'rejected',
                'IN_PROGRESS',
                'REJECTED',
                $note ?? 'Demande refusée.'
            );

            return $this->freshRequest($request);
        });
    }

    /**
     * Terminer une demande.
     */
    public function complete(
        InternalRequest $request,
        User $user,
        ?string $note = null
    ): InternalRequest {
        return DB::transaction(function () use (
            $request,
            $user,
            $note
        ) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $user,
                $request->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier si approbation nécessaire
            |--------------------------------------------------------------------------
            */

            $requiresApproval =
                $request->type->requires_approval;

            $allowedStatuses = $requiresApproval
                ? ['APPROVED']
                : ['IN_PROGRESS'];

            /*
            |--------------------------------------------------------------------------
            | Vérifier le statut
            |--------------------------------------------------------------------------
            */

            $this->ensureStatus(
                $request,
                $allowedStatuses,
                'Cette demande ne peut pas être terminée.'
            );

            $oldStatus = $request->status;

            /*
            |--------------------------------------------------------------------------
            | Terminer
            |--------------------------------------------------------------------------
            */

            $request->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $request,
                $user,
                'completed',
                $oldStatus,
                'COMPLETED',
                $note ?? 'Demande terminée.'
            );

            return $this->freshRequest($request);
        });
    }

    /**
     * Ajouter un commentaire.
     */
    public function comment(
        InternalRequest $request,
        User $user,
        string $message,
        bool $isInternal = false
    ): InternalRequestComment {
        return DB::transaction(function () use (
            $request,
            $user,
            $message,
            $isInternal
        ) {

            /*
            |--------------------------------------------------------------------------
            | Sécurité multi-environnement
            |--------------------------------------------------------------------------
            */

            $this->ensureEnvironmentAccess(
                $user,
                $request->environment_id
            );

            /*
            |--------------------------------------------------------------------------
            | Vérifier le message
            |--------------------------------------------------------------------------
            */

            $message = trim($message);

            if ($message === '') {
                throw ValidationException::withMessages([
                    'message' => 'Le message ne peut pas être vide.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Créer le commentaire
            |--------------------------------------------------------------------------
            */

            $comment = InternalRequestComment::create([
                'internal_request_id' => $request->id,
                'user_id' => $user->id,
                'message' => $message,
                'is_internal' => $isInternal,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Historique
            |--------------------------------------------------------------------------
            */

            $this->history(
                $request,
                $user,
                $isInternal
                    ? 'internal_note_added'
                    : 'comment_added',
                $request->status,
                $request->status,
                $isInternal
                    ? 'Note interne ajoutée.'
                    : 'Commentaire ajouté.'
            );

            return $comment->load('user');
        });
    }

    /**
     * Vérifier que l'utilisateur possède un accès actif
     * à l'environnement demandé.
     */
    private function ensureEnvironmentAccess(
        User $user,
        int $environmentId
    ): void {
        if (! $user->hasEnvironmentAccess($environmentId)) {
            throw ValidationException::withMessages([
                'environment_id' => 'Vous n’avez pas accès à cet environnement.',
            ]);
        }
    }

    /**
     * Vérifier le statut actuel.
     */
    private function ensureStatus(
        InternalRequest $request,
        array $allowedStatuses,
        string $message
    ): void {
        if (! in_array(
            $request->status,
            $allowedStatuses,
            true
        )) {
            throw ValidationException::withMessages([
                'status' => $message,
            ]);
        }
    }

    /**
     * Ajouter une entrée dans l'historique.
     */
    private function history(
        InternalRequest $request,
        ?User $user,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $note = null,
        ?array $metadata = null
    ): void {
        InternalRequestHistory::create([
            'internal_request_id' => $request->id,
            'user_id' => $user?->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Recharger la demande et ses relations.
     */
    private function freshRequest(
        InternalRequest $request
    ): InternalRequest {
        return $request->fresh([
            'environment',
            'type',
            'creator',
            'assignedTo',
            'approvedBy',
        ]);
    }

    /**
     * Référence temporaire avant obtention de l'ID.
     */
    private function temporaryReference(): string
    {
        return 'TMP-'.bin2hex(random_bytes(16));
    }

    /**
     * Générer la référence définitive.
     *
     * Exemple :
     * DDE-2026-000001
     */
    private function generateReference(
        InternalRequest $request
    ): string {
        return sprintf(
            'DDE-%s-%06d',
            $request->created_at->format('Y'),
            $request->id
        );
    }
}
