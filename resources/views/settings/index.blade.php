@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="mx-auto">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-cogs mr-3"></i>System Settings
                </h1>
                <p class="text-blue-100 mt-1">Manage customer divisions and types with custom reference IDs</p>
            </div>
            <div class="flex space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white">
                    <i class="fas fa-info-circle mr-1"></i>
                    Custom ID Generator
                </span>
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

    <!-- Quick Access Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
        <!-- Rate Management Card -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-100">
                            <i class="fas fa-calculator text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Rate Management</h3>
                        <p class="text-sm text-gray-600">Manage unit ranges and billing charges</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('settings.rates.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-cog mr-2"></i>Manage Rates
                    </a>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-chart-line mr-1"></i>
                    <span>{{ App\Models\Rate::active()->count() }} active rates</span>
                </div>
            </div>
        </div>

        <!-- Billing Settings Card -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-100">
                            <i class="fas fa-calendar-alt text-orange-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Billing Settings</h3>
                        <p class="text-sm text-gray-600">Individual customer billing dates</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('settings.billing.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-calendar mr-2"></i>Manage Billing
                    </a>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    <span>{{ App\Models\Customer::where('auto_billing_enabled', true)->count() }} auto-enabled</span>
                </div>
            </div>
        </div>

        <!-- System Billing Configuration Card -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-100">
                            <i class="fas fa-cogs text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">System Billing</h3>
                        <p class="text-sm text-gray-600">Default billing configuration</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('settings.system-billing') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-sliders-h mr-2"></i>Configure System
                    </a>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-calendar-day mr-1"></i>
                    @php
                        $defaultDay = \App\Models\SystemConfiguration::getDefaultBillingDay();
                        $suffix = $defaultDay == 1 ? 'st' : ($defaultDay == 2 ? 'nd' : ($defaultDay == 3 ? 'rd' : 'th'));
                    @endphp
                    <span>{{ $defaultDay }}{{ $suffix }} default</span>
                </div>
            </div>
        </div>

        <!-- Division Management Card -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-100">
                            <i class="fas fa-map-marked-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Customer Divisions</h3>
                        <p class="text-sm text-gray-600">Geographical area management</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button onclick="showTab('divisions')" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-map mr-2"></i>Manage Divisions
                    </button>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-building mr-1"></i>
                    <span>{{ $divisions->count() }} divisions</span>
                </div>
            </div>
        </div>

        <!-- Customer Types Card -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-100">
                            <i class="fas fa-tags text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Customer Types</h3>
                        <p class="text-sm text-gray-600">Customer category management</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button onclick="showTab('customer-types')" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-tag mr-2"></i>Manage Types
                    </button>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-users mr-1"></i>
                    <span>{{ $customerTypes->count() }} types</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6">
                <button class="tab-btn py-4 px-1 border-b-2 font-semibold text-sm border-blue-500 text-blue-600 flex items-center" 
                        onclick="showTab('divisions')">
                    <i class="fas fa-map-marked-alt mr-2"></i>
                    Customer Divisions
                    <span class="ml-2 bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">{{ $divisions->count() }}</span>
                </button>
                <button class="tab-btn py-4 px-1 border-b-2 font-semibold text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 flex items-center" 
                        onclick="showTab('customer-types')">
                    <i class="fas fa-tags mr-2"></i>
                    Customer Types
                    <span class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $customerTypes->count() }}</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Divisions Tab -->
    <div id="divisions-tab" class="tab-content">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-map-marked-alt text-blue-600 mr-2"></i>Customer Divisions
                </h3>
                <button onclick="openModal('division-modal')" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-plus mr-2"></i>Add Division
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Division</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custom ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($divisions as $division)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $division->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $division->custom_id }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $division->description ?: 'No description' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($division->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $division->customers()->count() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editDivision({{ $division->id }}, '{{ $division->name }}', '{{ $division->custom_id }}', '{{ $division->description }}', {{ $division->is_active ? 'true' : 'false' }})"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('settings.divisions.destroy', $division) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this division?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No divisions found. <button onclick="openModal('division-modal')" class="text-blue-600 hover:text-blue-900">Add the first division</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Customer Types Tab -->
    <div id="customer-types-tab" class="tab-content hidden">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-tags text-green-600 mr-2"></i>Customer Types
                </h3>
                <button onclick="openModal('customer-type-modal')" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-plus mr-2"></i>Add Customer Type
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custom ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($customerTypes as $customerType)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $customerType->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $customerType->custom_id }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $customerType->description ?: 'No description' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($customerType->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $customerType->customers()->count() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editCustomerType({{ $customerType->id }}, '{{ $customerType->name }}', '{{ $customerType->custom_id }}', '{{ $customerType->description }}', {{ $customerType->is_active ? 'true' : 'false' }})"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('settings.customer-types.destroy', $customerType) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this customer type?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No customer types found. <button onclick="openModal('customer-type-modal')" class="text-green-600 hover:text-green-900">Add the first customer type</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Division Modal -->
<div id="division-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="division-modal-title">
                <i class="fas fa-map-marked-alt mr-2"></i>Add Division
            </h3>
            <form id="division-form" action="{{ route('settings.divisions.store') }}" method="POST">
                @csrf
                <div id="division-method"></div>
                
                <div class="mb-4">
                    <label for="division_name" class="block text-sm font-medium text-gray-700">Division Name</label>
                    <input type="text" id="division_name" name="name" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div class="mb-4">
                    <label for="division_custom_id" class="block text-sm font-medium text-gray-700">Custom ID (Max 4 chars)</label>
                    <input type="text" id="division_custom_id" name="custom_id" required maxlength="4"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div class="mb-4">
                    <label for="division_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="division_description" name="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="division_is_active" name="is_active" value="1" checked
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('division-modal')"
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

<!-- Customer Type Modal -->
<div id="customer-type-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="customer-type-modal-title">
                <i class="fas fa-tags mr-2"></i>Add Customer Type
            </h3>
            <form id="customer-type-form" action="{{ route('settings.customer-types.store') }}" method="POST">
                @csrf
                <div id="customer-type-method"></div>
                
                <div class="mb-4">
                    <label for="customer_type_name" class="block text-sm font-medium text-gray-700">Type Name</label>
                    <input type="text" id="customer_type_name" name="name" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div class="mb-4">
                    <label for="customer_type_custom_id" class="block text-sm font-medium text-gray-700">Custom ID (Max 3 chars)</label>
                    <input type="text" id="customer_type_custom_id" name="custom_id" required maxlength="3"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div class="mb-4">
                    <label for="customer_type_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="customer_type_description" name="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="customer_type_is_active" name="is_active" value="1" checked
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('customer-type-modal')"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Highlight active tab
    event.target.classList.remove('border-transparent', 'text-gray-500');
    event.target.classList.add('border-blue-500', 'text-blue-600');
}

// Modal functionality
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    resetForm(modalId);
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function resetForm(modalId) {
    if (modalId === 'division-modal') {
        document.getElementById('division-form').reset();
        document.getElementById('division-form').action = "{{ route('settings.divisions.store') }}";
        document.getElementById('division-method').innerHTML = '';
        document.getElementById('division-modal-title').innerHTML = '<i class="fas fa-map-marked-alt mr-2"></i>Add Division';
        document.getElementById('division_is_active').checked = true;
    } else if (modalId === 'customer-type-modal') {
        document.getElementById('customer-type-form').reset();
        document.getElementById('customer-type-form').action = "{{ route('settings.customer-types.store') }}";
        document.getElementById('customer-type-method').innerHTML = '';
        document.getElementById('customer-type-modal-title').innerHTML = '<i class="fas fa-tags mr-2"></i>Add Customer Type';
        document.getElementById('customer_type_is_active').checked = true;
    }
}

// Edit functions
function editDivision(id, name, customId, description, isActive) {
    document.getElementById('division_name').value = name;
    document.getElementById('division_custom_id').value = customId;
    document.getElementById('division_description').value = description;
    document.getElementById('division_is_active').checked = isActive;
    
    document.getElementById('division-form').action = `/settings/divisions/${id}`;
    document.getElementById('division-method').innerHTML = '@method("PUT")';
    document.getElementById('division-modal-title').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Division';
    
    openModal('division-modal');
}

function editCustomerType(id, name, customId, description, isActive) {
    document.getElementById('customer_type_name').value = name;
    document.getElementById('customer_type_custom_id').value = customId;
    document.getElementById('customer_type_description').value = description;
    document.getElementById('customer_type_is_active').checked = isActive;
    
    document.getElementById('customer-type-form').action = `/settings/customer-types/${id}`;
    document.getElementById('customer-type-method').innerHTML = '@method("PUT")';
    document.getElementById('customer-type-modal-title').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Customer Type';
    
    openModal('customer-type-modal');
}

// Auto-uppercase for custom IDs
document.addEventListener('DOMContentLoaded', function() {
    ['division_custom_id', 'customer_type_custom_id'].forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            });
        }
    });
});
</script>
@endsection 