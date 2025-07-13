<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update customer_types table - change custom_id from 3 to 10 characters
        Schema::table('customer_types', function (Blueprint $table) {
            $table->string('custom_id', 10)->change();
        });
        
        // Update divisions table - change custom_id from 4 to 10 characters
        Schema::table('divisions', function (Blueprint $table) {
            $table->string('custom_id', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert customer_types table - change custom_id back to 3 characters
        Schema::table('customer_types', function (Blueprint $table) {
            $table->string('custom_id', 3)->change();
        });
        
        // Revert divisions table - change custom_id back to 4 characters
        Schema::table('divisions', function (Blueprint $table) {
            $table->string('custom_id', 4)->nullable()->change();
        });
    }
};
