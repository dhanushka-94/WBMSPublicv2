@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-list mr-3"></i>Bulk Meter Reading Entry
                    </h1>
                    <p class="text-blue-100 mt-2">Enter multiple meter readings efficiently for monthly billing</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('readings.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Readings
                    </a>
                    <a href="{{ route('readings.create') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Single Entry
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Instructions Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-blue-900">Bulk Reading Entry Instructions</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Fill in the current reading for each meter</li>
                            <li>Previous readings are automatically loaded from the system</li>
                            <li>Consumption will be calculated automatically</li>
                            <li>Use "Actual" for physical meter readings, "Estimated" for calculated readings</li>
                            <li>Only submit readings that are greater than or equal to the previous reading</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <form id="bulkReadingForm" action="{{ route('readings.bulk.store') }}" method="POST">
            @csrf
            
            <!-- Global Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cog mr-2"></i>Reading Settings
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="global_reading_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Reading Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="global_reading_date" value="{{ date('Y-m-d') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="global_reading_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Reading Type <span class="text-red-500">*</span>
                            </label>
                            <select id="global_reading_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="actual">Actual Reading</option>
                                <option value="estimated">Estimated Reading</option>
                                <option value="customer_read">Customer Read</option>
                            </select>
                        </div>

                        <div>
                            <label for="global_reader_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Reader Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="global_reader_name" value="{{ Auth::user()->name }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" id="applyGlobalSettings" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-magic mr-2"></i>Apply to All Rows
                        </button>
                    </div>
                </div>
            </div>

            <!-- Meter Readings Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-table mr-2"></i>Meter Readings (<span id="totalCount">{{ $waterMeters->count() }}</span> meters)
                        </h3>
                        <div class="text-sm text-gray-600">
                            <span id="filledCount">0</span> of <span id="visibleCount">{{ $waterMeters->count() }}</span> completed
                        </div>
                    </div>
                    
                    <!-- Search and Filter Section -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>Search Customers/Meters
                            </label>
                            <div class="relative">
                                <input type="text" id="meterTableSearch" 
                                       placeholder="Search by customer name, account number, or meter number..." 
                                       class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       oninput="filterMeterTable()">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-filter mr-1"></i>Filter by Status
                            </label>
                            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="filterMeterTable()">
                                <option value="">All Meters</option>
                                <option value="filled">Readings Entered</option>
                                <option value="empty">No Reading Yet</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-sort mr-1"></i>Sort by
                            </label>
                            <select id="sortFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="sortMeterTable()">
                                <option value="meter_number">Meter Number</option>
                                <option value="customer_name">Customer Name</option>
                                <option value="account_number">Account Number</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button type="button" onclick="showOnlyUnfilled()" class="text-sm bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full hover:bg-yellow-200 transition">
                            <i class="fas fa-eye mr-1"></i>Show Only Unfilled
                        </button>
                        <button type="button" onclick="showAll()" class="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full hover:bg-blue-200 transition">
                            <i class="fas fa-list mr-1"></i>Show All
                        </button>
                        <button type="button" onclick="clearAllFilters()" class="text-sm bg-gray-100 text-gray-800 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                            <i class="fas fa-times mr-1"></i>Clear Filters
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer & Meter</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous Reading</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Reading</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consumption</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reader</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($waterMeters as $index => $meter)
                                @php
                                    $lastReading = $meter->meterReadings->first();
                                    $previousReading = $lastReading ? $lastReading->current_reading : $meter->initial_reading;
                                @endphp
                                <tr class="hover:bg-gray-50 reading-row" 
                                    data-customer-name="{{ $meter->customer ? strtolower($meter->customer->full_name) : 'unassigned' }}"
                                    data-account-number="{{ $meter->customer ? strtolower($meter->customer->account_number) : 'no-account' }}"
                                    data-meter-number="{{ strtolower($meter->meter_number) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
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
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ number_format($previousReading) }}</div>
                                        @if($lastReading)
                                            <div class="text-xs text-gray-500">{{ $lastReading->reading_date ? $lastReading->reading_date->format('M d, Y') : 'No date' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <input type="number" 
                                               name="readings[{{ $index }}][current_reading]" 
                                               class="current-reading w-24 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                               min="{{ $previousReading }}" 
                                               step="1" 
                                               data-previous="{{ $previousReading }}"
                                               data-index="{{ $index }}">
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="consumption text-sm font-medium text-blue-600" data-index="{{ $index }}">0 units</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <input type="date" 
                                               name="readings[{{ $index }}][reading_date]" 
                                               class="reading-date w-32 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                               value="{{ date('Y-m-d') }}" 
                                               required>
                                    </td>
                                    <td class="px-4 py-4">
                                        <select name="readings[{{ $index }}][reading_type]" 
                                                class="reading-type w-24 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                                required>
                                            <option value="actual">Actual</option>
                                            <option value="estimated">Estimated</option>
                                            <option value="customer_read">Customer</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-4">
                                        <input type="text" 
                                               name="readings[{{ $index }}][reader_name]" 
                                               class="reader-name w-24 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                               value="{{ Auth::user()->name }}" 
                                               required>
                                    </td>
                                    <td class="px-4 py-4">
                                        <input type="text" 
                                               name="readings[{{ $index }}][notes]" 
                                               class="w-32 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                               placeholder="Optional notes...">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Form Actions -->
                <div class="p-6 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            <span id="progressText">Complete all readings before submitting</span>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('readings.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-600 transition duration-200">
                                Cancel
                            </a>
                            <button type="submit" id="submitButton" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200" disabled>
                                <i class="fas fa-save mr-2"></i>Save All Readings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bulkReadingForm');
    const submitButton = document.getElementById('submitButton');
    const filledCountElement = document.getElementById('filledCount');
    const progressText = document.getElementById('progressText');
    const totalRows = {{ $waterMeters->count() }};

    // Apply global settings
    document.getElementById('applyGlobalSettings').addEventListener('click', function() {
        const globalDate = document.getElementById('global_reading_date').value;
        const globalType = document.getElementById('global_reading_type').value;
        const globalReader = document.getElementById('global_reader_name').value;

        document.querySelectorAll('.reading-date').forEach(input => {
            input.value = globalDate;
        });

        document.querySelectorAll('.reading-type').forEach(select => {
            select.value = globalType;
        });

        document.querySelectorAll('.reader-name').forEach(input => {
            input.value = globalReader;
        });
    });

    // Calculate consumption and update progress
    function updateConsumption(input) {
        const index = input.dataset.index;
        const previousReading = parseFloat(input.dataset.previous) || 0;
        const currentReading = parseFloat(input.value) || 0;
        const consumption = Math.max(0, currentReading - previousReading);
        
        const consumptionElement = document.querySelector(`.consumption[data-index="${index}"]`);
        consumptionElement.textContent = consumption.toLocaleString() + ' units';
        
        // Highlight if consumption seems unusual
        if (consumption > 100) {
            consumptionElement.classList.add('text-red-600');
            consumptionElement.classList.remove('text-blue-600');
        } else {
            consumptionElement.classList.add('text-blue-600');
            consumptionElement.classList.remove('text-red-600');
        }
    }

    // Update progress counter
    function updateProgress() {
        const filledInputs = document.querySelectorAll('.current-reading').length;
        const completedInputs = Array.from(document.querySelectorAll('.current-reading')).filter(input => input.value.trim() !== '').length;
        
        filledCountElement.textContent = completedInputs;
        
        if (completedInputs === totalRows) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50');
            progressText.textContent = 'All readings completed - ready to submit';
            progressText.classList.add('text-green-600');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50');
            progressText.textContent = `Complete ${totalRows - completedInputs} more readings`;
            progressText.classList.remove('text-green-600');
        }
    }

    // Add event listeners to current reading inputs
    document.querySelectorAll('.current-reading').forEach(input => {
        input.addEventListener('input', function() {
            updateConsumption(this);
            updateProgress();
            
            // Validate reading is not less than previous
            const previousReading = parseFloat(this.dataset.previous) || 0;
            const currentReading = parseFloat(this.value) || 0;
            
            if (currentReading < previousReading) {
                this.setCustomValidity('Current reading cannot be less than previous reading');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });

        input.addEventListener('blur', function() {
            // Format the number when user leaves the field
            if (this.value) {
                this.value = Math.round(parseFloat(this.value) || 0);
            }
        });
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const emptyReadings = Array.from(document.querySelectorAll('.current-reading')).filter(input => !input.value.trim());
        
        if (emptyReadings.length > 0) {
            e.preventDefault();
            alert(`Please fill in all ${emptyReadings.length} missing readings before submitting.`);
            emptyReadings[0].focus();
            return false;
        }

        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        
        return true;
    });

    // Initial progress update
    updateProgress();
});

// Search and filter functions
function filterMeterTable() {
    const searchTerm = document.getElementById('meterTableSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.reading-row');
    let visibleCount = 0;

    rows.forEach(row => {
        let showRow = true;
        
        // Text search
        if (searchTerm) {
            const customerName = row.dataset.customerName;
            const accountNumber = row.dataset.accountNumber;
            const meterNumber = row.dataset.meterNumber;
            const searchData = customerName + ' ' + accountNumber + ' ' + meterNumber;
            
            if (!searchData.includes(searchTerm)) {
                showRow = false;
            }
        }
        
        // Status filter
        if (statusFilter && showRow) {
            const currentReadingInput = row.querySelector('.current-reading');
            const hasFilled = currentReadingInput.value.trim() !== '';
            
            if (statusFilter === 'filled' && !hasFilled) {
                showRow = false;
            } else if (statusFilter === 'empty' && hasFilled) {
                showRow = false;
            }
        }
        
        if (showRow) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update counter
    document.getElementById('visibleCount').textContent = visibleCount;
    updateProgress();
}

function sortMeterTable() {
    const sortBy = document.getElementById('sortFilter').value;
    const tbody = document.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('.reading-row'));
    
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch (sortBy) {
            case 'customer_name':
                aValue = a.querySelector('td:first-child .text-gray-900').textContent;
                bValue = b.querySelector('td:first-child .text-gray-900').textContent;
                break;
            case 'meter_number':
                aValue = a.querySelector('td:first-child .text-gray-500').textContent;
                bValue = b.querySelector('td:first-child .text-gray-500').textContent;
                break;
            case 'account_number':
                // Extract account number from customer name or data attributes
                aValue = a.querySelector('td:first-child .text-gray-900').textContent;
                bValue = b.querySelector('td:first-child .text-gray-900').textContent;
                break;
            default:
                aValue = a.querySelector('td:first-child .text-gray-500').textContent;
                bValue = b.querySelector('td:first-child .text-gray-500').textContent;
        }
        
        return aValue.localeCompare(bValue);
    });
    
    // Remove all rows and re-append in sorted order
    rows.forEach(row => tbody.appendChild(row));
}

function showOnlyUnfilled() {
    document.getElementById('statusFilter').value = 'empty';
    document.getElementById('meterTableSearch').value = '';
    filterMeterTable();
}

function showAll() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('meterTableSearch').value = '';
    filterMeterTable();
}

function clearAllFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('meterTableSearch').value = '';
    document.getElementById('sortFilter').value = 'meter_number';
    filterMeterTable();
    sortMeterTable();
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-save functionality (optional)
    let autoSaveTimeout;
    document.addEventListener('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // Could implement auto-save to localStorage here
            console.log('Auto-saving progress...');
        }, 2000);
    });
});
</script>
@endsection 