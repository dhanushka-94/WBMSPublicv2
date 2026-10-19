<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE customers MODIFY status ENUM('active', 'inactive', 'suspended', 'disconnected') NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'staff', 'manager', 'meter_reader', 'billing_officer', 'cashier') NOT NULL DEFAULT 'staff'");
        DB::statement("ALTER TABLE sms_notifications MODIFY type ENUM('new_bill', 'due_reminder', 'overdue_alert', 'payment_confirmation', 'late_fee_notice', 'service_disconnection', 'meter_reading_reminder', 'maintenance_notice', 'custom', 'reconnection') NOT NULL DEFAULT 'custom'");

        Schema::create('connection_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('action', ['disconnected', 'reconnected']);
            $table->date('event_date');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->date('paid_on');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('connection_events');
        DB::statement("ALTER TABLE sms_notifications MODIFY type ENUM('new_bill', 'due_reminder', 'overdue_alert', 'payment_confirmation', 'late_fee_notice', 'service_disconnection', 'meter_reading_reminder', 'maintenance_notice', 'custom') NOT NULL DEFAULT 'custom'");
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'staff', 'manager', 'meter_reader') NOT NULL DEFAULT 'staff'");
        DB::statement("ALTER TABLE customers MODIFY status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active'");
    }
};
