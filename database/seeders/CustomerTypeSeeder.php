<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CustomerType;

class CustomerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerTypes = [
            [
                'name' => 'Residential',
                'custom_id' => 'RES',
                'description' => 'Individual households and residential properties',
                'is_active' => true
            ],
            [
                'name' => 'Commercial',
                'custom_id' => 'COM',
                'description' => 'Businesses, shops, and commercial establishments',
                'is_active' => true
            ],
            [
                'name' => 'Industrial',
                'custom_id' => 'IND',
                'description' => 'Manufacturing plants and industrial facilities',
                'is_active' => true
            ],
            [
                'name' => 'Institutional',
                'custom_id' => 'INS',
                'description' => 'Schools, hospitals, and government institutions',
                'is_active' => true
            ],
            [
                'name' => 'Agricultural',
                'custom_id' => 'AGR',
                'description' => 'Farms and agricultural operations',
                'is_active' => true
            ]
        ];

        foreach ($customerTypes as $type) {
            CustomerType::create($type);
        }

        echo "Customer types created successfully!\n";
    }
}
