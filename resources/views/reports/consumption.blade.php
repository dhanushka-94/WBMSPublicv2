@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-chart-line mr-3"></i>Consumption Report
                    </h1>
                    <p class="text-purple-100 mt-2">Analyze water consumption patterns and trends</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="window.print()" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-medium hover:bg-purple-50 transition duration-200">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                    <button onclick="exportReport()" class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition duration-200">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-filter mr-2 text-purple-600"></i>Report Filters
            </h3>
            <form method="GET" action="{{ route('reports.consumption') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ $startDate }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ $endDate }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition duration-200">
                        <i class="fas fa-search mr-2"></i>Generate Report
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('reports.consumption') }}" class="w-full bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition duration-200 text-center">
                        <i class="fas fa-refresh mr-2"></i>Reset Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <i class="fas fa-tint text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Consumption</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ number_format($totalConsumption) }} L</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                            <i class="fas fa-chart-bar text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Average Consumption</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ number_format($averageConsumption, 1) }} L</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Report Period</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ Carbon\Carbon::parse($startDate)->format('M d') }} - {{ Carbon\Carbon::parse($endDate)->format('M d, Y') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consumption by Customer Type -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-users mr-2 text-purple-600"></i>Consumption by Customer Type
            </h3>
            @if($consumptionByType->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($consumptionByType as $type)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900 capitalize">{{ $type->customer_type }}</h4>
                                <span class="text-sm text-gray-500">{{ $type->reading_count }} readings</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total:</span>
                                    <span class="text-sm font-medium">{{ number_format($type->total_consumption) }} L</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Average:</span>
                                    <span class="text-sm font-medium">{{ number_format($type->avg_consumption, 1) }} L</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $totalConsumption > 0 ? ($type->total_consumption / $totalConsumption) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="flex flex-col items-center">
                        <div class="flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-chart-bar text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Data Available</h3>
                        <p class="text-gray-500">No consumption data found for the selected period.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Detailed Consumption Data -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-table mr-2 text-purple-600"></i>Detailed Consumption Data
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                @if($consumptionData->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meter #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Reading</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consumption</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($consumptionData as $reading)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $reading->reading_date->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $reading->reading_date->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                @if($reading->waterMeter && $reading->waterMeter->customer)
                                                    <div class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-white">{{ substr($reading->waterMeter->customer->first_name, 0, 1) }}{{ substr($reading->waterMeter->customer->last_name, 0, 1) }}</span>
                                                    </div>
                                                @else
                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-500 text-xs"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $reading->waterMeter && $reading->waterMeter->customer ? $reading->waterMeter->customer->full_name : 'Unknown Customer' }}</div>
                                                <div class="text-sm text-gray-500">{{ $reading->waterMeter && $reading->waterMeter->customer ? $reading->waterMeter->customer->account_number : 'No Account' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ $reading->waterMeter->meter_number }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">{{ number_format($reading->current_reading) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-900 mr-2">{{ number_format($reading->consumption) }} L</span>
                                            @if($reading->consumption > $averageConsumption * 1.5)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    High
                                                </span>
                                            @elseif($reading->consumption > $averageConsumption)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Above Avg
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Normal
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 capitalize">{{ $reading->waterMeter && $reading->waterMeter->customer ? $reading->waterMeter->customer->customer_type : 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($reading->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Pending
                                            </span>
                                        @elseif($reading->status === 'verified')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-check mr-1"></i>Verified
                                            </span>
                                        @elseif($reading->status === 'billed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-double mr-1"></i>Billed
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-12">
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                                <i class="fas fa-tint text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Consumption Data</h3>
                            <p class="text-gray-500">No meter readings found for the selected date range.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($consumptionData->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $consumptionData->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function exportReport() {
    const table = document.querySelector('table');
    if (!table) {
        alert('No data to export');
        return;
    }
    
    let csv = 'Reading Date,Customer Name,Account Number,Meter Number,Current Reading,Consumption (L),Customer Type,Status\n';
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = [
            cells[0].querySelector('.text-sm.font-medium').textContent.trim(),
            cells[1].querySelector('.text-sm.font-medium').textContent.trim(),
            cells[1].querySelector('.text-sm.text-gray-500').textContent.trim(),
            cells[2].textContent.trim(),
            cells[3].textContent.trim(),
            cells[4].querySelector('.text-sm.font-medium').textContent.trim().replace(' L', ''),
            cells[5].textContent.trim(),
            cells[6].querySelector('span').textContent.trim()
        ];
        csv += rowData.map(field => `"${field}"`).join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', `consumption-report-{{ $startDate }}-to-{{ $endDate }}.csv`);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>
@endsection 