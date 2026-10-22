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
        'new_bill' => 'Your water bill has been generated. Bill {bill_number} for {customer_name} is Rs.{amount}. Due date: {due_date}.',
        
        'due_reminder' => 'Your account has an outstanding balance of Rs.{amount} for bill {bill_number}. Due date: {due_date}.',
        
        'overdue_alert' => 'URGENT: Your water bill #{bill_number} of Rs.{amount} is overdue. Late fees may apply. Pay now: {payment_link}',
        
        'payment_confirmation' => 'Thank you for your payment of Rs.{amount} for bill {bill_number}. Remaining balance: Rs.{balance}.',
        
        'late_fee_notice' => 'Late fee of Rs.{late_fee} added to your account for overdue bill #{bill_number}. Total due: Rs.{total_due}',
        
        'service_disconnection' => 'Your water connection is scheduled for disconnection due to unpaid dues of Rs.{total_due}.',
        'reconnection' => 'Your water connection has been restored. Account {account_number}.',
        
        'meter_reading_reminder' => 'Dear {customer_name}, our meter reader will visit your premises on {date} between {time_range} for monthly reading.',
        
        'maintenance_notice' => 'Water supply interruption scheduled on {date} from {start_time} to {end_time} in your area for maintenance work.',
    ],
]; 