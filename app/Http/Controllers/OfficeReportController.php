<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\WalletEntry;
use App\Models\WaterMeter;
use App\Support\CollectionTotals;
use App\Support\TablePage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $usesMonth = in_array($report, [
            'new-connections',
            'billing-summary',
            'monthly-collection',
            'collection-efficiency',
        ], true);
        $usesDate = $report === 'daily-collection';

        return view('reports.office', [
            'report' => $report,
            'title' => $data['title'],
            'columns' => $data['columns'],
            'rows' => $data['rows'],
            'month' => $month,
            'date' => $date,
            'notes' => $data['notes'] ?? null,
            'usesMonth' => $usesMonth,
            'usesDate' => $usesDate,
            'canExportCsv' => $report === 'daily-collection',
        ]);
    }

    public function exportDailyCollection(Request $request): StreamedResponse
    {
        $date = $request->input('date', now()->toDateString());
        $cash = CollectionTotals::forDate($date);
        $payments = Payment::with(['customer', 'bill', 'receiver', 'overpaymentEntries'])
            ->whereDate('paid_on', $date)
            ->orderBy('id')
            ->get();

        $filename = 'daily_collection_'.$date.'.csv';

        return response()->streamDownload(function () use ($payments, $cash, $date) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date',
                'Receipt',
                'Account',
                'Customer',
                'Bill',
                'Cash received',
                'Applied to bill',
                'Wallet added',
                'Method',
                'Received by',
                'Paid on',
            ]);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $date,
                    $payment->receipt_number,
                    $payment->customer?->account_number,
                    $payment->customer?->full_name,
                    $payment->bill?->bill_number,
                    number_format($payment->exactPaidAmount(), 2, '.', ''),
                    number_format((float) $payment->amount, 2, '.', ''),
                    number_format($payment->walletAddedAmount(), 2, '.', ''),
                    str_replace('_', ' ', (string) $payment->payment_method),
                    $payment->receiver?->name,
                    optional($payment->paid_on)->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL',
                $cash['receipts'].' receipts',
                '',
                '',
                '',
                number_format($cash['cash'], 2, '.', ''),
                number_format($cash['applied'], 2, '.', ''),
                number_format($cash['overpaid'], 2, '.', ''),
                '',
                '',
                '',
            ]);
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function customerRegister(): array
    {
        $rows = Customer::with(['division', 'customerType'])->orderBy('account_number')->paginate(25)->withQueryString()->through(fn ($customer) => [
            $customer->account_number,
            $this->customerLink($customer),
            $customer->division?->name,
            $customer->meter_number,
            $customer->customerType?->name,
            ucfirst($customer->status),
        ]);

        return ['title' => 'Customer Register', 'columns' => ['Account', 'Name', 'Division', 'Meter', 'Type', 'Status'], 'rows' => $rows];
    }

    private function contacts(): array
    {
        $rows = Customer::orderBy('account_number')->paginate(25)->withQueryString()->through(fn ($customer) => [
            $customer->account_number,
            $this->customerLink($customer),
            $customer->phone,
            $customer->phone_two,
            $customer->address,
        ]);

        return ['title' => 'Contact List', 'columns' => ['Account', 'Name', 'Phone', 'Second phone', 'Address'], 'rows' => $rows];
    }

    private function newConnections(Carbon $start, Carbon $end): array
    {
        $rows = Customer::whereBetween('connection_date', [$start, $end])->orderBy('connection_date')->paginate(25)->withQueryString()->through(fn ($customer) => [
            $customer->connection_date?->format('Y-m-d'),
            $customer->account_number,
            $this->customerLink($customer),
            $customer->meter_number,
        ]);

        return ['title' => 'New Connections', 'columns' => ['Connected', 'Account', 'Name', 'Meter'], 'rows' => $rows];
    }

    private function disconnected(): array
    {
        $rows = Customer::with('connectionEvents')->where('status', 'disconnected')->orderBy('account_number')->paginate(25)->withQueryString()->through(function ($customer) {
            $event = $customer->connectionEvents->firstWhere('action', 'disconnected');

            return [
                $customer->account_number,
                $this->customerLink($customer),
                $event?->event_date?->format('Y-m-d'),
                $event?->reason,
                'LKR '.number_format($customer->getOutstandingBalance(), 2),
                'LKR '.number_format((float) $customer->wallet_balance, 2),
            ];
        });

        return [
            'title' => 'Disconnected Customers',
            'columns' => ['Account', 'Name', 'Disconnected', 'Reason', 'Outstanding', 'Wallet'],
            'rows' => $rows,
        ];
    }

    private function usageAlerts(): array
    {
        $rows = [];
        $customers = Customer::with(['waterMeters.meterReadings' => fn ($query) => $query->orderByDesc('reading_date')->limit(4)])
            ->whereHas('waterMeters.meterReadings', fn ($query) => $query->where('reading_date', '>=', now()->subMonths(6)))
            ->orderBy('account_number')
            ->get();

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
                    $rows[] = [
                        $customer->account_number,
                        $this->customerLink($customer),
                        $meter->meter_number,
                        $this->formatUnits($latest->consumption),
                        $reason,
                    ];
                }
            }
        }

        return [
            'title' => 'Usage Alerts',
            'columns' => ['Account', 'Name', 'Meter', 'Latest units', 'Alert'],
            'rows' => TablePage::collection(collect($rows)),
            'notes' => 'A sudden high reading is more than double the average of the previous readings.',
        ];
    }

    private function meterFaults(): array
    {
        $rows = WaterMeter::with('customer')
            ->where(function ($query) {
                $query->whereIn('status', ['faulty', 'replaced'])
                    ->orWhereHas('meterReadings', function ($readings) {
                        $readings->whereIn('meter_condition', ['damaged', 'needs_repair', 'leaking']);
                    });
            })
            ->orderBy('meter_number')
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($meter) => [
                $meter->meter_number,
                $this->customerLink($meter->customer),
                $meter->status,
                $meter->meterReadings()->latest('reading_date')->value('meter_condition'),
            ]);

        return ['title' => 'Meter Faults', 'columns' => ['Meter', 'Customer', 'Meter status', 'Latest condition'], 'rows' => $rows];
    }

    private function billingSummary(Carbon $start, Carbon $end): array
    {
        $bills = Bill::whereBetween('bill_date', [$start, $end])->where('status', '!=', 'cancelled');
        $billed = (clone $bills)->sum('total_amount');
        $cash = CollectionTotals::forPeriod($start, $end);

        return [
            'title' => 'Billing Summary',
            'columns' => ['Item', 'Amount'],
            'rows' => [
                ['Bills', (clone $bills)->count()],
                ['Billed', 'LKR '.number_format($billed, 2)],
                ['Cash received (exact)', 'LKR '.number_format($cash['cash'], 2)],
                ['Applied to bills', 'LKR '.number_format($cash['applied'], 2)],
                ['Overpaid to wallet', 'LKR '.number_format($cash['overpaid'], 2)],
                ['Still outstanding', 'LKR '.number_format((clone $bills)->sum('balance_amount'), 2)],
            ],
            'notes' => 'Cash received includes bill payments plus overpayments added to wallets.',
        ];
    }

    private function dailyCollection(string $date): array
    {
        $cash = CollectionTotals::forDate($date);
        $rows = Payment::with(['customer', 'bill', 'receiver', 'overpaymentEntries'])
            ->whereDate('paid_on', $date)
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($payment) => [
                $payment->receipt_number,
                $this->customerLink($payment->customer),
                $this->billLink($payment->bill),
                'LKR '.number_format($payment->exactPaidAmount(), 2),
                'LKR '.number_format((float) $payment->amount, 2),
                'LKR '.number_format($payment->walletAddedAmount(), 2),
                str_replace('_', ' ', $payment->payment_method),
                $payment->receiver?->name,
            ]);

        return [
            'title' => 'Daily Collection',
            'columns' => ['Receipt', 'Customer', 'Bill', 'Cash received', 'Applied to bill', 'Wallet added', 'Method', 'Received by'],
            'rows' => $rows,
            'notes' => 'Day total cash received: LKR '.number_format($cash['cash'], 2)
                .' (applied LKR '.number_format($cash['applied'], 2)
                .' · wallet LKR '.number_format($cash['overpaid'], 2)
                .' · '.$cash['receipts'].' receipts).',
        ];
    }

    private function monthlyCollection(Carbon $start, Carbon $end): array
    {
        $methodRows = Payment::whereBetween('paid_on', [$start, $end])
            ->selectRaw('payment_method, COUNT(*) as receipts, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($row) => [
                str_replace('_', ' ', $row->payment_method),
                $row->receipts,
                'LKR '.number_format((float) $row->total, 2),
            ])
            ->all();

        $overpaid = (float) WalletEntry::query()
            ->livingOverpayments()
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        if ($overpaid > 0) {
            $methodRows[] = ['wallet overpayment', '—', 'LKR '.number_format($overpaid, 2)];
        }

        $cash = CollectionTotals::forPeriod($start, $end);
        $methodRows[] = ['TOTAL CASH RECEIVED', $cash['receipts'], 'LKR '.number_format($cash['cash'], 2)];

        return [
            'title' => 'Monthly Collection',
            'columns' => ['Method', 'Receipts', 'Total'],
            'rows' => $methodRows,
            'notes' => 'Method totals are amounts applied on receipts. Wallet overpayment and TOTAL CASH RECEIVED include exact cash.',
        ];
    }

    private function collectionEfficiency(Carbon $start, Carbon $end): array
    {
        $billed = (float) Bill::whereBetween('bill_date', [$start, $end])->where('status', '!=', 'cancelled')->sum('total_amount');
        $cash = CollectionTotals::forPeriod($start, $end);
        $percent = $billed > 0 ? round(($cash['cash'] / $billed) * 100, 1) : 0;

        return [
            'title' => 'Collection Efficiency',
            'columns' => ['Item', 'Value'],
            'rows' => [
                ['Billed', 'LKR '.number_format($billed, 2)],
                ['Cash received (exact)', 'LKR '.number_format($cash['cash'], 2)],
                ['Applied to bills', 'LKR '.number_format($cash['applied'], 2)],
                ['Overpaid to wallet', 'LKR '.number_format($cash['overpaid'], 2)],
                ['Efficiency', $percent.'%'],
            ],
            'notes' => 'Efficiency uses exact cash received (bill payments + wallet overpayments) ÷ billed.',
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

    private function formatUnits(mixed $value): string
    {
        $units = (float) $value;

        return fmod(abs($units), 1.0) < 0.00001
            ? number_format($units, 0)
            : rtrim(rtrim(number_format($units, 2, '.', ','), '0'), '.');
    }

    private function customerLink(?Customer $customer): array|string
    {
        if (!$customer) {
            return '—';
        }

        return [
            'text' => $customer->full_name,
            'url' => route('customers.show', $customer),
        ];
    }

    private function billLink(?Bill $bill): array|string
    {
        if (!$bill) {
            return '—';
        }

        return [
            'text' => $bill->bill_number,
            'url' => route('bills.show', $bill),
        ];
    }
}
