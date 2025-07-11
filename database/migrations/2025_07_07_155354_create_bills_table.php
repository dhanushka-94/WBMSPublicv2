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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('water_meter_id')->constrained()->onDelete('cascade');
            $table->foreignId('meter_reading_id')->constrained()->onDelete('cascade');
            $table->string('bill_number')->unique();
            $table->date('bill_date');
            $table->date('due_date');
            $table->date('billing_period_from');
            $table->date('billing_period_to');
            $table->decimal('previous_reading', 10, 2);
            $table->decimal('current_reading', 10, 2);
            $table->decimal('consumption', 10, 2);
            $table->decimal('water_charges', 10, 2)->default(0);
            $table->decimal('fixed_charges', 10, 2)->default(0);
            $table->decimal('service_charges', 10, 2)->default(0);
            $table->decimal('late_fees', 10, 2)->default(0);
            $table->decimal('taxes', 10, 2)->default(0);
            $table->decimal('adjustments', 10, 2)->default(0); // Can be positive or negative
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance_amount', 10, 2);
            $table->enum('status', ['draft', 'generated', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->json('rate_breakdown')->nullable(); // Store detailed rate calculations
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'bill_date']);
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
