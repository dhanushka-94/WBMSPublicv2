<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add system control configurations
        DB::table('system_configurations')->insert([
            [
                'key' => 'system_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable/disable system features for license control',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'system_disabled_at',
                'value' => null,
                'type' => 'datetime',
                'description' => 'Timestamp when system was disabled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'disable_reason',
                'value' => null,
                'type' => 'string',
                'description' => 'Reason for disabling the system',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_configurations')
            ->whereIn('key', ['system_enabled', 'system_disabled_at', 'disable_reason'])
            ->delete();
    }
};