<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\CustomerType;
use App\Models\SystemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display the settings page with divisions and customer types
     */
    public function index(): View
    {
        $divisions = Division::orderBy('name')->get();
        $customerTypes = CustomerType::orderBy('name')->get();
        
        return view('settings.index', compact('divisions', 'customerTypes'));
    }

    // DIVISION CRUD METHODS
    
    /**
     * Store a new division
     */
    public function storeDivision(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name',
            'custom_id' => 'required|string|max:4|unique:divisions,custom_id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean'
        ]);

        // Handle checkbox: if not present or false, set to false; if present and true, set to true
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active');

        Division::create($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Division created successfully.');
    }

    /**
     * Update a division
     */
    public function updateDivision(Request $request, Division $division): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('divisions')->ignore($division->id)
            ],
            'custom_id' => [
                'required',
                'string',
                'max:4',
                Rule::unique('divisions')->ignore($division->id)
            ],
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean'
        ]);

        // Handle checkbox: if not present or false, set to false; if present and true, set to true
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active');

        $division->update($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Division updated successfully.');
    }

    /**
     * Delete a division
     */
    public function destroyDivision(Division $division): RedirectResponse
    {
        // Check if division has customers
        if ($division->customers()->count() > 0) {
            return redirect()->route('settings.index')
                ->with('error', 'Cannot delete division. It has associated customers.');
        }

        $division->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Division deleted successfully.');
    }

    // CUSTOMER TYPE CRUD METHODS
    
    /**
     * Store a new customer type
     */
    public function storeCustomerType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_types,name',
            'custom_id' => 'required|string|max:3|unique:customer_types,custom_id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean'
        ]);

        // Handle checkbox: if not present or false, set to false; if present and true, set to true
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active');

        CustomerType::create($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Customer type created successfully.');
    }

    /**
     * Update a customer type
     */
    public function updateCustomerType(Request $request, CustomerType $customerType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customer_types')->ignore($customerType->id)
            ],
            'custom_id' => [
                'required',
                'string',
                'max:3',
                Rule::unique('customer_types')->ignore($customerType->id)
            ],
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean'
        ]);

        // Handle checkbox: if not present or false, set to false; if present and true, set to true
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active');

        $customerType->update($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Customer type updated successfully.');
    }

    /**
     * Delete a customer type
     */
    public function destroyCustomerType(CustomerType $customerType): RedirectResponse
    {
        // Check if customer type has customers
        if ($customerType->customers()->count() > 0) {
            return redirect()->route('settings.index')
                ->with('error', 'Cannot delete customer type. It has associated customers.');
        }

        $customerType->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Customer type deleted successfully.');
    }

    // BILLING SETTINGS METHODS
    
    /**
     * Display billing settings page
     */
    public function billingSettings(Request $request): View
    {
        $query = \App\Models\Customer::with(['division', 'customerType'])
            ->select('id', 'account_number', 'first_name', 'last_name', 'division_id', 'customer_type_id', 
                     'billing_day', 'next_billing_date', 'auto_billing_enabled', 'status');

        // Apply filters
        if ($request->filled('division')) {
            $query->where('division_id', $request->division);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type_id', $request->customer_type);
        }

        if ($request->filled('billing_status')) {
            if ($request->billing_status === 'enabled') {
                $query->where('auto_billing_enabled', true);
            } elseif ($request->billing_status === 'disabled') {
                $query->where('auto_billing_enabled', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('account_number')->paginate(20);
        $divisions = \App\Models\Division::orderBy('name')->get();
        $customerTypes = \App\Models\CustomerType::orderBy('name')->get();

        return view('settings.billing', compact('customers', 'divisions', 'customerTypes'));
    }

    /**
     * Update individual customer billing settings
     */
    public function updateCustomerBilling(Request $request, \App\Models\Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'billing_day' => 'nullable|integer|min:1|max:31',
            'auto_billing_enabled' => 'boolean',
        ]);

        $customer->update($validated);
        
        // Recalculate next billing date if billing day was updated
        if (isset($validated['billing_day'])) {
            $customer->updateNextBillingDate();
        }

        return redirect()->route('settings.billing.index')
            ->with('success', 'Customer billing settings updated successfully.');
    }

    /**
     * Bulk update billing dates for multiple customers
     */
    public function bulkUpdateBillingDates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'billing_day' => 'required|integer|min:1|max:31',
            'customer_ids' => 'required|array|min:1',
            'auto_billing_enabled' => 'boolean',
        ]);

        // Decode the customer_ids JSON
        $customerIds = json_decode($validated['customer_ids'][0], true);
        
        // Check if all customers are selected
        if (in_array('ALL_CUSTOMERS', $customerIds)) {
            // Apply to all customers with current filters
            $query = \App\Models\Customer::query();
            
            // Apply same filters as the billing settings page
            if ($request->filled('division')) {
                $query->where('division_id', $request->division);
            }
            if ($request->filled('customer_type')) {
                $query->where('customer_type_id', $request->customer_type);
            }
            if ($request->filled('billing_status')) {
                if ($request->billing_status === 'enabled') {
                    $query->where('auto_billing_enabled', true);
                } elseif ($request->billing_status === 'disabled') {
                    $query->where('auto_billing_enabled', false);
                }
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('account_number', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            }
            
            $customers = $query->get();
        } else {
            // Apply to specific customers
            $customers = \App\Models\Customer::whereIn('id', $customerIds)->get();
        }
        
        foreach ($customers as $customer) {
            $customer->update([
                'billing_day' => $validated['billing_day'],
                'auto_billing_enabled' => $validated['auto_billing_enabled'] ?? true,
            ]);
            $customer->updateNextBillingDate();
        }

        $message = in_array('ALL_CUSTOMERS', $customerIds ?? []) 
            ? "Billing settings updated for all {$customers->count()} customers matching current filters."
            : "Billing settings updated for {$customers->count()} selected customers.";

        return redirect()->route('settings.billing.index')
            ->with('success', $message);
    }

    /**
     * Calculate billing dates for all customers
     */
    public function calculateBillingDates(): RedirectResponse
    {
        $customers = \App\Models\Customer::whereNotNull('billing_day')->get();
        
        foreach ($customers as $customer) {
            $customer->updateNextBillingDate();
        }

        return redirect()->route('settings.billing.index')
            ->with('success', "Billing dates recalculated for {$customers->count()} customers.");
    }

    // SYSTEM BILLING CONFIGURATION METHODS

    /**
     * Display system billing configuration page
     */
    public function systemBillingConfig(): View
    {
        $config = [
            'default_billing_day' => \App\Models\SystemConfiguration::getDefaultBillingDay(),
            'auto_billing_enabled_default' => \App\Models\SystemConfiguration::getDefaultAutoBilling(),
            'billing_cycle_type' => \App\Models\SystemConfiguration::getBillingCycleType(),
        ];

        return view('settings.system-billing', compact('config'));
    }

    /**
     * Update system billing configuration
     */
    public function updateSystemBillingConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_billing_day' => 'required|integer|min:1|max:31',
            'auto_billing_enabled_default' => 'boolean',
            'billing_cycle_type' => 'required|string|in:monthly,quarterly,semi-annual,annual',
        ]);

        \App\Models\SystemConfiguration::setDefaultBillingDay($validated['default_billing_day']);
        \App\Models\SystemConfiguration::setDefaultAutoBilling($validated['auto_billing_enabled_default'] ?? false);
        \App\Models\SystemConfiguration::setBillingCycleType($validated['billing_cycle_type']);

        return redirect()->route('settings.system-billing')
            ->with('success', 'System billing configuration updated successfully.');
    }

    /**
     * Apply system default billing day to all customers
     */
    public function applyDefaultBillingToAll(Request $request): RedirectResponse
    {
        $defaultBillingDay = \App\Models\SystemConfiguration::getDefaultBillingDay();
        $defaultAutoBilling = \App\Models\SystemConfiguration::getDefaultAutoBilling();
        
        $customers = \App\Models\Customer::all();
        
        foreach ($customers as $customer) {
            $customer->update([
                'billing_day' => $defaultBillingDay,
                'auto_billing_enabled' => $defaultAutoBilling,
            ]);
            $customer->updateNextBillingDate();
        }

        return redirect()->route('settings.system-billing')
            ->with('success', "System default billing settings applied to {$customers->count()} customers.");
    }
}
