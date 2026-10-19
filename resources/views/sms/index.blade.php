@extends('layouts.app')

@section('title', 'SMS Notifications Log')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-sms text-white text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-white">SMS Notifications Log</h1>
                    </div>
                    <a href="{{ route('sms.statistics') }}" 
                       class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i>Statistics
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-envelope text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($stats['total']) }}</div>
                        <div class="text-sm opacity-90">Total SMS</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-check-circle text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($stats['sent']) }}</div>
                        <div class="text-sm opacity-90">Sent</div>
                    </div>
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-times-circle text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($stats['failed']) }}</div>
                        <div class="text-sm opacity-90">Failed</div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-clock text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($stats['pending']) }}</div>
                        <div class="text-sm opacity-90">Pending</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-calendar-day text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($stats['today']) }}</div>
                        <div class="text-sm opacity-90">Today</div>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg p-4 text-white text-center shadow-lg">
                        <i class="fas fa-calendar-week text-2xl mb-2 opacity-80"></i>
                        <div class="text-2xl font-bold">{{ number_format($stats['this_month']) }}</div>
                        <div class="text-sm opacity-90">This Month</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <form method="GET" action="{{ route('sms.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">All Statuses</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">All Types</option>
                                <option value="new_bill" {{ request('type') == 'new_bill' ? 'selected' : '' }}>New Bill</option>
                                <option value="due_reminder" {{ request('type') == 'due_reminder' ? 'selected' : '' }}>Due Reminder</option>
                                <option value="overdue_alert" {{ request('type') == 'overdue_alert' ? 'selected' : '' }}>Overdue Alert</option>
                                <option value="payment_confirmation" {{ request('type') == 'payment_confirmation' ? 'selected' : '' }}>Payment Confirmation</option>
                                <option value="late_fee_notice" {{ request('type') == 'late_fee_notice' ? 'selected' : '' }}>Late Fee Notice</option>
                                <option value="service_disconnection" {{ request('type') == 'service_disconnection' ? 'selected' : '' }}>Service Disconnection</option>
                                <option value="meter_reading_reminder" {{ request('type') == 'meter_reading_reminder' ? 'selected' : '' }}>Meter Reading</option>
                                <option value="maintenance_notice" {{ request('type') == 'maintenance_notice' ? 'selected' : '' }}>Maintenance Notice</option>
                                <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                            <input type="text" name="customer" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" 
                                   placeholder="Name or Account#" value="{{ request('customer') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" name="phone" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" 
                                   placeholder="Phone number" value="{{ request('phone') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" 
                                   value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" name="date_to" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('sms.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                        <a href="{{ route('sms.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                            <i class="fas fa-download mr-2"></i>Export CSV
                        </a>
                    </div>
                </form>
            </div>

            <!-- SMS List -->
            <div class="p-6">
                @if($notifications->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                        <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($notifications as $notification)
                                    <tr class="hover:bg-gray-50 transition duration-200">
                                        <td class="px-4 py-4">
                                            <input type="checkbox" name="selected_ids[]" 
                                                   value="{{ $notification->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 row-checkbox">
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">
                                            <div>{{ $notification->created_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $notification->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if($notification->customer)
                                                <div class="text-sm font-medium text-gray-900">{{ $notification->customer->full_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $notification->customer->account_number }}</div>
                                            @else
                                                <span class="text-sm text-gray-400">No Customer</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $notification->phone_number }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $notification->getTypeLabelAttribute() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if($notification->status === 'sent')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>Sent
                                                </span>
                                            @elseif($notification->status === 'failed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i>Failed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>Pending
                                                </span>
                                            @endif
                                            @if($notification->retry_count > 0)
                                                <div class="text-xs text-gray-400 mt-1">Retries: {{ $notification->retry_count }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="max-w-xs">
                                                <p class="text-sm text-gray-900 truncate">{{ Str::limit($notification->message, 80) }}</p>
                                                @if(strlen($notification->message) > 80)
                                                    <button type="button" class="text-blue-600 hover:text-blue-800 text-xs mt-1" 
                                                            onclick="showMessage{{ $notification->id }}()">
                                                        Read more...
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('sms.show', $notification) }}" 
                                                   class="text-blue-600 hover:text-blue-800 transition duration-200" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($notification->status === 'failed')
                                                    <form method="POST" action="{{ route('sms.resend', $notification) }}" 
                                                          style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-yellow-600 hover:text-yellow-800 transition duration-200" 
                                                                title="Resend SMS" onclick="return confirm('Resend this SMS?')">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('sms.destroy', $notification) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 transition duration-200" 
                                                            title="Delete Record" onclick="return confirm('Delete this SMS record?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Message Modal Script -->
                                    @if(strlen($notification->message) > 80)
                                        <script>
                                            function showMessage{{ $notification->id }}() {
                                                alert(`{{ addslashes($notification->message) }}`);
                                            }
                                        </script>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="mt-6 flex items-center justify-between">
                        <div>
                            <form method="POST" action="{{ route('sms.bulk-delete') }}" id="bulk-delete-form" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hidden bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200" 
                                        id="bulk-delete-btn" onclick="return confirm('Delete selected SMS records?')">
                                    <i class="fas fa-trash mr-2"></i>Delete Selected
                                </button>
                            </form>
                        </div>
                        <div>
                            {{ $notifications->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-sms text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No SMS notifications found</h3>
                        <p class="text-gray-500">SMS messages will appear here once they are sent.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const bulkDeleteForm = document.getElementById('bulk-delete-form');

    // Select all functionality
    selectAll.addEventListener('change', function() {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        toggleBulkActions();
    });

    // Individual checkbox change
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            selectAll.checked = checkedCount === rowCheckboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
            toggleBulkActions();
        });
    });

    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkedBoxes.length > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            // Add hidden inputs for selected IDs
            bulkDeleteForm.querySelectorAll('input[name="selected_ids[]"]').forEach(input => input.remove());
            checkedBoxes.forEach(checkbox => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'selected_ids[]';
                hiddenInput.value = checkbox.value;
                bulkDeleteForm.appendChild(hiddenInput);
            });
        } else {
            bulkDeleteBtn.classList.add('hidden');
        }
    }
});
</script>
@endsection