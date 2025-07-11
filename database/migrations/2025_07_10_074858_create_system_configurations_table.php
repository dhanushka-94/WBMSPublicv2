<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default billing configuration
        DB::table('system_configurations')->insert([
            [
                'key' => 'default_billing_day',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Default billing day of the month (1-31) for new customers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_billing_enabled_default',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Default auto-billing status for new customers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'billing_cycle_type',
                'value' => 'monthly',
                'type' => 'string',
                'description' => 'Billing cycle frequency (monthly, quarterly, etc.)',
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
        Schema::dropIfExists('system_configurations');
    }
};
