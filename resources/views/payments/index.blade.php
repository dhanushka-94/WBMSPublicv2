@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Payments</h1>
                <p class="text-emerald-100 mt-1">Search, edit, or delete recorded payments. Deleting restores the bill balance.</p>
            </div>
            <a href="{{ route('payments.collect') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                <i class="fas fa-hand-holding-usd mr-2"></i>Take Payment
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('payments.index') }}" class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="search" name="q" id="q" value="{{ $query }}" placeholder="Receipt, bill, account, customer, or reference" class="w-full rounded-md border-gray-300">
            </div>
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 font-semibold text-white hover:bg-emerald-700">Search</button>
            <a href="{{ route('payments.index') }}" class="rounded-lg bg-gray-100 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-200 text-center">Clear</a>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Receipt</th>
                        <th class="px-4 py-3">Date &amp; time</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Bill</th>
                        <th class="px-4 py-3">Method</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $payment->receipt_number }}</td>
                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $payment->paidOnDisplay() }}</td>
                            <td class="px-4 py-3">
                                @if($payment->customer)
                                    <a href="{{ route('customers.show', $payment->customer) }}" class="text-blue-700 hover:text-blue-900">{{ $payment->customer->full_name }}</a>
                                    <div class="text-xs text-gray-500">{{ $payment->customer->account_number }}</div>
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
                            <td class="px-4 py-3 text-gray-700">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                Rs. {{ number_format($payment->exactPaidAmount(), 2) }}
                                @if($payment->walletAddedAmount() > 0)
                                    <div class="text-xs font-normal text-indigo-600">Bill Rs. {{ number_format($payment->amount, 2) }} · Wallet +Rs. {{ number_format($payment->walletAddedAmount(), 2) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200">Receipt</a>
                                    @if(auth()->user()->isSuperAdmin())
                                        <a href="{{ route('payments.edit', $payment) }}" class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 hover:bg-blue-200">Edit</a>
                                    @endif
                                    <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-800 hover:bg-red-200">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="px-4 py-3 border-t">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
