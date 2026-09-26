<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalRequestComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_request_id',
        'user_id',
        'message',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(
            InternalRequest::class,
            'internal_request_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
