@extends('layouts.app')

@section('title', 'Reader Statistics')

@section('content')
<div class="mx-auto">
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Reader Statistics</h1>
                <p class="text-blue-100 mt-1">Readings, units, and route coverage for {{ $label }}</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                @if($selected)
                    <input type="hidden" name="reader" value="{{ $selected->id }}">
                @endif
                <input type="month" name="month" value="{{ $month }}" class="rounded-md border-gray-300 text-gray-900">
                <button type="submit" class="bg-white text-blue-700 px-4 py-2 rounded-lg font-semibold">Show</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-5">
            <p class="text-sm text-gray-500">Readings</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totals['readings']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-5">
            <p class="text-sm text-gray-500">From the phone</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($totals['phone']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($totals['office']) }} from the office</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-5">
            <p class="text-sm text-gray-500">Units recorded</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totals['consumption'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-5">
            <p class="text-sm text-gray-500">Route covered</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($totals['covered']) }}</p>
            <p class="text-xs text-gray-500 mt-1">of {{ number_format($totals['assigned']) }} assigned</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reader</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Readings</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Office</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Meters</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Units</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Billed</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Faults</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Coverage</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($summaries as $summary)
                    <tr class="{{ $selected && $selected->id === $summary['reader']->id ? 'bg-blue-50' : '' }}">
                        <td class="px-4 py-4">
                            <a href="{{ route('reports.reader-statistics', ['month' => $month, 'reader' => $summary['reader']->id]) }}" class="font-medium text-blue-700 hover:text-blue-900">
                                {{ $summary['reader']->name }}
                            </a>
                            <div class="text-sm text-gray-500">{{ $summary['reader']->email }}</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            {{ $summary['divisions']->isEmpty() ? 'No division' : $summary['divisions']->join(', ') }}
                        </td>
                        <td class="px-4 py-4 text-right font-medium text-gray-900">{{ number_format($summary['readings']) }}</td>
                        <td class="px-4 py-4 text-right text-gray-900">{{ number_format($summary['phone']) }}</td>
                        <td class="px-4 py-4 text-right text-gray-900">{{ number_format($summary['office']) }}</td>
                        <td class="px-4 py-4 text-right text-gray-900">{{ number_format($summary['meters']) }}</td>
                        <td class="px-4 py-4 text-right text-gray-900">{{ number_format($summary['consumption'], 2) }}</td>
                        <td class="px-4 py-4 text-right text-gray-900">{{ number_format($summary['billed']) }}</td>
                        <td class="px-4 py-4 text-right {{ $summary['faults'] > 0 ? 'text-amber-700 font-medium' : 'text-gray-900' }}">{{ number_format($summary['faults']) }}</td>
                        <td class="px-4 py-4 text-right">
                            <div class="text-gray-900 font-medium">{{ $summary['coverage'] }}%</div>
                            <div class="text-xs text-gray-500">{{ number_format($summary['covered']) }}/{{ number_format($summary['assigned']) }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-10 text-center text-gray-500">No meter readers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-4">{{ $summaries->links() }}</div>
    </div>

    @if($unassigned > 0)
        <p class="mb-6 text-sm text-gray-600">{{ number_format($unassigned) }} {{ $unassigned === 1 ? 'reading was' : 'readings were' }} saved in {{ $label }} without a meter reader account.</p>
    @endif

    @if($selected)
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ $selected->name }} · daily readings</h2>
            </div>
            @if($days->isEmpty())
                <p class="px-6 py-10 text-center text-gray-500">No readings in {{ $label }}.</p>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($days as $day)
                        <div class="px-6 py-3 flex items-center gap-4">
                            <div class="w-28 text-sm text-gray-700">{{ \Carbon\Carbon::parse($day->reading_date)->format('d M Y') }}</div>
                            <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-3 bg-blue-600 rounded-full" style="width: {{ (int) round(($day->readings / $dayPeak) * 100) }}%"></div>
                            </div>
                            <div class="w-40 text-right text-sm text-gray-900">
                                {{ number_format($day->readings) }} readings
                                <span class="text-gray-500">· {{ number_format($day->consumption, 2) }} units</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Readings by {{ $selected->name }}</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meter</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reading</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Units</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bill</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($readings as $reading)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $reading->reading_date->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                @if($reading->waterMeter?->customer)
                                    <a href="{{ route('customers.show', $reading->waterMeter->customer) }}" class="font-medium text-gray-900 hover:text-blue-700">{{ $reading->waterMeter->customer->full_name }}</a>
                                    <div class="text-sm text-gray-500">{{ $reading->waterMeter->customer->account_number }}</div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $reading->waterMeter->meter_number ?? '—' }}</td>
                            <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($reading->current_reading, 2) }}</td>
                            <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($reading->consumption, 2) }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($reading->bill)
                                    <a href="{{ route('bills.show', $reading->bill) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $reading->bill->bill_number }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $reading->submitted_via === 'mobile_app' ? 'Phone' : 'Office' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($reading->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">No readings in {{ $label }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($readings->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">{{ $readings->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
