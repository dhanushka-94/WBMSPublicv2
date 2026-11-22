-- AquaBill SMS template update (English only, with line breaks)
-- Run inside a transaction. Review SELECT preview first.
-- Date: 2026-11-22

START TRANSACTION;

-- Preview current values
SELECT `key`, LEFT(`body`, 80) AS body_preview, `updated_at`
FROM `sms_templates`
WHERE `key` IN ('new_bill','due_reminder','overdue_alert','payment_confirmation','late_fee_notice','service_disconnection','reconnection','meter_reading_reminder','maintenance_notice','custom');

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Dear {customer_name},

Bill: {bill_number}
Period: {billing_period}
Amount: Rs.{bill_amount}
Due: {due_date}
Account: {account_number}
Outstanding: Rs.{outstanding_amount}

View bill:
{bill_link}',
    `updated_at` = NOW()
WHERE `key` = 'new_bill';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Dear {customer_name},

Reminder: bill {bill_number} is due on {due_date}.
Amount due: Rs.{outstanding_amount}
Account: {account_number}

Pay here:
{payment_link}',
    `updated_at` = NOW()
WHERE `key` = 'due_reminder';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

URGENT

Dear {customer_name},

Bill {bill_number} is overdue by {days_overdue} days.
Outstanding: Rs.{outstanding_amount}

Pay now:
{payment_link}',
    `updated_at` = NOW()
WHERE `key` = 'overdue_alert';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Dear {customer_name},

Payment received: Rs.{paid_amount}
Date: {payment_date}
Receipt: {receipt_number}
Bill: {bill_number}
Balance: Rs.{balance}

View receipt:
{receipt_link}',
    `updated_at` = NOW()
WHERE `key` = 'payment_confirmation';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Dear {customer_name},

Late fee Rs.{late_fee} was added to bill {bill_number}.
Outstanding now: Rs.{outstanding_amount}

Pay here:
{payment_link}',
    `updated_at` = NOW()
WHERE `key` = 'late_fee_notice';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Dear {customer_name},

Account {account_number} may be disconnected on {disconnection_date}.
Outstanding: Rs.{outstanding_amount}

Pay here:
{payment_link}',
    `updated_at` = NOW()
WHERE `key` = 'service_disconnection';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Dear {customer_name},

Account {account_number} has been reconnected.
Thank you.',
    `updated_at` = NOW()
WHERE `key` = 'reconnection';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Dear {customer_name},

Meter reading for account {account_number}
is scheduled on {date} ({day})
between {time_range}.

Please keep the meter accessible.',
    `updated_at` = NOW()
WHERE `key` = 'meter_reading_reminder';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Water supply interruption

Area: {area}
Date: {date} ({day})
Time: {start_time} to {end_time}
Reason: {reason}

Please store water in advance.',
    `updated_at` = NOW()
WHERE `key` = 'maintenance_notice';

UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project

Dear {customer_name},
Account: {account_number}

',
    `updated_at` = NOW()
WHERE `key` = 'custom';

-- Verify
SELECT `key`, CHAR_LENGTH(`body`) AS chars, `updated_at`
FROM `sms_templates`
WHERE `key` IN ('new_bill','due_reminder','overdue_alert','payment_confirmation','late_fee_notice','service_disconnection','reconnection','meter_reading_reminder','maintenance_notice','custom')
ORDER BY `key`;

COMMIT;
