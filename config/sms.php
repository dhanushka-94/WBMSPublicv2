<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SMS notifications using Notify.lk service
    |
    */

    'default' => env('SMS_DRIVER', 'notify_lk'),

    /*
    | Price (LKR) charged by the SMS provider for one segment.
    | SMS_COST_PER_SEGMENT in .env is only the starting rate. The Super Admin
    | changes the rate on the SMS Settings page, and every change is kept in the
    | sms_rate_changes table (see App\Models\SmsRateChange). Each SMS stores the
    | rate used when it was created, so old messages keep their original cost.
    */
    'cost_per_segment' => (float) env('SMS_COST_PER_SEGMENT', 0.90),

    'drivers' => [
        'notify_lk' => [
            'user_id' => env('NOTIFYLK_USER_ID', ''),
            'api_key' => env('NOTIFYLK_API_KEY', ''),
            'sender_id' => env('NOTIFYLK_SENDER_ID', 'NotifyDEMO'),
            'base_url' => env('NOTIFYLK_BASE_URL', 'https://app.notify.lk/api/v1'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Notifications Settings
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'enabled' => env('SMS_NOTIFICATIONS_ENABLED', true),
        'queue' => env('SMS_QUEUE_ENABLED', false),
        'retry_attempts' => env('SMS_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('SMS_RETRY_DELAY', 300), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Bill Notification Settings
    |--------------------------------------------------------------------------
    */

    'bill_notifications' => [
        'new_bill' => env('SMS_NEW_BILL_ENABLED', true),
        'due_reminder' => env('SMS_DUE_REMINDER_ENABLED', true),
        'overdue_alert' => env('SMS_OVERDUE_ALERT_ENABLED', true),
        'payment_confirmation' => env('SMS_PAYMENT_CONFIRMATION_ENABLED', true),
        'late_fee_notice' => env('SMS_LATE_FEE_NOTICE_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Templates (English only, with line breaks for readability)
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'new_bill' => "Dunsinane Water Project\n\nDear {customer_name},\n\nBill: {bill_number}\nPeriod: {billing_period}\nAmount: Rs.{bill_amount}\nDue: {due_date}\nAccount: {account_number}\nOutstanding: Rs.{outstanding_amount}\n\nView bill:\n{bill_link}",

        'due_reminder' => "Dunsinane Water Project\n\nDear {customer_name},\n\nReminder: bill {bill_number} is due on {due_date}.\nAmount due: Rs.{outstanding_amount}\nAccount: {account_number}\n\nPay here:\n{payment_link}",

        'overdue_alert' => "Dunsinane Water Project\n\nURGENT\n\nDear {customer_name},\n\nBill {bill_number} is overdue by {days_overdue} days.\nOutstanding: Rs.{outstanding_amount}\n\nPay now:\n{payment_link}",

        'payment_confirmation' => "Dunsinane Water Project\n\nDear {customer_name},\n\nPayment received: Rs.{paid_amount}\nDate: {payment_date}\nReceipt: {receipt_number}\nBill: {bill_number}\nBalance: Rs.{balance}\n\nView receipt:\n{receipt_link}",

        'late_fee_notice' => "Dunsinane Water Project\n\nDear {customer_name},\n\nLate fee Rs.{late_fee} was added to bill {bill_number}.\nOutstanding now: Rs.{outstanding_amount}\n\nPay here:\n{payment_link}",

        'service_disconnection' => "Dunsinane Water Project\n\nDear {customer_name},\n\nAccount {account_number} may be disconnected on {disconnection_date}.\nOutstanding: Rs.{outstanding_amount}\n\nPay here:\n{payment_link}",

        'reconnection' => "Dunsinane Water Project\n\nDear {customer_name},\n\nAccount {account_number} has been reconnected.\nThank you.",

        'meter_reading_reminder' => "Dunsinane Water Project\n\nDear {customer_name},\n\nMeter reading for account {account_number}\nis scheduled on {date} ({day})\nbetween {time_range}.\n\nPlease keep the meter accessible.",

        'maintenance_notice' => "Dunsinane Water Project\n\nWater supply interruption\n\nArea: {area}\nDate: {date} ({day})\nTime: {start_time} to {end_time}\nReason: {reason}\n\nPlease store water in advance.",

        'custom' => "Dunsinane Water Project\n\nDear {customer_name},\nAccount: {account_number}\n\n",
    ],
];
