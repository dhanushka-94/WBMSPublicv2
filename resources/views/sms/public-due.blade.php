@extends('sms.layouts.public')

@section('title', 'Amount due')

@section('content')
<section class="overflow-hidden rounded-3xl border border-white/90 bg-white/95 shadow-xl shadow-water-900/5">
    <div class="border-b border-water-100 bg-gradient-to-br from-water-500 to-water-700 px-5 py-5 text-white">
        <p class="text-xs font-medium uppercase tracking-wide text-white/75">Outstanding bills</p>
        <h1 class="mt-1 font-display text-2xl leading-tight">{{ $customer->full_name }}</h1>
        <p class="mt-1 text-sm text-white/85">{{ $customer->account_number }}</p>
    </div>

    <div class="space-y-3 px-5 py-5">
        @forelse($bills as $bill)
            @php
                $balance = (float) $bill->balance_amount;
                $overdue = $bill->isOverdue();
                $amountClass = $balance < 0 ? 'text-leaf-600' : ($balance > 0 ? 'text-rose-600' : 'text-water-900');
            @endphp
            <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('sms.bill', ['bill' => $bill->id]) }}"
               class="block rounded-2xl border border-water-100 bg-water-50/70 p-4 transition hover:border-water-300 hover:bg-white hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-water-900">{{ $bill->bill_number }}</p>
                        <p class="mt-0.5 text-xs {{ $overdue ? 'font-semibold text-rose-600' : 'text-water-700/80' }}">
                            Due {{ $bill->due_date->format('M d, Y') }}
                        </p>
                    </div>
                    @if($overdue)
                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">Overdue</span>
                    @elseif($balance < 0)
                        <span class="rounded-full bg-leaf-500/15 px-2 py-0.5 text-[11px] font-semibold text-leaf-700">Credit</span>
                    @endif
                </div>
                <p class="mt-2 font-display text-2xl {{ $amountClass }}">
                    Rs. {{ number_format(abs($balance), 2) }}@if($balance < 0)<span class="ml-1 text-base">CR</span>@endif
                </p>
                <p class="mt-2 text-xs font-medium text-water-600">View bill details →</p>
            </a>
        @empty
            <div class="rounded-2xl bg-water-50 px-4 py-6 text-center">
                <p class="font-display text-lg text-water-900">No outstanding bills</p>
                <p class="mt-1 text-sm text-water-700/80">This account is currently clear.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
