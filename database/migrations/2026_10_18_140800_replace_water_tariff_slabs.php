<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rates')->delete();

        $now = now();
        $effectiveFrom = '2025-01-01';
        $slabs = [
            ['label' => '0-0', 'tier_from' => 0, 'tier_to' => 0, 'rate_per_unit' => 0, 'fixed_charge' => 200],
            ['label' => '0-5', 'tier_from' => 0, 'tier_to' => 5, 'rate_per_unit' => 0, 'fixed_charge' => 0],
            ['label' => '6-10', 'tier_from' => 6, 'tier_to' => 10, 'rate_per_unit' => 12, 'fixed_charge' => 0],
            ['label' => '11-15', 'tier_from' => 11, 'tier_to' => 15, 'rate_per_unit' => 18, 'fixed_charge' => 0],
            ['label' => '16-20', 'tier_from' => 16, 'tier_to' => 20, 'rate_per_unit' => 25, 'fixed_charge' => 0],
            ['label' => '21-25', 'tier_from' => 21, 'tier_to' => 25, 'rate_per_unit' => 35, 'fixed_charge' => 0],
            ['label' => '26-100000', 'tier_from' => 26, 'tier_to' => 100000, 'rate_per_unit' => 40, 'fixed_charge' => 0],
        ];

        $rows = [];
        foreach (['residential', 'commercial', 'industrial'] as $customerType) {
            $label = ucfirst($customerType);
            foreach ($slabs as $slab) {
                $rows[] = [
                    'name' => "{$label} {$slab['label']}",
                    'customer_type' => $customerType,
                    'tier_from' => $slab['tier_from'],
                    'tier_to' => $slab['tier_to'],
                    'rate_per_unit' => $slab['rate_per_unit'],
                    'fixed_charge' => $slab['fixed_charge'],
                    'is_active' => true,
                    'effective_from' => $effectiveFrom,
                    'effective_to' => null,
                    'description' => "{$label} tariff, units {$slab['label']}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('rates')->insert($rows);
    }

    public function down(): void
    {
        DB::table('rates')->delete();
    }
};
