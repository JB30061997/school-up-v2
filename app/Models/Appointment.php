<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'environment_id',
        'appointment_type_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'location',
        'status',
        'confirmation_note',
        'rejection_reason',
        'cancellation_reason',
        'confirmed_at',
        'rejected_at',
        'cancelled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(
            AppointmentType::class,
            'appointment_type_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AppointmentHistory::class);
    }
}