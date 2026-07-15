<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'residential',
                'custom_id' => 'RE',
                'description' => 'Household / residential water connections',
                'is_active' => true,
            ],
            [
                'name' => 'commercial',
                'custom_id' => 'CO',
                'description' => 'Shops, offices, and small businesses',
                'is_active' => true,
            ],
            [
                'name' => 'industrial',
                'custom_id' => 'IN',
                'description' => 'Factory and industrial connections',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            CustomerType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        $this->command?->info('Customer types seeded.');
    }
}
