<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with system overview.
     */
    public function index(): View
    {
        // Customer Statistics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::active()->count();
        $newCustomersThisMonth = Customer::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Meter Statistics
        $totalMeters = WaterMeter::count();
        $activeMeters = WaterMeter::active()->count();
        $metersNeedingMaintenance = WaterMeter::dueForMaintenance()->count();

        // Reading Statistics
        $readingsThisMonth = MeterReading::whereMonth('reading_date', Carbon::now()->month)
            ->whereYear('reading_date', Carbon::now()->year)
            ->count();
        $pendingReadings = MeterReading::pending()->count();

        // Bill Statistics
        $totalBillsThisMonth = Bill::whereMonth('bill_date', Carbon::now()->month)
            ->whereYear('bill_date', Carbon::now()->year)
            ->count();
        $overdueBills = Bill::overdue()->count();
        $totalRevenue = Bill::paid()->sum('total_amount');
        $monthlyRevenue = Bill::paid()
            ->whereMonth('bill_date', Carbon::now()->month)
            ->whereYear('bill_date', Carbon::now()->year)
            ->sum('total_amount');
        $outstandingAmount = Bill::unpaid()->sum('balance_amount');
        $monthlyBilled = Bill::whereMonth('bill_date', Carbon::now()->month)
            ->whereYear('bill_date', Carbon::now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
        $monthlyCollected = \App\Models\Payment::whereMonth('paid_on', Carbon::now()->month)
            ->whereYear('paid_on', Carbon::now()->year)
            ->sum('amount');
        $collectionPercent = $monthlyBilled > 0 ? round(($monthlyCollected / $monthlyBilled) * 100, 1) : 0;
        $disconnectedCustomers = Customer::where('status', 'disconnected')->count();

        // Recent Activities
        $recentCustomers = Customer::latest()->limit(5)->get();
        $recentReadings = MeterReading::with(['waterMeter.customer'])
            ->latest('reading_date')
            ->limit(5)
            ->get();
        $recentBills = Bill::with(['customer'])
            ->latest('bill_date')
            ->limit(5)
            ->get();

        $chartYear = Carbon::now()->year;
        $yearChart = $this->currentYearMonths($chartYear);

        // Alerts and notifications
        $alerts = [
            'overdue_bills' => $overdueBills,
            'maintenance_due' => $metersNeedingMaintenance,
            'pending_readings' => $pendingReadings
        ];

        // Billing countdown data
        $defaultBillingDay = \App\Models\SystemConfiguration::getDefaultBillingDay();
        $nextBillingDate = $this->calculateNextSystemBillingDate($defaultBillingDay);
        $billingCountdown = [
            'next_billing_date' => $nextBillingDate,
            'days_until_billing' => Carbon::now()->diffInDays($nextBillingDate, false),
            'default_billing_day' => $defaultBillingDay,
            'customers_due_today' => Customer::where('next_billing_date', Carbon::now()->format('Y-m-d'))->count(),
            'customers_overdue' => Customer::where('next_billing_date', '<', Carbon::now()->format('Y-m-d'))
                                         ->whereNotNull('next_billing_date')->count()
        ];

        return view('dashboard', compact(
            'totalCustomers',
            'activeCustomers', 
            'newCustomersThisMonth',
            'totalMeters',
            'activeMeters',
            'metersNeedingMaintenance',
            'readingsThisMonth',
            'pendingReadings',
            'totalBillsThisMonth',
            'overdueBills',
            'totalRevenue',
            'monthlyRevenue',
            'monthlyBilled',
            'collectionPercent',
            'disconnectedCustomers',
            'outstandingAmount',
            'recentCustomers',
            'recentReadings',
            'recentBills',
            'chartYear',
            'yearChart',
            'alerts',
            'billingCountdown'
        ));
    }

    /**
     * Generate consumption report.
     */
    public function consumptionReport(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $consumptionData = MeterReading::with(['waterMeter.customer.customerType'])
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->orderBy('reading_date', 'desc')
            ->paginate(25);

        $totalConsumption = MeterReading::whereBetween('reading_date', [$startDate, $endDate])
            ->sum('consumption');

        $averageConsumption = MeterReading::whereBetween('reading_date', [$startDate, $endDate])
            ->avg('consumption');

        // Consumption by customer type
        $consumptionByType = DB::table('meter_readings')
            ->join('water_meters', 'meter_readings.water_meter_id', '=', 'water_meters.id')
            ->join('customers', 'water_meters.customer_id', '=', 'customers.id')
            ->join('customer_types', 'customers.customer_type_id', '=', 'customer_types.id')
            ->whereBetween('meter_readings.reading_date', [$startDate, $endDate])
            ->groupBy('customer_types.id', 'customer_types.name')
            ->select(
                'customer_types.name as customer_type',
                DB::raw('SUM(meter_readings.consumption) as total_consumption'),
                DB::raw('AVG(meter_readings.consumption) as avg_consumption'),
                DB::raw('COUNT(meter_readings.id) as reading_count')
            )
            ->get();

        return view('reports.consumption', compact(
            'consumptionData',
            'totalConsumption',
            'averageConsumption',
            'consumptionByType',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate revenue report.
     */
    public function revenueReport(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $bills = Bill::with(['customer.customerType'])
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->orderBy('bill_date', 'desc')
            ->paginate(25);

        $totalRevenue = Bill::whereBetween('bill_date', [$startDate, $endDate])
            ->sum('total_amount');

        $paidRevenue = Bill::paid()
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->sum('total_amount');

        $unpaidRevenue = Bill::unpaid()
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->sum('balance_amount');

        // Revenue by customer type
        $revenueByType = DB::table('bills')
            ->join('customers', 'bills.customer_id', '=', 'customers.id')
            ->join('customer_types', 'customers.customer_type_id', '=', 'customer_types.id')
            ->whereBetween('bills.bill_date', [$startDate, $endDate])
            ->groupBy('customer_types.id', 'customer_types.name')
            ->select(
                'customer_types.name as customer_type',
                DB::raw('SUM(bills.total_amount) as total_revenue'),
                DB::raw('SUM(bills.paid_amount) as paid_revenue'),
                DB::raw('SUM(bills.balance_amount) as outstanding_revenue'),
                DB::raw('COUNT(bills.id) as bill_count')
            )
            ->get();

        return view('reports.revenue', compact(
            'bills',
            'totalRevenue',
            'paidRevenue',
            'unpaidRevenue',
            'revenueByType',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate overdue bills report.
     */
    public function overdueReport(): View
    {
        $overdueBills = Bill::overdue()
            ->with(['customer', 'waterMeter'])
            ->orderBy('due_date', 'asc')
            ->paginate(25);

        $totalOverdueAmount = Bill::overdue()->sum('balance_amount');
        $overdueCount = Bill::overdue()->count();

        // Group by overdue periods
        $overdueByPeriod = [
            '1-30' => Bill::overdue()
                ->whereRaw('DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 30')
                ->sum('balance_amount'),
            '31-60' => Bill::overdue()
                ->whereRaw('DATEDIFF(CURDATE(), due_date) BETWEEN 31 AND 60')
                ->sum('balance_amount'),
            '61-90' => Bill::overdue()
                ->whereRaw('DATEDIFF(CURDATE(), due_date) BETWEEN 61 AND 90')
                ->sum('balance_amount'),
            '90+' => Bill::overdue()
                ->whereRaw('DATEDIFF(CURDATE(), due_date) > 90')
                ->sum('balance_amount')
        ];

        return view('reports.overdue', compact(
            'overdueBills',
            'totalOverdueAmount',
            'overdueCount',
            'overdueByPeriod'
        ));
    }

    /**
     * Billed, collected, draft, and usage figures for each month of a year.
     */
    private function currentYearMonths(int $year): array
    {
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = [
                'label' => Carbon::create($year, $month, 1)->format('M'),
                'billed' => 0.0,
                'collected' => 0.0,
                'draft' => 0.0,
                'units' => 0.0,
                'readings' => 0,
                'bills' => 0,
            ];
        }

        $billRows = Bill::query()
            ->whereBetween('bill_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('MONTH(bill_date) as month_number')
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN total_amount ELSE 0 END) as draft_amount")
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 0 ELSE total_amount END) as billed_amount")
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 0 ELSE 1 END) as bill_count")
            ->groupByRaw('MONTH(bill_date)')
            ->get();

        foreach ($billRows as $row) {
            $month = (int) $row->month_number;
            $months[$month]['draft'] = round((float) $row->draft_amount, 2);
            $months[$month]['billed'] = round((float) $row->billed_amount, 2);
            $months[$month]['bills'] = (int) $row->bill_count;
        }

        $paymentRows = Payment::query()
            ->whereBetween('paid_on', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('MONTH(paid_on) as month_number, SUM(amount) as collected')
            ->groupByRaw('MONTH(paid_on)')
            ->get();

        foreach ($paymentRows as $row) {
            $months[(int) $row->month_number]['collected'] = round((float) $row->collected, 2);
        }

        $readingRows = MeterReading::query()
            ->whereBetween('reading_date', [$start, $end])
            ->selectRaw('MONTH(reading_date) as month_number, SUM(consumption) as units, COUNT(*) as readings')
            ->groupByRaw('MONTH(reading_date)')
            ->get();

        foreach ($readingRows as $row) {
            $months[(int) $row->month_number]['units'] = round((float) $row->units, 2);
            $months[(int) $row->month_number]['readings'] = (int) $row->readings;
        }

        return array_values($months);
    }

    /**
     * Calculate next system billing date based on default billing day
     */
    private function calculateNextSystemBillingDate(int $billingDay): Carbon
    {
        $today = Carbon::now();
        $currentMonth = $today->month;
        $currentYear = $today->year;
        
        try {
            // Try to create billing date for current month
            $billingDate = Carbon::create($currentYear, $currentMonth, $billingDay);
            
            // If billing date has passed this month, move to next month
            if ($billingDate->isPast()) {
                $billingDate = $billingDate->addMonth();
            }
            
            return $billingDate;
        } catch (\Exception $e) {
            // If day doesn't exist in current month (e.g., 31st in February), use last day of month
            $billingDate = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
            
            if ($billingDate->isPast()) {
                $billingDate = $billingDate->addMonth()->endOfMonth();
            }
            
            return $billingDate;
        }
    }
}
