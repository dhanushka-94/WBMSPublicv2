@extends('sms.layouts.public')

@section('title', 'Receipt '.$payment->receipt_number)

@section('content')
@php
    $cash = method_exists($payment, 'exactPaidAmount') ? (float) $payment->exactPaidAmount() : (float) $payment->amount;
    $walletAdded = method_exists($payment, 'walletAddedAmount') ? (float) $payment->walletAddedAmount() : 0.0;
    $paidOn = $payment->paid_on ?? $payment->created_at;
@endphp

<section class="overflow-hidden rounded-3xl border border-white/90 bg-white/95 shadow-xl shadow-water-900/5">
    <div class="border-b border-water-100 bg-gradient-to-br from-leaf-600 to-water-700 px-5 py-5 text-white">
        <p class="text-xs font-medium uppercase tracking-wide text-white/75">Payment confirmed</p>
        <h1 class="mt-1 font-display text-2xl leading-tight">{{ $payment->customer->full_name }}</h1>
        <p class="mt-1 text-sm text-white/85">{{ $payment->customer->account_number }}</p>
    </div>

    <div class="space-y-5 px-5 py-6">
        <div>
            <p class="text-sm text-water-700/80">Amount received</p>
            <p class="mt-1 font-display text-4xl tracking-tight text-leaf-600">
                Rs. {{ number_format($cash, 2) }}
            </p>
            @if($walletAdded > 0)
                <p class="mt-1 text-sm text-water-700/75">
                    Applied to bill Rs. {{ number_format((float) $payment->amount, 2) }}
                    · Added to wallet Rs. {{ number_format($walletAdded, 2) }}
                </p>
            @endif
        </div>

        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-water-50 px-4 py-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-water-600">Receipt</dt>
                <dd class="mt-1 text-sm font-semibold text-water-900">{{ $payment->receipt_number }}</dd>
            </div>
            <div class="rounded-2xl bg-water-50 px-4 py-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-water-600">Paid on</dt>
                <dd class="mt-1 text-sm font-semibold text-water-900">{{ $paidOn?->format('M d, Y g:i A') ?? '—' }}</dd>
            </div>
            <div class="rounded-2xl bg-water-50 px-4 py-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-water-600">Bill</dt>
                <dd class="mt-1 text-sm font-semibold text-water-900">{{ $payment->bill->bill_number ?? '—' }}</dd>
            </div>
            <div class="rounded-2xl bg-water-50 px-4 py-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-water-600">Method</dt>
                <dd class="mt-1 text-sm font-semibold text-water-900">{{ ucfirst(str_replace('_', ' ', (string) $payment->payment_method)) }}</dd>
            </div>
            @if($payment->receiver)
            <div class="rounded-2xl bg-water-50 px-4 py-3 sm:col-span-2">
                <dt class="text-xs font-medium uppercase tracking-wide text-water-600">Received by</dt>
                <dd class="mt-1 text-sm font-semibold text-water-900">{{ $payment->receiver->name }}</dd>
            </div>
            @endif
        </dl>

        <div class="rounded-2xl border border-dashed border-leaf-500/40 bg-leaf-500/10 px-4 py-3 text-sm text-leaf-700">
            Thank you for your payment. Keep this receipt for your records.
        </div>
    </div>
</section>
@endsection
