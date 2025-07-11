<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillController extends Controller
{
    /**
     * Display a listing of bills with advanced filtering and search.
     */
    public function index(Request $request): View
    {
        $query = Bill::with(['customer', 'waterMeter', 'meterReading']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('account_number', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('bill_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('bill_date', '<=', $request->date_to);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Amount range filter
        if ($request->filled('amount_from')) {
            $query->where('total_amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('total_amount', '<=', $request->amount_to);
        }

        $bills = $query->latest('bill_date')->paginate(15);

        // Statistics
        $totalBills = Bill::count();
        $paidBills = Bill::paid()->count();
        $overdueBills = Bill::overdue()->count();
        $totalRevenue = Bill::paid()->sum('total_amount');
        $outstandingAmount = Bill::unpaid()->sum('balance_amount');
        $monthlyRevenue = Bill::paid()
            ->whereMonth('bill_date', Carbon::now()->month)
            ->whereYear('bill_date', Carbon::now()->year)
            ->sum('total_amount');

        $customers = Customer::active()->orderBy('first_name')->get();

        return view('bills.index', compact(
            'bills',
            'totalBills',
            'paidBills',
            'overdueBills',
            'totalRevenue',
            'outstandingAmount',
            'monthlyRevenue',
            'customers'
        ));
    }

    /**
     * Show the form for creating a new bill.
     */
    public function create(): View
    {
        $customers = Customer::active()->with('waterMeters')->orderBy('first_name')->get();
        $waterMeters = WaterMeter::active()->with('customer')->orderBy('meter_number')->get();
        
        return view('bills.create', compact('customers', 'waterMeters'));
    }

    /**
     * Store a newly created bill in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'water_meter_id' => 'required|exists:water_meters,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after:bill_date',
            'billing_period_from' => 'required|date',
            'billing_period_to' => 'required|date|after:billing_period_from',
            'previous_reading' => 'required|numeric|min:0',
            'current_reading' => 'required|numeric|min:0',
            'service_charges' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'adjustments' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Calculate consumption
            $consumption = max(0, $request->current_reading - $request->previous_reading);

            // Get customer and calculate charges
            $customer = Customer::findOrFail($request->customer_id);
            $customerType = $customer->customerType->type ?? 'residential';
            $charges = Rate::calculateCharges($customerType, $consumption, $request->bill_date);

            // Create the bill
            $bill = Bill::create([
                'customer_id' => $request->customer_id,
                'water_meter_id' => $request->water_meter_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'billing_period_from' => $request->billing_period_from,
                'billing_period_to' => $request->billing_period_to,
                'previous_reading' => $request->previous_reading,
                'current_reading' => $request->current_reading,
                'consumption' => $consumption,
                'water_charges' => $charges['water_charges'],
                'fixed_charges' => $charges['fixed_charges'],
                'service_charges' => $request->service_charges ?? 0,
                'taxes' => $request->taxes ?? 0,
                'adjustments' => $request->adjustments ?? 0,
                'rate_breakdown' => $charges['breakdown'],
                'notes' => $request->notes,
                'status' => 'generated'
            ]);

            DB::commit();

            return redirect()->route('bills.show', $bill)
                ->with('success', 'Bill created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bill: ' . $e->getMessage());
            return back()->with('error', 'Failed to create bill. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $bill): View
    {
        $bill->load(['customer', 'waterMeter', 'meterReading']);
        
        return view('bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified bill.
     */
    public function edit(Bill $bill): View
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.show', $bill)
                ->with('error', 'Cannot edit paid bills.');
        }

        $customers = Customer::active()->with('waterMeters')->orderBy('first_name')->get();
        $waterMeters = WaterMeter::active()->with('customer')->orderBy('meter_number')->get();
        
        return view('bills.edit', compact('bill', 'customers', 'waterMeters'));
    }

    /**
     * Update the specified bill in storage.
     */
    public function update(Request $request, Bill $bill): RedirectResponse
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.show', $bill)
                ->with('error', 'Cannot update paid bills.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'water_meter_id' => 'required|exists:water_meters,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after:bill_date',
            'billing_period_from' => 'required|date',
            'billing_period_to' => 'required|date|after:billing_period_from',
            'previous_reading' => 'required|numeric|min:0',
            'current_reading' => 'required|numeric|min:0',
            'service_charges' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'adjustments' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Calculate consumption
            $consumption = max(0, $request->current_reading - $request->previous_reading);

            // Get customer and calculate charges
            $customer = Customer::findOrFail($request->customer_id);
            $customerType = $customer->customerType->type ?? 'residential';
            $charges = Rate::calculateCharges($customerType, $consumption, $request->bill_date);

            // Update the bill
            $bill->update([
                'customer_id' => $request->customer_id,
                'water_meter_id' => $request->water_meter_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'billing_period_from' => $request->billing_period_from,
                'billing_period_to' => $request->billing_period_to,
                'previous_reading' => $request->previous_reading,
                'current_reading' => $request->current_reading,
                'consumption' => $consumption,
                'water_charges' => $charges['water_charges'],
                'fixed_charges' => $charges['fixed_charges'],
                'service_charges' => $request->service_charges ?? 0,
                'taxes' => $request->taxes ?? 0,
                'adjustments' => $request->adjustments ?? 0,
                'rate_breakdown' => $charges['breakdown'],
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('bills.show', $bill)
                ->with('success', 'Bill updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating bill: ' . $e->getMessage());
            return back()->with('error', 'Failed to update bill. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified bill from storage.
     */
    public function destroy(Bill $bill): RedirectResponse
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.index')
                ->with('error', 'Cannot delete paid bills.');
        }

        try {
            $bill->delete();
            return redirect()->route('bills.index')
                ->with('success', 'Bill deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting bill: ' . $e->getMessage());
            return redirect()->route('bills.index')
                ->with('error', 'Failed to delete bill. Please try again.');
        }
    }

    /**
     * Generate bills for all customers for the current month.
     */
    public function generate(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $billDate = Carbon::now()->day(20); // 20th of current month
            $dueDate = $billDate->copy()->addDays(30);
            $billingPeriodFrom = $billDate->copy()->startOfMonth();
            $billingPeriodTo = $billDate->copy()->endOfMonth();

            $generatedCount = 0;
            $errors = [];

            // Get all active customers with their active water meters
            $customers = Customer::active()
                ->with(['waterMeters' => function ($query) {
                    $query->where('status', 'active');
                }])
                ->get();

            foreach ($customers as $customer) {
                foreach ($customer->waterMeters as $meter) {
                    // Check if bill already exists for this period
                    $existingBill = Bill::where('customer_id', $customer->id)
                        ->where('water_meter_id', $meter->id)
                        ->whereMonth('bill_date', $billDate->month)
                        ->whereYear('bill_date', $billDate->year)
                        ->first();

                    if ($existingBill) {
                        continue; // Skip if bill already exists
                    }

                    // Get latest meter reading
                    $latestReading = $meter->meterReadings()
                        ->where('reading_date', '<=', $billDate)
                        ->latest('reading_date')
                        ->first();

                    if (!$latestReading) {
                        $errors[] = "No meter reading found for customer {$customer->full_name} (Meter: {$meter->meter_number})";
                        continue;
                    }

                    // Get previous reading
                    $previousReading = $meter->meterReadings()
                        ->where('reading_date', '<', $latestReading->reading_date)
                        ->latest('reading_date')
                        ->first();

                    $prevReading = $previousReading ? $previousReading->current_reading : $meter->initial_reading;
                    $consumption = max(0, $latestReading->current_reading - $prevReading);

                    // Calculate charges
                    $customerType = $customer->customerType->type ?? 'residential';
                    $charges = Rate::calculateCharges($customerType, $consumption, $billDate);

                    // Create bill
                    $bill = Bill::create([
                        'customer_id' => $customer->id,
                        'water_meter_id' => $meter->id,
                        'meter_reading_id' => $latestReading->id,
                        'bill_date' => $billDate,
                        'due_date' => $dueDate,
                        'billing_period_from' => $billingPeriodFrom,
                        'billing_period_to' => $billingPeriodTo,
                        'previous_reading' => $prevReading,
                        'current_reading' => $latestReading->current_reading,
                        'consumption' => $consumption,
                        'water_charges' => $charges['water_charges'],
                        'fixed_charges' => $charges['fixed_charges'],
                        'rate_breakdown' => $charges['breakdown'],
                        'status' => 'generated'
                    ]);

                    // Mark reading as billed
                    $latestReading->markAsBilled();
                    $generatedCount++;
                }
            }

            DB::commit();

            $message = "Generated {$generatedCount} bills successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', $errors);
            }

            return redirect()->route('bills.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating bills: ' . $e->getMessage());
            return redirect()->route('bills.index')
                ->with('error', 'Failed to generate bills. Please try again.');
        }
    }

    /**
     * Mark bill as sent.
     */
    public function send(Bill $bill): RedirectResponse
    {
        try {
            $bill->markAsSent();
            return redirect()->route('bills.show', $bill)
                ->with('success', 'Bill marked as sent successfully.');
        } catch (\Exception $e) {
            Log::error('Error marking bill as sent: ' . $e->getMessage());
            return redirect()->route('bills.show', $bill)
                ->with('error', 'Failed to mark bill as sent. Please try again.');
        }
    }

    /**
     * Record payment for a bill.
     */
    public function recordPayment(Request $request, Bill $bill): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $bill->balance_amount,
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,card',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $success = $bill->recordPayment($request->amount, $request->payment_method);
            
            if ($success) {
                // Log payment details (you might want to create a separate payments table)
                Log::info("Payment recorded for bill {$bill->bill_number}: Amount: {$request->amount}, Method: {$request->payment_method}");
                
                DB::commit();
                return redirect()->route('bills.show', $bill)
                    ->with('success', 'Payment recorded successfully.');
            } else {
                DB::rollBack();
                return redirect()->route('bills.show', $bill)
                    ->with('error', 'Invalid payment amount.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording payment: ' . $e->getMessage());
            return redirect()->route('bills.show', $bill)
                ->with('error', 'Failed to record payment. Please try again.');
        }
    }

    /**
     * Generate printable bill.
     */
    public function print(Bill $bill): View
    {
        $bill->load(['customer', 'waterMeter', 'meterReading']);
        
        return view('bills.print', compact('bill'));
    }

    /**
     * Get bills data for AJAX requests.
     */
    public function getBillsData(Request $request)
    {
        $query = Bill::with(['customer', 'waterMeter']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bills = $query->latest('bill_date')->take(10)->get();

        return response()->json($bills);
    }

    /**
     * Calculate late fees for overdue bills.
     */
    public function calculateLateFees(): RedirectResponse
    {
        try {
            $overdueBills = Bill::overdue()->get();
            $updatedCount = 0;

            foreach ($overdueBills as $bill) {
                $daysOverdue = $bill->getDaysOverdue();
                
                // Calculate late fee (e.g., 2% per month or Rs. 50 minimum)
                $lateFeeRate = 0.02; // 2% per month
                $minimumLateFee = 50;
                $monthsOverdue = ceil($daysOverdue / 30);
                
                $lateFee = max($minimumLateFee, $bill->total_amount * $lateFeeRate * $monthsOverdue);
                
                if ($bill->late_fees < $lateFee) {
                    $bill->addLateFees($lateFee - $bill->late_fees);
                    $updatedCount++;
                }
            }

            return redirect()->route('bills.index')
                ->with('success', "Late fees calculated for {$updatedCount} overdue bills.");
        } catch (\Exception $e) {
            Log::error('Error calculating late fees: ' . $e->getMessage());
            return redirect()->route('bills.index')
                ->with('error', 'Failed to calculate late fees. Please try again.');
        }
    }
}
