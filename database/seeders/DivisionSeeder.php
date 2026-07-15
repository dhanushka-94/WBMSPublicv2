<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'Upper Division',
                'custom_id' => 'UD',
                'description' => 'Upper estate bungalows and staff lines',
                'is_active' => true,
            ],
            [
                'name' => 'Lower Division',
                'custom_id' => 'LD',
                'description' => 'Lower estate residential blocks',
                'is_active' => true,
            ],
            [
                'name' => 'Factory Area',
                'custom_id' => 'FA',
                'description' => 'Tea factory and workshop zone',
                'is_active' => true,
            ],
            [
                'name' => 'Bazaar Section',
                'custom_id' => 'BZ',
                'description' => 'Estate shops and commercial stalls',
                'is_active' => true,
            ],
            [
                'name' => 'Field Lines',
                'custom_id' => 'FL',
                'description' => 'Field worker line housing',
                'is_active' => true,
            ],
        ];

        foreach ($divisions as $division) {
            Division::firstOrCreate(
                ['custom_id' => $division['custom_id']],
                $division
            );
        }

        $this->command?->info('Divisions seeded.');
    }
}
