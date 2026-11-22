-- AquaBill production update 2.6.19 (safe)
-- Covers: English SMS templates (line breaks) + bill_due_days = 14 (including issue day)
-- Does NOT change existing bill due_date rows (only future bills use the new rule).
--
-- How to run safely:
-- 1) Take a DB backup first.
-- 2) Run this whole script in phpMyAdmin / MySQL.
-- 3) Review the PREVIEW SELECTs.
-- 4) If results look wrong before COMMIT, run ROLLBACK; instead.
-- 5) After deploy: php artisan migrate --force  (or use this SQL if migrate is not available)
-- 6) php artisan config:clear && php artisan cache:clear

START TRANSACTION;

-- =========================================================
-- A) Preview current SMS templates
-- =========================================================
SELECT `key`, CHAR_LENGTH(`body`) AS chars, LEFT(`body`, 60) AS body_preview, `updated_at`
FROM `sms_templates`
WHERE `key` IN (
  'new_bill','due_reminder','overdue_alert','payment_confirmation','late_fee_notice',
  'service_disconnection','reconnection','meter_reading_reminder','maintenance_notice','custom'
)
ORDER BY `key`;

-- =========================================================
-- B) Preview current bill_due_days setting
-- =========================================================
SELECT `key`, `value`, `type`, `description`, `updated_at`
FROM `system_configurations`
WHERE `key` = 'bill_due_days';

-- =========================================================
-- C) Update SMS templates (English only, with line breaks)
-- =========================================================
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

-- =========================================================
-- D) Set bill due window = 14 days INCLUDING issue date
-- =========================================================
INSERT INTO `system_configurations` (`key`, `value`, `type`, `description`, `created_at`, `updated_at`)
VALUES (
  'bill_due_days',
  '14',
  'integer',
  'Payment window in days including the bill issue date',
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE
  `value` = '14',
  `type` = 'integer',
  `description` = 'Payment window in days including the bill issue date',
  `updated_at` = NOW();

-- =========================================================
-- E) Verify after update (still inside transaction)
-- =========================================================
SELECT `key`, CHAR_LENGTH(`body`) AS chars, LEFT(`body`, 40) AS body_preview, `updated_at`
FROM `sms_templates`
WHERE `key` IN (
  'new_bill','due_reminder','overdue_alert','payment_confirmation','late_fee_notice',
  'service_disconnection','reconnection','meter_reading_reminder','maintenance_notice','custom'
)
ORDER BY `key`;

SELECT `key`, `value`, `type`, `description`, `updated_at`
FROM `system_configurations`
WHERE `key` = 'bill_due_days';

-- If everything looks correct:
COMMIT;

-- If something looks wrong, do NOT commit. Run this instead:
-- ROLLBACK;
