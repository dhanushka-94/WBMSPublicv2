@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-plus-circle mr-3"></i>Create New Bill
                    </h1>
                    <p class="text-blue-100 mt-2">Generate a new water bill for customer</p>
                </div>
                <a href="{{ route('bills.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Bills
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('bills.store') }}" method="POST" id="billForm">
            @csrf
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <!-- Customer Selection -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-user mr-2"></i>Customer & Meter Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Customer <span class="text-red-500">*</span>
                            </label>
                            <select name="customer_id" id="customer_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('customer_id') border-red-500 @enderror">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->full_name }} ({{ $customer->account_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="water_meter_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Water Meter <span class="text-red-500">*</span>
                            </label>
                            <select name="water_meter_id" id="water_meter_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('water_meter_id') border-red-500 @enderror">
                                <option value="">Select Meter</option>
                                @foreach($waterMeters as $meter)
                                    <option value="{{ $meter->id }}" data-customer-id="{{ $meter->customer_id }}" {{ old('water_meter_id') == $meter->id ? 'selected' : '' }}>
                                        {{ $meter->meter_number }} - {{ $meter->customer->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('water_meter_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Bill Dates -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-calendar mr-2"></i>Bill Dates
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bill_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Bill Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="bill_date" id="bill_date" value="{{ old('bill_date', date('Y-m-d')) }}" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('bill_date') border-red-500 @enderror">
                            @error('bill_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('due_date') border-red-500 @enderror">
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_period_from" class="block text-sm font-medium text-gray-700 mb-2">
                                Billing Period From <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="billing_period_from" id="billing_period_from" value="{{ old('billing_period_from', date('Y-m-01')) }}" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('billing_period_from') border-red-500 @enderror">
                            @error('billing_period_from')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_period_to" class="block text-sm font-medium text-gray-700 mb-2">
                                Billing Period To <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="billing_period_to" id="billing_period_to" value="{{ old('billing_period_to', date('Y-m-t')) }}" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('billing_period_to') border-red-500 @enderror">
                            @error('billing_period_to')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Meter Readings -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-tachometer-alt mr-2"></i>Meter Readings
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="previous_reading" class="block text-sm font-medium text-gray-700 mb-2">
                                Previous Reading <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="previous_reading" id="previous_reading" value="{{ old('previous_reading') }}" 
                                   step="1" min="0" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('previous_reading') border-red-500 @enderror">
                            @error('previous_reading')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="current_reading" class="block text-sm font-medium text-gray-700 mb-2">
                                Current Reading <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="current_reading" id="current_reading" value="{{ old('current_reading') }}" 
                                   step="1" min="0" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_reading') border-red-500 @enderror">
                            @error('current_reading')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="consumption" class="block text-sm font-medium text-gray-700 mb-2">
                                Consumption (Units)
                            </label>
                            <input type="number" id="consumption" readonly 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                            <p class="mt-1 text-sm text-gray-500">Automatically calculated</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Charges -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-calculator mr-2"></i>Additional Charges
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="service_charges" class="block text-sm font-medium text-gray-700 mb-2">
                                Service Charges (Rs.)
                            </label>
                            <input type="number" name="service_charges" id="service_charges" value="{{ old('service_charges', 0) }}" 
                                   step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('service_charges') border-red-500 @enderror">
                            @error('service_charges')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="taxes" class="block text-sm font-medium text-gray-700 mb-2">
                                Taxes (Rs.)
                            </label>
                            <input type="number" name="taxes" id="taxes" value="{{ old('taxes', 0) }}" 
                                   step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('taxes') border-red-500 @enderror">
                            @error('taxes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="adjustments" class="block text-sm font-medium text-gray-700 mb-2">
                                Adjustments (Rs.)
                            </label>
                            <input type="number" name="adjustments" id="adjustments" value="{{ old('adjustments', 0) }}" 
                                   step="0.01" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('adjustments') border-red-500 @enderror">
                            @error('adjustments')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Use negative values for discounts</p>
                        </div>
                    </div>
                </div>

                <!-- Bill Preview -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-eye mr-2"></i>Bill Preview
                    </h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Water Charges:</p>
                                <p class="font-medium" id="water_charges_preview">Rs. 0.00</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Fixed Charges:</p>
                                <p class="font-medium" id="fixed_charges_preview">Rs. 0.00</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Service Charges:</p>
                                <p class="font-medium" id="service_charges_preview">Rs. 0.00</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Taxes:</p>
                                <p class="font-medium" id="taxes_preview">Rs. 0.00</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Adjustments:</p>
                                <p class="font-medium" id="adjustments_preview">Rs. 0.00</p>
                            </div>
                            <div class="border-t pt-2">
                                <p class="text-gray-600">Total Amount:</p>
                                <p class="font-bold text-lg text-blue-600" id="total_amount_preview">Rs. 0.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-sticky-note mr-2"></i>Notes
                    </h3>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                  placeholder="Any additional notes or comments...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="p-6">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('bills.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-600 transition duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-save mr-2"></i>Create Bill
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customer_id');
    const meterSelect = document.getElementById('water_meter_id');
    const previousReadingInput = document.getElementById('previous_reading');
    const currentReadingInput = document.getElementById('current_reading');
    const consumptionInput = document.getElementById('consumption');
    const serviceChargesInput = document.getElementById('service_charges');
    const taxesInput = document.getElementById('taxes');
    const adjustmentsInput = document.getElementById('adjustments');

    // Filter meters based on selected customer
    customerSelect.addEventListener('change', function() {
        const customerId = this.value;
        const meterOptions = meterSelect.querySelectorAll('option');
        
        meterOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const meterCustomerId = option.getAttribute('data-customer-id');
            if (customerId === '' || meterCustomerId === customerId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        meterSelect.value = '';
    });

    // Calculate consumption
    function calculateConsumption() {
        const previousReading = parseFloat(previousReadingInput.value) || 0;
        const currentReading = parseFloat(currentReadingInput.value) || 0;
        const consumption = Math.max(0, currentReading - previousReading);
        consumptionInput.value = consumption;
        updateBillPreview();
    }

    // Update bill preview
    function updateBillPreview() {
        const consumption = parseFloat(consumptionInput.value) || 0;
        const serviceCharges = parseFloat(serviceChargesInput.value) || 0;
        const taxes = parseFloat(taxesInput.value) || 0;
        const adjustments = parseFloat(adjustmentsInput.value) || 0;

        // This is a simplified calculation - in reality, you'd need to fetch rates from server
        let waterCharges = 0;
        let fixedCharges = 0;

        // Simplified Sri Lankan water billing calculation
        if (consumption === 0) {
            fixedCharges = 150;
        } else {
            // Apply tiered rates
            if (consumption <= 5) {
                waterCharges = 0;
            } else if (consumption <= 10) {
                waterCharges = (consumption - 5) * 12;
            } else if (consumption <= 15) {
                waterCharges = (5 * 12) + ((consumption - 10) * 18);
            } else if (consumption <= 20) {
                waterCharges = (5 * 12) + (5 * 18) + ((consumption - 15) * 25);
            } else if (consumption <= 25) {
                waterCharges = (5 * 12) + (5 * 18) + (5 * 25) + ((consumption - 20) * 35);
            } else {
                waterCharges = (5 * 12) + (5 * 18) + (5 * 25) + (5 * 35) + ((consumption - 25) * 40);
            }
        }

        const totalAmount = waterCharges + fixedCharges + serviceCharges + taxes + adjustments;

        // Update preview
        document.getElementById('water_charges_preview').textContent = 'Rs. ' + waterCharges.toFixed(2);
        document.getElementById('fixed_charges_preview').textContent = 'Rs. ' + fixedCharges.toFixed(2);
        document.getElementById('service_charges_preview').textContent = 'Rs. ' + serviceCharges.toFixed(2);
        document.getElementById('taxes_preview').textContent = 'Rs. ' + taxes.toFixed(2);
        document.getElementById('adjustments_preview').textContent = 'Rs. ' + adjustments.toFixed(2);
        document.getElementById('total_amount_preview').textContent = 'Rs. ' + totalAmount.toFixed(2);
    }

    // Event listeners
    previousReadingInput.addEventListener('input', calculateConsumption);
    currentReadingInput.addEventListener('input', calculateConsumption);
    serviceChargesInput.addEventListener('input', updateBillPreview);
    taxesInput.addEventListener('input', updateBillPreview);
    adjustmentsInput.addEventListener('input', updateBillPreview);

    // Initial calculation
    calculateConsumption();
});
</script>
@endsection 