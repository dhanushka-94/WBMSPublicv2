<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Division;
use App\Models\CustomerType;
use App\Models\Guarantor;
use App\Models\WaterMeter;
use App\Models\Bill;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    use LogsActivity;
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request): View
    {
        $query = Customer::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by customer type
        if ($request->filled('customer_type_id')) {
            $query->where('customer_type_id', $request->input('customer_type_id'));
        }

        $customers = $query->with(['waterMeters', 'customerType'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $customerTypes = CustomerType::active()->orderBy('name')->get();

        return view('customers.index', compact('customers', 'customerTypes'));
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
            'meter_number' => 'nullable|string|unique:customers,meter_number|max:50',
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
            'auto_billing_enabled' => 'boolean'
        ]);

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

        $customer = Customer::create($validated);

        // Log the customer creation activity
        $this->logModelCreated($customer);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
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
        }, 'customerType', 'division']);

        // Log customer view activity
        $this->logActivity('view', "Viewed customer profile for {$customer->full_name}", $customer);

        $activeMeters = $customer->waterMeters()->active()->get();
        $outstandingBalance = $customer->getOutstandingBalance();
        $lastBill = $customer->getLastBill();
        $currentReading = $customer->getCurrentReading();

        return view('customers.show', compact(
            'customer', 
            'activeMeters', 
            'outstandingBalance', 
            'lastBill', 
            'currentReading'
        ));
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
            'status' => 'required|in:active,inactive,suspended',
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
        if ($customer->waterMeters()->active()->exists()) {
            return back()->with('error', 'Cannot delete customer with active water meters.');
        }

        if ($customer->getOutstandingBalance() > 0) {
            return back()->with('error', 'Cannot delete customer with outstanding bills.');
        }

        // Log the customer deletion activity before deletion
        $this->logModelDeleted($customer);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
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
            ->paginate(15);

        $totalOutstanding = $customer->getOutstandingBalance();
        
        return view('customers.bills', compact('customer', 'bills', 'totalOutstanding'));
    }

    // Removed generateAccountNumber method - now handled in Customer model
}
