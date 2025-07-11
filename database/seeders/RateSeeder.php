<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rate;
use Carbon\Carbon;

class RateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing rates
        Rate::truncate();

        $effectiveDate = Carbon::now()->startOfMonth();

        // Sri Lankan Water Billing Rates (All customer types use same structure)
        $waterRates = [
            [
                'name' => 'Fixed Charge (0 units)',
                'customer_type' => 'residential',
                'tier_from' => 0,
                'tier_to' => 0,
                'rate_per_unit' => 0,
                'fixed_charge' => 150.00,
                'description' => 'Fixed monthly charge for 0 units consumption'
            ],
            [
                'name' => 'Free Allowance (0-5 units)',
                'customer_type' => 'residential',
                'tier_from' => 0,
                'tier_to' => 5,
                'rate_per_unit' => 0,
                'fixed_charge' => 0,
                'description' => 'Free allowance for first 5 units'
            ],
            [
                'name' => 'Tier 1 (6-10 units)',
                'customer_type' => 'residential',
                'tier_from' => 6,
                'tier_to' => 10,
                'rate_per_unit' => 12.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 12 per unit for 6-10 units'
            ],
            [
                'name' => 'Tier 2 (11-15 units)',
                'customer_type' => 'residential',
                'tier_from' => 11,
                'tier_to' => 15,
                'rate_per_unit' => 18.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 18 per unit for 11-15 units'
            ],
            [
                'name' => 'Tier 3 (16-20 units)',
                'customer_type' => 'residential',
                'tier_from' => 16,
                'tier_to' => 20,
                'rate_per_unit' => 25.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 25 per unit for 16-20 units'
            ],
            [
                'name' => 'Tier 4 (21-25 units)',
                'customer_type' => 'residential',
                'tier_from' => 21,
                'tier_to' => 25,
                'rate_per_unit' => 35.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 35 per unit for 21-25 units'
            ],
            [
                'name' => 'Tier 5 (26+ units)',
                'customer_type' => 'residential',
                'tier_from' => 26,
                'tier_to' => null,
                'rate_per_unit' => 40.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 40 per unit for usage above 25 units'
            ]
        ];

        // Commercial rates (same structure)
        $commercialRates = [
            [
                'name' => 'Fixed Charge (0 units)',
                'customer_type' => 'commercial',
                'tier_from' => 0,
                'tier_to' => 0,
                'rate_per_unit' => 0,
                'fixed_charge' => 150.00,
                'description' => 'Fixed monthly charge for 0 units consumption'
            ],
            [
                'name' => 'Free Allowance (0-5 units)',
                'customer_type' => 'commercial',
                'tier_from' => 0,
                'tier_to' => 5,
                'rate_per_unit' => 0,
                'fixed_charge' => 0,
                'description' => 'Free allowance for first 5 units'
            ],
            [
                'name' => 'Tier 1 (6-10 units)',
                'customer_type' => 'commercial',
                'tier_from' => 6,
                'tier_to' => 10,
                'rate_per_unit' => 12.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 12 per unit for 6-10 units'
            ],
            [
                'name' => 'Tier 2 (11-15 units)',
                'customer_type' => 'commercial',
                'tier_from' => 11,
                'tier_to' => 15,
                'rate_per_unit' => 18.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 18 per unit for 11-15 units'
            ],
            [
                'name' => 'Tier 3 (16-20 units)',
                'customer_type' => 'commercial',
                'tier_from' => 16,
                'tier_to' => 20,
                'rate_per_unit' => 25.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 25 per unit for 16-20 units'
            ],
            [
                'name' => 'Tier 4 (21-25 units)',
                'customer_type' => 'commercial',
                'tier_from' => 21,
                'tier_to' => 25,
                'rate_per_unit' => 35.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 35 per unit for 21-25 units'
            ],
            [
                'name' => 'Tier 5 (26+ units)',
                'customer_type' => 'commercial',
                'tier_from' => 26,
                'tier_to' => null,
                'rate_per_unit' => 40.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 40 per unit for usage above 25 units'
            ]
        ];

        // Industrial rates (same structure)
        $industrialRates = [
            [
                'name' => 'Fixed Charge (0 units)',
                'customer_type' => 'industrial',
                'tier_from' => 0,
                'tier_to' => 0,
                'rate_per_unit' => 0,
                'fixed_charge' => 150.00,
                'description' => 'Fixed monthly charge for 0 units consumption'
            ],
            [
                'name' => 'Free Allowance (0-5 units)',
                'customer_type' => 'industrial',
                'tier_from' => 0,
                'tier_to' => 5,
                'rate_per_unit' => 0,
                'fixed_charge' => 0,
                'description' => 'Free allowance for first 5 units'
            ],
            [
                'name' => 'Tier 1 (6-10 units)',
                'customer_type' => 'industrial',
                'tier_from' => 6,
                'tier_to' => 10,
                'rate_per_unit' => 12.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 12 per unit for 6-10 units'
            ],
            [
                'name' => 'Tier 2 (11-15 units)',
                'customer_type' => 'industrial',
                'tier_from' => 11,
                'tier_to' => 15,
                'rate_per_unit' => 18.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 18 per unit for 11-15 units'
            ],
            [
                'name' => 'Tier 3 (16-20 units)',
                'customer_type' => 'industrial',
                'tier_from' => 16,
                'tier_to' => 20,
                'rate_per_unit' => 25.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 25 per unit for 16-20 units'
            ],
            [
                'name' => 'Tier 4 (21-25 units)',
                'customer_type' => 'industrial',
                'tier_from' => 21,
                'tier_to' => 25,
                'rate_per_unit' => 35.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 35 per unit for 21-25 units'
            ],
            [
                'name' => 'Tier 5 (26+ units)',
                'customer_type' => 'industrial',
                'tier_from' => 26,
                'tier_to' => null,
                'rate_per_unit' => 40.00,
                'fixed_charge' => 0,
                'description' => 'Rs. 40 per unit for usage above 25 units'
            ]
        ];

        // Combine all rates
        $allRates = array_merge($waterRates, $commercialRates, $industrialRates);

        // Create rates
        foreach ($allRates as $rateData) {
            Rate::create([
                'name' => $rateData['name'],
                'customer_type' => $rateData['customer_type'],
                'tier_from' => $rateData['tier_from'],
                'tier_to' => $rateData['tier_to'],
                'rate_per_unit' => $rateData['rate_per_unit'],
                'fixed_charge' => $rateData['fixed_charge'],
                'is_active' => true,
                'effective_from' => $effectiveDate,
                'effective_to' => null,
                'description' => $rateData['description']
            ]);
        }

        $this->command->info('Sri Lankan Water Billing rates created successfully!');
        $this->command->info('Rate Structure:');
        $this->command->info('- 0 units: Rs. 150 (Fixed Charge)');
        $this->command->info('- 0-5 units: Rs. 0 (Free)');
        $this->command->info('- 6-10 units: Rs. 12 per unit');
        $this->command->info('- 11-15 units: Rs. 18 per unit');
        $this->command->info('- 16-20 units: Rs. 25 per unit');
        $this->command->info('- 21-25 units: Rs. 35 per unit');
        $this->command->info('- 26+ units: Rs. 40 per unit');
        $this->command->info('All rates are active from: ' . $effectiveDate->format('Y-m-d'));
    }
}
