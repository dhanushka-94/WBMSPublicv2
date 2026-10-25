<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ConnectionEvent;
use App\Models\Division;
use App\Models\CustomerType;
use App\Models\Guarantor;
use App\Models\WaterMeter;
use App\Models\Bill;
use App\Models\MeterReading;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CustomerController extends Controller
{
    use LogsActivity;
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('phone_two', 'like', "%{$search}%")
                  ->orWhere('nic', 'like', "%{$search}%")
                  ->orWhere('epf_number', 'like', "%{$search}%")
                  ->orWhere('meter_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhereHas('waterMeters', function ($meter) use ($search) {
                      $meter->where('meter_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('customer_type_id')) {
            $query->where('customer_type_id', $request->input('customer_type_id'));
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->input('division_id'));
        }

        if ($from = $this->filterDate($request->input('connected_from'))) {
            $query->whereDate('connection_date', '>=', $from);
        }

        if ($to = $this->filterDate($request->input('connected_to'))) {
            $query->whereDate('connection_date', '<=', $to);
        }

        if ($request->input('meter') === 'assigned') {
            $query->whereHas('waterMeters');
        } elseif ($request->input('meter') === 'unassigned') {
            $query->whereDoesntHave('waterMeters');
        }

        if ($request->input('phone') === 'has') {
            $query->where(function ($q) {
                $q->where(function ($phone) {
                    $phone->whereNotNull('phone')->where('phone', '!=', '');
                })->orWhere(function ($phone) {
                    $phone->whereNotNull('phone_two')->where('phone_two', '!=', '');
                });
            });
        } elseif ($request->input('phone') === 'missing') {
            $query->where(function ($q) {
                $q->whereNull('phone')->orWhere('phone', '');
            })->where(function ($q) {
                $q->whereNull('phone_two')->orWhere('phone_two', '');
            });
        }

        if ($request->input('wallet') === 'credit') {
            $query->where('wallet_balance', '>', 0);
        } elseif ($request->input('wallet') === 'zero') {
            $query->where(function ($q) {
                $q->whereNull('wallet_balance')->orWhere('wallet_balance', '<=', 0);
            });
        }

        if ($request->input('balance') === 'due') {
            $query->whereHas('bills', function ($bill) {
                $bill->where('balance_amount', '>', 0)->whereNotIn('status', ['paid', 'cancelled']);
            });
        } elseif ($request->input('balance') === 'clear') {
            $query->whereDoesntHave('bills', function ($bill) {
                $bill->where('balance_amount', '>', 0)->whereNotIn('status', ['paid', 'cancelled']);
            });
        }

        $statusCounts = (clone $query)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $customers = $query->with(['waterMeters', 'customerType', 'division'])
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $customerTypes = CustomerType::active()->orderBy('name')->get();
        $divisions = Division::active()->orderBy('name')->get();

        return view('customers.index', compact('customers', 'customerTypes', 'divisions', 'statusCounts'));
    }

    private function filterDate(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $divisions = Division::active()->orderBy('name')->get();
        $customerTypes = CustomerType::active()->orderBy('name')->get();
        $guarantors = Guarantor::active()->orderBy('first_name')->get();
        return view('customers.create', compact('divisions', 'customerTypes', 'guarantors'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // 'account_number' => 'required|string|unique:customers,account_number|max:50', // Removed - auto-generated
            'title' => 'required|in:Mr,Mrs,Miss,Ms,Dr', // Required
            'first_name' => 'required|string|max:255', // Required
            'last_name' => 'nullable|string|max:255', // Optional
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20', // Required (Phone Number One)
            'phone_two' => 'nullable|string|max:20',
            'nic' => 'nullable|string|unique:customers,nic|max:12',
            'epf_number' => 'nullable|string|unique:customers,epf_number|max:20',
            'address' => 'nullable|string', // Optional
            'city' => 'nullable|string|max:255', // Optional
            'postal_code' => 'nullable|string|max:10',
            'customer_type_id' => 'required|exists:customer_types,id', // Required
            'division_id' => 'required|exists:divisions,id', // Required
            'guarantor_id' => 'nullable|exists:guarantors,id',
            'connection_date' => 'required|date', // Required
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            // Billing settings
            'billing_day' => 'nullable|integer|min:1|max:31',
            'auto_billing_enabled' => 'boolean',
            // Meter assignment
            'water_meter_id' => [
                'nullable',
                'exists:water_meters,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $meter = \App\Models\WaterMeter::find($value);
                        if ($meter && $meter->customer_id) {
                            $fail('This meter is already assigned to another customer.');
                        }
                    }
                }
            ]
        ]);

        try {
            DB::beginTransaction();

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('customer-photos', 'public');
        }

        // Set default billing settings if not provided
        if (!isset($validated['billing_day'])) {
            $validated['billing_day'] = 1; // Default to 1st of month
        }
        
        if (!isset($validated['auto_billing_enabled'])) {
            $validated['auto_billing_enabled'] = true; // Default to enabled
        }

            // Remove water_meter_id from validated data as it's not a customer field
            $waterMeterId = $validated['water_meter_id'] ?? null;
            unset($validated['water_meter_id']);

        $customer = Customer::create($validated);

            // Assign water meter if selected
            if ($waterMeterId) {
                $waterMeter = \App\Models\WaterMeter::find($waterMeterId);
                if ($waterMeter && !$waterMeter->customer_id) {
                    $waterMeter->update(['customer_id' => $customer->id]);
                    
                    // Log meter assignment activity
                    $this->logActivity('assign_meter', "Assigned water meter {$waterMeter->meter_number} to customer {$customer->full_name}", $customer, [
                        'meter_id' => $waterMeter->id,
                        'meter_number' => $waterMeter->meter_number
                    ]);
                }
            }

        // Log the customer creation activity
        $this->logModelCreated($customer);

            DB::commit();

        return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer created successfully.' . ($waterMeterId ? ' Water meter has been assigned.' : ''));
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating customer: ' . $e->getMessage());
            return back()->with('error', 'Failed to create customer. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $customer->load(['waterMeters.meterReadings' => function ($query) {
            $query->latest('reading_date')->limit(5);
        }, 'bills' => function ($query) {
            $query->latest('bill_date')->limit(10);
        }, 'customerType', 'division', 'connectionEvents.user']);

        // Log customer view activity
        $this->logActivity('view', "Viewed customer profile for {$customer->full_name}", $customer);

        $activeMeters = $customer->waterMeters()->active()->get();
        $outstandingBalance = $customer->getOutstandingBalance();
        $lastBill = $customer->getLastBill();
        $currentReading = $customer->getCurrentReading();
        $financialSummary = [
            'billed' => (float) $customer->bills()->sum('total_amount'),
            'paid' => (float) $customer->bills()->sum('paid_amount'),
            'outstanding' => (float) $outstandingBalance,
            'overdue' => (float) $customer->bills()->where('status', 'overdue')->sum('balance_amount'),
            'bills' => $customer->bills()->count(),
            'payments' => $customer->payments()->count(),
            'last_payment' => $customer->payments()->latest('paid_on')->first(),
        ];
        $chartYear = Carbon::now()->year;
        $yearChart = $this->currentYearMonths($customer, $chartYear);

        return view('customers.show', compact(
            'customer', 
            'activeMeters', 
            'outstandingBalance', 
            'lastBill', 
            'currentReading',
            'financialSummary',
            'chartYear',
            'yearChart'
        ));
    }

    /**
     * This customer's billed, collected, draft, and usage figures for each month of a year.
     */
    private function currentYearMonths(Customer $customer, int $year): array
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

        $billRows = $customer->bills()
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

        $paymentRows = $customer->payments()
            ->whereBetween('paid_on', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('MONTH(paid_on) as month_number, SUM(amount) as collected')
            ->groupByRaw('MONTH(paid_on)')
            ->get();

        foreach ($paymentRows as $row) {
            $months[(int) $row->month_number]['collected'] = round((float) $row->collected, 2);
        }

        $readingRows = MeterReading::query()
            ->whereIn('water_meter_id', $customer->waterMeters()->select('id'))
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
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        $divisions = Division::active()->orderBy('name')->get();
        $customerTypes = CustomerType::active()->orderBy('name')->get();
        $guarantors = Guarantor::active()->orderBy('first_name')->get();
        return view('customers.edit', compact('customer', 'divisions', 'customerTypes', 'guarantors'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            // Account number should not be changeable after creation
            'reference_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers')->ignore($customer->id)
            ],
            'meter_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customers')->ignore($customer->id)
            ],
            'title' => 'required|in:Mr,Mrs,Miss,Ms,Dr', // Required
            'first_name' => 'required|string|max:255', // Required
            'last_name' => 'nullable|string|max:255', // Optional
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20', // Required (Phone Number One)
            'phone_two' => 'nullable|string|max:20',
            'nic' => [
                'nullable',
                'string',
                'max:12',
                Rule::unique('customers')->ignore($customer->id)
            ],
            'epf_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers')->ignore($customer->id)
            ],
            'address' => 'nullable|string', // Optional
            'city' => 'nullable|string|max:255', // Optional
            'postal_code' => 'nullable|string|max:10',
            'status' => 'required|in:active,inactive,suspended,disconnected',
            'customer_type_id' => 'required|exists:customer_types,id', // Required
            'division_id' => 'required|exists:divisions,id', // Required
            'guarantor_id' => 'nullable|exists:guarantors,id',
            'connection_date' => 'required|date', // Required
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            // Billing settings
            'billing_day' => 'nullable|integer|min:1|max:31',
            'auto_billing_enabled' => 'boolean'
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($customer->profile_photo) {
                Storage::disk('public')->delete($customer->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('customer-photos', 'public');
        }

        // Capture old values for activity logging
        $oldValues = $customer->getOriginal();

        $customer->update($validated);

        // Log the customer update activity
        $this->logModelUpdated($customer, $oldValues);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        // Check if customer has active meters or unpaid bills
        if (!auth()->user()?->isSuperAdmin()) {
            if ($customer->waterMeters()->active()->exists()) {
                return back()->with('error', 'Cannot delete customer with active water meters.');
            }

            if ($customer->getOutstandingBalance() > 0) {
                return back()->with('error', 'Cannot delete customer with outstanding bills.');
            }
        }

        // Log the customer deletion activity before deletion
        $this->logModelDeleted($customer);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function disconnect(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'event_date' => 'required|date',
            'reason' => 'required|string|max:500',
        ]);

        $customer->update(['status' => 'disconnected']);
        ConnectionEvent::create([
            'customer_id' => $customer->id,
            'user_id' => $request->user()->id,
            'action' => 'disconnected',
            'event_date' => $validated['event_date'],
            'reason' => $validated['reason'],
        ]);

        try {
            app(\App\Services\SmsNotificationService::class)
                ->sendServiceDisconnectionWarning(
                    $customer,
                    $customer->getOutstandingBalance(),
                    null,
                    \Carbon\Carbon::parse($validated['event_date'])
                );
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Customer disconnected.');
    }

    public function reconnect(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'event_date' => 'required|date',
            'reason' => 'required|string|max:500',
        ]);

        $customer->update(['status' => 'active']);
        ConnectionEvent::create([
            'customer_id' => $customer->id,
            'user_id' => $request->user()->id,
            'action' => 'reconnected',
            'event_date' => $validated['event_date'],
            'reason' => $validated['reason'],
        ]);

        try {
            app(\App\Services\SmsNotificationService::class)->sendReconnectionNotice($customer);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Customer reconnected.');
    }

    /**
     * Display customer's water meters.
     */
    public function meters(Customer $customer): View
    {
        $meters = $customer->waterMeters()
            ->with(['meterReadings' => function ($query) {
                $query->latest('reading_date')->limit(3);
            }])
            ->get();

        return view('customers.meters', compact('customer', 'meters'));
    }

    /**
     * Display customer's bills.
     */
    public function bills(Customer $customer): View
    {
        $bills = $customer->bills()
            ->with(['waterMeter', 'meterReading'])
            ->orderBy('bill_date', 'desc')
            ->paginate(25);

        $totalOutstanding = $customer->getOutstandingBalance();
        
        return view('customers.bills', compact('customer', 'bills', 'totalOutstanding'));
    }

    // Removed generateAccountNumber method - now handled in Customer model
}
