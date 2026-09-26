<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class InternalRequestPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Voir les demandes internes',
                'code' => 'requests.view',
            ],
            [
                'name' => 'Créer une demande interne',
                'code' => 'requests.create',
            ],
            [
                'name' => 'Modifier une demande interne',
                'code' => 'requests.update',
            ],
            [
                'name' => 'Affecter une demande interne',
                'code' => 'requests.assign',
            ],
            [
                'name' => 'Approuver une demande interne',
                'code' => 'requests.approve',
            ],
            [
                'name' => 'Terminer une demande interne',
                'code' => 'requests.complete',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['code' => $permission['code']],
                ['name' => $permission['name']]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::where('code', 'super_admin')->firstOrFail();

        $superAdmin->permissions()->syncWithoutDetaching(
            Permission::pluck('id')->all()
        );

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $this->attachPermissions('admin', [
            'requests.view',
            'requests.create',
            'requests.update',
            'requests.assign',
            'requests.approve',
            'requests.complete',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Responsable
        |--------------------------------------------------------------------------
        */

        $this->attachPermissions('responsable', [
            'requests.view',
            'requests.create',
            'requests.update',
            'requests.assign',
            'requests.approve',
            'requests.complete',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Support
        |--------------------------------------------------------------------------
        */

        $this->attachPermissions('support', [
            'requests.view',
            'requests.create',
            'requests.update',
            'requests.assign',
            'requests.complete',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Viewer
        |--------------------------------------------------------------------------
        */

        $this->attachPermissions('viewer', [
            'requests.view',
        ]);
    }

    private function attachPermissions(
        string $roleCode,
        array $permissionCodes
    ): void {
        $role = Role::where('code', $roleCode)->firstOrFail();

        $permissionIds = Permission::whereIn(
            'code',
            $permissionCodes
        )->pluck('id')->all();

        $role->permissions()->syncWithoutDetaching(
            $permissionIds
        );
    }
}
