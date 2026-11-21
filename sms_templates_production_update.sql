-- AquaBill SMS template update (safe for production)
-- Run inside a transaction. Review SELECT preview first.
-- Date: 2026-11-21 18:36:45

START TRANSACTION;

-- Preview current values
SELECT `key`, LEFT(`body`, 80) AS body_preview, `updated_at`
FROM `sms_templates`
WHERE `key` IN ('new_bill','due_reminder','overdue_alert','payment_confirmation','late_fee_notice','service_disconnection','reconnection','meter_reading_reminder','maintenance_notice','custom');

-- Update: new_bill
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Dear {customer_name}, bill {bill_number} ({billing_period}) is (இம்மாத கட்டணம் ரூ) Rs.{bill_amount}. Due {due_date}. Account {account_number} total Outstanding (மொத்த நிலுவை) Rs.{outstanding_amount}.
View: {bill_link}',
    `updated_at` = NOW()
WHERE `key` = 'new_bill';

-- Update: due_reminder
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Dear {customer_name}, bill {bill_number} of Rs.{outstanding_amount} is due on {due_date}. Account {account_number}. Pay: {payment_link}',
    `updated_at` = NOW()
WHERE `key` = 'due_reminder';

-- Update: overdue_alert
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: URGENT - Dear {customer_name}, bill {bill_number} is overdue ({days_overdue} days). Outstanding Rs.{outstanding_amount}. Pay now: {payment_link}',
    `updated_at` = NOW()
WHERE `key` = 'overdue_alert';

-- Update: payment_confirmation
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Dear {customer_name}, payment Rs.{paid_amount} received on {payment_date}. Receipt {receipt_number}, bill {bill_number}. Balance Rs.{balance}.


View: {receipt_link}',
    `updated_at` = NOW()
WHERE `key` = 'payment_confirmation';

-- Update: late_fee_notice
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Late fee Rs.{late_fee} added to bill {bill_number}. Outstanding now Rs.{outstanding_amount}. Pay: {payment_link}',
    `updated_at` = NOW()
WHERE `key` = 'late_fee_notice';

-- Update: service_disconnection
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Dear {customer_name}, account {account_number} may be disconnected on {disconnection_date}. Outstanding Rs.{outstanding_amount}. Pay: {payment_link}',
    `updated_at` = NOW()
WHERE `key` = 'service_disconnection';

-- Update: reconnection
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Dear {customer_name}, account {account_number} has been reconnected. Thank you.',
    `updated_at` = NOW()
WHERE `key` = 'reconnection';

-- Update: meter_reading_reminder
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Dear {customer_name}, meter reading for account {account_number} is scheduled on {date} ({day}) between {time_range}. Please keep the meter accessible.',
    `updated_at` = NOW()
WHERE `key` = 'meter_reading_reminder';

-- Update: maintenance_notice
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Water supply interruption in {area} on {date} ({day}) from {start_time} to {end_time}. Reason: {reason}.
டன்சினன் நீர் திட்டம்: அறிவித்தல்! {area} {date_ta} {day_ta} காலை {start_time_ta} மணி முதல் {end_time_ta} மணி வரை நீர் விநியோகம் தடைப்படும். காரணம்: {reason_ta}. தயவு செய்து நீரை சேமித்து வைக்கவும்.',
    `updated_at` = NOW()
WHERE `key` = 'maintenance_notice';

-- Update: custom
UPDATE `sms_templates`
SET `body` = 'Dunsinane Water Project: Dear {customer_name}, account {account_number}: ',
    `updated_at` = NOW()
WHERE `key` = 'custom';

-- Verify
SELECT `key`, CHAR_LENGTH(`body`) AS chars, `updated_at`
FROM `sms_templates`
WHERE `key` IN ('new_bill','due_reminder','overdue_alert','payment_confirmation','late_fee_notice','service_disconnection','reconnection','meter_reading_reminder','maintenance_notice','custom')
ORDER BY `key`;

-- If everything looks correct:
COMMIT;
-- If something is wrong:
-- ROLLBACK;
