<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'Voir le dashboard', 'code' => 'dashboard.view', 'module' => 'dashboard'],

            // Utilisateurs
            ['name' => 'Voir les utilisateurs', 'code' => 'users.view', 'module' => 'users'],
            ['name' => 'Créer un utilisateur', 'code' => 'users.create', 'module' => 'users'],
            ['name' => 'Modifier un utilisateur', 'code' => 'users.update', 'module' => 'users'],
            ['name' => 'Supprimer un utilisateur', 'code' => 'users.delete', 'module' => 'users'],

            // Environnements
            ['name' => 'Voir les environnements', 'code' => 'environments.view', 'module' => 'environments'],
            ['name' => 'Gérer les environnements', 'code' => 'environments.manage', 'module' => 'environments'],

            // Réclamations / Tickets
            ['name' => 'Voir les tickets', 'code' => 'tickets.view', 'module' => 'tickets'],
            ['name' => 'Créer un ticket', 'code' => 'tickets.create', 'module' => 'tickets'],
            ['name' => 'Modifier un ticket', 'code' => 'tickets.update', 'module' => 'tickets'],
            ['name' => 'Affecter un ticket', 'code' => 'tickets.assign', 'module' => 'tickets'],
            ['name' => 'Clôturer un ticket', 'code' => 'tickets.close', 'module' => 'tickets'],

            // Rendez-vous
            ['name' => 'Voir les rendez-vous', 'code' => 'rdv.view', 'module' => 'rdv'],
            ['name' => 'Créer un rendez-vous', 'code' => 'rdv.create', 'module' => 'rdv'],
            ['name' => 'Modifier un rendez-vous', 'code' => 'rdv.update', 'module' => 'rdv'],
            ['name' => 'Valider un rendez-vous', 'code' => 'rdv.validate', 'module' => 'rdv'],
            ['name' => 'Annuler un rendez-vous', 'code' => 'rdv.cancel', 'module' => 'rdv'],

            // Administration
            ['name' => 'Gérer les rôles', 'code' => 'roles.manage', 'module' => 'administration'],
            ['name' => 'Gérer les permissions', 'code' => 'permissions.manage', 'module' => 'administration'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['code' => $permission['code']],
                $permission
            );
        }

        // Super Admin = toutes les permissions
        $superAdmin = Role::where('code', 'super_admin')->first();

        if ($superAdmin) {
            $superAdmin->permissions()->sync(
                Permission::pluck('id')->all()
            );
        }

        // Viewer = consultation uniquement
        $viewer = Role::where('code', 'viewer')->first();

        if ($viewer) {
            $viewer->permissions()->sync(
                Permission::whereIn('code', [
                    'dashboard.view',
                    'tickets.view',
                    'rdv.view',
                ])->pluck('id')->all()
            );
        }
    }
}