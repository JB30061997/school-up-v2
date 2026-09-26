<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'environment_id',
        'request_type_id',
        'created_by',
        'assigned_to',
        'approved_by',
        'subject',
        'description',
        'status',
        'priority',
        'submitted_at',
        'assigned_at',
        'started_at',
        'due_at',
        'approved_at',
        'rejected_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'due_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RequestType::class, 'request_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(InternalRequestComment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(InternalRequestHistory::class);
    }
}
