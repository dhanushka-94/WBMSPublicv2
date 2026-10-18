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

        $slabs = [
            ['label' => '0-0', 'tier_from' => 0, 'tier_to' => 0, 'rate_per_unit' => 0, 'fixed_charge' => 200],
            ['label' => '0-5', 'tier_from' => 0, 'tier_to' => 5, 'rate_per_unit' => 0, 'fixed_charge' => 0],
            ['label' => '6-10', 'tier_from' => 6, 'tier_to' => 10, 'rate_per_unit' => 12, 'fixed_charge' => 0],
            ['label' => '11-15', 'tier_from' => 11, 'tier_to' => 15, 'rate_per_unit' => 18, 'fixed_charge' => 0],
            ['label' => '16-20', 'tier_from' => 16, 'tier_to' => 20, 'rate_per_unit' => 25, 'fixed_charge' => 0],
            ['label' => '21-25', 'tier_from' => 21, 'tier_to' => 25, 'rate_per_unit' => 35, 'fixed_charge' => 0],
            ['label' => '26-100000', 'tier_from' => 26, 'tier_to' => 100000, 'rate_per_unit' => 40, 'fixed_charge' => 0],
        ];

        $tiers = [];
        foreach (['residential', 'commercial', 'industrial'] as $customerType) {
            $label = ucfirst($customerType);
            $tiers[$customerType] = array_map(function ($slab) use ($label) {
                return [
                    'name' => "{$label} {$slab['label']}",
                    'tier_from' => $slab['tier_from'],
                    'tier_to' => $slab['tier_to'],
                    'rate_per_unit' => $slab['rate_per_unit'],
                    'fixed_charge' => $slab['fixed_charge'],
                ];
            }, $slabs);
        }

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
