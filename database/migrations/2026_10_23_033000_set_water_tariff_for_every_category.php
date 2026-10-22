<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE rates MODIFY customer_type VARCHAR(100) NOT NULL");

        $categories = DB::table('customer_types')->pluck('name')->filter()->unique()->values()->all();

        if ($categories === []) {
            $categories = ['Staff', 'Non Staff'];
        }

        DB::table('rates')->whereIn('customer_type', ['residential', 'commercial', 'industrial'])->delete();

        $slabs = [
            ['Fixed charge', 0, 0, 0, 200],
            ['0-5 units', 0, 5, 0, 0],
            ['6-10 units', 6, 10, 12, 0],
            ['11-15 units', 11, 15, 18, 0],
            ['16-20 units', 16, 20, 25, 0],
            ['21-25 units', 21, 25, 35, 0],
            ['26-100000 units', 26, 100000, 40, 0],
        ];

        DB::table('rates')->whereIn('customer_type', $categories)->delete();

        $now = now();
        $rows = [];

        foreach ($categories as $category) {
            foreach ($slabs as [$label, $from, $to, $rate, $fixed]) {
                $rows[] = [
                    'name' => $category . ' ' . $label,
                    'customer_type' => $category,
                    'tier_from' => $from,
                    'tier_to' => $to,
                    'rate_per_unit' => $rate,
                    'fixed_charge' => $fixed,
                    'is_active' => true,
                    'effective_from' => '2025-01-01',
                    'effective_to' => null,
                    'description' => 'Applies to every division. Fixed charge is LKR 200 per billing period.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('rates')->insert($rows);
    }

    public function down(): void
    {
        DB::table('rates')->whereIn('customer_type', ['Staff', 'Non Staff'])->delete();
        DB::statement("ALTER TABLE rates MODIFY customer_type ENUM('residential','commercial','industrial') NOT NULL");
    }
};
