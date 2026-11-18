@extends('layouts.app')

@section('content')
@php
    $tabClass = fn ($name) => $tab === $name
        ? 'border-b-2 border-teal-600 text-teal-700'
        : 'border-b-2 border-transparent text-slate-600 hover:text-slate-900';
@endphp
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Operations</h1>
                <p class="mt-1 text-sm text-slate-600">One place to see and manage all meter readings, bills, and payments.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('readings.create') }}" class="inline-flex items-center rounded-lg bg-violet-600 px-3 py-2 text-sm font-medium text-white hover:bg-violet-700">
                    <i class="fas fa-plus mr-2"></i>Add reading
                </a>
                <a href="{{ route('bills.create') }}" class="inline-flex items-center rounded-lg bg-orange-500 px-3 py-2 text-sm font-medium text-white hover:bg-orange-600">
                    <i class="fas fa-file-invoice mr-2"></i>Create bill
                </a>
                <a href="{{ route('payments.collect') }}" class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    <i class="fas fa-hand-holding-usd mr-2"></i>Take payment
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
        @endif

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <a href="{{ route('operations.index', ['tab' => 'readings']) }}" class="rounded-xl border border-violet-200 bg-white p-4 shadow-sm hover:border-violet-400">
                <p class="text-xs font-semibold uppercase tracking-wide text-violet-600">Meter readings</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['readings']) }}</p>
                <p class="text-sm text-slate-500">{{ number_format($summary['readings_today']) }} today</p>
            </a>
            <a href="{{ route('operations.index', ['tab' => 'bills']) }}" class="rounded-xl border border-orange-200 bg-white p-4 shadow-sm hover:border-orange-400">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">Bills created</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['bills']) }}</p>
                <p class="text-sm text-slate-500">{{ number_format($summary['bills_open']) }} still open</p>
            </a>
            <a href="{{ route('operations.index', ['tab' => 'payments']) }}" class="rounded-xl border border-teal-200 bg-white p-4 shadow-sm hover:border-teal-400">
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Payments recorded</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['payments']) }}</p>
                <p class="text-sm text-slate-500">Exact paid Rs. {{ number_format($summary['paid_exact'], 2) }}</p>
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap gap-1 border-b border-slate-200 px-4">
                <a href="{{ route('operations.index', array_filter(['tab' => 'readings', 'q' => $q, 'from' => $from, 'to' => $to])) }}" class="px-4 py-3 text-sm font-semibold {{ $tabClass('readings') }}">
                    All readings
                </a>
                <a href="{{ route('operations.index', array_filter(['tab' => 'bills', 'q' => $q, 'from' => $from, 'to' => $to])) }}" class="px-4 py-3 text-sm font-semibold {{ $tabClass('bills') }}">
                    All bills
                </a>
                <a href="{{ route('operations.index', array_filter(['tab' => 'payments', 'q' => $q, 'from' => $from, 'to' => $to])) }}" class="px-4 py-3 text-sm font-semibold {{ $tabClass('payments') }}">
                    All payments
                </a>
            </div>

            <form method="GET" action="{{ route('operations.index') }}" class="grid grid-cols-1 gap-3 border-b border-slate-200 p-4 md:grid-cols-5">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs text-slate-500">Search</label>
                    <input type="search" name="q" value="{{ $q }}" placeholder="{{ $tab === 'payments' ? 'Receipt, customer, bill…' : ($tab === 'bills' ? 'Bill, customer…' : 'Meter, customer, reader…') }}" class="w-full rounded-md border-slate-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-500">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="w-full rounded-md border-slate-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-500">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="w-full rounded-md border-slate-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-500">
                        {{ $tab === 'payments' ? 'Method' : 'Status' }}
                    </label>
                    @if($tab === 'payments')
                        <select name="status" class="w-full rounded-md border-slate-300 text-sm">
                            <option value="">All</option>
                            @foreach(['cash','bank_transfer','cheque','card','wallet','mobile_payment'] as $method)
                                <option value="{{ $method }}" @selected($status === $method)>{{ str_replace('_', ' ', ucfirst($method)) }}</option>
                            @endforeach
                        </select>
                    @elseif($tab === 'bills')
                        <select name="status" class="w-full rounded-md border-slate-300 text-sm">
                            <option value="">All</option>
                            @foreach(['draft','generated','sent','partial','paid','overdue','cancelled'] as $billStatus)
                                <option value="{{ $billStatus }}" @selected($status === $billStatus)>{{ ucfirst($billStatus) }}</option>
                            @endforeach
                        </select>
                    @else
                        <select name="status" class="w-full rounded-md border-slate-300 text-sm">
                            <option value="">All</option>
                            @foreach(['pending','verified','billed','rejected'] as $readingStatus)
                                <option value="{{ $readingStatus }}" @selected($status === $readingStatus)>{{ ucfirst($readingStatus) }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="md:col-span-5 flex gap-2">
                    <button class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                    <a href="{{ route('operations.index', ['tab' => $tab]) }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Clear</a>
                </div>
            </form>

            @if($tab === 'readings')
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Customer / Meter</th>
                                <th class="px-4 py-3 text-right">Previous</th>
                                <th class="px-4 py-3 text-right">Current</th>
                                <th class="px-4 py-3 text-right">Units</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Bill</th>
                                <th class="px-4 py-3 text-right">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($readings as $reading)
                                @php $customer = $reading->waterMeter?->customer; @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ optional($reading->reading_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        @if($customer)
                                            <a href="{{ route('customers.show', $customer) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $customer->full_name }}</a>
                                            <div class="text-xs text-slate-500">{{ $customer->account_number }} · {{ $reading->waterMeter->meter_number ?? '—' }}</div>
                                        @else
                                            <span class="text-slate-700">{{ $reading->waterMeter->meter_number ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums">@units($reading->previous_reading)</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-semibold">@units($reading->current_reading)</td>
                                    <td class="px-4 py-3 text-right tabular-nums text-violet-700">@units($reading->consumption)</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ ucfirst($reading->status ?? 'recorded') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($reading->bill)
                                            <a href="{{ route('bills.show', $reading->bill) }}" class="text-blue-700 hover:text-blue-900">{{ $reading->bill->bill_number }}</a>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('readings.show', $reading) }}" class="text-xs font-medium text-slate-700 hover:text-slate-900">View</a>
                                        <a href="{{ route('readings.edit', $reading) }}" class="ml-2 text-xs font-medium text-blue-700 hover:text-blue-900">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500">No meter readings found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($readings->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3">{{ $readings->links() }}</div>
                @endif
            @endif

            @if($tab === 'bills')
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Bill</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-right">Paid (exact)</th>
                                <th class="px-4 py-3 text-right">Balance</th>
                                <th class="px-4 py-3 text-right">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($bills as $bill)
                                @php $settle = $bill->settlementSummary(); @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('bills.show', $bill) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $bill->bill_number }}</a>
                                        <div class="text-xs text-slate-500">{{ $bill->waterMeter->meter_number ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($bill->customer)
                                            <a href="{{ route('customers.show', $bill->customer) }}" class="text-blue-700 hover:text-blue-900">{{ $bill->customer->full_name }}</a>
                                            <div class="text-xs text-slate-500">{{ $bill->customer->account_number }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ optional($bill->bill_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $bill->status === 'paid' ? 'bg-green-100 text-green-800' : ($bill->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums">Rs. {{ number_format((float) $bill->total_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-semibold text-teal-700">
                                        Rs. {{ number_format($settle['cash_received'], 2) }}
                                        @if($settle['wallet_added'] > 0)
                                            <div class="text-xs font-normal text-indigo-600">Wallet +Rs. {{ number_format($settle['wallet_added'], 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums {{ $bill->balance_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        Rs. {{ number_format((float) $bill->balance_amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('bills.show', $bill) }}" class="text-xs font-medium text-slate-700 hover:text-slate-900">View</a>
                                        <a href="{{ route('bills.print', $bill) }}" target="_blank" class="ml-2 text-xs font-medium text-slate-700 hover:text-slate-900">Print</a>
                                        @if($bill->status !== 'paid')
                                            <a href="{{ route('bills.edit', $bill) }}" class="ml-2 text-xs font-medium text-blue-700 hover:text-blue-900">Edit</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500">No bills found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($bills->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3">{{ $bills->links() }}</div>
                @endif
            @endif

            @if($tab === 'payments')
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Receipt</th>
                                <th class="px-4 py-3">Date &amp; time</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Bill</th>
                                <th class="px-4 py-3">Method</th>
                                <th class="px-4 py-3 text-right">Paid (exact)</th>
                                <th class="px-4 py-3 text-right">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($payments as $payment)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $payment->receipt_number }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $payment->paidOnDisplay() }}</td>
                                    <td class="px-4 py-3">
                                        @if($payment->customer)
                                            <a href="{{ route('customers.show', $payment->customer) }}" class="text-blue-700 hover:text-blue-900">{{ $payment->customer->full_name }}</a>
                                            <div class="text-xs text-slate-500">{{ $payment->customer->account_number }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($payment->bill)
                                            <a href="{{ route('bills.show', $payment->bill) }}" class="text-blue-700 hover:text-blue-900">{{ $payment->bill->bill_number }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold tabular-nums">
                                        Rs. {{ number_format($payment->exactPaidAmount(), 2) }}
                                        @if($payment->walletAddedAmount() > 0)
                                            <div class="text-xs font-normal text-indigo-600">Bill Rs. {{ number_format($payment->amount, 2) }} · Wallet +Rs. {{ number_format($payment->walletAddedAmount(), 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="text-xs font-medium text-slate-700 hover:text-slate-900">Receipt</a>
                                        @if(auth()->user()->isSuperAdmin())
                                            <a href="{{ route('payments.edit', $payment) }}" class="ml-2 text-xs font-medium text-blue-700 hover:text-blue-900">Edit</a>
                                        @endif
                                        <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="ml-2 inline" onsubmit="return confirm('Delete this payment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-700 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">No payments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($payments->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3">{{ $payments->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
