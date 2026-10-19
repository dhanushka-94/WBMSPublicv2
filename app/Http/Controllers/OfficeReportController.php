<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\WaterMeter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeReportController extends Controller
{
    public function show(Request $request, string $report): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        $date = $request->input('date', now()->toDateString());
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $data = match ($report) {
            'customer-register' => $this->customerRegister(),
            'contacts' => $this->contacts(),
            'new-connections' => $this->newConnections($start, $end),
            'disconnected' => $this->disconnected(),
            'usage-alerts' => $this->usageAlerts(),
            'meter-faults' => $this->meterFaults(),
            'billing-summary' => $this->billingSummary($start, $end),
            'daily-collection' => $this->dailyCollection($date),
            'monthly-collection' => $this->monthlyCollection($start, $end),
            'collection-efficiency' => $this->collectionEfficiency($start, $end),
            'age-analysis' => $this->ageAnalysis(),
            default => abort(404),
        };

        return view('reports.office', [
            'report' => $report,
            'title' => $data['title'],
            'columns' => $data['columns'],
            'rows' => $data['rows'],
            'month' => $month,
            'date' => $date,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function customerRegister(): array
    {
        $rows = Customer::with('division')->orderBy('account_number')->limit(500)->get()->map(fn ($customer) => [
            $customer->account_number,
            $customer->full_name,
            $customer->division?->name,
            $customer->meter_number,
            $customer->customer_type,
            ucfirst($customer->status),
        ])->all();

        return ['title' => 'Customer Register', 'columns' => ['Account', 'Name', 'Division', 'Meter', 'Type', 'Status'], 'rows' => $rows];
    }

    private function contacts(): array
    {
        $rows = Customer::orderBy('account_number')->limit(500)->get()->map(fn ($customer) => [
            $customer->account_number,
            $customer->full_name,
            $customer->phone,
            $customer->phone_two,
            $customer->address,
        ])->all();

        return ['title' => 'Contact List', 'columns' => ['Account', 'Name', 'Phone', 'Second phone', 'Address'], 'rows' => $rows];
    }

    private function newConnections(Carbon $start, Carbon $end): array
    {
        $rows = Customer::whereBetween('connection_date', [$start, $end])->orderBy('connection_date')->get()->map(fn ($customer) => [
            $customer->connection_date?->format('Y-m-d'),
            $customer->account_number,
            $customer->full_name,
            $customer->meter_number,
        ])->all();

        return ['title' => 'New Connections', 'columns' => ['Connected', 'Account', 'Name', 'Meter'], 'rows' => $rows];
    }

    private function disconnected(): array
    {
        $rows = Customer::with('connectionEvents')->where('status', 'disconnected')->orderBy('account_number')->get()->map(function ($customer) {
            $event = $customer->connectionEvents->firstWhere('action', 'disconnected');

            return [
                $customer->account_number,
                $customer->full_name,
                $event?->event_date?->format('Y-m-d'),
                $event?->reason,
                'LKR '.number_format($customer->getOutstandingBalance(), 2),
            ];
        })->all();

        return ['title' => 'Disconnected Customers', 'columns' => ['Account', 'Name', 'Disconnected', 'Reason', 'Outstanding'], 'rows' => $rows];
    }

    private function usageAlerts(): array
    {
        $rows = [];
        $customers = Customer::with(['waterMeters.meterReadings' => fn ($query) => $query->orderByDesc('reading_date')->limit(4)])->limit(400)->get();

        foreach ($customers as $customer) {
            foreach ($customer->waterMeters as $meter) {
                $readings = $meter->meterReadings;
                $latest = $readings->first();
                if (!$latest) {
                    continue;
                }
                $previous = $readings->slice(1)->avg('consumption');
                $reason = null;
                if ((float) $latest->consumption === 0.0 && $previous > 0) {
                    $reason = 'Zero consumption';
                } elseif ($previous > 0 && $latest->consumption > ($previous * 2)) {
                    $reason = 'Sudden high reading';
                } elseif (in_array($latest->meter_condition, ['damaged', 'needs_repair', 'leaking'], true)) {
                    $reason = 'Possible leak or fault';
                }
                if ($reason) {
                    $rows[] = [$customer->account_number, $customer->full_name, $meter->meter_number, $latest->consumption, $reason];
                }
            }
        }

        return ['title' => 'Usage Alerts', 'columns' => ['Account', 'Name', 'Meter', 'Latest units', 'Alert'], 'rows' => $rows, 'notes' => 'A sudden high reading is more than double the average of the previous readings.'];
    }

    private function meterFaults(): array
    {
        $rows = WaterMeter::with('customer')->whereIn('status', ['faulty', 'replaced'])->orWhereHas('meterReadings', function ($query) {
            $query->whereIn('meter_condition', ['damaged', 'needs_repair', 'leaking']);
        })->limit(300)->get()->map(fn ($meter) => [
            $meter->meter_number,
            $meter->customer?->full_name,
            $meter->status,
            $meter->meterReadings()->latest('reading_date')->value('meter_condition'),
        ])->all();

        return ['title' => 'Meter Faults', 'columns' => ['Meter', 'Customer', 'Meter status', 'Latest condition'], 'rows' => $rows];
    }

    private function billingSummary(Carbon $start, Carbon $end): array
    {
        $bills = Bill::whereBetween('bill_date', [$start, $end])->where('status', '!=', 'cancelled');
        $billed = (clone $bills)->sum('total_amount');
        $collected = Payment::whereBetween('paid_on', [$start, $end])->sum('amount');

        return [
            'title' => 'Billing Summary',
            'columns' => ['Item', 'Amount'],
            'rows' => [
                ['Bills', (clone $bills)->count()],
                ['Billed', 'LKR '.number_format($billed, 2)],
                ['Collected', 'LKR '.number_format($collected, 2)],
                ['Still outstanding', 'LKR '.number_format((clone $bills)->sum('balance_amount'), 2)],
            ],
        ];
    }

    private function dailyCollection(string $date): array
    {
        $rows = Payment::with(['customer', 'bill', 'receiver'])->whereDate('paid_on', $date)->orderBy('id')->get()->map(fn ($payment) => [
            $payment->receipt_number,
            $payment->customer?->full_name,
            $payment->bill?->bill_number,
            'LKR '.number_format($payment->amount, 2),
            str_replace('_', ' ', $payment->payment_method),
            $payment->receiver?->name,
        ])->all();

        return ['title' => 'Daily Collection', 'columns' => ['Receipt', 'Customer', 'Bill', 'Amount', 'Method', 'Received by'], 'rows' => $rows];
    }

    private function monthlyCollection(Carbon $start, Carbon $end): array
    {
        $rows = Payment::whereBetween('paid_on', [$start, $end])
            ->selectRaw('payment_method, COUNT(*) as receipts, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($row) => [str_replace('_', ' ', $row->payment_method), $row->receipts, 'LKR '.number_format($row->total, 2)])
            ->all();

        return ['title' => 'Monthly Collection', 'columns' => ['Method', 'Receipts', 'Total'], 'rows' => $rows];
    }

    private function collectionEfficiency(Carbon $start, Carbon $end): array
    {
        $billed = (float) Bill::whereBetween('bill_date', [$start, $end])->where('status', '!=', 'cancelled')->sum('total_amount');
        $collected = (float) Payment::whereBetween('paid_on', [$start, $end])->sum('amount');
        $percent = $billed > 0 ? round(($collected / $billed) * 100, 1) : 0;

        return [
            'title' => 'Collection Efficiency',
            'columns' => ['Item', 'Value'],
            'rows' => [
                ['Billed', 'LKR '.number_format($billed, 2)],
                ['Collected', 'LKR '.number_format($collected, 2)],
                ['Efficiency', $percent.'%'],
            ],
        ];
    }

    private function ageAnalysis(): array
    {
        $bills = Bill::with('customer')->where('balance_amount', '>', 0)->whereNotIn('status', ['cancelled', 'draft', 'paid'])->get();
        $buckets = ['0-30 days' => 0, '31-60 days' => 0, '61-90 days' => 0, 'Over 90 days' => 0];
        foreach ($bills as $bill) {
            $days = $bill->due_date ? $bill->due_date->diffInDays(now(), false) : 0;
            $key = match (true) {
                $days <= 30 => '0-30 days',
                $days <= 60 => '31-60 days',
                $days <= 90 => '61-90 days',
                default => 'Over 90 days',
            };
            $buckets[$key] += (float) $bill->balance_amount;
        }
        $rows = [];
        foreach ($buckets as $label => $amount) {
            $rows[] = [$label, 'LKR '.number_format($amount, 2)];
        }

        return ['title' => 'Outstanding Age Analysis', 'columns' => ['Age from due date', 'Balance'], 'rows' => $rows];
    }
}
