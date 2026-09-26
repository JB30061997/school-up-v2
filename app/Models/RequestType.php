<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'environment_id',
        'name',
        'code',
        'description',
        'sla_minutes',
        'requires_approval',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sla_minutes' => 'integer',
            'requires_approval' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(InternalRequest::class);
    }
}