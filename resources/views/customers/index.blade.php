@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        Customer Management
                    </h1>
                    <p class="mt-2 text-gray-600">DN WASSIP - Manage Water Service Customers</p>
                </div>
                <a href="{{ route('customers.create') }}" 
                   class="mt-4 md:mt-0 inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 border border-transparent rounded-lg font-semibold text-white hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Customer
                </a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Customers</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $customers->total() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg">
                                <i class="fas fa-check-circle text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Active</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $customers->where('status', 'active')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg">
                                <i class="fas fa-pause-circle text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Suspended</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $customers->where('status', 'suspended')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow-lg">
                                <i class="fas fa-times-circle text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Inactive</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $customers->where('status', 'inactive')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200 mb-8">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-search mr-3"></i>
                    Search & Filter Customers
                </h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('customers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Search</label>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Name, account number, email..."
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:outline-none transition-colors">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Status</label>
                        <div class="relative">
                            <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:outline-none transition-colors appearance-none">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>🟢 Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>🔴 Inactive</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>🟡 Suspended</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Customer Type</label>
                        <div class="relative">
                            <select name="customer_type_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:outline-none transition-colors appearance-none">
                                <option value="">All Types</option>
                                @foreach($customerTypes ?? [] as $type)
                                    <option value="{{ $type->id }}" {{ request('customer_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-end space-x-3">
                        <button type="submit" 
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i>
                            Search
                        </button>
                        <a href="{{ route('customers.index') }}" 
                           class="px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i>
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-table mr-3"></i>
                        Customer Directory
                    </h3>
                    <div class="text-blue-100 text-sm">
                        Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} 
                        of {{ $customers->total() }} customers
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                @if($customers->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Account Details</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Type & Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($customers as $customer)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-12 w-12">
                                                @if($customer->profile_photo)
                                                    <img class="h-12 w-12 rounded-full object-cover border-2 border-blue-200 shadow-lg" 
                                                         src="{{ $customer->profile_photo_url }}" 
                                                         alt="{{ $customer->full_name }}">
                                                @else
                                                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                                        {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name ?? '', 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-base font-bold text-gray-900">{{ $customer->full_name }}</div>
                                                @if($customer->title)
                                                    <div class="text-sm text-gray-500">{{ $customer->title }}</div>
                                                @endif
                                                @if($customer->address || $customer->city)
                                                    <div class="text-sm text-gray-500">
                                                        {{ $customer->address }}@if($customer->address && $customer->city), @endif{{ $customer->city }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-base font-bold text-blue-600">{{ $customer->account_number }}</div>
                                        @if($customer->reference_number)
                                            <div class="text-sm text-gray-500">Ref: {{ $customer->reference_number }}</div>
                                        @endif
                                        @if($customer->connection_date)
                                            <div class="text-sm text-gray-500">Connected: {{ $customer->connection_date->format('M d, Y') }}</div>
                                        @endif
                                        @if($customer->meter_number)
                                            <div class="text-sm text-gray-500">Meter: {{ $customer->meter_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 space-y-1">
                                            @if($customer->email)
                                                <div class="flex items-center">
                                                    <i class="fas fa-envelope h-4 w-4 text-gray-400 mr-2"></i>
                                                    <span class="truncate max-w-xs">{{ $customer->email }}</span>
                                                </div>
                                            @endif
                                            @if($customer->phone)
                                                <div class="flex items-center">
                                                    <i class="fas fa-phone h-4 w-4 text-gray-400 mr-2"></i>
                                                    {{ $customer->phone }}
                                                </div>
                                            @endif
                                            @if($customer->phone_two)
                                                <div class="flex items-center">
                                                    <i class="fas fa-mobile-alt h-4 w-4 text-gray-400 mr-2"></i>
                                                    {{ $customer->phone_two }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-2">
                                            @if($customer->customerType)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {{ $customer->customerType->name }}
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 
                                                   ($customer->status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                @if($customer->status === 'active') 🟢 @elseif($customer->status === 'suspended') 🟡 @else 🔴 @endif
                                                {{ ucfirst($customer->status) }}
                                            </span>
                                            @if($customer->division)
                                                <div class="text-xs text-gray-500">{{ $customer->division->name }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('customers.show', $customer) }}" 
                                               class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-lg transition-colors">
                                                <i class="fas fa-eye mr-1"></i>
                                                View
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}" 
                                               class="inline-flex items-center px-3 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-lg transition-colors">
                                                <i class="fas fa-edit mr-1"></i>
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-16">
                        <div class="mx-auto h-24 w-24 text-blue-400 mb-6">
                            <i class="fas fa-users text-6xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No customers found</h3>
                        <p class="text-gray-600 mb-8 max-w-sm mx-auto">
                            @if(request()->hasAny(['search', 'status', 'customer_type_id']))
                                No customers match your search criteria. Try adjusting your filters.
                            @else
                                Get started by adding your first customer to the water billing system.
                            @endif
                        </p>
                        <div class="space-x-4">
                            @if(request()->hasAny(['search', 'status', 'customer_type_id']))
                                <a href="{{ route('customers.index') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-times mr-2"></i>
                                    Clear Filters
                                </a>
                            @endif
                            <a href="{{ route('customers.create') }}" 
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 border border-transparent rounded-lg font-semibold text-white hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <i class="fas fa-plus mr-2"></i>
                                Add Your First Customer
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            
            @if($customers->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 