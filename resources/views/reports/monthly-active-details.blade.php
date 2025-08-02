@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-users-cog text-blue-500 mr-3"></i>
                        Active Customers Details - {{ $monthName }}
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Detailed list of active customers with disconnection charges
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('reports.monthly-active') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Report
                    </a>
                    @if($totalCustomers > 0)
                    <a href="{{ route('reports.monthly-active.customers.download', ['year' => $year, 'month' => $month]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-150">
                        <i class="fas fa-download mr-2"></i>Download CSV
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">Total Customers</h3>
                        <p class="text-3xl font-bold">{{ number_format($totalCustomers) }}</p>
                    </div>
                    <i class="fas fa-users text-3xl opacity-80"></i>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">Monthly Charges</h3>
                        <p class="text-3xl font-bold">Rs. {{ number_format($totalMonthlyCharges) }}</p>
                    </div>
                    <i class="fas fa-calendar-check text-3xl opacity-80"></i>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">Disconnection Charges</h3>
                        <p class="text-3xl font-bold">Rs. {{ number_format($totalDisconnectionCharges) }}</p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-3xl opacity-80"></i>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold opacity-90">Grand Total</h3>
                        <p class="text-3xl font-bold">Rs. {{ number_format($grandTotal) }}</p>
                    </div>
                    <i class="fas fa-calculator text-3xl opacity-80"></i>
                </div>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Billing Explanation
            </h2>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-blue-800 mb-2">💰 Monthly Charge</h4>
                        <p class="text-sm text-blue-700">
                            <strong>Rs. {{ number_format($ratePerConnection) }}</strong> per active customer for {{ $monthName }}
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-800 mb-2">⚠️ Disconnection Charge</h4>
                        <p class="text-sm text-blue-700">
                            <strong>Rs. {{ number_format($ratePerConnection) }}</strong> per month for each month customer was inactive
                        </p>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-blue-100 rounded">
                    <p class="text-sm text-blue-800">
                        <strong>Example:</strong> If a customer was inactive for 3 months and then became active in {{ $monthName }}, 
                        they will be charged Rs. {{ number_format($ratePerConnection) }} (monthly) + Rs. {{ number_format($ratePerConnection * 3) }} (3 months disconnection) = 
                        <strong>Rs. {{ number_format($ratePerConnection * 4) }}</strong> total.
                    </p>
                </div>
            </div>
        </div>

        <!-- Customer Details Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Customer Billing Details</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $totalCustomers }} active customers in {{ $monthName }}</p>
            </div>

            @if($customersWithCharges->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Charge</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inactive Months</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disconnection Charge</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Charge</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meters</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($customersWithCharges as $customerData)
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-4 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                {{ $customerData['customer']->first_name }} {{ $customerData['customer']->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">ID: {{ $customerData['customer']->id }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm">
                                            @if($customerData['customer']->email)
                                                <div class="text-gray-900">{{ $customerData['customer']->email }}</div>
                                            @endif
                                            @if($customerData['customer']->phone)
                                                <div class="text-gray-500">{{ $customerData['customer']->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            Rs. {{ number_format($customerData['monthly_charge']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if($customerData['inactive_months'] > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                {{ $customerData['inactive_months'] }} months
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                                None
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($customerData['disconnection_charge'] > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                Rs. {{ number_format($customerData['disconnection_charge']) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                                Rs. 0
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold 
                                            {{ $customerData['total_charge'] > $customerData['monthly_charge'] ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                                            Rs. {{ number_format($customerData['total_charge']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        {{ $customerData['last_active'] ? $customerData['last_active']->format('M Y') : 'First Time' }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $customerData['meter_count'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-4 py-4 text-sm font-semibold text-gray-900">TOTALS</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-200 text-green-900">
                                        Rs. {{ number_format($totalMonthlyCharges) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center text-sm font-medium text-gray-700">
                                    {{ $customersWithCharges->sum('inactive_months') }} months
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-200 text-red-900">
                                        Rs. {{ number_format($totalDisconnectionCharges) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-purple-200 text-purple-900">
                                        Rs. {{ number_format($grandTotal) }}
                                    </span>
                                </td>
                                <td colspan="2" class="px-4 py-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <i class="fas fa-users-slash text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Customers</h3>
                    <p class="text-gray-600">No customers were active in {{ $monthName }}.</p>
                </div>
            @endif
        </div>

        <!-- Summary Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-cogs text-blue-500 mr-2"></i>Next Steps
            </h2>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-800 mb-2">1. Review Charges</h4>
                    <p class="text-sm text-blue-700">
                        Review the customer charges above, especially those with disconnection fees.
                    </p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-semibold text-green-800 mb-2">2. Download Data</h4>
                    <p class="text-sm text-green-700">
                        Download the CSV file for importing into your billing/accounting system.
                    </p>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-800 mb-2">3. Process Billing</h4>
                    <p class="text-sm text-purple-700">
                        Use the total charge amounts to bill customers for {{ $monthName }}.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection