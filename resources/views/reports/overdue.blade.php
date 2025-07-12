@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-red-600 to-orange-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-exclamation-triangle mr-3"></i>Overdue Bills Report
                    </h1>
                    <p class="text-red-100 mt-2">Track overdue payments and collection priorities</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="window.print()" class="bg-white text-red-600 px-4 py-2 rounded-lg font-medium hover:bg-red-50 transition duration-200">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                    <button onclick="exportReport()" class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-yellow-600 transition duration-200">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </button>
                    <button onclick="generateDemandLetters()" class="bg-blue-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-600 transition duration-200">
                        <i class="fas fa-envelope mr-2"></i>Generate Demand Letters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                            <i class="fas fa-file-invoice-dollar text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Overdue Bills</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ number_format($overdueCount) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-500 text-white">
                            <i class="fas fa-dollar-sign text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Overdue Amount</dt>
                            <dd class="text-2xl font-semibold text-gray-900">LKR {{ number_format($totalOverdueAmount, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Average Overdue</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $overdueCount > 0 ? number_format($totalOverdueAmount / $overdueCount, 0) : 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                            <i class="fas fa-calendar-times text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">90+ Days</dt>
                            <dd class="text-2xl font-semibold text-gray-900">LKR {{ number_format($overdueByPeriod['90+'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aging Analysis -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-chart-pie mr-2 text-red-600"></i>Aging Analysis
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-yellow-800">1-30 Days</h4>
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-yellow-900">LKR {{ number_format($overdueByPeriod['1-30'], 2) }}</div>
                    <div class="text-sm text-yellow-700 mt-1">Recently overdue</div>
                </div>

                <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-orange-800">31-60 Days</h4>
                        <i class="fas fa-exclamation text-orange-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-orange-900">LKR {{ number_format($overdueByPeriod['31-60'], 2) }}</div>
                    <div class="text-sm text-orange-700 mt-1">Follow up required</div>
                </div>

                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-red-800">61-90 Days</h4>
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-red-900">LKR {{ number_format($overdueByPeriod['61-90'], 2) }}</div>
                    <div class="text-sm text-red-700 mt-1">Urgent action needed</div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-800">90+ Days</h4>
                        <i class="fas fa-ban text-gray-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">LKR {{ number_format($overdueByPeriod['90+'], 2) }}</div>
                    <div class="text-sm text-gray-700 mt-1">Collection/Legal action</div>
                </div>
            </div>
        </div>

        <!-- Overdue Bills Table -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-table mr-2 text-red-600"></i>Overdue Bills Details
                </h3>
                <div class="flex space-x-2">
                    <input type="text" id="searchInput" placeholder="Search customers..." class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <select id="agingFilter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">All Ages</option>
                        <option value="1-30">1-30 Days</option>
                        <option value="31-60">31-60 Days</option>
                        <option value="61-90">61-90 Days</option>
                        <option value="90+">90+ Days</option>
                    </select>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                @if($overdueBills->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200" id="overdueTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overdue Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($overdueBills as $bill)
                                @php
                                    $daysOverdue = $bill->due_date->diffInDays(now());
                                    $priority = $daysOverdue > 90 ? 'critical' : ($daysOverdue > 60 ? 'high' : ($daysOverdue > 30 ? 'medium' : 'low'));
                                    $priorityClass = [
                                        'critical' => 'bg-red-100 text-red-800',
                                        'high' => 'bg-orange-100 text-orange-800',
                                        'medium' => 'bg-yellow-100 text-yellow-800',
                                        'low' => 'bg-blue-100 text-blue-800'
                                    ][$priority];
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150" data-days="{{ $daysOverdue }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="bill-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500" value="{{ $bill->id }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-red-500 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-white">{{ substr($bill->customer->first_name, 0, 1) }}{{ substr($bill->customer->last_name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $bill->customer ? $bill->customer->full_name : 'Unknown Customer' }}</div>
                                                <div class="text-sm text-gray-500">{{ $bill->customer ? $bill->customer->account_number : 'No Account' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $bill->bill_number }}</div>
                                        <div class="text-sm text-gray-500">Bill Date: {{ $bill->bill_date->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">Due Date: {{ $bill->due_date->format('M d, Y') }}</div>
                                        @if($bill->waterMeter)
                                            <div class="text-sm text-gray-500">Meter: {{ $bill->waterMeter->meter_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-red-600">LKR {{ number_format($bill->balance_amount, 2) }}</div>
                                        <div class="text-sm text-gray-500">Total: LKR {{ number_format($bill->total_amount, 2) }}</div>
                                        <div class="text-sm text-gray-500">Paid: LKR {{ number_format($bill->paid_amount, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-gray-900">{{ $daysOverdue }}</div>
                                        <div class="text-sm text-gray-500">{{ $daysOverdue === 1 ? 'day' : 'days' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if($bill->customer->phone_number)
                                                <div class="flex items-center mb-1">
                                                    <i class="fas fa-phone text-green-600 mr-2"></i>
                                                    <a href="tel:{{ $bill->customer->phone_number }}" class="text-blue-600 hover:text-blue-800">{{ $bill->customer->phone_number }}</a>
                                                </div>
                                            @endif
                                            @if($bill->customer->email)
                                                <div class="flex items-center">
                                                    <i class="fas fa-envelope text-blue-600 mr-2"></i>
                                                    <a href="mailto:{{ $bill->customer->email }}" class="text-blue-600 hover:text-blue-800 text-xs">{{ $bill->customer->email }}</a>
                                                </div>
                                            @endif
                                            @if(!$bill->customer->phone_number && !$bill->customer->email)
                                                <span class="text-gray-400 text-sm">No contact info</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityClass }}">
                                            @if($priority === 'critical')
                                                <i class="fas fa-ban mr-1"></i>Critical
                                            @elseif($priority === 'high')
                                                <i class="fas fa-exclamation-triangle mr-1"></i>High
                                            @elseif($priority === 'medium')
                                                <i class="fas fa-exclamation mr-1"></i>Medium
                                            @else
                                                <i class="fas fa-info mr-1"></i>Low
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('bills.show', $bill) }}" class="text-blue-600 hover:text-blue-900" title="View Bill">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customers.show', $bill->customer) }}" class="text-green-600 hover:text-green-900" title="View Customer">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            @if($bill->customer->phone_number)
                                                <a href="tel:{{ $bill->customer->phone_number }}" class="text-purple-600 hover:text-purple-900" title="Call Customer">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            @endif
                                            <button onclick="sendReminder({{ $bill->id }})" class="text-yellow-600 hover:text-yellow-900" title="Send Reminder">
                                                <i class="fas fa-bell"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-12">
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <i class="fas fa-check-circle text-3xl text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Overdue Bills!</h3>
                            <p class="text-gray-500">All bills are up to date. Great job on collections!</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($overdueBills->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $overdueBills->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const agingFilter = document.getElementById('agingFilter');
    const table = document.getElementById('overdueTable');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const ageFilter = agingFilter.value;
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const days = parseInt(row.getAttribute('data-days'));
            
            let showBySearch = searchTerm === '' || text.includes(searchTerm);
            let showByAge = ageFilter === '' || 
                (ageFilter === '1-30' && days >= 1 && days <= 30) ||
                (ageFilter === '31-60' && days >= 31 && days <= 60) ||
                (ageFilter === '61-90' && days >= 61 && days <= 90) ||
                (ageFilter === '90+' && days > 90);

            row.style.display = showBySearch && showByAge ? '' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filterTable);
    agingFilter.addEventListener('change', filterTable);

    // Select all functionality
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.bill-checkbox');

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function exportReport() {
    const table = document.querySelector('table');
    if (!table) {
        alert('No data to export');
        return;
    }
    
    let csv = 'Customer Name,Account Number,Bill Number,Bill Date,Due Date,Overdue Amount,Days Overdue,Phone,Email,Priority\n';
    
    const rows = table.querySelectorAll('tbody tr[style!="display: none;"]');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = [
            cells[1].querySelector('.text-sm.font-medium').textContent.trim(),
            cells[1].querySelectorAll('.text-sm.text-gray-500')[0].textContent.trim(),
            cells[2].querySelector('.text-sm.font-medium').textContent.trim(),
            cells[2].querySelectorAll('.text-sm.text-gray-500')[0].textContent.replace('Bill Date: ', '').trim(),
            cells[2].querySelectorAll('.text-sm.text-gray-500')[1].textContent.replace('Due Date: ', '').trim(),
            cells[3].querySelector('.text-lg.font-bold').textContent.trim(),
            cells[4].querySelector('.text-lg.font-bold').textContent.trim(),
            cells[5].querySelector('a[href^="tel:"]') ? cells[5].querySelector('a[href^="tel:"]').textContent.trim() : '',
            cells[5].querySelector('a[href^="mailto:"]') ? cells[5].querySelector('a[href^="mailto:"]').textContent.trim() : '',
            cells[6].querySelector('span').textContent.trim()
        ];
        csv += rowData.map(field => `"${field}"`).join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'overdue-bills-report.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function generateDemandLetters() {
    const selectedBills = Array.from(document.querySelectorAll('.bill-checkbox:checked')).map(cb => cb.value);
    
    if (selectedBills.length === 0) {
        alert('Please select bills to generate demand letters for.');
        return;
    }
    
    // Here you would implement the demand letter generation logic
    alert(`Generating demand letters for ${selectedBills.length} selected bills...`);
}

function sendReminder(billId) {
    // Here you would implement the reminder sending logic
    alert(`Sending reminder for bill ID: ${billId}`);
}
</script>
@endsection 