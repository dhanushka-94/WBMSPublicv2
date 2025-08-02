<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Customer;
use Carbon\Carbon;

class SmsTemplateService
{
    /**
     * Generate SMS message for new bill notification
     */
    public function newBillMessage(Bill $bill): string
    {
        $template = config('sms.templates.new_bill');
        
        return $this->replaceVariables($template, [
            'customer_name' => $bill->customer->full_name,
            'bill_number' => $bill->bill_number,
            'amount' => number_format($bill->total_amount, 2),
            'due_date' => $bill->due_date->format('M d, Y'),
            'payment_link' => $this->generatePaymentLink($bill),
        ]);
    }

    /**
     * Generate SMS message for payment due reminder
     */
    public function dueReminderMessage(Bill $bill): string
    {
        $template = config('sms.templates.due_reminder');
        
        return $this->replaceVariables($template, [
            'customer_name' => $bill->customer->full_name,
            'bill_number' => $bill->bill_number,
            'amount' => number_format($bill->balance_amount, 2),
            'due_date' => $bill->due_date->format('M d, Y'),
            'payment_link' => $this->generatePaymentLink($bill),
        ]);
    }

    /**
     * Generate SMS message for overdue bill alert
     */
    public function overdueAlertMessage(Bill $bill): string
    {
        $template = config('sms.templates.overdue_alert');
        
        $daysOverdue = $bill->due_date->diffInDays(now());
        
        return $this->replaceVariables($template, [
            'customer_name' => $bill->customer->full_name,
            'bill_number' => $bill->bill_number,
            'amount' => number_format($bill->balance_amount, 2),
            'days_overdue' => $daysOverdue,
            'payment_link' => $this->generatePaymentLink($bill),
        ]);
    }

    /**
     * Generate SMS message for payment confirmation
     */
    public function paymentConfirmationMessage(Bill $bill, float $paidAmount): string
    {
        $template = config('sms.templates.payment_confirmation');
        
        return $this->replaceVariables($template, [
            'customer_name' => $bill->customer->full_name,
            'amount' => number_format($paidAmount, 2),
            'bill_number' => $bill->bill_number,
            'balance' => number_format($bill->balance_amount, 2),
            'payment_date' => now()->format('M d, Y'),
        ]);
    }

    /**
     * Generate SMS message for late fee notice
     */
    public function lateFeeNoticeMessage(Bill $bill, float $lateFeeAmount): string
    {
        $template = config('sms.templates.late_fee_notice');
        
        return $this->replaceVariables($template, [
            'customer_name' => $bill->customer->full_name,
            'late_fee' => number_format($lateFeeAmount, 2),
            'bill_number' => $bill->bill_number,
            'total_due' => number_format($bill->balance_amount, 2),
            'payment_link' => $this->generatePaymentLink($bill),
        ]);
    }

    /**
     * Generate SMS message for service disconnection warning
     */
    public function serviceDisconnectionMessage(Customer $customer, float $totalDue): string
    {
        $template = config('sms.templates.service_disconnection');
        
        return $this->replaceVariables($template, [
            'customer_name' => $customer->full_name,
            'total_due' => number_format($totalDue, 2),
            'account_number' => $customer->account_number,
            'payment_link' => $this->generateCustomerPaymentLink($customer),
        ]);
    }

    /**
     * Generate SMS message for meter reading reminder
     */
    public function meterReadingReminderMessage(Customer $customer, Carbon $readingDate, string $timeRange = '9:00 AM - 4:00 PM'): string
    {
        $template = config('sms.templates.meter_reading_reminder');
        
        return $this->replaceVariables($template, [
            'customer_name' => $customer->full_name,
            'date' => $readingDate->format('M d, Y'),
            'day' => $readingDate->format('l'),
            'time_range' => $timeRange,
            'account_number' => $customer->account_number,
        ]);
    }

    /**
     * Generate SMS message for maintenance notice
     */
    public function maintenanceNoticeMessage(string $area, Carbon $date, string $startTime, string $endTime, string $reason = 'routine maintenance'): string
    {
        $template = config('sms.templates.maintenance_notice');
        
        return $this->replaceVariables($template, [
            'area' => $area,
            'date' => $date->format('M d, Y'),
            'day' => $date->format('l'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'reason' => $reason,
        ]);
    }

    /**
     * Generate custom SMS message with variables
     */
    public function customMessage(string $template, array $variables): string
    {
        return $this->replaceVariables($template, $variables);
    }

    /**
     * Replace variables in template with actual values
     */
    private function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        // Remove any unreplaced variables
        $template = preg_replace('/\{[^}]+\}/', '', $template);
        
        return trim($template);
    }

    /**
     * Generate payment link for a bill
     */
    private function generatePaymentLink(Bill $bill): string
    {
        // You can customize this to your actual payment gateway URL
        return url("/bills/{$bill->id}/pay");
    }

    /**
     * Generate payment link for a customer
     */
    private function generateCustomerPaymentLink(Customer $customer): string
    {
        return url("/customers/{$customer->id}/bills");
    }

    /**
     * Get all available templates
     */
    public function getAvailableTemplates(): array
    {
        return [
            'new_bill' => [
                'name' => 'New Bill Notification',
                'template' => config('sms.templates.new_bill'),
                'variables' => ['{customer_name}', '{bill_number}', '{amount}', '{due_date}', '{payment_link}']
            ],
            'due_reminder' => [
                'name' => 'Payment Due Reminder',
                'template' => config('sms.templates.due_reminder'),
                'variables' => ['{customer_name}', '{bill_number}', '{amount}', '{due_date}', '{payment_link}']
            ],
            'overdue_alert' => [
                'name' => 'Overdue Bill Alert',
                'template' => config('sms.templates.overdue_alert'),
                'variables' => ['{customer_name}', '{bill_number}', '{amount}', '{days_overdue}', '{payment_link}']
            ],
            'payment_confirmation' => [
                'name' => 'Payment Confirmation',
                'template' => config('sms.templates.payment_confirmation'),
                'variables' => ['{customer_name}', '{amount}', '{bill_number}', '{balance}', '{payment_date}']
            ],
            'late_fee_notice' => [
                'name' => 'Late Fee Notice',
                'template' => config('sms.templates.late_fee_notice'),
                'variables' => ['{customer_name}', '{late_fee}', '{bill_number}', '{total_due}', '{payment_link}']
            ],
            'service_disconnection' => [
                'name' => 'Service Disconnection Warning',
                'template' => config('sms.templates.service_disconnection'),
                'variables' => ['{customer_name}', '{total_due}', '{account_number}', '{payment_link}']
            ],
            'meter_reading_reminder' => [
                'name' => 'Meter Reading Reminder',
                'template' => config('sms.templates.meter_reading_reminder'),
                'variables' => ['{customer_name}', '{date}', '{day}', '{time_range}', '{account_number}']
            ],
            'maintenance_notice' => [
                'name' => 'Maintenance Notice',
                'template' => config('sms.templates.maintenance_notice'),
                'variables' => ['{area}', '{date}', '{day}', '{start_time}', '{end_time}', '{reason}']
            ],
        ];
    }

    /**
     * Preview a template with sample data
     */
    public function previewTemplate(string $templateType, array $sampleData = []): string
    {
        $defaults = [
            'customer_name' => 'John Doe',
            'bill_number' => 'WB202407001',
            'amount' => '2,500.00',
            'due_date' => 'Aug 15, 2024',
            'balance' => '1,250.00',
            'payment_date' => 'Jul 29, 2024',
            'account_number' => 'AC001234',
            'total_due' => '5,000.00',
            'late_fee' => '250.00',
            'days_overdue' => '5',
            'date' => 'Jul 30, 2024',
            'day' => 'Tuesday',
            'time_range' => '9:00 AM - 4:00 PM',
            'area' => 'Colombo 07',
            'start_time' => '6:00 AM',
            'end_time' => '2:00 PM',
            'reason' => 'routine maintenance',
            'payment_link' => 'https://example.com/pay/123'
        ];

        $variables = array_merge($defaults, $sampleData);
        $template = config("sms.templates.{$templateType}");

        return $this->replaceVariables($template, $variables);
    }
} 