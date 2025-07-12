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
        Schema::create('water_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('meter_number')->unique();
            $table->string('meter_brand')->nullable();
            $table->string('meter_model')->nullable();
            $table->integer('meter_size')->nullable(); // in mm or inches
            $table->enum('meter_type', ['mechanical', 'digital', 'smart'])->default('mechanical');
            $table->date('installation_date');
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->decimal('initial_reading', 10, 2)->default(0);
            $table->decimal('current_reading', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'faulty', 'replaced'])->default('active');
            $table->decimal('multiplier', 8, 4)->default(1); // For meter reading calculations
            $table->text('location_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_meters');
    }
};
