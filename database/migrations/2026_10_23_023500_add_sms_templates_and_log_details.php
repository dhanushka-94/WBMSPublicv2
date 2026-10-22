<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('sms_templates')->insert([
            ['key' => 'new_bill', 'name' => 'Bill Created', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => 'Dear {customer_name}, bill {bill_number} for {billing_period} is Rs.{bill_amount}. Due {due_date}. View: {bill_link}'],
            ['key' => 'payment_confirmation', 'name' => 'Payment Confirmation', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => 'Dear {customer_name}, we received Rs.{paid_amount} on {payment_date}. Receipt {receipt_number}. View: {receipt_link}'],
            ['key' => 'overdue_alert', 'name' => 'Overdue Reminder', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => 'Dear {customer_name}, bill {bill_number} is overdue. Outstanding Rs.{outstanding_amount}. Pay: {payment_link}'],
            ['key' => 'service_disconnection', 'name' => 'Disconnection Notice', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => 'Dear {customer_name}, account {account_number} is scheduled for disconnection on {disconnection_date}. Outstanding Rs.{outstanding_amount}. Pay: {payment_link}'],
            ['key' => 'custom', 'name' => 'Custom SMS', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => 'Dear {customer_name}, account {account_number}: '],
            ['key' => 'due_reminder', 'name' => 'Due Reminder', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => 'Dear {customer_name}, bill {bill_number} of Rs.{outstanding_amount} is due on {due_date}. Pay: {payment_link}'],
            ['key' => 'late_fee_notice', 'name' => 'Late Fee Notice', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => 'Late fee added to bill {bill_number}. Outstanding Rs.{outstanding_amount}. Pay: {payment_link}'],
            ['key' => 'reconnection', 'name' => 'Reconnection Notice', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => 'Dear {customer_name}, account {account_number} has been reconnected.'],
        ]);

        Schema::table('sms_notifications', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('bill_id')->constrained()->nullOnDelete();
            $table->string('receipt_number')->nullable()->after('payment_id');
            $table->unsignedSmallInteger('segments')->default(1)->after('message');
            $table->string('source', 20)->default('automated')->after('status');
        });

        DB::statement("ALTER TABLE sms_notifications MODIFY type ENUM('new_bill', 'due_reminder', 'overdue_alert', 'payment_confirmation', 'late_fee_notice', 'service_disconnection', 'meter_reading_reminder', 'maintenance_notice', 'custom', 'reconnection') NOT NULL DEFAULT 'custom'");
    }

    public function down(): void
    {
        Schema::table('sms_notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
            $table->dropColumn(['receipt_number', 'segments', 'source']);
        });

        DB::statement("ALTER TABLE sms_notifications MODIFY type ENUM('new_bill', 'due_reminder', 'overdue_alert', 'payment_confirmation', 'late_fee_notice', 'service_disconnection', 'meter_reading_reminder', 'maintenance_notice', 'custom') NOT NULL DEFAULT 'custom'");

        Schema::dropIfExists('sms_templates');
    }
};
