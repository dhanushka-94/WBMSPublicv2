@extends('layouts.app')

@section('title', 'Reader Route Report')

@section('content')
<div class="mx-auto">
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Reader Route Report</h1>
                <p class="text-blue-100 mt-1">Assigned customers, completed readings, and missed customers</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" class="rounded-md border-gray-300 text-gray-900">
                <button type="submit" class="bg-white text-blue-700 px-4 py-2 rounded-lg font-semibold">Show</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-5">
            <p class="text-sm text-gray-500">Assigned</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totals['assigned']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-5">
            <p class="text-sm text-gray-500">Completed</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($totals['completed']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-5">
            <p class="text-sm text-gray-500">Missed</p>
            <p class="text-2xl font-bold text-red-700">{{ number_format($totals['missed']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reader</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Assigned</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Completed</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">By this reader</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Missed</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Done</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($summaries as $summary)
                    @php
                        $percent = $summary['assigned'] > 0
                            ? (int) round(($summary['completed'] / $summary['assigned']) * 100)
                            : 0;
                    @endphp
                    <tr class="{{ $selected && $selected->id === $summary['reader']->id ? 'bg-blue-50' : '' }}">
                        <td class="px-6 py-4">
                            <a href="{{ route('reports.reader-routes', ['date' => $date, 'reader' => $summary['reader']->id, 'list' => 'missed']) }}" class="font-medium text-blue-700 hover:text-blue-900">
                                {{ $summary['reader']->name }}
                            </a>
                            <div class="text-sm text-gray-500">{{ $summary['reader']->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ $summary['divisions']->isEmpty() ? 'No division' : $summary['divisions']->join(', ') }}
                        </td>
                        <td class="px-6 py-4 text-right text-gray-900">{{ number_format($summary['assigned']) }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('reports.reader-routes', ['date' => $date, 'reader' => $summary['reader']->id, 'list' => 'completed']) }}" class="text-green-700 font-medium">
                                {{ number_format($summary['completed']) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-right text-gray-900">{{ number_format($summary['by_reader']) }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('reports.reader-routes', ['date' => $date, 'reader' => $summary['reader']->id, 'list' => 'missed']) }}" class="text-red-700 font-medium">
                                {{ number_format($summary['missed']) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-gray-900">{{ $percent }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">No meter readers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($selected)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <h2 class="text-lg font-semibold text-gray-900">{{ $selected->name }} · {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h2>
                <div class="flex gap-2 text-sm">
                    @foreach(['missed' => 'Missed', 'completed' => 'Completed', 'all' => 'All'] as $key => $label)
                        <a href="{{ route('reports.reader-routes', ['date' => $date, 'reader' => $selected->id, 'list' => $key]) }}"
                           class="px-3 py-1 rounded-full {{ $list === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Division</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reading</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-6 py-4">
                                <a href="{{ route('customers.show', $row['customer']) }}" class="font-medium text-gray-900 hover:text-blue-700">{{ $row['customer']->full_name }}</a>
                                <div class="text-sm text-gray-500">{{ $row['customer']->account_number }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $row['customer']->meter_number ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $row['customer']->division->name ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if($row['status'] === 'completed')
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Completed</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Missed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @if($row['reading'])
                                    {{ number_format($row['reading']->current_reading, 2) }}
                                    · {{ $row['reading']->reader_name ?: 'Unknown reader' }}
                                    @unless($row['by_this_reader'])
                                        <span class="text-amber-700"> (other reader)</span>
                                    @endunless
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">Nothing in this list.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($rows->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">{{ $rows->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
