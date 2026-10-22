<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\SmsTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class SmsTemplateService
{
    /**
     * Generate SMS message for new bill notification
     */
    public function newBillMessage(Bill $bill): string
    {
        return $this->fill('new_bill', $this->billVariables($bill));
    }

    /**
     * Generate SMS message for payment due reminder
     */
    public function dueReminderMessage(Bill $bill): string
    {
        return $this->fill('due_reminder', $this->billVariables($bill));
    }

    /**
     * Generate SMS message for overdue bill alert
     */
    public function overdueAlertMessage(Bill $bill): string
    {
        return $this->fill('overdue_alert', $this->billVariables($bill));
    }

    /**
     * Generate SMS message for payment confirmation
     */
    public function paymentConfirmationMessage(Bill $bill, float $paidAmount, ?Payment $payment = null): string
    {
        $variables = $this->billVariables($bill);
        $variables['paid_amount'] = number_format($paidAmount, 2);
        $variables['amount'] = $variables['paid_amount'];
        $variables['payment_date'] = ($payment?->paid_on ?? now())->format('M d, Y');
        $variables['receipt_number'] = $payment->receipt_number ?? '';
        $variables['receipt_link'] = $payment ? $this->receiptLink($payment) : '';

        return $this->fill('payment_confirmation', $variables);
    }

    /**
     * Generate SMS message for late fee notice
     */
    public function lateFeeNoticeMessage(Bill $bill, float $lateFeeAmount): string
    {
        $variables = $this->billVariables($bill);
        $variables['late_fee'] = number_format($lateFeeAmount, 2);

        return $this->fill('late_fee_notice', $variables);
    }

    /**
     * Generate SMS message for service disconnection warning
     */
    public function serviceDisconnectionMessage(Customer $customer, float $totalDue, ?Carbon $disconnectionDate = null): string
    {
        return $this->fill('service_disconnection', [
            'customer_name' => $customer->full_name,
            'account_number' => $customer->account_number,
            'outstanding_amount' => number_format($totalDue, 2),
            'bill_amount' => number_format($totalDue, 2),
            'total_due' => number_format($totalDue, 2),
            'disconnection_date' => ($disconnectionDate ?? now())->format('M d, Y'),
            'payment_link' => $this->customerDueLink($customer),
            'bill_link' => $this->customerDueLink($customer),
        ]);
    }

    public function reconnectionMessage(Customer $customer): string
    {
        return $this->fill('reconnection', [
            'customer_name' => $customer->full_name,
            'account_number' => $customer->account_number,
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

    public function fill(string $key, array $variables): string
    {
        $template = SmsTemplate::bodyFor($key, (string) config("sms.templates.{$key}", ''));

        return $this->replaceVariables($template, $variables);
    }

    public function billVariables(Bill $bill): array
    {
        $period = $bill->billing_period_from->format('M d, Y').' - '.$bill->billing_period_to->format('M d, Y');
        $outstanding = number_format((float) $bill->balance_amount, 2);
        $amount = number_format((float) $bill->total_amount, 2);
        $link = $this->billLink($bill);

        return [
            'customer_name' => $bill->customer->full_name,
            'account_number' => $bill->customer->account_number,
            'bill_number' => $bill->bill_number,
            'billing_period' => $period,
            'bill_amount' => $amount,
            'amount' => $amount,
            'outstanding_amount' => $outstanding,
            'balance' => $outstanding,
            'total_due' => $outstanding,
            'due_date' => $bill->due_date->format('M d, Y'),
            'days_overdue' => (string) $bill->due_date->diffInDays(now()),
            'bill_link' => $link,
            'payment_link' => $link,
        ];
    }

    public function billLink(Bill $bill): string
    {
        return URL::signedRoute('sms.bill', ['bill' => $bill->id]);
    }

    public function receiptLink(Payment $payment): string
    {
        return URL::signedRoute('sms.receipt', ['payment' => $payment->id]);
    }

    public function customerDueLink(Customer $customer): string
    {
        $bill = $customer->bills()
            ->where('balance_amount', '>', 0)
            ->whereNotIn('status', ['cancelled', 'draft', 'paid'])
            ->orderByDesc('balance_amount')
            ->first();

        return $bill ? $this->billLink($bill) : URL::signedRoute('sms.due', ['customer' => $customer->id]);
    }

    /**
     * Replace variables in template with actual values
     */
    public function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        $template = preg_replace('/\{[^}]+\}/', '', $template);

        return trim($template);
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
            'payment_link' => 'https://example.com/bills/1',
            'bill_link' => 'https://example.com/bills/1',
            'receipt_link' => 'https://example.com/receipts/1',
            'bill_amount' => '2,500.00',
            'paid_amount' => '1,000.00',
            'outstanding_amount' => '1,500.00',
            'billing_period' => 'Jul 01, 2026 - Jul 31, 2026',
            'receipt_number' => 'RCT-00021',
            'disconnection_date' => 'Aug 01, 2026',
        ];

        $variables = array_merge($defaults, $sampleData);
        $template = SmsTemplate::bodyFor($templateType, (string) config("sms.templates.{$templateType}", ''));

        return $this->replaceVariables($template, $variables);
    }
} 