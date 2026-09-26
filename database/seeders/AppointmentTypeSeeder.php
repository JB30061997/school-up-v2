<?php

namespace Database\Seeders;

use App\Models\AppointmentType;
use App\Models\Environment;
use Illuminate\Database\Seeder;

class AppointmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Direction',
                'code' => 'DIRECTION',
                'description' => 'Rendez-vous avec la direction.',
                'duration_minutes' => 30,
                'sort_order' => 1,
            ],
            [
                'name' => 'Administration',
                'code' => 'ADMINISTRATION',
                'description' => 'Rendez-vous avec le service administratif.',
                'duration_minutes' => 30,
                'sort_order' => 2,
            ],
            [
                'name' => 'Scolarité',
                'code' => 'SCOLARITE',
                'description' => 'Rendez-vous concernant la scolarité.',
                'duration_minutes' => 30,
                'sort_order' => 3,
            ],
            [
                'name' => 'Support IT',
                'code' => 'SUPPORT-IT',
                'description' => 'Rendez-vous avec le service informatique.',
                'duration_minutes' => 30,
                'sort_order' => 4,
            ],
            [
                'name' => 'Comptabilité',
                'code' => 'COMPTABILITE',
                'description' => 'Rendez-vous avec le service comptabilité.',
                'duration_minutes' => 30,
                'sort_order' => 5,
            ],
            [
                'name' => 'Autre',
                'code' => 'OTHER',
                'description' => 'Autre type de rendez-vous.',
                'duration_minutes' => 30,
                'sort_order' => 6,
            ],
        ];

        Environment::query()
            ->where('active', true)
            ->each(function (Environment $environment) use ($types) {
                foreach ($types as $type) {
                    AppointmentType::updateOrCreate(
                        [
                            'environment_id' => $environment->id,
                            'code' => $type['code'],
                        ],
                        [
                            'name' => $type['name'],
                            'description' => $type['description'],
                            'duration_minutes' => $type['duration_minutes'],
                            'active' => true,
                            'sort_order' => $type['sort_order'],
                        ]
                    );
                }
            });
    }
}
