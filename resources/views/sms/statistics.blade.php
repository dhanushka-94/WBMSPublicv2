@extends('layouts.app')

@section('title', 'SMS Statistics Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-bar text-white text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-white">SMS Statistics Dashboard</h1>
                    </div>
                    <a href="{{ route('sms.index') }}" 
                       class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                        <i class="fas fa-list mr-2"></i>SMS Log
                    </a>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 rounded-xl p-6 text-white shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Total SMS Sent</p>
                                <p class="text-3xl font-bold">{{ number_format($stats['total']) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-3">
                                <i class="fas fa-envelope text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-500 via-green-600 to-green-700 rounded-xl p-6 text-white shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">Success Rate</p>
                                <p class="text-3xl font-bold">{{ $stats['success_rate'] }}%</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-3">
                                <i class="fas fa-percentage text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-500 via-purple-600 to-purple-700 rounded-xl p-6 text-white shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm">Today's SMS</p>
                                <p class="text-3xl font-bold">{{ number_format($stats['today']) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-3">
                                <i class="fas fa-calendar-day text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-indigo-500 via-indigo-600 to-indigo-700 rounded-xl p-6 text-white shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-sm">This Month</p>
                                <p class="text-3xl font-bold">{{ number_format($stats['this_month']) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-3">
                                <i class="fas fa-calendar-alt text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Breakdown -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Breakdown</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-green-200 rounded-lg p-4 text-center">
                        <i class="fas fa-check-circle text-3xl text-green-500 mb-2"></i>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['sent']) }}</p>
                        <p class="text-sm text-gray-600">Successfully Sent</p>
                    </div>
                    <div class="bg-white border border-red-200 rounded-lg p-4 text-center">
                        <i class="fas fa-times-circle text-3xl text-red-500 mb-2"></i>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($stats['failed']) }}</p>
                        <p class="text-sm text-gray-600">Failed</p>
                    </div>
                    <div class="bg-white border border-yellow-200 rounded-lg p-4 text-center">
                        <i class="fas fa-clock text-3xl text-yellow-500 mb-2"></i>
                        <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending']) }}</p>
                        <p class="text-sm text-gray-600">Pending</p>
                    </div>
                    <div class="bg-white border border-blue-200 rounded-lg p-4 text-center">
                        <i class="fas fa-calendar-week text-3xl text-blue-500 mb-2"></i>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['this_week']) }}</p>
                        <p class="text-sm text-gray-600">This Week</p>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- SMS by Type Chart -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-pie-chart text-blue-500 mr-2"></i>SMS by Type
                        </h3>
                        <div class="h-64">
                            <canvas id="smsTypeChart"></canvas>
                        </div>
                    </div>

                    <!-- Success Rate by Type -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-chart-bar text-green-500 mr-2"></i>Success Rate by Type
                        </h3>
                        <div class="h-64">
                            <canvas id="successRateChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Daily Activity Chart -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-line text-purple-500 mr-2"></i>Daily SMS Activity (Last 30 Days)
                    </h3>
                    <div class="h-64">
                        <canvas id="dailyActivityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Customers and Recent Activity -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Customers by SMS Count -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-users text-indigo-500 mr-2"></i>Top Customers by SMS Count
                        </h3>
                        @if($stats['top_customers']->count() > 0)
                            <div class="space-y-3">
                                @foreach($stats['top_customers'] as $customer)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="bg-indigo-100 rounded-full p-2 mr-3">
                                                <i class="fas fa-user text-indigo-600"></i>
                                            </div>
                                            <div>
                                                @if($customer->customer)
                                                    <p class="font-medium text-gray-900">{{ $customer->customer->full_name }}</p>
                                                    <p class="text-sm text-gray-500">{{ $customer->customer->account_number }}</p>
                                                @else
                                                    <p class="font-medium text-gray-500">Unknown Customer</p>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium">
                                            {{ $customer->sms_count }} SMS
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-users-slash text-4xl mb-2"></i>
                                <p>No customer data available</p>
                            </div>
                        @endif
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-history text-orange-500 mr-2"></i>Recent Activity
                        </h3>
                        @if($stats['recent_activity']->count() > 0)
                            <div class="space-y-3">
                                @foreach($stats['recent_activity'] as $activity)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="bg-orange-100 rounded-full p-2 mr-3">
                                                <i class="fas fa-sms text-orange-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">
                                                    @if($activity->customer)
                                                        {{ $activity->customer->full_name }}
                                                    @else
                                                        {{ $activity->phone_number }}
                                                    @endif
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $activity->getTypeLabelAttribute() }} • {{ $activity->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        @if($activity->status === 'sent')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                                <i class="fas fa-check-circle mr-1"></i>Sent
                                            </span>
                                        @elseif($activity->status === 'failed')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                                <i class="fas fa-times-circle mr-1"></i>Failed
                                            </span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">
                                                <i class="fas fa-clock mr-1"></i>Pending
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-center">
                                <a href="{{ route('sms.index') }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View All SMS →
                                </a>
                            </div>
                        @else
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-clock text-4xl mb-2"></i>
                                <p>No recent activity</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // SMS by Type Pie Chart
    const smsTypeData = @json($stats['by_type']);
    const typeLabels = Object.keys(smsTypeData).map(key => {
        const labels = {
            'new_bill': 'New Bill',
            'due_reminder': 'Due Reminder',
            'overdue_alert': 'Overdue Alert',
            'payment_confirmation': 'Payment Confirmation',
            'late_fee_notice': 'Late Fee Notice',
            'service_disconnection': 'Service Disconnection',
            'meter_reading_reminder': 'Meter Reading',
            'maintenance_notice': 'Maintenance Notice',
            'custom': 'Custom'
        };
        return labels[key] || key;
    });
    const typeValues = Object.values(smsTypeData);

    if (typeValues.length > 0) {
        new Chart(document.getElementById('smsTypeChart'), {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
                        '#8B5CF6', '#F97316', '#06B6D4', '#84CC16', '#EC4899'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // Success Rate by Type Bar Chart
    const successRateData = @json($stats['success_rate_by_type']);
    const successLabels = Object.keys(successRateData).map(key => {
        const labels = {
            'new_bill': 'New Bill',
            'due_reminder': 'Due Reminder',
            'overdue_alert': 'Overdue Alert',
            'payment_confirmation': 'Payment Confirmation',
            'late_fee_notice': 'Late Fee Notice',
            'service_disconnection': 'Service Disconnection',
            'meter_reading_reminder': 'Meter Reading',
            'maintenance_notice': 'Maintenance Notice',
            'custom': 'Custom'
        };
        return labels[key] || key;
    });
    const successValues = Object.values(successRateData);

    if (successValues.length > 0) {
        new Chart(document.getElementById('successRateChart'), {
            type: 'bar',
            data: {
                labels: successLabels,
                datasets: [{
                    label: 'Success Rate (%)',
                    data: successValues,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Daily Activity Line Chart
    const dailyStats = @json($stats['daily_stats']);
    const dates = dailyStats.map(stat => new Date(stat.date).toLocaleDateString());
    const totalCounts = dailyStats.map(stat => stat.total);
    const sentCounts = dailyStats.map(stat => stat.sent);
    const failedCounts = dailyStats.map(stat => stat.failed);

    new Chart(document.getElementById('dailyActivityChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Total SMS',
                    data: totalCounts,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Sent Successfully',
                    data: sentCounts,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Failed',
                    data: failedCounts,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
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
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
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