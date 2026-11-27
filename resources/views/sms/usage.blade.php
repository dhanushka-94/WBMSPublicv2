@extends('layouts.app')

@section('title', 'SMS Usage & Cost')

@php
    $input = 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm';
    $money = fn ($v) => 'Rs. '.number_format((float) $v, 2);
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @include('sms.partials.archive-note', ['usageNote' => true])
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-coins text-white text-2xl mr-3"></i>
                    <h1 class="text-2xl font-bold text-white">SMS Usage &amp; Cost</h1>
                </div>
                <a href="{{ route('sms.index') }}" class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-md font-medium text-sm">
                    <i class="fas fa-list mr-2"></i>SMS Logs
                </a>
            </div>

            <form method="GET" action="{{ route('sms.usage') }}" class="p-6 bg-gray-50 border-b border-gray-200 rounded-b-lg">
                @if($errors->any())
                    <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-800">{{ $errors->first() }}</div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date from</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date to</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="{{ $input }}">
                            <option value="sent" @selected($filters['status'] === 'sent')>Sent (charged)</option>
                            <option value="failed" @selected($filters['status'] === 'failed')>Failed</option>
                            <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                            <option value="all" @selected($filters['status'] === 'all')>All</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="{{ $input }}">
                            <option value="">All types</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                        <select name="source" class="{{ $input }}">
                            <option value="">All sources</option>
                            @foreach($sources as $source)
                                <option value="{{ $source }}" @selected($filters['source'] === $source)>{{ ucfirst($source) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Group by</label>
                        <select name="group" class="{{ $input }}">
                            <option value="day" @selected($filters['group'] === 'day')>Day</option>
                            <option value="month" @selected($filters['group'] === 'month')>Month</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 pt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"><i class="fas fa-search mr-2"></i>Apply</button>
                    <a href="{{ route('sms.usage') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"><i class="fas fa-times mr-2"></i>Reset</a>
                    <a href="{{ route('sms.usage.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"><i class="fas fa-download mr-2"></i>Export CSV</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs uppercase text-gray-500">Messages</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($totals->messages) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs uppercase text-gray-500">Segments</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($totals->segments) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs uppercase text-gray-500">Estimated cost</div>
                <div class="text-2xl font-bold text-green-700">{{ $money($totals->cost) }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-xs uppercase text-gray-500">Current rate / segment</div>
                <div class="text-2xl font-bold text-gray-900">{{ $money($rate) }}</div>
                <div class="text-xs text-gray-500">@if(auth()->user()?->role === 'super_admin')<a href="{{ route('sms.settings') }}" class="text-blue-600 hover:text-blue-800">Set in SMS Settings</a>@else Set by the Super Admin @endif</div>
            </div>
        </div>

        <p class="text-xs text-gray-500">
            Only sent messages are charged. Each message uses the rate it was sent at; older messages without a stored rate use the current rate.
            @if($filters['status'] === 'all')
                Status is set to All, so the message and segment counts include failed and pending messages, but cost counts sent messages only.
            @endif
            Statuses in this range:
            @forelse($byStatus as $status => $count)
                {{ ucfirst($status) }} {{ number_format($count) }}@if(!$loop->last),@endif
            @empty
                none
            @endforelse
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b font-semibold text-gray-800">By type</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr><th class="px-4 py-2 text-left">Type</th><th class="px-4 py-2 text-right">Messages</th><th class="px-4 py-2 text-right">Segments</th><th class="px-4 py-2 text-right">Cost</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($byType as $row)
                            <tr><td class="px-4 py-2">{{ $row->label }}</td><td class="px-4 py-2 text-right">{{ number_format($row->messages) }}</td><td class="px-4 py-2 text-right">{{ number_format($row->segments) }}</td><td class="px-4 py-2 text-right">{{ $money($row->cost) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No SMS found for these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b font-semibold text-gray-800">By source</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr><th class="px-4 py-2 text-left">Source</th><th class="px-4 py-2 text-right">Messages</th><th class="px-4 py-2 text-right">Segments</th><th class="px-4 py-2 text-right">Cost</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($bySource as $row)
                            <tr><td class="px-4 py-2">{{ ucfirst($row->label ?? 'unknown') }}</td><td class="px-4 py-2 text-right">{{ number_format($row->messages) }}</td><td class="px-4 py-2 text-right">{{ number_format($row->segments) }}</td><td class="px-4 py-2 text-right">{{ $money($row->cost) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No SMS found for these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold text-gray-800">By {{ $filters['group'] }}</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr><th class="px-4 py-2 text-left">{{ ucfirst($filters['group']) }}</th><th class="px-4 py-2 text-right">Messages</th><th class="px-4 py-2 text-right">Segments</th><th class="px-4 py-2 text-right">Cost</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($byPeriod as $row)
                            <tr><td class="px-4 py-2">{{ $row->label }}</td><td class="px-4 py-2 text-right">{{ number_format($row->messages) }}</td><td class="px-4 py-2 text-right">{{ number_format($row->segments) }}</td><td class="px-4 py-2 text-right">{{ $money($row->cost) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No SMS found for these filters.</td></tr>
                        @endforelse
                    </tbody>
                    @if($byPeriod->isNotEmpty())
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr><td class="px-4 py-2">Total</td><td class="px-4 py-2 text-right">{{ number_format($totals->messages) }}</td><td class="px-4 py-2 text-right">{{ number_format($totals->segments) }}</td><td class="px-4 py-2 text-right">{{ $money($totals->cost) }}</td></tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
