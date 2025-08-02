<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsNotificationService;
use App\Models\Bill;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessSmsNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:process 
                            {--scheduled : Process scheduled notifications}
                            {--retry : Retry failed notifications}
                            {--due-reminders=3 : Send due reminders for bills due in X days}
                            {--overdue-alerts : Send overdue alerts}
                            {--test-connection : Test SMS service connection}
                            {--account-status : Check SMS account status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process SMS notifications, send reminders, and manage SMS tasks';

    protected SmsNotificationService $smsService;

    public function __construct(SmsNotificationService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔔 SMS Notification Processor Started');
        $this->info('⏰ ' . now()->format('Y-m-d H:i:s'));

        try {
            // Test connection if requested
            if ($this->option('test-connection')) {
                return $this->testConnection();
            }

            // Check account status if requested
            if ($this->option('account-status')) {
                return $this->checkAccountStatus();
            }

            // Process scheduled notifications
            if ($this->option('scheduled')) {
                $this->processScheduledNotifications();
            }

            // Retry failed notifications
            if ($this->option('retry')) {
                $this->retryFailedNotifications();
            }

            // Send due reminders
            if ($this->option('due-reminders')) {
                $this->sendDueReminders((int) $this->option('due-reminders'));
            }

            // Send overdue alerts
            if ($this->option('overdue-alerts')) {
                $this->sendOverdueAlerts();
            }

            // If no specific option provided, run default processing
            if (!$this->option('scheduled') && !$this->option('retry') && 
                !$this->option('due-reminders') && !$this->option('overdue-alerts')) {
                $this->runDefaultProcessing();
            }

            $this->info('✅ SMS processing completed successfully');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ SMS processing failed: ' . $e->getMessage());
            Log::error('SMS Processing Command Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Test SMS service connection
     */
    protected function testConnection(): int
    {
        $this->info('🔍 Testing SMS service connection...');
        
        $errors = $this->smsService->validateConfiguration();
        if (!empty($errors)) {
            $this->error('❌ Configuration errors:');
            foreach ($errors as $error) {
                $this->line('  • ' . $error);
            }
            return 1;
        }

        $status = $this->smsService->getAccountStatus();
        if ($status['success']) {
            $this->info('✅ SMS service connection successful');
            $this->info('📊 Account Status: ' . ($status['active'] ? 'Active' : 'Inactive'));
            $this->info('💰 Balance: Rs. ' . number_format($status['balance'], 2));
            return 0;
        } else {
            $this->error('❌ SMS service connection failed: ' . $status['message']);
            return 1;
        }
    }

    /**
     * Check SMS account status
     */
    protected function checkAccountStatus(): int
    {
        $this->info('📊 Checking SMS account status...');
        
        $status = $this->smsService->getAccountStatus();
        $stats = $this->smsService->getStatistics();

        if ($status['success']) {
            $this->info('Account Information:');
            $this->line('  • Status: ' . ($status['active'] ? '✅ Active' : '❌ Inactive'));
            $this->line('  • Balance: Rs. ' . number_format($status['balance'], 2));
            
            $this->info('SMS Statistics:');
            $this->line('  • Total SMS: ' . number_format($stats['total']));
            $this->line('  • Sent: ' . number_format($stats['sent']));
            $this->line('  • Pending: ' . number_format($stats['pending']));
            $this->line('  • Failed: ' . number_format($stats['failed']));
            $this->line('  • Today: ' . number_format($stats['today_sent']));
            $this->line('  • This Month: ' . number_format($stats['this_month_sent']));
            
            return 0;
        } else {
            $this->error('❌ Failed to get account status: ' . $status['message']);
            return 1;
        }
    }

    /**
     * Process scheduled notifications
     */
    protected function processScheduledNotifications(): void
    {
        $this->info('📅 Processing scheduled notifications...');
        
        $result = $this->smsService->processScheduledNotifications();
        
        $this->info("📊 Processed: {$result['processed']} notifications");
        $this->info("✅ Sent: {$result['sent']}");
        $this->info("❌ Failed: {$result['failed']}");
        
        if (!empty($result['errors'])) {
            $this->warn('⚠️  Errors encountered:');
            foreach ($result['errors'] as $error) {
                $this->line('  • ' . $error);
            }
        }
    }

    /**
     * Retry failed notifications
     */
    protected function retryFailedNotifications(): void
    {
        $this->info('🔄 Retrying failed notifications...');
        
        $result = $this->smsService->retryFailedNotifications();
        
        $this->info("📊 Retried: {$result['processed']} notifications");
        $this->info("✅ Sent: {$result['sent']}");
        $this->info("❌ Still Failed: {$result['failed']}");
        
        if (!empty($result['errors'])) {
            $this->warn('⚠️  Retry errors:');
            foreach ($result['errors'] as $error) {
                $this->line('  • ' . $error);
            }
        }
    }

    /**
     * Send due reminders for bills
     */
    protected function sendDueReminders(int $daysBeforeDue): void
    {
        $this->info("📋 Sending due reminders for bills due in {$daysBeforeDue} days...");
        
        $targetDate = Carbon::now()->addDays($daysBeforeDue);
        $bills = Bill::with('customer')
            ->where('status', 'generated')
            ->whereDate('due_date', $targetDate)
            ->get();

        if ($bills->isEmpty()) {
            $this->info('ℹ️  No bills found due on ' . $targetDate->format('Y-m-d'));
            return;
        }

        $sentCount = 0;
        $this->info("📤 Found {$bills->count()} bills due on {$targetDate->format('M d, Y')}");
        
        foreach ($bills as $bill) {
            if ($bill->customer && $bill->customer->canReceiveSms()) {
                if ($bill->sendDueReminder()) {
                    $sentCount++;
                    $this->line("✅ Sent to {$bill->customer->full_name} ({$bill->customer->formatted_phone})");
                } else {
                    $this->line("❌ Failed to send to {$bill->customer->full_name}");
                }
            } else {
                $customerName = $bill->customer?->full_name ?? 'Unknown';
                $this->line("⚠️  Skipped {$customerName} (no phone/inactive)");
            }
        }
        
        $this->info("📊 Due reminders sent: {$sentCount}/{$bills->count()}");
    }

    /**
     * Send overdue alerts
     */
    protected function sendOverdueAlerts(): void
    {
        $this->info('🚨 Sending overdue alerts...');
        
        $overdueBills = Bill::with('customer')
            ->where('status', 'overdue')
            ->get();

        if ($overdueBills->isEmpty()) {
            $this->info('ℹ️  No overdue bills found');
            return;
        }

        $sentCount = 0;
        $this->info("📤 Found {$overdueBills->count()} overdue bills");
        
        foreach ($overdueBills as $bill) {
            if ($bill->customer && $bill->customer->canReceiveSms()) {
                if ($bill->sendOverdueAlert()) {
                    $sentCount++;
                    $this->line("✅ Sent to {$bill->customer->full_name} ({$bill->customer->formatted_phone})");
                } else {
                    $this->line("❌ Failed to send to {$bill->customer->full_name}");
                }
            } else {
                $customerName = $bill->customer?->full_name ?? 'Unknown';
                $this->line("⚠️  Skipped {$customerName} (no phone/inactive)");
            }
        }
        
        $this->info("📊 Overdue alerts sent: {$sentCount}/{$overdueBills->count()}");
    }

    /**
     * Run default processing (scheduled + retry)
     */
    protected function runDefaultProcessing(): void
    {
        $this->info('🔄 Running default SMS processing...');
        $this->processScheduledNotifications();
        $this->retryFailedNotifications();
    }
}
