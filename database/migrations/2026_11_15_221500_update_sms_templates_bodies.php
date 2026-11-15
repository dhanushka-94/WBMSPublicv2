<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $templates = [
            'new_bill' => [
                'name' => 'Bill Created',
                'body' => 'Dunsinane Water: Dear {customer_name}, bill {bill_number} ({billing_period}) is Rs.{bill_amount}. Due {due_date}. Account {account_number}. View: {bill_link}',
            ],
            'payment_confirmation' => [
                'name' => 'Payment Confirmation',
                'body' => 'Dunsinane Water: Dear {customer_name}, payment Rs.{paid_amount} received on {payment_date}. Receipt {receipt_number}, bill {bill_number}. Balance Rs.{balance}. View: {receipt_link}',
            ],
            'due_reminder' => [
                'name' => 'Due Reminder',
                'body' => 'Dunsinane Water: Dear {customer_name}, bill {bill_number} of Rs.{outstanding_amount} is due on {due_date}. Account {account_number}. Pay: {payment_link}',
            ],
            'overdue_alert' => [
                'name' => 'Overdue Reminder',
                'body' => 'Dunsinane Water: URGENT - Dear {customer_name}, bill {bill_number} is overdue ({days_overdue} days). Outstanding Rs.{outstanding_amount}. Pay now: {payment_link}',
            ],
            'late_fee_notice' => [
                'name' => 'Late Fee Notice',
                'body' => 'Dunsinane Water: Late fee Rs.{late_fee} added to bill {bill_number}. Outstanding now Rs.{outstanding_amount}. Pay: {payment_link}',
            ],
            'service_disconnection' => [
                'name' => 'Disconnection Notice',
                'body' => 'Dunsinane Water: Dear {customer_name}, account {account_number} may be disconnected on {disconnection_date}. Outstanding Rs.{outstanding_amount}. Pay: {payment_link}',
            ],
            'reconnection' => [
                'name' => 'Reconnection Notice',
                'body' => 'Dunsinane Water: Dear {customer_name}, account {account_number} has been reconnected. Thank you.',
            ],
            'custom' => [
                'name' => 'Custom SMS',
                'body' => 'Dunsinane Water: Dear {customer_name}, account {account_number}: ',
            ],
            'meter_reading_reminder' => [
                'name' => 'Meter Reading Reminder',
                'body' => 'Dunsinane Water: Dear {customer_name}, meter reading for account {account_number} is scheduled on {date} ({day}) between {time_range}. Please keep the meter accessible.',
            ],
            'maintenance_notice' => [
                'name' => 'Maintenance Notice',
                'body' => 'Dunsinane Water: Water supply interruption in {area} on {date} ({day}) from {start_time} to {end_time}. Reason: {reason}.',
            ],
        ];

        foreach ($templates as $key => $row) {
            $existing = DB::table('sms_templates')->where('key', $key)->first();

            if ($existing) {
                DB::table('sms_templates')->where('key', $key)->update([
                    'name' => $row['name'],
                    'body' => $row['body'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('sms_templates')->insert([
                    'key' => $key,
                    'name' => $row['name'],
                    'body' => $row['body'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Intentional no-op: template wording is content, not schema.
    }
};
