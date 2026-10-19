<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Settings are saved from the office. A fresh database starts with an empty configuration table.
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