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
        // Add name field to divisions table
        Schema::table('divisions', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->boolean('is_active')->default(true)->after('name');
        });

        // Add division_id and staff_type to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('customer_type')->constrained('divisions')->onDelete('set null');
            $table->enum('staff_type', ['STAFF', 'NON STAFF', 'MANAGEMENT'])->nullable()->after('division_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn(['division_id', 'staff_type']);
        });

        Schema::table('divisions', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_active']);
        });
    }
};
