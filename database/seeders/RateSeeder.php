<?php

namespace Database\Seeders;

use App\Models\Rate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RateSeeder extends Seeder
{
    public function run(): void
    {
        if (Rate::count() > 0) {
            $this->command?->info('Rates already exist — skipping RateSeeder.');
            return;
        }

        $effectiveFrom = Carbon::create(2025, 1, 1);

        $tiers = [
            'residential' => [
                ['name' => 'Residential Fixed', 'tier_from' => 0, 'tier_to' => 0, 'rate_per_unit' => 0, 'fixed_charge' => 250],
                ['name' => 'Residential 1-10', 'tier_from' => 1, 'tier_to' => 10, 'rate_per_unit' => 15, 'fixed_charge' => 0],
                ['name' => 'Residential 11-20', 'tier_from' => 11, 'tier_to' => 20, 'rate_per_unit' => 25, 'fixed_charge' => 0],
                ['name' => 'Residential 21-30', 'tier_from' => 21, 'tier_to' => 30, 'rate_per_unit' => 40, 'fixed_charge' => 0],
                ['name' => 'Residential 31+', 'tier_from' => 31, 'tier_to' => null, 'rate_per_unit' => 60, 'fixed_charge' => 0],
            ],
            'commercial' => [
                ['name' => 'Commercial Fixed', 'tier_from' => 0, 'tier_to' => 0, 'rate_per_unit' => 0, 'fixed_charge' => 750],
                ['name' => 'Commercial 1-20', 'tier_from' => 1, 'tier_to' => 20, 'rate_per_unit' => 35, 'fixed_charge' => 0],
                ['name' => 'Commercial 21-50', 'tier_from' => 21, 'tier_to' => 50, 'rate_per_unit' => 55, 'fixed_charge' => 0],
                ['name' => 'Commercial 51+', 'tier_from' => 51, 'tier_to' => null, 'rate_per_unit' => 80, 'fixed_charge' => 0],
            ],
            'industrial' => [
                ['name' => 'Industrial Fixed', 'tier_from' => 0, 'tier_to' => 0, 'rate_per_unit' => 0, 'fixed_charge' => 2000],
                ['name' => 'Industrial 1-50', 'tier_from' => 1, 'tier_to' => 50, 'rate_per_unit' => 45, 'fixed_charge' => 0],
                ['name' => 'Industrial 51-100', 'tier_from' => 51, 'tier_to' => 100, 'rate_per_unit' => 70, 'fixed_charge' => 0],
                ['name' => 'Industrial 101+', 'tier_from' => 101, 'tier_to' => null, 'rate_per_unit' => 95, 'fixed_charge' => 0],
            ],
        ];

        foreach ($tiers as $customerType => $rateTiers) {
            foreach ($rateTiers as $tier) {
                Rate::create([
                    'name' => $tier['name'],
                    'customer_type' => $customerType,
                    'tier_from' => $tier['tier_from'],
                    'tier_to' => $tier['tier_to'],
                    'rate_per_unit' => $tier['rate_per_unit'],
                    'fixed_charge' => $tier['fixed_charge'],
                    'is_active' => true,
                    'effective_from' => $effectiveFrom,
                    'effective_to' => null,
                    'description' => "Sample {$customerType} tariff tier for Dunsinane Estate",
                ]);
            }
        }

        $this->command?->info('Rate structures seeded.');
    }
}
