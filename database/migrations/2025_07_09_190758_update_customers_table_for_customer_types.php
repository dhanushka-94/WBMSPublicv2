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
        Schema::table('customers', function (Blueprint $table) {
            // Add new customer_type_id foreign key
            $table->foreignId('customer_type_id')->nullable()->after('customer_type')->constrained('customer_types')->onDelete('set null');
        });
        
        // Note: We'll keep the old customer_type enum field for now to maintain data integrity
        // It can be removed in a future migration after data migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['customer_type_id']);
            $table->dropColumn('customer_type_id');
        });
    }
};
