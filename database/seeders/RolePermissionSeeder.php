<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = Permission::query()
            ->pluck('id')
            ->all();

        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        | Accès total à toute l'application.
        */

        $superAdmin = Role::query()
            ->where('code', 'super_admin')
            ->firstOrFail();

        $superAdmin->permissions()->sync($allPermissions);

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $this->syncRole('admin', [
            'dashboard.view',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'environments.view',

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.close',

            'rdv.view',
            'rdv.create',
            'rdv.update',
            'rdv.validate',
            'rdv.cancel',
        ]);

        /*
        |--------------------------------------------------------------------------
        | RESPONSABLE
        |--------------------------------------------------------------------------
        */

        $this->syncRole('responsable', [
            'dashboard.view',

            'users.view',
            'environments.view',

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.close',

            'rdv.view',
            'rdv.create',
            'rdv.update',
            'rdv.validate',
            'rdv.cancel',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SUPPORT
        |--------------------------------------------------------------------------
        */

        $this->syncRole('support', [
            'dashboard.view',

            'environments.view',

            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.close',

            'rdv.view',
            'rdv.create',
            'rdv.update',
        ]);

        /*
        |--------------------------------------------------------------------------
        | VIEWER
        |--------------------------------------------------------------------------
        */

        $this->syncRole('viewer', [
            'dashboard.view',
            'environments.view',
            'tickets.view',
            'rdv.view',
        ]);
    }

    private function syncRole(
        string $roleCode,
        array $permissionCodes
    ): void {
        $role = Role::query()
            ->where('code', $roleCode)
            ->firstOrFail();

        $permissionIds = Permission::query()
            ->whereIn('code', $permissionCodes)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);
    }
}