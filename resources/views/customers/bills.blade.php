@extends('layouts.app')

@section('title', 'Customer Bills')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->full_name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $customer->account_number }} · All bills</p>
                </div>
                <a href="{{ route('customers.show', $customer) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Customer
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm text-gray-600">Bills</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($bills->total()) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm text-gray-600">Outstanding</p>
                <p class="text-2xl font-bold text-gray-900">@lkr($totalOutstanding)</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consumption</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($bills as $bill)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $bill->bill_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $bill->bill_date->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">Due: {{ $bill->due_date->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $bill->billing_period_from->format('M d') }} – {{ $bill->billing_period_to->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $bill->waterMeter->meter_number ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($bill->consumption) }} units</div>
                                    <div class="text-sm text-gray-500">{{ number_format($bill->previous_reading) }} → {{ number_format($bill->current_reading) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">@lkr($bill->total_amount)</div>
                                    @if($bill->balance_amount > 0)
                                        <div class="text-sm text-red-600">Balance: @lkr($bill->balance_amount)</div>
                                    @else
                                        <div class="text-sm text-green-600">Paid</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'generated' => 'bg-blue-100 text-blue-800',
                                            'sent' => 'bg-yellow-100 text-yellow-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                            'overdue' => 'bg-red-100 text-red-800',
                                            'cancelled' => 'bg-gray-100 text-gray-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$bill->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($bill->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-3">
                                        <a href="{{ route('bills.show', $bill) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('bills.print', $bill) }}" class="text-green-600 hover:text-green-900" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-file-invoice text-4xl mb-4"></i>
                                    <p>No bills for this customer.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bills->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
