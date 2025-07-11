@extends('layouts.app')

@section('title', 'Rate Management')

@section('content')
<div class="mx-auto">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">Rate Management</h1>
                <p class="text-blue-100 mt-1">Manage billing rates and unit charges</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('settings.index') }}" 
                   class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Settings
                </a>
                <a href="{{ route('settings.rates.create') }}" 
                   class="bg-white text-blue-600 px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                    <i class="fas fa-plus mr-2"></i>Add New Rate
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
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

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="customer_type_filter" class="block text-sm font-medium text-gray-700 mb-2">Customer Type</label>
                <select id="customer_type_filter" class="form-select w-full rounded-md border-gray-300">
                    <option value="">All Customer Types</option>
                    @foreach($customerTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-48">
                <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status_filter" class="form-select w-full rounded-md border-gray-300">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="button" id="filterBtn" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <button type="button" id="clearBtn" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-2"></i>Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Rates Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Range</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate/Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fixed Charge</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Effective Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="ratesTableBody">
                    @forelse($rates as $rate)
                        <tr class="hover:bg-gray-50" data-customer-type="{{ $rate->customer_type }}" data-status="{{ $rate->is_active ? 'active' : 'inactive' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $rate->name }}</div>
                                @if($rate->description)
                                    <div class="text-sm text-gray-500">{{ Str::limit($rate->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $rate->customer_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($rate->tier_to)
                                        {{ $rate->tier_from }} - {{ $rate->tier_to }} units
                                    @else
                                        {{ $rate->tier_from }}+ units
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    Rs. {{ number_format($rate->rate_per_unit, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($rate->fixed_charge > 0)
                                        Rs. {{ number_format($rate->fixed_charge, 2) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $rate->effective_from->format('M d, Y') }}
                                    @if($rate->effective_to)
                                        <br><span class="text-gray-500">to {{ $rate->effective_to->format('M d, Y') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($rate->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('settings.rates.show', $rate) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-sm" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('settings.rates.edit', $rate) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 text-sm" 
                                       title="Edit Rate">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('settings.rates.duplicate', $rate) }}" 
                                       class="text-green-600 hover:text-green-900 text-sm" 
                                       title="Duplicate Rate">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    <form action="{{ route('settings.rates.toggle-status', $rate) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="{{ $rate->is_active ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900' }} text-sm"
                                                title="{{ $rate->is_active ? 'Deactivate' : 'Activate' }}"
                                                onclick="return confirm('Are you sure you want to {{ $rate->is_active ? 'deactivate' : 'activate' }} this rate?')">
                                            <i class="fas fa-{{ $rate->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('settings.rates.destroy', $rate) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 text-sm" 
                                                title="Delete Rate"
                                                onclick="return confirm('Are you sure you want to delete this rate? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-calculator text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No rates found</p>
                                    <p class="text-sm">Create your first billing rate to get started.</p>
                                    <a href="{{ route('settings.rates.create') }}" 
                                       class="inline-block mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>Add New Rate
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($rates->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $rates->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerTypeFilter = document.getElementById('customer_type_filter');
    const statusFilter = document.getElementById('status_filter');
    const filterBtn = document.getElementById('filterBtn');
    const clearBtn = document.getElementById('clearBtn');
    const tableBody = document.getElementById('ratesTableBody');
    const rows = tableBody.querySelectorAll('tr');

    function filterRates() {
        const customerType = customerTypeFilter.value.toLowerCase();
        const status = statusFilter.value.toLowerCase();

        rows.forEach(row => {
            const rowCustomerType = row.dataset.customerType?.toLowerCase() || '';
            const rowStatus = row.dataset.status?.toLowerCase() || '';

            const customerTypeMatch = !customerType || rowCustomerType.includes(customerType);
            const statusMatch = !status || rowStatus === status;

            if (customerTypeMatch && statusMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function clearFilters() {
        customerTypeFilter.value = '';
        statusFilter.value = '';
        rows.forEach(row => {
            row.style.display = '';
        });
    }

    filterBtn.addEventListener('click', filterRates);
    clearBtn.addEventListener('click', clearFilters);
    customerTypeFilter.addEventListener('change', filterRates);
    statusFilter.addEventListener('change', filterRates);
});
</script>
@endsection 