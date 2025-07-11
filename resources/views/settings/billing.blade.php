@extends('layouts.app')

@section('title', 'Billing Settings')

@section('content')
<div class="mx-auto">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-calendar-alt mr-3"></i>Billing Settings
                </h1>
                <p class="text-blue-100 mt-1">Manage automated billing dates for customers</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('settings.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Customers</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $customers->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Auto Billing Enabled</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $customers->where('auto_billing_enabled', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Due Today</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $customers->where('next_billing_date', now()->format('Y-m-d'))->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation text-red-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Overdue</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $customers->where('next_billing_date', '<', now()->format('Y-m-d'))->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <form method="GET" action="{{ route('settings.billing.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Account number or name..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                    <select name="division" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">All Divisions</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ request('division') == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Type</label>
                    <select name="customer_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">All Types</option>
                        @foreach($customerTypes as $type)
                            <option value="{{ $type->id }}" {{ request('customer_type') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Billing Status</label>
                    <select name="billing_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">All</option>
                        <option value="enabled" {{ request('billing_status') == 'enabled' ? 'selected' : '' }}>Enabled</option>
                        <option value="disabled" {{ request('billing_status') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </div>

                <div class="flex items-end">
                    <a href="{{ route('settings.billing.index') }}" 
                       class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md text-center">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div class="border-t border-gray-200 p-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-sm text-gray-600">Select Page</span>
                        </label>
                        <button onclick="selectAllCustomers()" 
                                class="text-sm text-blue-600 hover:text-blue-800 underline">
                            Select All ({{ $customers->total() }})
                        </button>
                        <button onclick="deselectAll()" 
                                class="text-sm text-gray-600 hover:text-gray-800 underline">
                            Deselect All
                        </button>
                    </div>
                    <span id="selected-count" class="text-sm text-gray-500">0 selected</span>
                </div>
                <div class="flex space-x-2">
                    <button onclick="openBulkUpdateModal()" 
                            class="inline-flex items-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-md disabled:opacity-50"
                            id="bulk-update-btn" disabled>
                        <i class="fas fa-calendar mr-2"></i>Bulk Update Billing
                    </button>
                    <form action="{{ route('settings.billing.calculate-dates') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('This will recalculate billing dates for all customers. Continue?')"
                                class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md">
                            <i class="fas fa-calculator mr-2"></i>Recalculate All Dates
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="header-checkbox" class="rounded border-gray-300 text-blue-600">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Division</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Billing</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" 
                                       class="customer-checkbox rounded border-gray-300 text-blue-600">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $customer->account_number }}</div>
                                <div class="text-sm text-gray-500">{{ $customer->first_name }} {{ $customer->last_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $customer->division->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $customer->customerType->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($customer->billing_day)
                                    <span class="text-sm font-medium text-gray-900">{{ $customer->billing_day_text }}</span>
                                @else
                                    <span class="text-sm text-gray-400">Not set</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($customer->next_billing_date)
                                    @php
                                        $billingDate = \Carbon\Carbon::parse($customer->next_billing_date);
                                        $isPast = $billingDate->isPast();
                                        $isToday = $billingDate->isToday();
                                    @endphp
                                    <span class="text-sm {{ $isPast ? 'text-red-600 font-semibold' : ($isToday ? 'text-yellow-600 font-semibold' : 'text-gray-900') }}">
                                        {{ $billingDate->format('M d, Y') }}
                                        @if($isPast)
                                            <i class="fas fa-exclamation-triangle ml-1"></i>
                                        @elseif($isToday)
                                            <i class="fas fa-clock ml-1"></i>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">Not calculated</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($customer->auto_billing_enabled)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editCustomerBilling({{ $customer->id }}, {{ $customer->billing_day ?? 'null' }}, {{ $customer->auto_billing_enabled ? 'true' : 'false' }})"
                                        class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No customers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Edit Customer Modal -->
<div id="edit-customer-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-calendar-alt mr-2"></i>Edit Billing Settings
            </h3>
            <form id="edit-customer-form" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="edit_billing_day" class="block text-sm font-medium text-gray-700">Billing Day (1-31)</label>
                    <input type="number" id="edit_billing_day" name="billing_day" min="1" max="31"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <p class="text-xs text-gray-500 mt-1">Day of month when bill is generated</p>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_auto_billing_enabled" name="auto_billing_enabled" value="1"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Enable automatic billing</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('edit-customer-modal')"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div id="bulk-update-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-calendar-alt mr-2"></i>Bulk Update Billing Settings
            </h3>
            <form id="bulk-update-form" action="{{ route('settings.billing.bulk-update') }}" method="POST">
                @csrf
                <input type="hidden" id="bulk_customer_ids" name="customer_ids">
                
                <div class="mb-4">
                    <label for="bulk_billing_day" class="block text-sm font-medium text-gray-700">Billing Day (1-31)</label>
                    <input type="number" id="bulk_billing_day" name="billing_day" min="1" max="31" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <p class="text-xs text-gray-500 mt-1">Day of month when bill is generated</p>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="auto_billing_enabled" value="1" checked
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Enable automatic billing</span>
                    </label>
                </div>
                
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        This will update billing settings for <span id="bulk-count">0</span> selected customers.
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('bulk-update-modal')"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        Update All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global variables for tracking selections
let selectedCustomerIds = new Set();
let allCustomersSelected = false;

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Edit customer billing
function editCustomerBilling(customerId, billingDay, autoBillingEnabled) {
    document.getElementById('edit_billing_day').value = billingDay || '';
    document.getElementById('edit_auto_billing_enabled').checked = autoBillingEnabled;
    document.getElementById('edit-customer-form').action = `/settings/billing/customer/${customerId}`;
    openModal('edit-customer-modal');
}

// Select all customers across all pages
function selectAllCustomers() {
    allCustomersSelected = true;
    
    // Select all visible checkboxes
    const customerCheckboxes = document.querySelectorAll('.customer-checkbox');
    customerCheckboxes.forEach(cb => cb.checked = true);
    
    // Update UI
    document.getElementById('select-all').checked = true;
    document.getElementById('header-checkbox').checked = true;
    updateSelectedCount();
    
    // Show notification
    showNotification('All {{ $customers->total() }} customers selected across all pages', 'info');
}

// Deselect all customers
function deselectAll() {
    allCustomersSelected = false;
    selectedCustomerIds.clear();
    
    // Deselect all visible checkboxes
    const customerCheckboxes = document.querySelectorAll('.customer-checkbox');
    customerCheckboxes.forEach(cb => cb.checked = false);
    
    // Update UI
    document.getElementById('select-all').checked = false;
    document.getElementById('header-checkbox').checked = false;
    updateSelectedCount();
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white z-50 ${
        type === 'info' ? 'bg-blue-600' : 
        type === 'success' ? 'bg-green-600' : 
        type === 'warning' ? 'bg-yellow-600' : 'bg-red-600'
    }`;
    notification.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Bulk update modal with enhanced customer selection
function openBulkUpdateModal() {
    let customerIds = [];
    let totalCount = 0;
    
    if (allCustomersSelected) {
        // Use special marker for all customers
        customerIds = ['ALL_CUSTOMERS'];
        totalCount = {{ $customers->total() }};
    } else {
        // Get only checked visible customers
        const selectedCheckboxes = document.querySelectorAll('.customer-checkbox:checked');
        customerIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        totalCount = customerIds.length;
    }
    
    if (totalCount === 0) {
        showNotification('Please select customers first', 'warning');
        return;
    }
    
    document.getElementById('bulk_customer_ids').value = JSON.stringify(customerIds);
    document.getElementById('bulk-count').textContent = totalCount;
    
    openModal('bulk-update-modal');
}

// Checkbox management
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const headerCheckbox = document.getElementById('header-checkbox');
    const customerCheckboxes = document.querySelectorAll('.customer-checkbox');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkUpdateBtn = document.getElementById('bulk-update-btn');

    function updateSelectedCount() {
        let selectedCount = 0;
        
        if (allCustomersSelected) {
            selectedCount = {{ $customers->total() }};
        } else {
            selectedCount = document.querySelectorAll('.customer-checkbox:checked').length;
        }
        
        selectedCountSpan.textContent = `${selectedCount} selected`;
        bulkUpdateBtn.disabled = selectedCount === 0;
        
        // Update button text based on selection
        if (allCustomersSelected) {
            selectedCountSpan.innerHTML = `${selectedCount} selected <span class="text-blue-600 font-medium">(All Customers)</span>`;
        }
    }

    // Select all functionality (page only)
    [selectAllCheckbox, headerCheckbox].forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                customerCheckboxes.forEach(cb => cb.checked = true);
                allCustomersSelected = false; // Only page selection
            } else {
                customerCheckboxes.forEach(cb => cb.checked = false);
                allCustomersSelected = false;
            }
            updateSelectedCount();
        });
    });

    // Individual checkbox change
    customerCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // If unchecking, clear all customers selection
            if (!this.checked) {
                allCustomersSelected = false;
            }
            
            updateSelectedCount();
            
            // Update select all checkboxes
            const allChecked = Array.from(customerCheckboxes).every(cb => cb.checked);
            const noneChecked = Array.from(customerCheckboxes).every(cb => !cb.checked);
            
            if (!allCustomersSelected) {
                [selectAllCheckbox, headerCheckbox].forEach(cb => {
                    cb.checked = allChecked;
                    cb.indeterminate = !allChecked && !noneChecked;
                });
            }
        });
    });

    // Initial count
    updateSelectedCount();
});
</script>
@endsection 