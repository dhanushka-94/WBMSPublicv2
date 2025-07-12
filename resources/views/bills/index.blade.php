@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-file-invoice-dollar mr-3"></i>Bills Management
                    </h1>
                    <p class="text-blue-100 mt-2">Generate, manage, and track water bills for customers</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('bills.create') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-plus mr-2"></i>New Bill
                    </a>
                    <form action="{{ route('bills.generate') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition duration-200" onclick="return confirm('Generate bills for all customers?')">
                            <i class="fas fa-magic mr-2"></i>Generate Bills
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Bills</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalBills) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Paid Bills</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($paidBills) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Overdue Bills</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($overdueBills) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-coins text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">@lkr($totalRevenue)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Outstanding</p>
                        <p class="text-2xl font-bold text-gray-900">@lkr($outstandingAmount)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-lg">
                        <i class="fas fa-calendar-alt text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Monthly Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">@lkr($monthlyRevenue)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-filter mr-2"></i>Filter Bills
                </h3>
                
                <form method="GET" action="{{ route('bills.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Bill number, customer name..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="generated" {{ request('status') == 'generated' ? 'selected' : '' }}>Generated</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <select name="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->full_name }} ({{ $customer->account_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <div class="flex space-x-2">
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="md:col-span-2 lg:col-span-4">
                        <div class="flex space-x-3">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                            <a href="{{ route('bills.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-600 transition duration-200">
                                <i class="fas fa-times mr-2"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bills Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list mr-2"></i>Bills List
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consumption</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($bills as $bill)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $bill->bill_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $bill->bill_date->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">Due: {{ $bill->due_date->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover" 
                                                 src="{{ $bill->customer->profile_photo_url }}" 
                                                 alt="{{ $bill->customer->full_name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $bill->customer ? $bill->customer->full_name : 'Unknown Customer' }}</div>
                                            <div class="text-sm text-gray-500">{{ $bill->customer ? $bill->customer->account_number : 'No Account' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $bill->billing_period_from->format('M d') }} - {{ $bill->billing_period_to->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $bill->getBillingPeriodDays() }} days</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($bill->consumption) }} units</div>
                                    <div class="text-sm text-gray-500">{{ number_format($bill->previous_reading) }} → {{ number_format($bill->current_reading) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">@lkr($bill->total_amount)</div>
                                    @if($bill->balance_amount > 0)
                                        <div class="text-sm text-red-600">Balance: @lkr($bill->balance_amount)</div>
                                    @else
                                        <div class="text-sm text-green-600">Paid</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'generated' => 'bg-blue-100 text-blue-800',
                                            'sent' => 'bg-yellow-100 text-yellow-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                            'overdue' => 'bg-red-100 text-red-800'
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$bill->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($bill->status) }}
                                    </span>
                                    @if($bill->isOverdue())
                                        <div class="text-xs text-red-600 mt-1">{{ $bill->getDaysOverdue() }} days overdue</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('bills.show', $bill) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($bill->status !== 'paid')
                                            <a href="{{ route('bills.edit', $bill) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('bills.print', $bill) }}" class="text-green-600 hover:text-green-900" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if($bill->status !== 'paid')
                                            <form action="{{ route('bills.destroy', $bill) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-file-invoice text-4xl mb-4"></i>
                                    <p>No bills found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bills->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 