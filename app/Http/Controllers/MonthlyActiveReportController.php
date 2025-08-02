<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyActiveReportController extends Controller
{
    /**
     * Display monthly active connections report
     */
    public function index(Request $request)
    {
        // Fixed rate per active connection
        $ratePerConnection = 20.00; // Rs 20.00 per active connection

        // Get date range - default to last 12 months
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now();
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subMonths(11);

        // Ensure we're working with month boundaries
        $startDate = $startDate->startOfMonth();
        $endDate = $endDate->endOfMonth();

        // Get monthly active connections data
        $monthlyData = $this->getMonthlyActiveConnections($startDate, $endDate, $ratePerConnection);
        
        // Get current month details
        $currentMonth = $this->getCurrentMonthDetails($ratePerConnection);
        
        // Get summary statistics
        $summaryStats = $this->getSummaryStatistics($monthlyData);

        return view('reports.monthly-active', compact(
            'monthlyData', 
            'currentMonth', 
            'summaryStats', 
            'startDate', 
            'endDate',
            'ratePerConnection'
        ));
    }

    /**
     * Get detailed active customers for a specific month
     */
    public function getMonthlyActiveCustomers(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('n'));
        $ratePerConnection = 20.00;
        
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Get active customers with their details
        $activeCustomers = Customer::where(function ($query) use ($monthStart, $monthEnd) {
            $query->whereHas('bills', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('created_at', [$monthStart, $monthEnd]);
            })->orWhereHas('meterReadings', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('reading_date', [$monthStart, $monthEnd]);
            })->orWhereHas('waterMeters', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('installation_date', [$monthStart, $monthEnd]);
            });
        })->with(['waterMeters', 'bills' => function($q) use ($monthStart, $monthEnd) {
            $q->whereBetween('created_at', [$monthStart, $monthEnd]);
        }])->get();

        // Calculate disconnection charges for each customer
        $customersWithCharges = $activeCustomers->map(function ($customer) use ($monthStart, $ratePerConnection) {
            // Find inactive months before this active month
            $lastActiveMonth = $this->getLastActiveMonth($customer, $monthStart);
            $inactiveMonths = $this->calculateInactiveMonths($lastActiveMonth, $monthStart);
            
            $disconnectionCharge = $inactiveMonths * $ratePerConnection;
            $monthlyCharge = $ratePerConnection;
            $totalCharge = $monthlyCharge + $disconnectionCharge;

            return [
                'customer' => $customer,
                'monthly_charge' => $monthlyCharge,
                'inactive_months' => $inactiveMonths,
                'disconnection_charge' => $disconnectionCharge,
                'total_charge' => $totalCharge,
                'last_active' => $lastActiveMonth,
                'meter_count' => $customer->waterMeters->count(),
                'bills_this_month' => $customer->bills->count()
            ];
        });

        $monthName = $monthStart->format('F Y');
        $totalCustomers = $customersWithCharges->count();
        $totalMonthlyCharges = $customersWithCharges->sum('monthly_charge');
        $totalDisconnectionCharges = $customersWithCharges->sum('disconnection_charge');
        $grandTotal = $customersWithCharges->sum('total_charge');

        return view('reports.monthly-active-details', compact(
            'customersWithCharges',
            'monthName',
            'year',
            'month',
            'totalCustomers',
            'totalMonthlyCharges', 
            'totalDisconnectionCharges',
            'grandTotal',
            'ratePerConnection'
        ));
    }

    /**
     * Download detailed active customers for a specific month
     */
    public function downloadMonthlyActiveCustomers(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('n'));
        $ratePerConnection = 20.00;
        
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Get active customers with their details (same logic as above)
        $activeCustomers = Customer::where(function ($query) use ($monthStart, $monthEnd) {
            $query->whereHas('bills', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('created_at', [$monthStart, $monthEnd]);
            })->orWhereHas('meterReadings', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('reading_date', [$monthStart, $monthEnd]);
            })->orWhereHas('waterMeters', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('installation_date', [$monthStart, $monthEnd]);
            });
        })->with(['waterMeters', 'bills' => function($q) use ($monthStart, $monthEnd) {
            $q->whereBetween('created_at', [$monthStart, $monthEnd]);
        }])->get();

        // Calculate charges for each customer
        $customersWithCharges = $activeCustomers->map(function ($customer) use ($monthStart, $ratePerConnection) {
            $lastActiveMonth = $this->getLastActiveMonth($customer, $monthStart);
            $inactiveMonths = $this->calculateInactiveMonths($lastActiveMonth, $monthStart);
            
            return [
                'customer_id' => $customer->id,
                'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'monthly_charge' => $ratePerConnection,
                'inactive_months' => $inactiveMonths,
                'disconnection_charge' => $inactiveMonths * $ratePerConnection,
                'total_charge' => $ratePerConnection + ($inactiveMonths * $ratePerConnection),
                'last_active' => $lastActiveMonth ? $lastActiveMonth->format('M Y') : 'First Time',
                'meter_count' => $customer->waterMeters->count(),
                'bills_this_month' => $customer->bills->count()
            ];
        });

        $filename = 'active_customers_' . $monthStart->format('Y_m') . '_with_charges.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($customersWithCharges, $monthStart, $ratePerConnection) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Customer ID',
                'Customer Name', 
                'Email',
                'Phone',
                'Address',
                'Monthly Charge (Rs.)',
                'Inactive Months',
                'Disconnection Charge (Rs.)',
                'Total Charge (Rs.)',
                'Last Active Month',
                'Active Meters',
                'Bills This Month',
                'Month',
                'Rate Per Connection (Rs.)'
            ]);

            foreach ($customersWithCharges as $data) {
                fputcsv($file, [
                    $data['customer_id'],
                    $data['customer_name'],
                    $data['email'],
                    $data['phone'],
                    $data['address'],
                    number_format($data['monthly_charge'], 2),
                    $data['inactive_months'],
                    number_format($data['disconnection_charge'], 2),
                    number_format($data['total_charge'], 2),
                    $data['last_active'],
                    $data['meter_count'],
                    $data['bills_this_month'],
                    $monthStart->format('F Y'),
                    number_format($ratePerConnection, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export monthly active connections report
     */
    public function export(Request $request)
    {
        $ratePerConnection = 20.00; // Rs 20.00 per active connection
        
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now();
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subMonths(11);

        $startDate = $startDate->startOfMonth();
        $endDate = $endDate->endOfMonth();

        $monthlyData = $this->getMonthlyActiveConnections($startDate, $endDate, $ratePerConnection);

        $filename = 'monthly_active_connections_billing_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($monthlyData, $ratePerConnection) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Month',
                'Year',
                'Active Customers',
                'System Billing Amount (Rs.)',
                'Rate Per Connection (Rs.)',
                'Active Meters',
                'Bills Generated',
                'Paid Bills',
                'Water Revenue (Rs.)',
                'Payment Rate (%)'
            ]);

            foreach ($monthlyData as $data) {
                fputcsv($file, [
                    $data['month_name'],
                    $data['year'],
                    $data['active_customers'],
                    number_format($data['system_billing_amount'], 2),
                    number_format($ratePerConnection, 2),
                    $data['active_meters'],
                    $data['bills_generated'],
                    $data['paid_bills'],
                    number_format($data['revenue'], 2),
                    number_format($data['payment_rate'], 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get monthly active connections data
     */
    private function getMonthlyActiveConnections($startDate, $endDate, $ratePerConnection = 20.00)
    {
        $monthlyData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            // Active customers (customers with activity in the month)
            $activeCustomers = Customer::where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereHas('bills', function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('created_at', [$monthStart, $monthEnd]);
                })->orWhereHas('meterReadings', function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('reading_date', [$monthStart, $monthEnd]);
                })->orWhereHas('waterMeters', function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('installation_date', [$monthStart, $monthEnd]);
                });
            })->count();

            // Active meters (meters with readings or bills in the month)
            $activeMeters = WaterMeter::where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereHas('meterReadings', function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('reading_date', [$monthStart, $monthEnd]);
                })->orWhereHas('customer.bills', function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('created_at', [$monthStart, $monthEnd]);
                })->orWhere(function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('installation_date', [$monthStart, $monthEnd]);
                });
            })->count();

            // Bills data for the month
            $billsData = Bill::whereBetween('created_at', [$monthStart, $monthEnd])
                ->selectRaw('
                    COUNT(*) as bills_generated,
                    SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_bills,
                    SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as revenue
                ')
                ->first();

            // Calculate rates
            $connectionRate = $activeCustomers > 0 ? ($activeMeters / max($activeCustomers, 1)) * 100 : 0;
            $paymentRate = $billsData->bills_generated > 0 ? ($billsData->paid_bills / $billsData->bills_generated) * 100 : 0;

            // Calculate system billing amount (Active Customers × Rate)
            $systemBillingAmount = $activeCustomers * $ratePerConnection;

            $monthlyData[] = [
                'month' => $current->month,
                'year' => $current->year,
                'month_name' => $current->format('F'),
                'month_year' => $current->format('M Y'),
                'active_customers' => $activeCustomers,
                'active_meters' => $activeMeters,
                'bills_generated' => $billsData->bills_generated ?? 0,
                'paid_bills' => $billsData->paid_bills ?? 0,
                'revenue' => $billsData->revenue ?? 0,
                'connection_rate' => round($connectionRate, 2),
                'payment_rate' => round($paymentRate, 2),
                'system_billing_amount' => $systemBillingAmount,
                'date' => $current->copy()
            ];

            $current->addMonth();
        }

        return collect($monthlyData);
    }

    /**
     * Get current month details
     */
    private function getCurrentMonthDetails($ratePerConnection = 20.00)
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        // Current month active customers
        $activeCustomers = Customer::where(function ($query) use ($monthStart, $monthEnd) {
            $query->whereHas('bills', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('created_at', [$monthStart, $monthEnd]);
            })->orWhereHas('meterReadings', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('reading_date', [$monthStart, $monthEnd]);
            });
        })->count();

        // Total registered customers
        $totalCustomers = Customer::count();

        // New customers this month
        $newCustomers = Customer::whereBetween('created_at', [$monthStart, $monthEnd])->count();

        // Active meters this month
        $activeMeters = WaterMeter::whereHas('meterReadings', function ($q) use ($monthStart, $monthEnd) {
            $q->whereBetween('reading_date', [$monthStart, $monthEnd]);
        })->count();

        // Calculate current month billing amount
        $currentMonthBilling = $activeCustomers * $ratePerConnection;

        return [
            'month_name' => $now->format('F Y'),
            'active_customers' => $activeCustomers,
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'active_meters' => $activeMeters,
            'activity_rate' => $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100, 2) : 0,
            'current_month_billing' => $currentMonthBilling
        ];
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStatistics($monthlyData)
    {
        if ($monthlyData->isEmpty()) {
            return [
                'avg_active_customers' => 0,
                'max_active_customers' => 0,
                'total_revenue' => 0,
                'avg_payment_rate' => 0,
                'growth_rate' => 0,
                'total_system_billing' => 0,
                'avg_monthly_billing' => 0
            ];
        }

        $avgActiveCustomers = round($monthlyData->avg('active_customers'), 0);
        $maxActiveCustomers = $monthlyData->max('active_customers');
        $totalRevenue = $monthlyData->sum('revenue');
        $avgPaymentRate = round($monthlyData->avg('payment_rate'), 2);
        $totalSystemBilling = $monthlyData->sum('system_billing_amount');
        $avgMonthlyBilling = round($monthlyData->avg('system_billing_amount'), 2);

        // Calculate growth rate (compare first and last month)
        $firstMonth = $monthlyData->first();
        $lastMonth = $monthlyData->last();
        $growthRate = 0;
        
        if ($firstMonth['active_customers'] > 0 && $monthlyData->count() > 1) {
            $growthRate = round((($lastMonth['active_customers'] - $firstMonth['active_customers']) / $firstMonth['active_customers']) * 100, 2);
        }

        return [
            'avg_active_customers' => $avgActiveCustomers,
            'max_active_customers' => $maxActiveCustomers,
            'total_revenue' => $totalRevenue,
            'avg_payment_rate' => $avgPaymentRate,
            'growth_rate' => $growthRate,
            'total_system_billing' => $totalSystemBilling,
            'avg_monthly_billing' => $avgMonthlyBilling
        ];
    }

    /**
     * Get the last active month for a customer before a given date
     */
    private function getLastActiveMonth($customer, $beforeDate)
    {
        // Look for the most recent activity before the given date
        $lastBill = $customer->bills()
            ->where('created_at', '<', $beforeDate)
            ->orderBy('created_at', 'desc')
            ->first();

        $lastReading = $customer->meterReadings()
            ->where('reading_date', '<', $beforeDate)
            ->orderBy('reading_date', 'desc')
            ->first();

        $lastMeterInstall = $customer->waterMeters()
            ->where('installation_date', '<', $beforeDate)
            ->orderBy('installation_date', 'desc')
            ->first();

        // Find the most recent activity
        $dates = array_filter([
            $lastBill ? Carbon::parse($lastBill->created_at) : null,
            $lastReading ? Carbon::parse($lastReading->reading_date) : null,
            $lastMeterInstall ? Carbon::parse($lastMeterInstall->installation_date) : null
        ]);

        if (empty($dates)) {
            return null; // No previous activity found
        }

        return collect($dates)->max()->startOfMonth();
    }

    /**
     * Calculate inactive months between last active date and current date
     */
    private function calculateInactiveMonths($lastActiveMonth, $currentMonth)
    {
        if (!$lastActiveMonth) {
            return 0; // First time customer, no disconnection charge
        }

        $current = $currentMonth->copy()->startOfMonth();
        $lastActive = $lastActiveMonth->copy()->startOfMonth();

        // Calculate months between (excluding the current month)
        $monthsDifference = $current->diffInMonths($lastActive);
        
        // If difference is 1, they were active last month, so no disconnection charge
        // If difference is 2, they missed 1 month, so charge for 1 month
        // If difference is 3, they missed 2 months, so charge for 2 months, etc.
        return max(0, $monthsDifference - 1);
    }
}