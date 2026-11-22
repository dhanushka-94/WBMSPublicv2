<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('system_configurations')->updateOrInsert(
            ['key' => 'bill_due_days'],
            [
                'value' => '14',
                'type' => 'integer',
                'description' => 'Payment window in days including the bill issue date',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        Cache::forget('system_config_bill_due_days');
    }

    public function down(): void
    {
        DB::table('system_configurations')
            ->where('key', 'bill_due_days')
            ->update([
                'value' => '30',
                'updated_at' => now(),
            ]);

        Cache::forget('system_config_bill_due_days');
    }
};
