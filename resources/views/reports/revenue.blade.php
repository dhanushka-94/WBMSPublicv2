@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-blue-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-chart-bar mr-3"></i>Revenue Report
                    </h1>
                    <p class="text-green-100 mt-2">Track billing revenue and financial performance</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="window.print()" class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-green-50 transition duration-200">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                    <button onclick="exportReport()" class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-yellow-600 transition duration-200">
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
                <i class="fas fa-filter mr-2 text-green-600"></i>Report Filters
            </h3>
            <form method="GET" action="{{ route('reports.revenue') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ $startDate }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ $endDate }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-200">
                        <i class="fas fa-search mr-2"></i>Generate Report
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('reports.revenue') }}" class="w-full bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition duration-200 text-center">
                        <i class="fas fa-refresh mr-2"></i>Reset Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Revenue Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <i class="fas fa-dollar-sign text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                            <dd class="text-2xl font-semibold text-gray-900">LKR {{ number_format($totalRevenue, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Paid Revenue</dt>
                            <dd class="text-2xl font-semibold text-gray-900">LKR {{ number_format($paidRevenue, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Outstanding</dt>
                            <dd class="text-2xl font-semibold text-gray-900">LKR {{ number_format($unpaidRevenue, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                            <i class="fas fa-percentage text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Collection Rate</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $totalRevenue > 0 ? round(($paidRevenue / $totalRevenue) * 100, 1) : 0 }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Customer Type -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-users mr-2 text-green-600"></i>Revenue by Customer Type
            </h3>
            @if($revenueByType->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($revenueByType as $type)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900 capitalize">{{ $type->customer_type }}</h4>
                                <span class="text-sm text-gray-500">{{ $type->bill_count }} bills</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total:</span>
                                    <span class="text-sm font-medium">LKR {{ number_format($type->total_revenue, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Paid:</span>
                                    <span class="text-sm font-medium text-green-600">LKR {{ number_format($type->paid_revenue, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Outstanding:</span>
                                    <span class="text-sm font-medium text-red-600">LKR {{ number_format($type->outstanding_revenue, 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $totalRevenue > 0 ? ($type->total_revenue / $totalRevenue) * 100 : 0 }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 text-center">
                                    {{ $type->total_revenue > 0 ? round(($type->paid_revenue / $type->total_revenue) * 100, 1) : 0 }}% collection rate
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
                        <p class="text-gray-500">No revenue data found for the selected period.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Detailed Revenue Data -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-table mr-2 text-green-600"></i>Detailed Revenue Data
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                @if($bills->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bills as $bill)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $bill->bill_date->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-blue-600">{{ $bill->bill_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">{{ substr($bill->customer->first_name, 0, 1) }}{{ substr($bill->customer->last_name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $bill->customer->full_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $bill->customer->account_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">LKR {{ number_format($bill->total_amount, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-green-600">LKR {{ number_format($bill->paid_amount, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium {{ $bill->balance_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                            LKR {{ number_format($bill->balance_amount, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($bill->status === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i>Paid
                                            </span>
                                        @elseif($bill->status === 'partial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Partial
                                            </span>
                                        @elseif($bill->status === 'overdue')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Overdue
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-circle mr-1"></i>{{ ucfirst($bill->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $bill->due_date->format('M d, Y') }}</div>
                                        @if($bill->due_date->isPast() && $bill->balance_amount > 0)
                                            <div class="text-xs text-red-500">{{ $bill->due_date->diffForHumans() }}</div>
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
                                <i class="fas fa-file-invoice text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Revenue Data</h3>
                            <p class="text-gray-500">No bills found for the selected date range.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($bills->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $bills->appends(request()->query())->links() }}
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
    
    let csv = 'Bill Date,Bill Number,Customer Name,Account Number,Total Amount,Paid Amount,Balance,Status,Due Date\n';
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = [
            cells[0].querySelector('.text-sm.font-medium').textContent.trim(),
            cells[1].querySelector('.text-sm.font-medium').textContent.trim(),
            cells[2].querySelector('.text-sm.font-medium').textContent.trim(),
            cells[2].querySelector('.text-sm.text-gray-500').textContent.trim(),
            cells[3].textContent.trim(),
            cells[4].textContent.trim(),
            cells[5].textContent.trim(),
            cells[6].querySelector('span').textContent.trim(),
            cells[7].querySelector('.text-sm.text-gray-900').textContent.trim()
        ];
        csv += rowData.map(field => `"${field}"`).join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', `revenue-report-{{ $startDate }}-to-{{ $endDate }}.csv`);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>
@endsection 