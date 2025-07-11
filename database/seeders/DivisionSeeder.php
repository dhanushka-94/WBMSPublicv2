<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'MAHATMA GANDHIPURAM',
                'custom_id' => 'MGP',
                'description' => 'Mahatma Gandhipuram Division',
                'is_active' => true
            ],
            [
                'name' => 'GANDHIPURAM',
                'custom_id' => 'GDP',
                'description' => 'Gandhipuram Division',
                'is_active' => true
            ],
            [
                'name' => 'MIDDLE DIVISION',
                'custom_id' => 'MID',
                'description' => 'Middle Division Area',
                'is_active' => true
            ],
            [
                'name' => 'FACTORY DIVISION',
                'custom_id' => 'FAC',
                'description' => 'Factory Division Area',
                'is_active' => true
            ],
            [
                'name' => 'LOWER DIVISION',
                'custom_id' => 'LOW',
                'description' => 'Lower Division Area',
                'is_active' => true
            ],
            [
                'name' => 'UPPER',
                'custom_id' => 'UPP',
                'description' => 'Upper Division Area',
                'is_active' => true
            ],
            [
                'name' => 'UPPER HOUSING',
                'custom_id' => 'UPH',
                'description' => 'Upper Housing Division',
                'is_active' => true
            ],
            [
                'name' => 'UPPER PUNDULOYA SHEEN',
                'custom_id' => 'UPS',
                'description' => 'Upper Punduloya Sheen Division',
                'is_active' => true
            ],
            [
                'name' => 'NORTH',
                'custom_id' => 'NTH',
                'description' => 'North Division Area',
                'is_active' => true
            ]
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }

        echo "Divisions created successfully!\n";
    }
}
