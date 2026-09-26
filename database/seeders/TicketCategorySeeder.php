<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pronote',
                'code' => 'PRONOTE',
                'description' => 'Incidents et demandes liés à Pronote.',
                'sla_minutes' => 240,
                'sort_order' => 1,
            ],
            [
                'name' => 'Odoo',
                'code' => 'ODOO',
                'description' => 'Incidents et demandes liés à Odoo.',
                'sla_minutes' => 240,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sage',
                'code' => 'SAGE',
                'description' => 'Incidents et demandes liés à Sage.',
                'sla_minutes' => 480,
                'sort_order' => 3,
            ],
            [
                'name' => 'Moovapps',
                'code' => 'MOOVAPPS',
                'description' => 'Incidents et demandes liés à Moovapps.',
                'sla_minutes' => 480,
                'sort_order' => 4,
            ],
            [
                'name' => 'Matériel informatique',
                'code' => 'HARDWARE',
                'description' => 'Ordinateurs, imprimantes et matériel informatique.',
                'sla_minutes' => 480,
                'sort_order' => 5,
            ],
            [
                'name' => 'Réseau / Internet',
                'code' => 'NETWORK',
                'description' => 'Connexion réseau, Wi-Fi et Internet.',
                'sla_minutes' => 240,
                'sort_order' => 6,
            ],
            [
                'name' => 'Autre',
                'code' => 'OTHER',
                'description' => 'Autres demandes de support.',
                'sla_minutes' => 480,
                'sort_order' => 7,
            ],
        ];

        Environment::query()
            ->where('active', true)
            ->each(function (Environment $environment) use ($categories) {

                foreach ($categories as $category) {

                    TicketCategory::updateOrCreate(
                        [
                            'environment_id' => $environment->id,
                            'code' => $category['code'],
                        ],
                        [
                            'name' => $category['name'],
                            'description' => $category['description'],
                            'sla_minutes' => $category['sla_minutes'],
                            'sort_order' => $category['sort_order'],
                            'active' => true,
                        ]
                    );
                }
            });
    }
}