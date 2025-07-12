@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-calendar-alt mr-3"></i>Monthly Reading Schedule
                    </h1>
                    <p class="text-blue-100 mt-2">Track meter reading progress for {{ Carbon\Carbon::create(null, $currentMonth)->format('F') }} {{ $currentYear }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('readings.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Readings
                    </a>
                    <a href="{{ route('readings.create') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition duration-200">
                        <i class="fas fa-plus mr-2"></i>New Reading
                    </a>
                    <a href="{{ route('readings.bulk') }}" class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-yellow-600 transition duration-200">
                        <i class="fas fa-list mr-2"></i>Bulk Entry
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <i class="fas fa-tachometer-alt text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Meters</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $readMeters->count() + $unreadMeters->count() }}</dd>
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Readings Completed</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $readMeters->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Readings</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $unreadMeters->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                            <i class="fas fa-percentage text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completion Rate</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                {{ ($readMeters->count() + $unreadMeters->count()) > 0 ? round(($readMeters->count() / ($readMeters->count() + $unreadMeters->count())) * 100, 1) : 0 }}%
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active" data-tab="pending">
                        <i class="fas fa-clock mr-2"></i>Pending Readings ({{ $unreadMeters->count() }})
                    </button>
                    <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="completed">
                        <i class="fas fa-check-circle mr-2"></i>Completed Readings ({{ $readMeters->count() }})
                    </button>
                </nav>
            </div>

            <!-- Pending Readings Tab -->
            <div id="pending-tab" class="tab-content p-6">
                @if($unreadMeters->count() > 0)
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            Meters Requiring Readings ({{ $unreadMeters->count() }})
                        </h3>
                        <div class="flex space-x-2">
                            <input type="text" id="pendingSearch" placeholder="Search meters..." class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button onclick="printPendingList()" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-print mr-2"></i>Print List
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="pendingTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meter #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Reading</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($unreadMeters as $meter)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-sm font-medium text-gray-900">{{ $meter->meter_number }}</span>
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Pending
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    @if($meter->customer)
                                                        <img class="h-10 w-10 rounded-full object-cover" 
                                                             src="{{ $meter->customer->profile_photo_url }}" 
                                                             alt="{{ $meter->customer->full_name }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <i class="fas fa-user text-gray-500"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $meter->customer ? $meter->customer->full_name : 'Unassigned Customer' }}</div>
                                                    <div class="text-sm text-gray-500">{{ $meter->customer ? $meter->customer->account_number : 'No Account' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $meter->customer ? $meter->customer->account_number : 'N/A' }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $meter->customer ? $meter->customer->address : 'No address' }}</div>
                                            @if($meter->location_notes)
                                                <div class="text-sm text-gray-500">📍 {{ $meter->location_notes }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $lastReading = $meter->meterReadings->sortByDesc('reading_date')->first();
                                            @endphp
                                            @if($lastReading)
                                                <div class="text-sm text-gray-900">{{ number_format($lastReading->current_reading) }} units</div>
                                                <div class="text-sm text-gray-500">{{ $lastReading->reading_date ? $lastReading->reading_date->format('M d, Y') : 'No date' }}</div>
                                            @else
                                                <div class="text-sm text-gray-500">Initial: {{ number_format($meter->initial_reading) }}</div>
                                                <div class="text-sm text-gray-400">No previous readings</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('readings.create', ['meter' => $meter->id]) }}" class="text-blue-600 hover:text-blue-900" title="Add Reading">
                                                    <i class="fas fa-plus-circle"></i>
                                                </a>
                                                <a href="{{ route('meters.show', $meter) }}" class="text-gray-600 hover:text-gray-900" title="View Meter">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($meter->customer)
                                                    <a href="{{ route('customers.show', $meter->customer) }}" class="text-green-600 hover:text-green-900" title="View Customer">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                @else
                                                    <span class="text-gray-400" title="No customer assigned">
                                                        <i class="fas fa-user-slash"></i>
                                                    </span>
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
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <i class="fas fa-check-circle text-3xl text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">All Readings Complete!</h3>
                            <p class="text-gray-500">All meters have been read for {{ Carbon\Carbon::create(null, $currentMonth)->format('F') }} {{ $currentYear }}.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Completed Readings Tab -->
            <div id="completed-tab" class="tab-content p-6 hidden">
                @if($readMeters->count() > 0)
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Completed Readings ({{ $readMeters->count() }})
                        </h3>
                        <input type="text" id="completedSearch" placeholder="Search meters..." class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="completedTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meter #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($readMeters as $meter)
                                    @php
                                        $latestReading = $meter->meterReadings->first();
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-sm font-medium text-gray-900">{{ $meter->meter_number }}</span>
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Read
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($meter->customer)
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-white">{{ substr($meter->customer->first_name, 0, 1) }}{{ substr($meter->customer->last_name, 0, 1) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $meter->customer->full_name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $meter->customer->account_number }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-orange-500 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-white">?</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-orange-600">Unassigned</div>
                                                        <div class="text-sm text-gray-500">No customer assigned</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($latestReading)
                                                <div class="text-sm font-medium text-gray-900">{{ number_format($latestReading->current_reading) }} units</div>
                                                <div class="text-sm text-gray-500">Reader: {{ $latestReading->reader_name }}</div>
                                            @else
                                                <div class="text-sm font-medium text-gray-500">No reading available</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($latestReading && $latestReading->reading_date)
                                                <div class="text-sm text-gray-900">{{ $latestReading->reading_date->format('M d, Y') }}</div>
                                                <div class="text-sm text-gray-500">{{ $latestReading->reading_date->format('h:i A') }}</div>
                                            @else
                                                <div class="text-sm text-gray-500">No date available</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($latestReading)
                                                @if($latestReading->status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1"></i>Pending
                                                    </span>
                                                @elseif($latestReading->status === 'verified')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check mr-1"></i>Verified
                                                    </span>
                                                @elseif($latestReading->status === 'billed')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check-double mr-1"></i>Billed
                                                    </span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-minus mr-1"></i>No Reading
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                @if($latestReading && $latestReading->id)
                                                    <a href="{{ route('readings.show', $latestReading) }}" class="text-blue-600 hover:text-blue-900" title="View Reading">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($latestReading->status !== 'billed')
                                                        <a href="{{ route('readings.edit', $latestReading) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit Reading">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400" title="No reading available">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                                @if($meter->customer)
                                                    <a href="{{ route('customers.show', $meter->customer) }}" class="text-green-600 hover:text-green-900" title="View Customer">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                @else
                                                    <span class="text-gray-400" title="No customer assigned">
                                                        <i class="fas fa-user-slash"></i>
                                                    </span>
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
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
                                <i class="fas fa-calendar-alt text-3xl text-blue-600"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Readings Yet</h3>
                            <p class="text-gray-500">No meter readings have been recorded for {{ Carbon\Carbon::create(null, $currentMonth)->format('F') }} {{ $currentYear }} yet.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Update button states
            tabButtons.forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600', 'active');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600', 'active');
            
            // Show/hide content
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(targetTab + '-tab').classList.remove('hidden');
        });
    });

    // Search functionality for pending meters
    const pendingSearch = document.getElementById('pendingSearch');
    if (pendingSearch) {
        pendingSearch.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('pendingTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
    }

    // Search functionality for completed meters
    const completedSearch = document.getElementById('completedSearch');
    if (completedSearch) {
        completedSearch.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('completedTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
    }
});

// Print pending meters list
function printPendingList() {
    const printContent = document.getElementById('pending-tab').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write(`
        <html>
        <head>
            <title>Pending Meter Readings - {{ Carbon\Carbon::create(null, $currentMonth)->format('F') }} {{ $currentYear }}</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .no-print { display: none; }
            </style>
        </head>
        <body>
            <h2>Pending Meter Readings - {{ Carbon\Carbon::create(null, $currentMonth)->format('F') }} {{ $currentYear }}</h2>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>
@endsection 