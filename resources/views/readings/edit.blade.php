@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-edit mr-3"></i>Edit Meter Reading
                    </h1>
                    <p class="text-blue-100 mt-2">Update meter reading information</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('readings.show', $reading) }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Reading
                    </a>
                    <a href="{{ route('readings.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-gray-600 transition duration-200">
                        <i class="fas fa-list mr-2"></i>All Readings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Status Warning -->
            @if($reading->status === 'billed')
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-900">Reading Cannot Be Modified</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>This reading has been included in a bill and cannot be edited. If you need to make changes, please contact your supervisor.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Instructions Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-900">Edit Reading Guidelines</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Ensure the reading is accurate and matches the meter display</li>
                                    <li>The reading must be greater than or equal to the previous reading</li>
                                    <li>Add detailed notes if making significant changes</li>
                                    <li>Verified readings will need re-verification after editing</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Current Reading Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info mr-2"></i>Current Reading Information
                    </h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <div class="flex items-center mb-2">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <img class="h-12 w-12 rounded-full object-cover" 
                                         src="{{ $reading->waterMeter->customer->profile_photo_url }}" 
                                         alt="{{ $reading->waterMeter->customer->full_name }}">
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $reading->waterMeter->customer->full_name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $reading->waterMeter->customer->account_number }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-700">Meter Number</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $reading->waterMeter->meter_number }}</p>
                            <p class="text-sm text-gray-500">{{ ucfirst($reading->waterMeter->meter_type) }} meter</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-700">Current Status</p>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'verified' => 'bg-green-100 text-green-800',
                                    'billed' => 'bg-blue-100 text-blue-800'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$reading->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($reading->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-edit mr-2"></i>Update Reading Details
                    </h3>
                </div>

                <form action="{{ route('readings.update', $reading) }}" method="POST" id="editReadingForm" @if($reading->status === 'billed') class="pointer-events-none opacity-60" @endif>
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 space-y-6">
                        <!-- Water Meter (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Water Meter
                            </label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                                {{ $reading->waterMeter->meter_number }} - {{ $reading->waterMeter->customer->full_name }}
                            </div>
                            <input type="hidden" name="water_meter_id" value="{{ $reading->water_meter_id }}">
                        </div>

                        <!-- Previous Reading Display -->
                        @php
                            $previousReading = $reading->waterMeter->meterReadings()
                                ->where('id', '!=', $reading->id)
                                ->where('reading_date', '<', $reading->reading_date)
                                ->latest('reading_date')
                                ->first();
                            $prevValue = $previousReading ? $previousReading->current_reading : $reading->waterMeter->initial_reading;
                        @endphp
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Previous Reading Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Previous Reading:</span>
                                    <span class="text-gray-900 font-semibold">{{ number_format($prevValue) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Date:</span>
                                    <span class="text-gray-900">{{ $previousReading ? $previousReading->reading_date->format('M d, Y') : 'Initial Reading' }}</span>
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
                                       value="{{ old('reading_date', $reading->reading_date->format('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reading_date') border-red-500 @enderror" 
                                       required @if($reading->status === 'billed') disabled @endif>
                                @error('reading_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="current_reading" class="block text-sm font-medium text-gray-700 mb-2">
                                    Current Reading <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="current_reading" id="current_reading" 
                                       value="{{ old('current_reading', $reading->current_reading) }}"
                                       min="{{ $prevValue }}" step="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_reading') border-red-500 @enderror" 
                                       placeholder="Enter meter reading..."
                                       oninput="calculateConsumption()"
                                       required @if($reading->status === 'billed') disabled @endif>
                                @error('current_reading')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Minimum reading: {{ number_format($prevValue) }}</p>
                            </div>
                        </div>

                        <!-- Consumption Display -->
                        <div id="consumptionDisplay" class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tint text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-md font-semibold text-green-900">Consumption Calculation</h4>
                                    <p class="text-sm text-green-700">
                                        <span id="consumptionText">{{ number_format(max(0, $reading->current_reading - $prevValue)) }} units</span> consumed since last reading
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
                                        required @if($reading->status === 'billed') disabled @endif>
                                    <option value="actual" {{ old('reading_type', $reading->reading_type) == 'actual' ? 'selected' : '' }}>Actual Reading</option>
                                    <option value="estimated" {{ old('reading_type', $reading->reading_type) == 'estimated' ? 'selected' : '' }}>Estimated Reading</option>
                                    <option value="customer_read" {{ old('reading_type', $reading->reading_type) == 'customer_read' ? 'selected' : '' }}>Customer Read</option>
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
                                       value="{{ old('reader_name', $reading->reader_name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reader_name') border-red-500 @enderror" 
                                       placeholder="Enter reader name..."
                                       required @if($reading->status === 'billed') disabled @endif>
                                @error('reader_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror" 
                                      placeholder="Add any notes about the reading changes or meter condition..."
                                      @if($reading->status === 'billed') disabled @endif>{{ old('notes', $reading->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Change Summary -->
                        @if($reading->status !== 'billed')
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-yellow-600 text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-md font-semibold text-yellow-900">Important Notes</h4>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <ul class="list-disc list-inside space-y-1">
                                                @if($reading->status === 'verified')
                                                    <li>This reading has been verified. Changes will reset it to pending status.</li>
                                                @endif
                                                <li>All changes are logged for audit purposes.</li>
                                                <li>Consider adding detailed notes explaining the reason for changes.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Form Actions -->
                    <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('readings.show', $reading) }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-600 transition duration-200">
                                Cancel
                            </a>
                            @if($reading->status !== 'billed')
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-save mr-2"></i>Update Reading
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const previousReading = {{ $prevValue }};

// Calculate consumption when current reading changes
function calculateConsumption() {
    const currentReading = parseFloat(document.getElementById('current_reading').value) || 0;
    
    if (currentReading > 0) {
        const consumption = Math.max(0, currentReading - previousReading);
        
        document.getElementById('consumptionText').textContent = consumption.toLocaleString() + ' units';
        
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
    }
}

// Form validation before submission
@if($reading->status !== 'billed')
document.getElementById('editReadingForm').addEventListener('submit', function(e) {
    const currentReading = parseFloat(document.getElementById('current_reading').value) || 0;
    
    if (currentReading < previousReading) {
        e.preventDefault();
        alert(`Current reading (${currentReading.toLocaleString()}) cannot be less than previous reading (${previousReading.toLocaleString()})`);
        return false;
    }
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    }
    
    return true;
});
@endif

// Initialize consumption calculation
document.addEventListener('DOMContentLoaded', function() {
    calculateConsumption();
});
</script>
@endsection 