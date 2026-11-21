@extends('sms.layouts.public')

@section('title', 'Bill '.$bill->bill_number)

@section('content')
@php
    $balance = (float) $bill->balance_amount;
    $overdue = $bill->isOverdue();
    $opening = $bill->isOpeningBalance();
    $statusLabel = $opening ? 'Opening balance' : ($overdue ? 'Overdue' : ucfirst((string) $bill->status));
    $amountClass = $balance < 0 ? 'text-leaf-600' : ($balance > 0 ? 'text-rose-600' : 'text-water-900');
    $amountLabel = $balance < 0 ? 'Credit balance' : 'Amount due';
@endphp

<section class="overflow-hidden rounded-3xl border border-white/90 bg-white/95 shadow-xl shadow-water-900/5">
    <div class="border-b border-water-100 bg-gradient-to-br from-water-500 to-water-700 px-5 py-5 text-white">
        <p class="text-xs font-medium uppercase tracking-wide text-white/75">Account</p>
        <h1 class="mt-1 font-display text-2xl leading-tight">{{ $customer->full_name }}</h1>
        <p class="mt-1 text-sm text-white/85">{{ $customer->account_number }}</p>
    </div>

    <div class="space-y-5 px-5 py-6">
        <div>
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm text-water-700/80">{{ $amountLabel }}</p>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                    {{ $opening ? 'bg-amber-100 text-amber-800' : ($overdue ? 'bg-rose-100 text-rose-700' : ($balance < 0 ? 'bg-leaf-500/15 text-leaf-700' : 'bg-water-100 text-water-700')) }}">
                    {{ $statusLabel }}
                </span>
            </div>
            <p class="mt-1 font-display text-4xl tracking-tight {{ $amountClass }}">
                Rs. {{ number_format(abs($balance), 2) }}@if($balance < 0)<span class="ml-1 text-2xl">CR</span>@endif
            </p>
            <p class="mt-1 text-sm text-water-700/75">
                of Rs. {{ number_format((float) $bill->total_amount, 2) }} billed
            </p>
        </div>

        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-water-50 px-4 py-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-water-600">Bill number</dt>
                <dd class="mt-1 text-sm font-semibold text-water-900">{{ $bill->bill_number }}</dd>
            </div>
            <div class="rounded-2xl bg-water-50 px-4 py-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-water-600">Due date</dt>
                <dd class="mt-1 text-sm font-semibold {{ $overdue ? 'text-rose-600' : 'text-water-900' }}">{{ $bill->due_date->format('M d, Y') }}</dd>
            </div>
            <div class="rounded-2xl bg-water-50 px-4 py-3 sm:col-span-2">
                <dt class="text-xs font-medium uppercase tracking-wide text-water-600">Billing period</dt>
                <dd class="mt-1 text-sm font-semibold text-water-900">
                    {{ $bill->billing_period_from->format('M d, Y') }}
                    –
                    {{ $bill->billing_period_to->format('M d, Y') }}
                </dd>
            </div>
        </dl>

        <div class="rounded-2xl border border-dashed border-water-200 bg-water-50/70 px-4 py-3 text-sm text-water-800">
            Please pay this bill at the Dunsinane Water Supply Scheme office and keep your receipt for records.
        </div>
    </div>
</section>
@endsection
