<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\SupportTeam;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTeamSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()
            ->where('username', 'admin')
            ->first();

        Environment::query()
            ->where('active', true)
            ->each(function (Environment $environment) use ($admin) {

                $team = SupportTeam::updateOrCreate(
                    [
                        'environment_id' => $environment->id,
                        'code' => 'IT-SUPPORT',
                    ],
                    [
                        'name' => 'Support IT',
                        'description' => 'Équipe de support informatique.',
                        'leader_id' => $admin?->id,
                        'active' => true,
                    ]
                );

                if ($admin) {
                    $team->users()->syncWithoutDetaching([
                        $admin->id,
                    ]);
                }
            });
    }
}