@extends('layouts.app')

@section('title', 'Edit Payment '.$payment->receipt_number)

@section('content')
<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit payment</h1>
        <p class="text-gray-600 mt-1">{{ $payment->receipt_number }} — changing the amount updates the bill balance.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Customer</dt>
                <dd class="font-medium text-gray-900">
                    @if($payment->customer)
                        <a href="{{ route('customers.show', $payment->customer) }}" class="text-blue-700 hover:text-blue-900">{{ $payment->customer->full_name }}</a>
                        <div class="text-xs text-gray-500">{{ $payment->customer->account_number }}</div>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Bill</dt>
                <dd class="font-medium text-gray-900">
                    @if($payment->bill)
                        <a href="{{ route('bills.show', $payment->bill) }}" class="text-blue-700 hover:text-blue-900">{{ $payment->bill->bill_number }}</a>
                        <div class="text-xs text-gray-500">Balance Rs. {{ number_format($payment->bill->balance_amount, 2) }}</div>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Received by</dt>
                <dd class="font-medium text-gray-900">{{ $payment->receivedByLabel() }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Wallet available</dt>
                <dd class="font-medium text-gray-900">Rs. {{ number_format($payment->customer?->wallet_balance ?? 0, 2) }}</dd>
            </div>
        </dl>
    </div>

    <form method="POST" action="{{ route('payments.update', $payment) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (Rs.)</label>
                <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                       value="{{ old('amount', $payment->amount) }}"
                       class="w-full rounded-md border-gray-300">
                <p class="mt-1 text-xs text-gray-500">
                    Max Rs. {{ number_format((float) $payment->amount + (float) ($payment->bill?->balance_amount ?? 0), 2) }}
                    (current payment + remaining bill balance).
                </p>
            </div>
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                <select name="payment_method" id="payment_method" required class="w-full rounded-md border-gray-300">
                    @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'wallet' => 'Wallet', 'mobile_payment' => 'Mobile payment'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', $payment->payment_method) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="paid_on" class="block text-sm font-medium text-gray-700 mb-1">Payment date &amp; time</label>
                <input type="datetime-local" name="paid_on" id="paid_on" required
                       value="{{ old('paid_on', optional($payment->paid_on)->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded-md border-gray-300">
            </div>
            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                <input type="text" name="reference" id="reference"
                       value="{{ old('reference', $payment->reference) }}"
                       class="w-full rounded-md border-gray-300">
            </div>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="w-full rounded-md border-gray-300">{{ old('notes', $payment->notes) }}</textarea>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700">Save changes</button>
            <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="rounded-lg bg-gray-100 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-200">Print receipt</a>
            <a href="{{ route('payments.index') }}" class="rounded-lg bg-gray-100 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-200">← Back to payments</a>
        </div>
    </form>

    <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="mt-6 bg-white rounded-xl border border-red-200 p-6">
        @csrf
        @method('DELETE')
        <h2 class="text-lg font-semibold text-red-800">Delete this payment</h2>
        <p class="mt-1 text-sm text-red-700">The bill paid amount is reduced by Rs. {{ number_format($payment->amount, 2) }}. If this was a wallet payment, the wallet is restored.</p>
        <button type="submit" class="mt-4 rounded-lg bg-red-600 px-5 py-2 font-semibold text-white hover:bg-red-700">Delete payment</button>
    </form>
</div>
@endsection
