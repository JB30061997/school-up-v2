<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'from_status_id',
        'to_status_id',
        'note',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(
            TicketStatus::class,
            'from_status_id'
        );
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(
            TicketStatus::class,
            'to_status_id'
        );
    }
}
