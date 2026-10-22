@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-amber-500 to-orange-600 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-tachometer-alt mr-3"></i>Initial Meter Readings
                    </h1>
                    <p class="text-amber-100 mt-2">Enter each meter’s opening dial once. This does not create a bill.</p>
                </div>
                <a href="{{ route('readings.bulk') }}" class="bg-white text-amber-700 px-4 py-2 rounded-lg font-medium hover:bg-amber-50 transition duration-200">
                    <i class="fas fa-list mr-2"></i>Bulk Meter Reading Entry
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                <p class="font-medium">Check the readings and try again.</p>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="initialReadingForm" action="{{ route('readings.initial.store') }}" method="POST">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ $waterMeters->count() }} meters
                                <span class="text-sm font-normal text-gray-500">· {{ $waterMeters->where('initial_reading', 0)->count() }} still at 0</span>
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Type a dial in the Initial reading column. Blank rows are skipped.</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full md:w-auto">
                            <input type="text" id="initialSearch" placeholder="Name, reference, or meter"
                                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <select id="divisionFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">All divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                            <select id="zeroFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">All meters</option>
                                <option value="zero">Still at 0</option>
                                <option value="set">Already entered</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[70vh]">
                    <table class="w-full">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Division</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meter number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saved initial</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Initial reading</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($waterMeters as $meter)
                                @php
                                    $saved = (float) $meter->initial_reading;
                                @endphp
                                <tr class="initial-row hover:bg-amber-50"
                                    data-search="{{ strtolower(($meter->customer?->full_name ?? '') . ' ' . ($meter->customer?->account_number ?? '') . ' ' . $meter->meter_number) }}"
                                    data-division="{{ $meter->customer?->division_id ?? '' }}"
                                    data-zero="{{ $saved == 0 ? '1' : '0' }}">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $meter->customer?->full_name ?? 'Unassigned' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $meter->customer?->account_number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $meter->customer?->division?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $meter->meter_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($saved, 0) }}</td>
                                    <td class="px-4 py-3">
                                        <input type="hidden"
                                               name="initial_readings[{{ $meter->id }}][water_meter_id]"
                                               value="{{ $meter->id }}"
                                               class="meter-key" disabled>
                                        <input type="number"
                                               name="initial_readings[{{ $meter->id }}][initial_reading]"
                                               value="{{ old('initial_readings.'.$meter->id.'.initial_reading') }}"
                                               min="0" max="9999999" step="1"
                                               placeholder="Enter dial"
                                               class="initial-entry w-32 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                    <p id="entryCount" class="text-sm text-gray-600">No new readings typed yet</p>
                    <button type="submit" id="saveInitials" class="bg-amber-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-amber-700 disabled:opacity-50" disabled>
                        <i class="fas fa-save mr-2"></i>Save initial readings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = Array.from(document.querySelectorAll('.initial-row'));
    const search = document.getElementById('initialSearch');
    const division = document.getElementById('divisionFilter');
    const zero = document.getElementById('zeroFilter');
    const count = document.getElementById('entryCount');
    const button = document.getElementById('saveInitials');

    function refresh() {
        const term = search.value.trim().toLowerCase();
        const divisionId = division.value;
        const zeroMode = zero.value;
        let typed = 0;

        rows.forEach(row => {
            const entry = row.querySelector('.initial-entry');
            const key = row.querySelector('.meter-key');
            const hasValue = entry.value.trim() !== '';
            entry.disabled = false;
            key.disabled = !hasValue;
            if (hasValue) {
                typed++;
            }

            const matchesSearch = term === '' || row.dataset.search.includes(term);
            const matchesDivision = divisionId === '' || row.dataset.division === divisionId;
            const matchesZero = zeroMode === ''
                || (zeroMode === 'zero' && row.dataset.zero === '1')
                || (zeroMode === 'set' && row.dataset.zero === '0');
            row.style.display = matchesSearch && matchesDivision && matchesZero ? '' : 'none';
        });

        count.textContent = typed === 0
            ? 'No new readings typed yet'
            : typed + (typed === 1 ? ' reading ready to save' : ' readings ready to save');
        button.disabled = typed === 0;
    }

    rows.forEach(row => {
        row.querySelector('.initial-entry').addEventListener('input', function () {
            const key = row.querySelector('.meter-key');
            const hasValue = this.value.trim() !== '';
            this.disabled = false;
            key.disabled = !hasValue;
            refresh();
        });
    });

    document.getElementById('initialReadingForm').addEventListener('submit', function (event) {
        let typed = 0;
        rows.forEach(row => {
            const entry = row.querySelector('.initial-entry');
            const key = row.querySelector('.meter-key');
            const hasValue = entry.value.trim() !== '';
            entry.disabled = !hasValue;
            key.disabled = !hasValue;
            if (hasValue) {
                typed++;
            }
        });
        if (typed === 0) {
            event.preventDefault();
            refresh();
        }
    });

    [search, division, zero].forEach(control => control.addEventListener('input', refresh));
    division.addEventListener('change', refresh);
    zero.addEventListener('change', refresh);
    refresh();
});
</script>
@endsection
