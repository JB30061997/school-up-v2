<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\RequestType;
use Illuminate\Database\Seeder;

class RequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Demande d’accès',
                'code' => 'ACCESS',
                'description' => 'Demande de création ou modification d’un accès applicatif.',
                'sla_minutes' => 240,
                'requires_approval' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Demande de matériel',
                'code' => 'EQUIPMENT',
                'description' => 'Demande de matériel informatique.',
                'sla_minutes' => 480,
                'requires_approval' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Demande d’achat',
                'code' => 'PURCHASE',
                'description' => 'Demande d’achat nécessitant une validation.',
                'sla_minutes' => 1440,
                'requires_approval' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Demande de paramétrage',
                'code' => 'CONFIGURATION',
                'description' => 'Demande de paramétrage d’une application.',
                'sla_minutes' => 480,
                'requires_approval' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Autre demande',
                'code' => 'OTHER',
                'description' => 'Autre demande interne.',
                'sla_minutes' => 480,
                'requires_approval' => false,
                'sort_order' => 5,
            ],
        ];

        Environment::query()
            ->where('active', true)
            ->each(function (Environment $environment) use ($types) {
                foreach ($types as $type) {
                    RequestType::updateOrCreate(
                        [
                            'environment_id' => $environment->id,
                            'code' => $type['code'],
                        ],
                        [
                            'name' => $type['name'],
                            'description' => $type['description'],
                            'sla_minutes' => $type['sla_minutes'],
                            'requires_approval' => $type['requires_approval'],
                            'active' => true,
                            'sort_order' => $type['sort_order'],
                        ]
                    );
                }
            });
    }
}