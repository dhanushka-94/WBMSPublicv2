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
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Residential Basic", "Commercial Standard"
            $table->enum('customer_type', ['residential', 'commercial', 'industrial']);
            $table->integer('tier_from'); // Starting units for this tier (e.g., 0, 21, 51)
            $table->integer('tier_to')->nullable(); // Ending units for this tier (null for unlimited)
            $table->decimal('rate_per_unit', 8, 4); // Rate per cubic meter/gallon
            $table->decimal('fixed_charge', 8, 2)->default(0); // Monthly fixed charge
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['customer_type', 'is_active', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
