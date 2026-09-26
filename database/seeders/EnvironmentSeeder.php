<?php

namespace Database\Seeders;

use App\Models\Environment;
use Illuminate\Database\Seeder;

class EnvironmentSeeder extends Seeder
{
    public function run(): void
    {
        $environments = [
            [
                'name' => 'Al Jabr Oasis',
                'code' => 'AJ-OASIS',
                'app_name' => 'School Up',
                'url' => null,
                'current_exercise' => '2026-2027',
                'active' => true,
            ],
            [
                'name' => 'AIS Bouskoura',
                'code' => 'AIS-BSK',
                'app_name' => 'School Up',
                'url' => null,
                'current_exercise' => '2026-2027',
                'active' => true,
            ],
            [
                'name' => 'Al Massalik',
                'code' => 'MASSALIK',
                'app_name' => 'School Up',
                'url' => null,
                'current_exercise' => '2026-2027',
                'active' => true,
            ],
        ];

        foreach ($environments as $environment) {
            Environment::updateOrCreate(
                ['code' => $environment['code']],
                $environment
            );
        }
    }
}