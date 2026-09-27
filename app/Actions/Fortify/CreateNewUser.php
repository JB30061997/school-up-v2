<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\Environment;
use App\Models\Role;
use App\Models\User;
use App\Models\UserEnvironment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        $allowedRoles = [
            'student',
            'parent',
            'teacher',
            'admin',
        ];

        Validator::make($input, [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'username' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'alpha_dash',
                'unique:users,username',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'password' => $this->passwordRules(),

            'role' => [
                'required',
                'string',
                Rule::in($allowedRoles),
            ],

            'environment_id' => [
                'required',
                'integer',
                Rule::exists('environments', 'id')
                    ->where(fn($query) => $query->where('active', true)),
            ],
        ], [
            'role.required' => 'Veuillez sélectionner votre profil.',
            'role.in' => 'Le profil sélectionné est invalide.',

            'environment_id.required' =>
            'Veuillez sélectionner votre établissement.',

            'environment_id.exists' =>
            'L’établissement sélectionné est invalide ou inactif.',

            'username.required' =>
            'Veuillez choisir un identifiant.',

            'username.unique' =>
            'Cet identifiant est déjà utilisé.',

            'email.unique' =>
            'Cette adresse e-mail est déjà utilisée.',
        ])->validate();

        return DB::transaction(function () use ($input) {

            /*
            |--------------------------------------------------------------------------
            | Environment
            |--------------------------------------------------------------------------
            */

            $environment = Environment::query()
                ->whereKey($input['environment_id'])
                ->where('active', true)
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            $role = Role::query()
                ->where('code', $input['role'])
                ->where('active', true)
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $firstName = trim($input['first_name']);
            $lastName = trim($input['last_name']);

            $user = User::create([
                'name' => trim($firstName . ' ' . $lastName),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $input['username'],
                'email' => strtolower(trim($input['email'])),
                'phone' => $input['phone'] ?? null,
                'password' => $input['password'],

                /*
                 * Le compte existe.
                 *
                 * L'accès à l'établissement est contrôlé séparément
                 * dans user_environments.
                 */
                'active' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Approval policy
            |--------------------------------------------------------------------------
            |
            | Élève + Parent :
            | inscription automatiquement approuvée.
            |
            | Professeur + Administrateur :
            | validation obligatoire.
            |
            */

            $requiresApproval = in_array(
                $role->code,
                [
                    'teacher',
                    'admin',
                ],
                true
            );

            /*
            |--------------------------------------------------------------------------
            | Environment assignment
            |--------------------------------------------------------------------------
            */

            UserEnvironment::create([
                'user_id' => $user->id,
                'environment_id' => $environment->id,
                'role_id' => $role->id,

                'approval_status' => $requiresApproval
                    ? 'pending'
                    : 'approved',

                'approved_at' => $requiresApproval
                    ? null
                    : now(),

                'approved_by' => null,

                'is_default' => true,

                /*
                 * Un accès pending n'est PAS encore actif.
                 */
                'active' => ! $requiresApproval,
            ]);

            return $user;
        });
    }
}
