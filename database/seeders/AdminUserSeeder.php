<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\Role;
use App\Models\User;
use App\Models\UserEnvironment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@schoolup.local'],
            [
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'username' => 'admin',
                'phone' => null,
                'password' => Hash::make('Admin@2026'),
                'active' => true,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('code', 'super_admin')->firstOrFail();

        $environments = Environment::where('active', true)->get();

        foreach ($environments as $index => $environment) {
            UserEnvironment::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'environment_id' => $environment->id,
                ],
                [
                    'role_id' => $role->id,
                    'is_default' => $index === 0,
                    'active' => true,
                ]
            );
        }
    }
}