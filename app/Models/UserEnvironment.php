<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEnvironment extends Model
{
    use HasFactory;

    protected $table = 'user_environments';

    protected $fillable = [
        'user_id',
        'environment_id',
        'role_id',
        'is_default',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Utilisateur concerné.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Environnement / école.
     */
    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    /**
     * Rôle de l'utilisateur dans cet environnement.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}