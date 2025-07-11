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
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_meter_id')->constrained()->onDelete('cascade');
            $table->date('reading_date');
            $table->decimal('current_reading', 10, 2);
            $table->decimal('previous_reading', 10, 2)->nullable();
            $table->decimal('consumption', 10, 2)->nullable(); // Calculated consumption
            $table->enum('reading_type', ['actual', 'estimated', 'customer_read'])->default('actual');
            $table->string('reader_name')->nullable(); // Staff member who took the reading
            $table->text('notes')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->enum('status', ['pending', 'verified', 'billed'])->default('pending');
            $table->timestamps();
            
            $table->unique(['water_meter_id', 'reading_date']);
            $table->index(['reading_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
