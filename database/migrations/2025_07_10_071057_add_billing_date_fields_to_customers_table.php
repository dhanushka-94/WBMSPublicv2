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
            $table->integer('billing_day')->nullable()->after('status')->comment('Day of month for billing (1-31)');
            $table->date('next_billing_date')->nullable()->after('billing_day')->comment('Next scheduled billing date');
            $table->boolean('auto_billing_enabled')->default(true)->after('next_billing_date')->comment('Enable automatic billing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['billing_day', 'next_billing_date', 'auto_billing_enabled']);
        });
    }
};
