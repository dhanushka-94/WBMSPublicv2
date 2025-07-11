@extends('layouts.app')

@section('title', 'Edit Rate')

@section('content')
<div class="mx-auto">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">Edit Rate</h1>
                <p class="text-blue-100 mt-1">Update billing rate and unit charge</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('settings.rates.show', $rate) }}" 
                   class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors">
                    <i class="fas fa-eye mr-2"></i>View Rate
                </a>
                <a href="{{ route('settings.rates.index') }}" 
                   class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Rates
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md">
        <form action="{{ route('settings.rates.update', $rate) }}" method="POST" id="rateForm">
            @csrf
            @method('PUT')
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Basic Information</h3>
                    </div>

                    <!-- Rate Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Rate Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name', $rate->name) }}"
                               class="form-input w-full rounded-md border-gray-300 @error('name') border-red-500 @enderror"
                               placeholder="e.g., Domestic 6-10 Units"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Customer Type -->
                    <div>
                        <label for="customer_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer Type <span class="text-red-500">*</span>
                        </label>
                        <select name="customer_type" 
                                id="customer_type" 
                                class="form-select w-full rounded-md border-gray-300 @error('customer_type') border-red-500 @enderror"
                                required>
                            <option value="">Select Customer Type</option>
                            @foreach($customerTypes as $customerType)
                                <option value="{{ $customerType->name }}" 
                                        {{ old('customer_type', $rate->customer_type) == $customerType->name ? 'selected' : '' }}>
                                    {{ $customerType->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Unit Range -->
                    <div class="lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Unit Range</h3>
                    </div>

                    <!-- Tier From -->
                    <div>
                        <label for="tier_from" class="block text-sm font-medium text-gray-700 mb-2">
                            From Unit <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="tier_from" 
                               id="tier_from"
                               value="{{ old('tier_from', $rate->tier_from) }}"
                               class="form-input w-full rounded-md border-gray-300 @error('tier_from') border-red-500 @enderror"
                               placeholder="0"
                               min="0"
                               step="1"
                               required>
                        @error('tier_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Starting unit number for this tier</p>
                    </div>

                    <!-- Tier To -->
                    <div>
                        <label for="tier_to" class="block text-sm font-medium text-gray-700 mb-2">
                            To Unit
                        </label>
                        <input type="number" 
                               name="tier_to" 
                               id="tier_to"
                               value="{{ old('tier_to', $rate->tier_to) }}"
                               class="form-input w-full rounded-md border-gray-300 @error('tier_to') border-red-500 @enderror"
                               placeholder="Leave empty for unlimited"
                               min="0"
                               step="1">
                        @error('tier_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Ending unit number (leave empty for unlimited tier)</p>
                    </div>

                    <!-- Charges -->
                    <div class="lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Charges</h3>
                    </div>

                    <!-- Rate Per Unit -->
                    <div>
                        <label for="rate_per_unit" class="block text-sm font-medium text-gray-700 mb-2">
                            Rate per Unit (Rs.) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rs.</span>
                            </div>
                            <input type="number" 
                                   name="rate_per_unit" 
                                   id="rate_per_unit"
                                   value="{{ old('rate_per_unit', $rate->rate_per_unit) }}"
                                   class="form-input w-full pl-12 rounded-md border-gray-300 @error('rate_per_unit') border-red-500 @enderror"
                                   placeholder="0.00"
                                   min="0"
                                   step="0.01"
                                   required>
                        </div>
                        @error('rate_per_unit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fixed Charge -->
                    <div>
                        <label for="fixed_charge" class="block text-sm font-medium text-gray-700 mb-2">
                            Fixed Charge (Rs.)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rs.</span>
                            </div>
                            <input type="number" 
                                   name="fixed_charge" 
                                   id="fixed_charge"
                                   value="{{ old('fixed_charge', $rate->fixed_charge) }}"
                                   class="form-input w-full pl-12 rounded-md border-gray-300 @error('fixed_charge') border-red-500 @enderror"
                                   placeholder="0.00"
                                   min="0"
                                   step="0.01">
                        </div>
                        @error('fixed_charge')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Monthly fixed charge (independent of consumption)</p>
                    </div>

                    <!-- Effective Period -->
                    <div class="lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Effective Period</h3>
                    </div>

                    <!-- Effective From -->
                    <div>
                        <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-2">
                            Effective From <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="effective_from" 
                               id="effective_from"
                               value="{{ old('effective_from', $rate->effective_from->format('Y-m-d')) }}"
                               class="form-input w-full rounded-md border-gray-300 @error('effective_from') border-red-500 @enderror"
                               required>
                        @error('effective_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Effective To -->
                    <div>
                        <label for="effective_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Effective To
                        </label>
                        <input type="date" 
                               name="effective_to" 
                               id="effective_to"
                               value="{{ old('effective_to', $rate->effective_to?->format('Y-m-d')) }}"
                               class="form-input w-full rounded-md border-gray-300 @error('effective_to') border-red-500 @enderror">
                        @error('effective_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Leave empty if this rate has no end date</p>
                    </div>

                    <!-- Description -->
                    <div class="lg:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3"
                                  class="form-textarea w-full rounded-md border-gray-300 @error('description') border-red-500 @enderror"
                                  placeholder="Optional description or notes about this rate">{{ old('description', $rate->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $rate->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Active (rate is available for billing)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-md font-semibold text-gray-900 mb-2">Rate Preview</h4>
                    <div id="ratePreview" class="text-sm text-gray-600">
                        <p>Complete the form to see rate preview</p>
                    </div>
                </div>

                <!-- Warning if used in bills -->
                @if($rate->bills()->exists())
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Rate in Use
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>This rate is currently being used in existing bills. Changes may affect historical billing calculations. Please proceed with caution.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
                <a href="{{ route('settings.rates.show', $rate) }}" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Rate
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('rateForm');
    const tierFromInput = document.getElementById('tier_from');
    const tierToInput = document.getElementById('tier_to');
    const ratePerUnitInput = document.getElementById('rate_per_unit');
    const fixedChargeInput = document.getElementById('fixed_charge');
    const nameInput = document.getElementById('name');
    const customerTypeInput = document.getElementById('customer_type');
    const previewDiv = document.getElementById('ratePreview');

    function updatePreview() {
        const tierFrom = tierFromInput.value;
        const tierTo = tierToInput.value;
        const ratePerUnit = parseFloat(ratePerUnitInput.value) || 0;
        const fixedCharge = parseFloat(fixedChargeInput.value) || 0;
        const name = nameInput.value;
        const customerType = customerTypeInput.value;

        if (!tierFrom || !ratePerUnit || !name || !customerType) {
            previewDiv.innerHTML = '<p class="text-gray-600">Complete the form to see rate preview</p>';
            return;
        }

        let tierRange = tierTo ? `${tierFrom} - ${tierTo} units` : `${tierFrom}+ units`;
        
        let preview = `
            <div class="space-y-2">
                <p><strong>Rate:</strong> ${name}</p>
                <p><strong>Customer Type:</strong> ${customerType}</p>
                <p><strong>Range:</strong> ${tierRange}</p>
                <p><strong>Rate per Unit:</strong> Rs. ${ratePerUnit.toFixed(2)}</p>
                ${fixedCharge > 0 ? `<p><strong>Fixed Charge:</strong> Rs. ${fixedCharge.toFixed(2)}</p>` : ''}
            </div>
        `;

        previewDiv.innerHTML = preview;
    }

    // Validation for tier range
    tierToInput.addEventListener('input', function() {
        const tierFrom = parseInt(tierFromInput.value) || 0;
        const tierTo = parseInt(this.value);

        if (tierTo && tierTo < tierFrom) {
            this.setCustomValidity('To unit must be greater than or equal to From unit');
        } else {
            this.setCustomValidity('');
        }
        updatePreview();
    });

    tierFromInput.addEventListener('input', function() {
        const tierFrom = parseInt(this.value) || 0;
        const tierTo = parseInt(tierToInput.value);

        if (tierTo && tierTo < tierFrom) {
            tierToInput.setCustomValidity('To unit must be greater than or equal to From unit');
        } else {
            tierToInput.setCustomValidity('');
        }
        updatePreview();
    });

    // Update preview on all input changes
    [nameInput, customerTypeInput, ratePerUnitInput, fixedChargeInput].forEach(input => {
        input.addEventListener('input', updatePreview);
    });

    // Initial preview update
    updatePreview();
});
</script>
@endsection 