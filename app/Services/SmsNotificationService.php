<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\SmsNotification;
use Carbon\Carbon;
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
    public function sendPaymentConfirmation(Bill $bill, float $paidAmount, ?Carbon $scheduledAt = null): ?SmsNotification
    {
        if (!config('sms.bill_notifications.payment_confirmation', true)) {
            return null;
        }

        $message = $this->templateService->paymentConfirmationMessage($bill, $paidAmount);
        
        return $this->createAndSendNotification(
            $bill->customer,
            'payment_confirmation',
            $message,
            $bill,
            $scheduledAt
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
    public function sendServiceDisconnectionWarning(Customer $customer, float $totalDue, ?Carbon $scheduledAt = null): ?SmsNotification
    {
        $message = $this->templateService->serviceDisconnectionMessage($customer, $totalDue);
        
        return $this->createAndSendNotification(
            $customer,
            'service_disconnection',
            $message,
            null,
            $scheduledAt
        );
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
            $scheduledAt
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
    protected function createAndSendNotification(
        Customer $customer,
        string $type,
        string $message,
        ?Bill $bill = null,
        ?Carbon $scheduledAt = null
    ): ?SmsNotification {
        if (!config('sms.notifications.enabled', true)) {
            Log::info('SMS notifications are disabled');
            return null;
        }

        if (!$customer->phone) {
            Log::warning("Customer {$customer->id} has no phone number for SMS notification");
            return null;
        }

        try {
            // Create notification record
            $notification = SmsNotification::create([
                'customer_id' => $customer->id,
                'bill_id' => $bill?->id,
                'phone_number' => $customer->phone,
                'message' => $message,
                'type' => $type,
                'scheduled_at' => $scheduledAt,
            ]);

            // Send immediately if not scheduled
            if (!$scheduledAt || $scheduledAt <= now()) {
                $this->sendSmsNotification($notification);
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
                $notification->customer->address ?? null
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