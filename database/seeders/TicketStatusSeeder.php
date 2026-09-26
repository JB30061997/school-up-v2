<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Ouvert',
                'code' => 'OPEN',
                'is_closed' => false,
                'is_default' => true,
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'name' => 'En cours',
                'code' => 'IN_PROGRESS',
                'is_closed' => false,
                'is_default' => false,
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'name' => 'En attente',
                'code' => 'PENDING',
                'is_closed' => false,
                'is_default' => false,
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'name' => 'Résolu',
                'code' => 'RESOLVED',
                'is_closed' => false,
                'is_default' => false,
                'sort_order' => 4,
                'active' => true,
            ],
            [
                'name' => 'Clôturé',
                'code' => 'CLOSED',
                'is_closed' => true,
                'is_default' => false,
                'sort_order' => 5,
                'active' => true,
            ],
            [
                'name' => 'Annulé',
                'code' => 'CANCELLED',
                'is_closed' => true,
                'is_default' => false,
                'sort_order' => 6,
                'active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            TicketStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}