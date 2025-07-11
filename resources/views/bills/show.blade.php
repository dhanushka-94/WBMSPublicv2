@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-file-invoice mr-3"></i>Bill Details
                    </h1>
                    <p class="text-blue-100 mt-2">{{ $bill->bill_number }} - {{ $bill->customer->full_name }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('bills.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Bills
                    </a>
                    <a href="{{ route('bills.print', $bill) }}" target="_blank" class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition duration-200">
                        <i class="fas fa-print mr-2"></i>Print Bill
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Bill Details -->
            <div class="lg:col-span-2">
                <!-- Bill Status Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Bill Status</h3>
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'generated' => 'bg-blue-100 text-blue-800',
                                    'sent' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'overdue' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$bill->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($bill->status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Bill Date</p>
                                <p class="font-medium">{{ $bill->bill_date->format('M d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Due Date</p>
                                <p class="font-medium {{ $bill->isOverdue() ? 'text-red-600' : '' }}">
                                    {{ $bill->due_date->format('M d, Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">Total Amount</p>
                                <p class="font-medium text-lg">@lkr($bill->total_amount)</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Balance</p>
                                <p class="font-medium text-lg {{ $bill->balance_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    @lkr($bill->balance_amount)
                                </p>
                            </div>
                        </div>

                        @if($bill->isOverdue())
                            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-red-800 text-sm">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    This bill is {{ $bill->getDaysOverdue() }} days overdue.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-user mr-2"></i>Customer Information
                        </h3>
                        
                        <div class="flex items-center mb-4">
                            <img class="h-16 w-16 rounded-full object-cover mr-4" 
                                 src="{{ $bill->customer->profile_photo_url }}" 
                                 alt="{{ $bill->customer->full_name }}">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">{{ $bill->customer->full_name }}</h4>
                                <p class="text-gray-600">{{ $bill->customer->account_number }}</p>
                                <p class="text-gray-600">{{ $bill->customer->email }}</p>
                                <p class="text-gray-600">{{ $bill->customer->phone }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Address</p>
                                <p class="font-medium">{{ $bill->customer->address }}</p>
                                <p class="font-medium">{{ $bill->customer->city }}, {{ $bill->customer->postal_code }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Customer Type</p>
                                <p class="font-medium">{{ $bill->customer->customerType->name ?? 'Residential' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meter Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-tachometer-alt mr-2"></i>Meter Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Meter Number</p>
                                <p class="font-medium">{{ $bill->waterMeter->meter_number }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Meter Type</p>
                                <p class="font-medium">{{ ucfirst($bill->waterMeter->meter_type) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Meter Size</p>
                                <p class="font-medium">{{ $bill->waterMeter->meter_size }}mm</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Consumption Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-chart-line mr-2"></i>Consumption Details
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-4">
                            <div>
                                <p class="text-gray-600">Previous Reading</p>
                                <p class="font-medium text-lg">{{ number_format($bill->previous_reading) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Current Reading</p>
                                <p class="font-medium text-lg">{{ number_format($bill->current_reading) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Consumption</p>
                                <p class="font-medium text-lg text-blue-600">{{ number_format($bill->consumption) }} units</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Billing Period</p>
                                <p class="font-medium">{{ $bill->billing_period_from->format('M d') }} - {{ $bill->billing_period_to->format('M d, Y') }}</p>
                                <p class="text-gray-500">{{ $bill->getBillingPeriodDays() }} days</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rate Breakdown -->
                @if($bill->rate_breakdown)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-calculator mr-2"></i>Rate Breakdown
                        </h3>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Tier</th>
                                        <th class="px-4 py-2 text-left">Range</th>
                                        <th class="px-4 py-2 text-left">Units</th>
                                        <th class="px-4 py-2 text-left">Rate</th>
                                        <th class="px-4 py-2 text-left">Charge</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($bill->rate_breakdown as $tier)
                                        <tr>
                                            <td class="px-4 py-2">{{ $tier['tier_name'] }}</td>
                                            <td class="px-4 py-2">
                                                {{ $tier['tier_from'] }}{{ $tier['tier_to'] ? ' - ' . $tier['tier_to'] : '+' }}
                                            </td>
                                            <td class="px-4 py-2">{{ number_format($tier['consumption']) }}</td>
                                            <td class="px-4 py-2">@lkr($tier['rate_per_unit'])</td>
                                            <td class="px-4 py-2">@lkr($tier['charge'])</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Bill Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-receipt mr-2"></i>Bill Summary
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Water Charges</span>
                                <span class="font-medium">@lkr($bill->water_charges)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fixed Charges</span>
                                <span class="font-medium">@lkr($bill->fixed_charges)</span>
                            </div>
                            @if($bill->service_charges > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Service Charges</span>
                                    <span class="font-medium">@lkr($bill->service_charges)</span>
                                </div>
                            @endif
                            @if($bill->taxes > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Taxes</span>
                                    <span class="font-medium">@lkr($bill->taxes)</span>
                                </div>
                            @endif
                            @if($bill->late_fees > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Late Fees</span>
                                    <span class="font-medium text-red-600">@lkr($bill->late_fees)</span>
                                </div>
                            @endif
                            @if($bill->adjustments != 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Adjustments</span>
                                    <span class="font-medium {{ $bill->adjustments > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        @lkr($bill->adjustments)
                                    </span>
                                </div>
                            @endif
                            <div class="border-t pt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold">Total Amount</span>
                                    <span class="text-lg font-bold text-blue-600">@lkr($bill->total_amount)</span>
                                </div>
                            </div>
                            @if($bill->paid_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Paid Amount</span>
                                    <span class="font-medium text-green-600">@lkr($bill->paid_amount)</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold">Balance Amount</span>
                                <span class="text-lg font-bold {{ $bill->balance_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    @lkr($bill->balance_amount)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-cogs mr-2"></i>Quick Actions
                        </h3>
                        
                        <div class="space-y-3">
                            @if($bill->status !== 'paid')
                                <a href="{{ route('bills.edit', $bill) }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i>Edit Bill
                                </a>
                            @endif

                            @if($bill->status === 'generated')
                                <form action="{{ route('bills.send', $bill) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-yellow-700 transition duration-200">
                                        <i class="fas fa-paper-plane mr-2"></i>Mark as Sent
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('bills.print', $bill) }}" target="_blank" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition duration-200 flex items-center justify-center">
                                <i class="fas fa-print mr-2"></i>Print Bill
                            </a>

                            @if($bill->status !== 'paid')
                                <form action="{{ route('bills.destroy', $bill) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition duration-200" onclick="return confirm('Are you sure you want to delete this bill?')">
                                        <i class="fas fa-trash mr-2"></i>Delete Bill
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Record Payment -->
                @if($bill->balance_amount > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-money-bill-wave mr-2"></i>Record Payment
                        </h3>
                        
                        <form action="{{ route('bills.payment', $bill) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        Amount (Rs.) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="amount" id="amount" 
                                           step="0.01" min="0.01" max="{{ $bill->balance_amount }}" 
                                           value="{{ $bill->balance_amount }}" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="text-sm text-gray-500 mt-1">Balance: @lkr($bill->balance_amount)</p>
                                </div>

                                <div>
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                        Payment Method <span class="text-red-500">*</span>
                                    </label>
                                    <select name="payment_method" id="payment_method" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="card">Card Payment</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Payment Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="payment_date" id="payment_date" 
                                           value="{{ date('Y-m-d') }}" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">
                                        Reference Number
                                    </label>
                                    <input type="text" name="reference" id="reference" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Cheque number, transaction ID, etc.">
                                </div>

                                <div>
                                    <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notes
                                    </label>
                                    <textarea name="notes" id="payment_notes" rows="2" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Payment notes..."></textarea>
                                </div>

                                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition duration-200">
                                    <i class="fas fa-check mr-2"></i>Record Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Bill Notes -->
                @if($bill->notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-sticky-note mr-2"></i>Notes
                        </h3>
                        <p class="text-gray-700 text-sm">{{ $bill->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 