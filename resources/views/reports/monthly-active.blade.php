@extends('layouts.app')

@section('title', 'Monthly Active Connections Report')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-white text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-white">Monthly Active Connections Report</h1>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('reports.monthly-active.export', request()->query()) }}" 
                           class="bg-white text-green-600 hover:bg-green-50 px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                            <i class="fas fa-download mr-2"></i>Export CSV
                        </a>
                    </div>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <form method="GET" action="{{ route('reports.monthly-active') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="month" name="start_date" 
                               class="rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" 
                               value="{{ request('start_date', $startDate->format('Y-m')) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="month" name="end_date" 
                               class="rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" 
                               value="{{ request('end_date', $endDate->format('Y-m')) }}">
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                        <i class="fas fa-filter mr-2"></i>Apply Filter
                    </button>
                    <a href="{{ route('reports.monthly-active') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                        <i class="fas fa-refresh mr-2"></i>Reset
                    </a>
                </form>
            </div>

            <!-- Current Month Overview -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Month Overview - {{ $currentMonth['month_name'] }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-users text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($currentMonth['active_customers']) }}</div>
                        <div class="text-sm opacity-90">Active Customers</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-money-bill-wave text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">Rs. {{ number_format($currentMonth['current_month_billing']) }}</div>
                        <div class="text-sm opacity-90">System Billing</div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-user-friends text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($currentMonth['total_customers']) }}</div>
                        <div class="text-sm opacity-90">Total Customers</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-user-plus text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($currentMonth['new_customers']) }}</div>
                        <div class="text-sm opacity-90">New This Month</div>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-tachometer-alt text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($currentMonth['active_meters']) }}</div>
                        <div class="text-sm opacity-90">Active Meters</div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-percentage text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ $currentMonth['activity_rate'] }}%</div>
                        <div class="text-sm opacity-90">Activity Rate</div>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Summary Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4">
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="fas fa-chart-bar text-2xl text-blue-500 mb-2"></i>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($summaryStats['avg_active_customers']) }}</div>
                        <div class="text-sm text-gray-600">Avg Active/Month</div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="fas fa-money-check-alt text-2xl text-green-500 mb-2"></i>
                        <div class="text-2xl font-bold text-green-600">Rs. {{ number_format($summaryStats['total_system_billing']) }}</div>
                        <div class="text-sm text-gray-600">Total System Billing</div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="fas fa-calculator text-2xl text-emerald-500 mb-2"></i>
                        <div class="text-2xl font-bold text-emerald-600">Rs. {{ number_format($summaryStats['avg_monthly_billing']) }}</div>
                        <div class="text-sm text-gray-600">Avg Monthly Billing</div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="fas fa-arrow-up text-2xl text-indigo-500 mb-2"></i>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($summaryStats['max_active_customers']) }}</div>
                        <div class="text-sm text-gray-600">Peak Active</div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="fas fa-money-bill-wave text-2xl text-yellow-500 mb-2"></i>
                        <div class="text-2xl font-bold text-gray-900">Rs. {{ number_format($summaryStats['total_revenue']) }}</div>
                        <div class="text-sm text-gray-600">Water Bill Revenue</div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="fas fa-credit-card text-2xl text-purple-500 mb-2"></i>
                        <div class="text-2xl font-bold text-gray-900">{{ $summaryStats['avg_payment_rate'] }}%</div>
                        <div class="text-sm text-gray-600">Avg Payment Rate</div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="fas fa-trending-up text-2xl {{ $summaryStats['growth_rate'] >= 0 ? 'text-green-500' : 'text-red-500' }} mb-2"></i>
                        <div class="text-2xl font-bold {{ $summaryStats['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $summaryStats['growth_rate'] > 0 ? '+' : '' }}{{ $summaryStats['growth_rate'] }}%
                        </div>
                        <div class="text-sm text-gray-600">Growth Rate</div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend Chart -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Active Connections Trend</h2>
                <div class="h-64">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>

            <!-- Monthly Data Table -->
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Detailed Monthly Data</h2>
                @if($monthlyData->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Customers</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System Billing</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Meters</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bills Generated</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Bills</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Water Revenue</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Rate</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($monthlyData->reverse() as $data)
                                    <tr class="hover:bg-gray-50 transition duration-200">
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                            {{ $data['month_year'] }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">
                                                {{ number_format($data['active_customers']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-bold">
                                                Rs. {{ number_format($data['system_billing_amount']) }}
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ number_format($data['active_customers']) }} × Rs. {{ number_format($ratePerConnection) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full font-medium">
                                                {{ number_format($data['active_meters']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">{{ number_format($data['bills_generated']) }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-900">{{ number_format($data['paid_bills']) }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <span class="font-medium">Rs. {{ number_format($data['revenue'], 2) }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($data['payment_rate'], 100) }}%"></div>
                                                </div>
                                                <span class="text-xs">{{ $data['payment_rate'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('reports.monthly-active.customers', ['year' => $data['year'], 'month' => $data['month']]) }}" 
                                                   class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium rounded-full transition duration-150">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    View Details
                                                </a>
                                                @if($data['active_customers'] > 0)
                                                <a href="{{ route('reports.monthly-active.customers.download', ['year' => $data['year'], 'month' => $data['month']]) }}" 
                                                   class="inline-flex items-center px-3 py-1 bg-green-100 hover:bg-green-200 text-green-800 text-xs font-medium rounded-full transition duration-150">
                                                    <i class="fas fa-download mr-1"></i>
                                                    Download
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-chart-line text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No data available</h3>
                        <p class="text-gray-500">Adjust your date range to see monthly active connections data.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Billing Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-calculator text-green-500 mr-2"></i>System Billing Information
            </h2>
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-green-800 mb-3">📊 Fixed Rate Billing Model</h3>
                        <div class="bg-white rounded-lg p-4 border border-green-200">
                            <div class="text-center">
                                <i class="fas fa-money-bill-wave text-3xl text-green-600 mb-2"></i>
                                <div class="text-2xl font-bold text-green-600">Rs. {{ number_format($ratePerConnection) }}</div>
                                <div class="text-sm text-gray-600">Per Active Connection/Month</div>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-green-700">
                            <p><strong>Billing Formula:</strong></p>
                            <p class="bg-green-100 p-2 rounded mt-1 font-mono">
                                Monthly Charge = Active Customers × Rs. {{ number_format($ratePerConnection) }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-green-800 mb-3">📋 How to Use This Report</h3>
                        <ul class="text-sm text-green-700 space-y-2">
                            <li>• <strong>Active Customers:</strong> Customers with activity (bills, readings, new connections) in that month</li>
                            <li>• <strong>System Billing:</strong> Exact amount to charge (Active Customers × Rs. {{ number_format($ratePerConnection) }})</li>
                            <li>• <strong>Export CSV:</strong> Download data for your billing/accounting system</li>
                            <li>• <strong>Track Trends:</strong> Monitor growth and plan capacity</li>
                            <li>• <strong>Monthly Reporting:</strong> Use date filters for specific billing periods</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-6 bg-white rounded-lg p-4 border border-green-200">
                    <h4 class="font-semibold text-green-800 mb-2">💡 Example Calculation:</h4>
                    <div class="text-sm text-green-700">
                        <p>If you have <strong>50 active customers</strong> in March 2024:</p>
                        <p class="bg-green-100 p-2 rounded mt-1 font-mono">
                            System Charge = 50 × Rs. {{ number_format($ratePerConnection) }} = <strong>Rs. {{ number_format(50 * $ratePerConnection) }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trend Chart
    const monthlyData = @json($monthlyData->values());
    const labels = monthlyData.map(data => data.month_year);
    const activeCustomers = monthlyData.map(data => data.active_customers);
    const activeMeters = monthlyData.map(data => data.active_meters);
    const revenue = monthlyData.map(data => data.revenue);

    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Active Customers',
                    data: activeCustomers,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Active Meters',
                    data: activeMeters,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        afterBody: function(context) {
                            const dataIndex = context[0].dataIndex;
                            const revenueValue = revenue[dataIndex];
                            return `Revenue: Rs. ${revenueValue.toLocaleString()}`;
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>
@endsection