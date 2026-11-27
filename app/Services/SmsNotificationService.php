<?php

namespace App\Services;

use App\Jobs\SendSmsNotificationJob;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\SmsNotification;
use App\Models\SmsTemplate;
use App\Models\SystemConfiguration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    protected NotifyLkSmsService $smsService;
    protected SmsTemplateService $templateService;

    public function __construct(
        NotifyLkSmsService $smsService,
        SmsTemplateService $templateService
    ) {
        $this->smsService = $smsService;
        $this->templateService = $templateService;
    }

    /**
     * Send new bill notification
     */
    public function sendNewBillNotification(Bill $bill, ?Carbon $scheduledAt = null): ?SmsNotification
    {
        if (!config('sms.bill_notifications.new_bill', true)) {
            return null;
        }

        $message = $this->templateService->newBillMessage($bill);
        
        return $this->createAndSendNotification(
            $bill->customer,
            'new_bill',
            $message,
            $bill,
            $scheduledAt
        );
    }

    /**
     * Send payment due reminder
     */
    public function sendDueReminder(Bill $bill, ?Carbon $scheduledAt = null): ?SmsNotification
    {
        if (!config('sms.bill_notifications.due_reminder', true)) {
            return null;
        }

        $message = $this->templateService->dueReminderMessage($bill);
        
        return $this->createAndSendNotification(
            $bill->customer,
            'due_reminder',
            $message,
            $bill,
            $scheduledAt
        );
    }

    /**
     * Send overdue bill alert
     */
    public function sendOverdueAlert(Bill $bill, ?Carbon $scheduledAt = null): ?SmsNotification
    {
        if (!config('sms.bill_notifications.overdue_alert', true)) {
            return null;
        }

        $message = $this->templateService->overdueAlertMessage($bill);
        
        return $this->createAndSendNotification(
            $bill->customer,
            'overdue_alert',
            $message,
            $bill,
            $scheduledAt
        );
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(Bill $bill, float $paidAmount, ?Carbon $scheduledAt = null, ?Payment $payment = null): ?SmsNotification
    {
        if (!config('sms.bill_notifications.payment_confirmation', true)) {
            return null;
        }

        $message = $this->templateService->paymentConfirmationMessage($bill, $paidAmount, $payment);

        return $this->createAndSendNotification(
            $bill->customer,
            'payment_confirmation',
            $message,
            $bill,
            $scheduledAt,
            ['payment' => $payment]
        );
    }

    /**
     * Send late fee notice
     */
    public function sendLateFeeNotice(Bill $bill, float $lateFeeAmount, ?Carbon $scheduledAt = null): ?SmsNotification
    {
        if (!config('sms.bill_notifications.late_fee_notice', true)) {
            return null;
        }

        $message = $this->templateService->lateFeeNoticeMessage($bill, $lateFeeAmount);
        
        return $this->createAndSendNotification(
            $bill->customer,
            'late_fee_notice',
            $message,
            $bill,
            $scheduledAt
        );
    }

    /**
     * Send service disconnection warning
     */
    public function sendServiceDisconnectionWarning(Customer $customer, float $totalDue, ?Carbon $scheduledAt = null, ?Carbon $disconnectionDate = null): ?SmsNotification
    {
        $message = $this->templateService->serviceDisconnectionMessage($customer, $totalDue, $disconnectionDate ?? $scheduledAt);
        
        return $this->createAndSendNotification(
            $customer,
            'service_disconnection',
            $message,
            null,
            $scheduledAt
        );
    }

    public function sendReconnectionNotice(Customer $customer): ?SmsNotification
    {
        $message = $this->templateService->reconnectionMessage($customer);

        return $this->createAndSendNotification($customer, 'reconnection', $message, null, null);
    }

    /**
     * Send meter reading reminder
     */
    public function sendMeterReadingReminder(Customer $customer, Carbon $readingDate, string $timeRange = '9:00 AM - 4:00 PM', ?Carbon $scheduledAt = null): ?SmsNotification
    {
        $message = $this->templateService->meterReadingReminderMessage($customer, $readingDate, $timeRange);
        
        return $this->createAndSendNotification(
            $customer,
            'meter_reading_reminder',
            $message,
            null,
            $scheduledAt
        );
    }

    /**
     * Send maintenance notice
     */
    public function sendMaintenanceNotice(
        array $customers,
        string $area,
        Carbon $date,
        string $startTime,
        string $endTime,
        string $reason = 'routine maintenance',
        ?Carbon $scheduledAt = null
    ): array {
        $results = [];
        $message = $this->templateService->maintenanceNoticeMessage($area, $date, $startTime, $endTime, $reason);

        foreach ($customers as $customer) {
            $results[] = $this->createAndSendNotification(
                $customer,
                'maintenance_notice',
                $message,
                null,
                $scheduledAt
            );
        }

        return $results;
    }

    /**
     * Send custom SMS notification
     */
    public function sendCustomNotification(
        Customer $customer,
        string $message,
        ?Carbon $scheduledAt = null
    ): ?SmsNotification {
        return $this->createAndSendNotification(
            $customer,
            'custom',
            $message,
            null,
            $scheduledAt,
            ['source' => 'manual', 'queue' => true]
        );
    }

    /**
     * Send bulk custom notifications
     */
    public function sendBulkCustomNotifications(
        array $customers,
        string $message,
        ?Carbon $scheduledAt = null
    ): array {
        $results = [];

        foreach ($customers as $customer) {
            $results[] = $this->createAndSendNotification(
                $customer,
                'custom',
                $message,
                null,
                $scheduledAt
            );
        }

        return $results;
    }

    /**
     * Process scheduled notifications
     */
    public function processScheduledNotifications(): array
    {
        $notifications = SmsNotification::pending()
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('scheduled_at')
            ->limit(100) // Process in batches
            ->get();

        $results = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($notifications as $notification) {
            $result = $this->sendSmsNotification($notification);
            $results['processed']++;
            
            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Notification ID {$notification->id}: {$result['message']}";
            }
        }

        return $results;
    }

    /**
     * Retry failed notifications
     */
    public function retryFailedNotifications(): array
    {
        $notifications = SmsNotification::failed()
            ->get()
            ->filter(function ($notification) {
                return $notification->canRetry();
            });

        $results = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($notifications as $notification) {
            $result = $this->sendSmsNotification($notification);
            $results['processed']++;
            
            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Notification ID {$notification->id}: {$result['message']}";
            }
        }

        return $results;
    }

    /**
     * Create and send SMS notification
     */
    public function sendExisting(SmsNotification $notification): array
    {
        return $this->sendSmsNotification($notification);
    }

    /**
     * Resend a failed SMS. Result keys: success, message, and optionally
     * skipped (no longer failed) or disabled (sending is turned off).
     */
    public function resend(SmsNotification $notification): array
    {
        if (!$this->smsEnabled()) {
            return ['success' => false, 'disabled' => true, 'message' => 'SMS sending is turned off in SMS Settings.'];
        }

        // Move failed -> pending in one statement so a double click or two staff members cannot send it twice.
        $claimed = SmsNotification::whereKey($notification->id)
            ->where('status', 'failed')
            ->update(['status' => 'pending', 'error_message' => null]);

        if (!$claimed) {
            return ['success' => false, 'skipped' => true, 'message' => 'This SMS is no longer in the failed state.'];
        }

        $notification->refresh();

        // If staff corrected the customer's number after the failure, send to the corrected number.
        $notification->phone_number = $this->preferredPhone($notification);

        // A successful resend is charged at today's rate.
        $notification->cost_per_segment = (float) config('sms.cost_per_segment', 0);
        $notification->save();

        return $this->sendSmsNotification($notification);
    }

    /**
     * Send a delivered SMS again (for example when the customer says it never arrived).
     * The original row is left untouched and a new row is logged, so the extra charge is counted.
     * Result keys: success, message, notification (the new row), and optionally skipped or disabled.
     */
    public function sendAgain(SmsNotification $original): array
    {
        if (!$this->smsEnabled()) {
            return ['success' => false, 'disabled' => true, 'message' => 'SMS sending is turned off in SMS Settings.'];
        }

        if ($original->status !== 'sent') {
            return ['success' => false, 'message' => 'Only delivered messages can be sent again. Use Resend for failed messages.'];
        }

        // Stops a double click or two staff members from sending it twice at the same moment.
        $lock = Cache::lock('sms-send-again:' . $original->id, 20);
        if (!$lock->get()) {
            return ['success' => false, 'skipped' => true, 'message' => 'This SMS is already being sent again.'];
        }

        try {
            $copy = SmsNotification::create([
                'customer_id' => $original->customer_id,
                'bill_id' => $original->bill_id,
                'payment_id' => $original->payment_id,
                'receipt_number' => $original->receipt_number,
                'phone_number' => $this->preferredPhone($original),
                'message' => $original->message,
                'segments' => NotifyLkSmsService::segmentCount($original->message),
                'cost_per_segment' => (float) config('sms.cost_per_segment', 0),
                'type' => $original->type,
                'source' => 'resend',
            ]);

            $result = $this->sendSmsNotification($copy);
            $result['notification'] = $copy->fresh();

            return $result;
        } finally {
            $lock->release();
        }
    }

    /**
     * The customer's current mobile number when it is valid, otherwise the number stored on the SMS.
     */
    private function preferredPhone(SmsNotification $notification): string
    {
        $stored = (string) $notification->phone_number;
        $current = $notification->customer?->phone;
        $lastNine = fn ($number) => substr(preg_replace('/\D+/', '', (string) $number), -9);

        if ($current && $this->smsService->hasValidPhone($current) && $lastNine($current) !== $lastNine($stored)) {
            return $current;
        }

        return $stored;
    }

    protected function smsEnabled(): bool
    {
        $stored = SystemConfiguration::get('sms_enabled');

        if ($stored === null) {
            return (bool) config('sms.notifications.enabled', true);
        }

        return !in_array($stored, ['0', 0, false], true);
    }

    protected function createAndSendNotification(
        Customer $customer,
        string $type,
        string $message,
        ?Bill $bill = null,
        ?Carbon $scheduledAt = null,
        array $meta = []
    ): ?SmsNotification {
        if (!$this->smsEnabled()) {
            Log::info('SMS notifications are disabled');
            return null;
        }

        if (!SmsTemplate::allows($type)) {
            Log::info("SMS template {$type} is turned off");
            return null;
        }

        if (!$customer->phone || !$this->smsService->hasValidPhone($customer->phone)) {
            Log::warning("Customer {$customer->id} has no usable phone number for SMS");
            return null;
        }

        try {
            $payment = $meta['payment'] ?? null;
            $notification = SmsNotification::create([
                'customer_id' => $customer->id,
                'bill_id' => $bill?->id,
                'payment_id' => $payment?->id,
                'receipt_number' => $payment?->receipt_number,
                'phone_number' => $customer->phone,
                'message' => $message,
                'segments' => NotifyLkSmsService::segmentCount($message),
                'cost_per_segment' => (float) config('sms.cost_per_segment', 0),
                'type' => $type,
                'source' => $meta['source'] ?? 'automated',
                'scheduled_at' => $scheduledAt,
            ]);

            $shouldQueue = ($meta['queue'] ?? false) || (bool) config('sms.notifications.queue', false);
            if (!$scheduledAt || $scheduledAt <= now()) {
                if ($shouldQueue) {
                    SendSmsNotificationJob::dispatch($notification->id);
                } else {
                    $this->sendSmsNotification($notification);
                }
            }

            return $notification;
        } catch (\Exception $e) {
            Log::error("Failed to create SMS notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSmsNotification(SmsNotification $notification): array
    {
        try {
            $result = $this->smsService->sendSms(
                $notification->phone_number,
                $notification->message,
                $notification->customer->first_name ?? null,
                $notification->customer->last_name ?? null,
                $notification->customer->email ?? null,
                $notification->customer->address ?? null,
                unicode: false,
                logAttempt: false
            );

            if ($result['success']) {
                $notification->markAsSent(
                    $result['response'] ?? [],
                    $result['response']['http_status'] ?? 200
                );
                
                Log::info("SMS sent successfully to {$notification->phone_number} (Notification ID: {$notification->id})");
            } else {
                $notification->markAsFailed(
                    $result['message'],
                    $result['response'] ?? [],
                    $result['response']['http_status'] ?? 0
                );
                
                Log::error("SMS failed to {$notification->phone_number}: {$result['message']} (Notification ID: {$notification->id})");
            }

            return $result;
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error("SMS Service Exception: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get SMS account status
     */
    public function getAccountStatus(): array
    {
        return $this->smsService->getAccountStatus();
    }

    /**
     * Validate SMS configuration
     */
    public function validateConfiguration(): array
    {
        return $this->smsService->validateConfiguration();
    }

    /**
     * Get SMS statistics
     */
    public function getStatistics(): array
    {
        return SmsNotification::getStatistics();
    }
} 