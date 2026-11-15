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
    | SMS Templates
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'new_bill' => 'Dunsinane Water: Dear {customer_name}, bill {bill_number} ({billing_period}) is Rs.{bill_amount}. Due {due_date}. Account {account_number}. View: {bill_link}',

        'due_reminder' => 'Dunsinane Water: Dear {customer_name}, bill {bill_number} of Rs.{outstanding_amount} is due on {due_date}. Account {account_number}. Pay: {payment_link}',

        'overdue_alert' => 'Dunsinane Water: URGENT - Dear {customer_name}, bill {bill_number} is overdue ({days_overdue} days). Outstanding Rs.{outstanding_amount}. Pay now: {payment_link}',

        'payment_confirmation' => 'Dunsinane Water: Dear {customer_name}, payment Rs.{paid_amount} received on {payment_date}. Receipt {receipt_number}, bill {bill_number}. Balance Rs.{balance}. View: {receipt_link}',

        'late_fee_notice' => 'Dunsinane Water: Late fee Rs.{late_fee} added to bill {bill_number}. Outstanding now Rs.{outstanding_amount}. Pay: {payment_link}',

        'service_disconnection' => 'Dunsinane Water: Dear {customer_name}, account {account_number} may be disconnected on {disconnection_date}. Outstanding Rs.{outstanding_amount}. Pay: {payment_link}',

        'reconnection' => 'Dunsinane Water: Dear {customer_name}, account {account_number} has been reconnected. Thank you.',

        'meter_reading_reminder' => 'Dunsinane Water: Dear {customer_name}, meter reading for account {account_number} is scheduled on {date} ({day}) between {time_range}. Please keep the meter accessible.',

        'maintenance_notice' => 'Dunsinane Water: Water supply interruption in {area} on {date} ({day}) from {start_time} to {end_time}. Reason: {reason}.',

        'custom' => 'Dunsinane Water: Dear {customer_name}, account {account_number}: ',
    ],
]; 