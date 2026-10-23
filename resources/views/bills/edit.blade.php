@extends('layouts.app')

@section('content')
@php
    $dateField = function (string $field) use ($bill) {
        $value = old($field, $bill->{$field});
        return $value instanceof \Carbon\CarbonInterface ? $value->format('Y-m-d') : $value;
    };
@endphp
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-edit mr-3"></i>Edit Bill
                    </h1>
                    <p class="text-blue-100 mt-2">{{ $bill->bill_number }}</p>
                </div>
                <a href="{{ route('bills.show', $bill) }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Bill
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('bills.update', $bill) }}" method="POST" id="billForm">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
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
                                    <option value="{{ $customer->id }}" {{ (string) old('customer_id', $bill->customer_id) === (string) $customer->id ? 'selected' : '' }}>
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
                                    <option value="{{ $meter->id }}" data-customer-id="{{ $meter->customer_id }}" data-previous-reading="{{ $meter->current_reading }}" {{ (string) old('water_meter_id', $bill->water_meter_id) === (string) $meter->id ? 'selected' : '' }}>
                                        {{ $meter->meter_number }} - {{ $meter->customer->full_name ?? 'Customer' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('water_meter_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-calendar mr-2"></i>Bill Dates
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bill_date" class="block text-sm font-medium text-gray-700 mb-2">Bill Date <span class="text-red-500">*</span></label>
                            <input type="date" name="bill_date" id="bill_date" value="{{ $dateField('bill_date') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('bill_date') border-red-500 @enderror">
                            @error('bill_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date <span class="text-red-500">*</span></label>
                            <input type="date" name="due_date" id="due_date" value="{{ $dateField('due_date') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('due_date') border-red-500 @enderror">
                            @error('due_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="billing_period_from" class="block text-sm font-medium text-gray-700 mb-2">Billing Period From <span class="text-red-500">*</span></label>
                            <input type="date" name="billing_period_from" id="billing_period_from" value="{{ $dateField('billing_period_from') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('billing_period_from') border-red-500 @enderror">
                            @error('billing_period_from')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="billing_period_to" class="block text-sm font-medium text-gray-700 mb-2">Billing Period To <span class="text-red-500">*</span></label>
                            <input type="date" name="billing_period_to" id="billing_period_to" value="{{ $dateField('billing_period_to') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('billing_period_to') border-red-500 @enderror">
                            @error('billing_period_to')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-tachometer-alt mr-2"></i>Meter Readings
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="previous_reading" class="block text-sm font-medium text-gray-700 mb-2">Previous Reading <span class="text-red-500">*</span></label>
                            <input type="number" name="previous_reading" id="previous_reading" value="{{ old('previous_reading', $bill->previous_reading) }}"
                                   step="1" min="0" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('previous_reading') border-red-500 @enderror">
                            @error('previous_reading')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="current_reading" class="block text-sm font-medium text-gray-700 mb-2">Current Reading <span class="text-red-500">*</span></label>
                            <input type="number" name="current_reading" id="current_reading" value="{{ old('current_reading', $bill->current_reading) }}"
                                   step="1" min="0" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_reading') border-red-500 @enderror">
                            @error('current_reading')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="consumption" class="block text-sm font-medium text-gray-700 mb-2">Consumption (Units)</label>
                            <input type="number" id="consumption" readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                            <p class="mt-1 text-sm text-gray-500">Automatically calculated</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-calculator mr-2"></i>Additional Charges
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach([
                            'service_charges' => 'Service Charges (Rs.)',
                            'damage_charges' => 'Damage Charges (Rs.)',
                            'late_fees' => 'Late Fees (Rs.)',
                            'taxes' => 'Taxes (Rs.)',
                        ] as $field => $label)
                            <div>
                                <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>
                                <input type="number" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $bill->{$field} ?? 0) }}"
                                       step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($field) border-red-500 @enderror">
                                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        <div>
                            <label for="adjustments" class="block text-sm font-medium text-gray-700 mb-2">Adjustments (Rs.)</label>
                            <input type="number" name="adjustments" id="adjustments" value="{{ old('adjustments', $bill->adjustments ?? 0) }}"
                                   step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('adjustments') border-red-500 @enderror">
                            @error('adjustments')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            <p class="mt-1 text-sm text-gray-500">Use a negative amount for a discount</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-eye mr-2"></i>Bill Preview
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm">
                        <p class="mb-2 font-medium text-gray-700">Consumption: <span id="consumption_units_preview">0</span> units</p>
                        <div id="charge_breakdown" class="mb-3 space-y-1 text-gray-700"></div>
                        <div class="space-y-1 border-t border-gray-200 pt-3">
                            <div class="flex justify-between"><span>Water charges</span><span id="water_charges_preview">Rs. 0.00</span></div>
                            <div class="flex justify-between"><span>Fixed charge</span><span id="fixed_charges_preview">Rs. 0.00</span></div>
                            <div class="flex justify-between"><span>Service charges</span><span id="service_charges_preview">Rs. 0.00</span></div>
                            <div class="flex justify-between"><span>Damage charges</span><span id="damage_charges_preview">Rs. 0.00</span></div>
                            <div class="flex justify-between"><span>Late fees</span><span id="late_fees_preview">Rs. 0.00</span></div>
                            <div class="flex justify-between"><span>Taxes</span><span id="taxes_preview">Rs. 0.00</span></div>
                            <div class="flex justify-between"><span>Adjustments</span><span id="adjustments_preview">Rs. 0.00</span></div>
                            <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold text-blue-700">
                                <span>Total</span><span id="total_amount_preview">Rs. 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-b border-gray-200">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                              placeholder="Any additional notes or comments...">{{ old('notes', $bill->notes) }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="p-6">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('bills.show', $bill) }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-600 transition duration-200">Cancel</a>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-save mr-2"></i>Update Bill
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
    const chargeInputs = ['service_charges', 'damage_charges', 'late_fees', 'taxes', 'adjustments']
        .map(id => document.getElementById(id));
    const token = document.querySelector('meta[name="csrf-token"]').content;
    let quoted = { breakdown: [], water_charges: 0, fixed_charges: 0 };
    let quoteRequest = 0;

    function money(value) {
        return 'Rs. ' + (Number(value) || 0).toFixed(2);
    }

    function filterMeters() {
        const customerId = customerSelect.value;
        meterSelect.querySelectorAll('option').forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = customerId && option.dataset.customerId !== customerId;
        });
    }

    function selectCustomerMeter() {
        filterMeters();
        const customerId = customerSelect.value;
        let match = null;
        meterSelect.querySelectorAll('option').forEach(option => {
            if (option.value && !option.hidden && !match) {
                match = option;
            }
        });
        meterSelect.value = customerId && match ? match.value : '';
        fillPreviousReading();
    }

    function fillPreviousReading() {
        const option = meterSelect.selectedOptions[0];
        previousReadingInput.value = option && option.value ? option.dataset.previousReading : '';
        calculateConsumption();
    }

    function calculateConsumption() {
        if (currentReadingInput.value === '') {
            consumptionInput.value = '';
            quoted = { breakdown: [], water_charges: 0, fixed_charges: 0 };
            renderPreview();
            return;
        }
        const previous = parseFloat(previousReadingInput.value) || 0;
        const current = parseFloat(currentReadingInput.value) || 0;
        consumptionInput.value = Math.max(0, current - previous);
        refreshQuote();
    }

    function refreshQuote() {
        const requestId = ++quoteRequest;
        if (!customerSelect.value) {
            quoted = { breakdown: [], water_charges: 0, fixed_charges: 0 };
            renderPreview();
            return;
        }
        const body = new FormData();
        body.append('customer_id', customerSelect.value);
        body.append('consumption', consumptionInput.value || 0);
        body.append('bill_date', document.getElementById('bill_date').value);
        fetch(@json(route('bills.quote')), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body
        }).then(response => response.json()).then(data => {
            if (requestId !== quoteRequest) return;
            quoted = data;
            renderPreview();
        }).catch(() => {
            if (requestId === quoteRequest) renderPreview();
        });
    }

    function renderPreview() {
        const units = consumptionInput.value === '' ? 0 : Number(consumptionInput.value);
        document.getElementById('consumption_units_preview').textContent = units;
        const lines = (quoted.breakdown || []).map(row => {
            if (Number(row.fixed_charge) > 0) {
                return row.tier_name + ': ' + money(row.fixed_charge);
            }
            if (Number(row.consumption) > 0) {
                return row.consumption + ' units × Rs. ' + Number(row.rate_per_unit).toFixed(2) + ' = ' + money(row.charge);
            }
            return '';
        }).filter(Boolean);
        document.getElementById('charge_breakdown').innerHTML = lines.map(line => '<div>' + line + '</div>').join('');

        const extras = {
            service_charges: parseFloat(document.getElementById('service_charges').value) || 0,
            damage_charges: parseFloat(document.getElementById('damage_charges').value) || 0,
            late_fees: parseFloat(document.getElementById('late_fees').value) || 0,
            taxes: parseFloat(document.getElementById('taxes').value) || 0,
            adjustments: parseFloat(document.getElementById('adjustments').value) || 0,
        };
        const water = Number(quoted.water_charges) || 0;
        const fixed = Number(quoted.fixed_charges) || 0;
        document.getElementById('water_charges_preview').textContent = money(water);
        document.getElementById('fixed_charges_preview').textContent = money(fixed);
        document.getElementById('service_charges_preview').textContent = money(extras.service_charges);
        document.getElementById('damage_charges_preview').textContent = money(extras.damage_charges);
        document.getElementById('late_fees_preview').textContent = money(extras.late_fees);
        document.getElementById('taxes_preview').textContent = money(extras.taxes);
        document.getElementById('adjustments_preview').textContent = money(extras.adjustments);
        document.getElementById('total_amount_preview').textContent = money(
            water + fixed + extras.service_charges + extras.damage_charges + extras.late_fees + extras.taxes + extras.adjustments
        );
    }

    customerSelect.addEventListener('change', selectCustomerMeter);
    meterSelect.addEventListener('change', function () {
        filterMeters();
        fillPreviousReading();
    });
    previousReadingInput.addEventListener('input', calculateConsumption);
    currentReadingInput.addEventListener('input', calculateConsumption);
    document.getElementById('bill_date').addEventListener('change', calculateConsumption);
    chargeInputs.forEach(input => input.addEventListener('input', renderPreview));

    filterMeters();
    calculateConsumption();
});
</script>
@endsection
