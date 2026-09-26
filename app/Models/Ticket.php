<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'environment_id',
        'ticket_category_id',
        'ticket_status_id',
        'created_by',
        'support_team_id',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'due_at',
        'assigned_at',
        'started_at',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Limite les tickets à un environnement précis.
     */
    public function scopeForEnvironment(
        Builder $query,
        int $environmentId
    ): Builder {
        return $query->where(
            $this->qualifyColumn('environment_id'),
            $environmentId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            TicketCategory::class,
            'ticket_category_id'
        );
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(
            TicketStatus::class,
            'ticket_status_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function supportTeam(): BelongsTo
    {
        return $this->belongsTo(
            SupportTeam::class,
            'support_team_id'
        );
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class);
    }
}