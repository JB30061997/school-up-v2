<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'code' => 'super_admin',
                'description' => 'Accès complet à toute la plateforme School Up.',
                'active' => true,
            ],
            [
                'name' => 'Admin',
                'code' => 'admin',
                'description' => 'Administrateur d’un environnement.',
                'active' => true,
            ],
            [
                'name' => 'Responsable',
                'code' => 'responsable',
                'description' => 'Responsable fonctionnel d’un environnement.',
                'active' => true,
            ],
            [
                'name' => 'Support',
                'code' => 'support',
                'description' => 'Utilisateur chargé du support et du traitement des demandes.',
                'active' => true,
            ],
            [
                'name' => 'Viewer',
                'code' => 'viewer',
                'description' => 'Accès en consultation uniquement.',
                'active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['code' => $role['code']],
                $role
            );
        }
    }
}