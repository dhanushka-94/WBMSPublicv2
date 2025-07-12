@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-plus-circle mr-3"></i>New Meter Reading
                    </h1>
                    <p class="text-blue-100 mt-2">Record a new water meter reading for billing</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('readings.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Readings
                    </a>
                    <a href="{{ route('readings.bulk') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition duration-200">
                        <i class="fas fa-list mr-2"></i>Bulk Entry
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Instructions Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-blue-900">Reading Entry Guidelines</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Select the water meter you want to record a reading for</li>
                                <li>Enter the current reading as shown on the meter display</li>
                                <li>Ensure the reading is greater than or equal to the previous reading</li>
                                <li>Use "Actual" for physical meter readings, "Estimated" for calculated readings</li>
                                <li>Add any relevant notes about the reading or meter condition</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reading Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-edit mr-2"></i>Meter Reading Details
                    </h3>
                </div>

                <form action="{{ route('readings.store') }}" method="POST" id="readingForm">
                    @csrf
                    
                    <div class="p-6 space-y-6">
                        <!-- Water Meter Selection -->
                        <div>
                            <label for="water_meter_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Water Meter <span class="text-red-500">*</span>
                            </label>
                            
                            <!-- Search Input -->
                            <div class="relative mb-3">
                                <input type="text" id="meterSearch" 
                                       placeholder="Search by customer name, account number, or meter number..." 
                                       class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       oninput="filterMeters()">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                            
                            <!-- Enhanced Meter Selection -->
                            <select name="water_meter_id" id="water_meter_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('water_meter_id') border-red-500 @enderror" 
                                    required onchange="loadMeterDetails()" size="8">
                                <option value="">Select a water meter...</option>
                                @foreach($waterMeters as $meter)
                                    <option value="{{ $meter->id }}" 
                                            data-brand="{{ $meter->meter_brand }}" 
                                            data-model="{{ $meter->meter_model }}" 
                                            data-type="{{ $meter->meter_type }}" 
                                            data-last-reading="{{ $meter->current_reading }}" 
                                            data-search="{{ $meter->customer ? strtolower($meter->meter_number . ' ' . $meter->customer->full_name . ' ' . $meter->customer->account_number) : strtolower($meter->meter_number . ' unassigned no-account') }}"
                                            {{ old('water_meter_id') == $meter->id ? 'selected' : '' }}>
                                        📊 {{ $meter->meter_number }} | 👤 {{ $meter->customer ? $meter->customer->full_name : 'Unassigned Customer' }} | 🏠 {{ $meter->customer ? $meter->customer->account_number : 'No Account' }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <div class="mt-2 text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>{{ $waterMeters->count() }}</strong> active meters available. 
                                Use the search box above to quickly find customers by name, account number, or meter number.
                            </div>
                            
                            @error('water_meter_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Meter Details (Loaded via AJAX) -->
                        <div id="meterDetails" class="hidden bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Meter Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Customer:</span>
                                    <span id="customerName" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Account Number:</span>
                                    <span id="accountNumber" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Meter Type:</span>
                                    <span id="meterType" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Location:</span>
                                    <span id="meterLocation" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Previous Reading:</span>
                                    <span id="previousReading" class="text-gray-900 font-semibold"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Last Reading Date:</span>
                                    <span id="lastReadingDate" class="text-gray-900"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Reading Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="reading_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reading Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="reading_date" id="reading_date" 
                                       value="{{ old('reading_date', date('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reading_date') border-red-500 @enderror" 
                                       required>
                                @error('reading_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="current_reading" class="block text-sm font-medium text-gray-700 mb-2">
                                    Current Reading <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="current_reading" id="current_reading" 
                                       value="{{ old('current_reading') }}"
                                       min="0" step="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_reading') border-red-500 @enderror" 
                                       placeholder="Enter meter reading..."
                                       oninput="calculateConsumption()"
                                       required>
                                @error('current_reading')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Consumption Display -->
                        <div id="consumptionDisplay" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tint text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-md font-semibold text-green-900">Consumption Calculation</h4>
                                    <p class="text-sm text-green-700">
                                        <span id="consumptionText">0 units</span> consumed since last reading
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Reading Type and Reader Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="reading_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reading Type <span class="text-red-500">*</span>
                                </label>
                                <select name="reading_type" id="reading_type" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reading_type') border-red-500 @enderror" 
                                        required>
                                    <option value="actual" {{ old('reading_type') == 'actual' ? 'selected' : '' }}>Actual Reading</option>
                                    <option value="estimated" {{ old('reading_type') == 'estimated' ? 'selected' : '' }}>Estimated Reading</option>
                                    <option value="customer_read" {{ old('reading_type') == 'customer_read' ? 'selected' : '' }}>Customer Read</option>
                                </select>
                                @error('reading_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="reader_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reader Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="reader_name" id="reader_name" 
                                       value="{{ old('reader_name', Auth::user()->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reader_name') border-red-500 @enderror" 
                                       placeholder="Enter reader name..."
                                       required>
                                @error('reader_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror" 
                                      placeholder="Add any notes about the reading or meter condition...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('readings.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-600 transition duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>Save Reading
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let meterData = {};

// Load meter details when meter is selected
function loadMeterDetails() {
    const meterSelect = document.getElementById('water_meter_id');
    const meterId = meterSelect.value;
    
    if (!meterId) {
        document.getElementById('meterDetails').classList.add('hidden');
        document.getElementById('consumptionDisplay').classList.add('hidden');
        return;
    }

    // Show loading state
    const detailsDiv = document.getElementById('meterDetails');
    detailsDiv.classList.remove('hidden');
    detailsDiv.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-blue-600"></i> Loading meter details...</div>';

    // Fetch meter details via AJAX
    fetch(`{{ route('readings.meter-details') }}?meter_id=${meterId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                meterData = data.meter;
                displayMeterDetails(data.meter);
                updateReadingValidation(data.meter.previous_reading);
            } else {
                detailsDiv.innerHTML = '<div class="text-red-600">Error loading meter details</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            detailsDiv.innerHTML = '<div class="text-red-600">Error loading meter details</div>';
        });
}

// Display meter details
function displayMeterDetails(meter) {
    document.getElementById('customerName').textContent = meter.customer_name;
    document.getElementById('accountNumber').textContent = meter.account_number;
    document.getElementById('meterType').textContent = meter.meter_type;
    document.getElementById('meterLocation').textContent = meter.location_notes || 'Not specified';
    document.getElementById('previousReading').textContent = Number(meter.previous_reading).toLocaleString();
    document.getElementById('lastReadingDate').textContent = meter.last_reading_date || 'No previous reading';

    // Restore meter details HTML
    const detailsDiv = document.getElementById('meterDetails');
    detailsDiv.innerHTML = `
        <h4 class="text-md font-semibold text-gray-900 mb-3">Meter Information</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700">Customer:</span>
                <span id="customerName" class="text-gray-900">${meter.customer_name}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Account Number:</span>
                <span id="accountNumber" class="text-gray-900">${meter.account_number}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Meter Type:</span>
                <span id="meterType" class="text-gray-900">${meter.meter_type}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Location:</span>
                <span id="meterLocation" class="text-gray-900">${meter.location_notes || 'Not specified'}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Previous Reading:</span>
                <span id="previousReading" class="text-gray-900 font-semibold">${Number(meter.previous_reading).toLocaleString()}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Last Reading Date:</span>
                <span id="lastReadingDate" class="text-gray-900">${meter.last_reading_date || 'No previous reading'}</span>
            </div>
        </div>
    `;
}

// Update reading validation based on previous reading
function updateReadingValidation(previousReading) {
    const currentReadingInput = document.getElementById('current_reading');
    currentReadingInput.min = previousReading;
    currentReadingInput.placeholder = `Enter reading (minimum: ${Number(previousReading).toLocaleString()})`;
}

// Calculate consumption when current reading changes
function calculateConsumption() {
    const currentReading = parseFloat(document.getElementById('current_reading').value) || 0;
    const previousReading = meterData.previous_reading || 0;
    
    if (currentReading > 0 && previousReading !== undefined) {
        const consumption = Math.max(0, currentReading - previousReading);
        
        document.getElementById('consumptionText').textContent = consumption.toLocaleString() + ' units';
        document.getElementById('consumptionDisplay').classList.remove('hidden');
        
        // Highlight unusual consumption
        const displayDiv = document.getElementById('consumptionDisplay');
        if (consumption > 100) {
            displayDiv.className = 'bg-red-50 border border-red-200 rounded-lg p-4';
            displayDiv.querySelector('i').className = 'fas fa-exclamation-triangle text-red-600 text-xl';
            displayDiv.querySelector('h4').className = 'text-md font-semibold text-red-900';
            displayDiv.querySelector('p').className = 'text-sm text-red-700';
            document.getElementById('consumptionText').textContent = consumption.toLocaleString() + ' units (High consumption - please verify)';
        } else {
            displayDiv.className = 'bg-green-50 border border-green-200 rounded-lg p-4';
            displayDiv.querySelector('i').className = 'fas fa-tint text-green-600 text-xl';
            displayDiv.querySelector('h4').className = 'text-md font-semibold text-green-900';
            displayDiv.querySelector('p').className = 'text-sm text-green-700';
        }
    } else {
        document.getElementById('consumptionDisplay').classList.add('hidden');
    }
}

// Filter meters based on search input
function filterMeters() {
    const searchInput = document.getElementById('meterSearch');
    const meterSelect = document.getElementById('water_meter_id');
    const searchTerm = searchInput.value.toLowerCase();
    
    Array.from(meterSelect.options).forEach(option => {
        if (option.value === '') {
            option.style.display = 'block'; // Always show the default option
            return;
        }
        
        const searchData = option.getAttribute('data-search') || '';
        if (searchData.includes(searchTerm)) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Update the select size based on visible options
    const visibleOptions = Array.from(meterSelect.options).filter(option => 
        option.style.display !== 'none'
    ).length;
    meterSelect.size = Math.min(Math.max(visibleOptions, 3), 10);
    
    // Update info text
    const infoDiv = meterSelect.nextElementSibling;
    if (infoDiv && infoDiv.className.includes('text-gray-600')) {
        const totalOptions = Array.from(meterSelect.options).length - 1; // Exclude default option
        infoDiv.innerHTML = `
            <i class="fas fa-info-circle mr-1"></i>
            <strong>${visibleOptions - 1}</strong> of <strong>${totalOptions}</strong> meters match your search.
            ${searchTerm ? `<button type="button" onclick="clearSearch()" class="ml-2 text-blue-600 underline">Clear search</button>` : ''}
        `;
    }
}

// Clear search function
function clearSearch() {
    document.getElementById('meterSearch').value = '';
    filterMeters();
}

// Form validation before submission
document.getElementById('readingForm').addEventListener('submit', function(e) {
    const currentReading = parseFloat(document.getElementById('current_reading').value) || 0;
    const previousReading = meterData.previous_reading || 0;
    
    if (currentReading < previousReading) {
        e.preventDefault();
        alert(`Current reading (${currentReading.toLocaleString()}) cannot be less than previous reading (${previousReading.toLocaleString()})`);
        return false;
    }
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    return true;
});

// Auto-select meter if passed in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const meterId = urlParams.get('meter_id');
    if (meterId) {
        document.getElementById('water_meter_id').value = meterId;
        loadMeterDetails();
    }
});
</script>
@endsection 