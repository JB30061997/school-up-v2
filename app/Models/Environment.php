<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Environment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'app_name',
        'url',

        // Branding
        'logo',
        'logo_dark',
        'primary_color',
        'secondary_color',
        'sidebar_color',
        'sidebar_text_color',
        'accent_color',
        'favicon',

        'current_exercise',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_environments'
        )
            ->withPivot([
                'id',
                'role_id',
                'is_default',
                'active',
            ])
            ->withTimestamps();
    }

    public function userEnvironments(): HasMany
    {
        return $this->hasMany(UserEnvironment::class);
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | School Years
    |--------------------------------------------------------------------------
    */

    public function schoolYears(): HasMany
    {
        return $this->hasMany(SchoolYear::class);
    }

    public function currentSchoolYear(): HasOne
    {
        return $this->hasOne(SchoolYear::class)
            ->where('is_current', true)
            ->where('active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | School Structure
    |--------------------------------------------------------------------------
    */

    public function cycles(): HasMany
    {
        return $this->hasMany(Cycle::class)
            ->orderBy('sort_order');
    }
}
