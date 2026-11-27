@extends('layouts.app')

@section('content')
@php
    $displayNotes = trim(preg_replace('/\s*Imported from customer-readings-payments\.xlsx\.\s*Sheet name:.*$/i', '', (string) $customer->notes));
    $statusClass = match ($customer->status) {
        'active' => 'bg-emerald-100 text-emerald-800',
        'suspended' => 'bg-amber-100 text-amber-800',
        'disconnected' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp
<div class="min-h-screen bg-slate-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm text-slate-500">Customer</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $customer->full_name }}</h1>
                <p class="mt-1 text-sm text-slate-600 font-mono">{{ $customer->account_number }}
                    @if($customer->meter_number)
                        · Meter {{ $customer->meter_number }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="#make-payment" class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    <i class="fas fa-hand-holding-usd mr-2"></i>Make Payment
                </a>
                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                @if(auth()->user()->isSuperAdmin())
                    <form method="POST" action="{{ route('customers.destroy', $customer) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                @endif
                <a href="{{ route('customers.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
        @endif
        @php($walletErrors = $errors->getBag('wallet'))
        @if($walletErrors->any())
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                <p class="font-semibold">The wallet was not changed.</p>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach($walletErrors->all() as $walletError)
                        <li>{{ $walletError }}</li>
                    @endforeach
                </ul>
                <a href="#wallet" class="mt-2 inline-block text-sm font-medium underline">Go to the wallet form</a>
            </div>
        @endif

        <nav class="mb-6 flex gap-2 overflow-x-auto pb-1 text-sm">
            <a href="#overview" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Overview</a>
            <a href="#charts" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Charts</a>
            <a href="#readings" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Readings</a>
            <a href="#customer-bills" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Bills</a>
            <a href="#customer-payments" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Payments</a>
            <a href="#make-payment" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Pay</a>
            <a href="#wallet" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Wallet</a>
            <a href="#connection" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Connection</a>
            <a href="#profile" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">Profile</a>
        </nav>

        {{-- 1. Overview --}}
        <section id="overview" class="mb-8 scroll-mt-24 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-slate-800 px-5 py-5 text-white">
                    <div class="flex items-center gap-4">
                        @if($customer->profile_photo)
                            <img class="h-16 w-16 rounded-full object-cover ring-2 ring-white/40 cursor-pointer" src="{{ $customer->profile_photo_url }}" alt="{{ $customer->full_name }}" data-lightbox>
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/15 ring-2 ring-white/40">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                        @endif
                        <div>
                            <h2 class="text-lg font-semibold">{{ $customer->full_name }}</h2>
                            <p class="text-slate-300 text-sm">{{ $customer->customerType->name ?? 'Customer' }}</p>
                            <span class="mt-2 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($customer->status) }}</span>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 p-5 text-sm">
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Division</span><span class="font-medium text-slate-900">{{ $customer->division->name ?? '—' }}</span></div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Phone</span><span class="font-medium text-slate-900">{{ $customer->phone ?: '—' }}</span></div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Last bill</span><span class="font-medium text-slate-900">{{ $lastBill ? 'Rs. '.number_format($lastBill->total_amount, 2) : '—' }}</span></div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Current reading</span><span class="font-medium text-slate-900">@if($currentReading)@units($currentReading->current_reading)@else—@endif</span></div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Wallet</span><span class="font-medium text-teal-700">Rs. {{ number_format($customer->wallet_balance, 2) }}</span></div>
                </div>
            </div>

            <div class="lg:col-span-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-emerald-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Financial Summary</h2>
                    <p class="text-emerald-100 text-sm">Billed vs received, including overpayments added to wallet</p>
                </div>
                <div class="grid grid-cols-2 gap-4 p-5 md:grid-cols-3">
                    <div class="rounded-lg bg-blue-50 p-4">
                        <p class="text-xs font-medium uppercase text-blue-700">Total billed</p>
                        <p class="mt-1 text-xl font-bold text-blue-900">Rs. {{ number_format($financialSummary['billed'], 2) }}</p>
                        <p class="text-xs text-blue-700">{{ $financialSummary['bills'] }} bills</p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-4">
                        <p class="text-xs font-medium uppercase text-green-700">Applied to bills</p>
                        <p class="mt-1 text-xl font-bold text-green-900">Rs. {{ number_format($financialSummary['paid'], 2) }}</p>
                        <p class="text-xs text-green-700">{{ $financialSummary['payments'] }} receipts</p>
                    </div>
                    <div class="rounded-lg bg-indigo-50 p-4">
                        <p class="text-xs font-medium uppercase text-indigo-700">Overpaid to wallet</p>
                        <p class="mt-1 text-xl font-bold text-indigo-900">Rs. {{ number_format($financialSummary['overpaid'], 2) }}</p>
                        <p class="text-xs text-indigo-700">Extra cash received</p>
                    </div>
                    <div class="rounded-lg bg-cyan-50 p-4">
                        <p class="text-xs font-medium uppercase text-cyan-700">Total received</p>
                        <p class="mt-1 text-xl font-bold text-cyan-900">Rs. {{ number_format($financialSummary['received'], 2) }}</p>
                        @if($financialSummary['last_payment'])
                            <p class="text-xs text-cyan-700">Last {{ $financialSummary['last_payment']->paidOnDisplay('M d, Y H:i') }}</p>
                        @endif
                    </div>
                    <div class="rounded-lg bg-amber-50 p-4">
                        <p class="text-xs font-medium uppercase text-amber-700">Outstanding</p>
                        <p class="mt-1 text-xl font-bold text-amber-900">Rs. {{ number_format($financialSummary['outstanding'], 2) }}</p>
                        <p class="text-xs text-amber-700">Overdue Rs. {{ number_format($financialSummary['overdue'], 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-teal-50 p-4">
                        <p class="text-xs font-medium uppercase text-teal-700">Wallet now</p>
                        <p class="mt-1 text-xl font-bold text-teal-900">Rs. {{ number_format($customer->wallet_balance, 2) }}</p>
                        <a href="#make-payment" class="text-xs font-medium text-teal-700 hover:text-teal-900">Make payment</a>
                        <span class="text-xs text-teal-400">·</span>
                        <a href="#wallet" class="text-xs font-medium text-teal-700 hover:text-teal-900">Wallet history</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. Charts --}}
        <section id="charts" class="mb-8 scroll-mt-24 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white"><i class="fas fa-chart-bar mr-2"></i>{{ $chartYear }} bills & payments</h3>
                    <p class="mt-1 text-sm text-emerald-100">Draft, billed, exact paid (cash), and overpayments to wallet</p>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="customerYearMoneyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden rounded-xl border border-cyan-100 bg-white shadow-sm">
                <div class="bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white"><i class="fas fa-chart-line mr-2"></i>{{ $chartYear }} water use</h3>
                    <p class="mt-1 text-sm text-cyan-100">Units consumed and readings taken</p>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="customerYearUsageChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. Readings --}}
        <section id="readings" class="mb-8 scroll-mt-24 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-violet-600 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">Meter Readings</h2>
                    <p class="text-sm text-violet-100">{{ $financialSummary['readings'] }} total readings for this customer</p>
                </div>
                @if($activeMeters->count() > 0)
                    <a href="{{ route('customers.meters', $customer) }}" class="rounded-lg bg-white/15 px-3 py-1.5 text-sm font-medium text-white hover:bg-white/25">Manage meters</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Meter</th>
                            <th class="px-4 py-3 text-right">Previous</th>
                            <th class="px-4 py-3 text-right">Current</th>
                            <th class="px-4 py-3 text-right">Consumption</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customerReadings as $reading)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-800">{{ optional($reading->reading_date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $reading->waterMeter->meter_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">@units($reading->previous_reading)</td>
                                <td class="px-4 py-3 text-right tabular-nums font-semibold">@units($reading->current_reading)</td>
                                <td class="px-4 py-3 text-right tabular-nums text-violet-700">@units($reading->consumption)</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ ucfirst($reading->status ?? 'recorded') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">No meter readings yet for this customer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($customerReadings->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $customerReadings->links() }}</div>
            @endif
        </section>

        {{-- 4. Bills & Payments --}}
        <div class="mb-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div id="customer-bills" class="scroll-mt-24 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-orange-500 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Bills</h2>
                    <p class="text-sm text-orange-100">{{ $financialSummary['bills'] }} total · filter below</p>
                </div>
                <form method="GET" action="{{ route('customers.show', $customer) }}#customer-bills" class="grid grid-cols-1 gap-3 border-b border-slate-200 p-4 md:grid-cols-4">
                    @foreach($paymentFilters as $key => $value)
                        @if($value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    @if(request('payments_page'))
                        <input type="hidden" name="payments_page" value="{{ request('payments_page') }}">
                    @endif
                    @if(request('readings_page'))
                        <input type="hidden" name="readings_page" value="{{ request('readings_page') }}">
                    @endif
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">Search</label>
                        <input type="search" name="bill_q" value="{{ $billFilters['bill_q'] }}" placeholder="Bill number" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">Status</label>
                        <select name="bill_status" class="w-full rounded-md border-slate-300 text-sm">
                            <option value="">All</option>
                            @foreach(['draft','generated','sent','partial','paid','overdue','cancelled'] as $status)
                                <option value="{{ $status }}" @selected($billFilters['bill_status'] === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">From</label>
                        <input type="date" name="bill_from" value="{{ $billFilters['bill_from'] }}" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">To</label>
                        <input type="date" name="bill_to" value="{{ $billFilters['bill_to'] }}" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Filter</button>
                        <a href="{{ route('customers.show', $customer) }}#customer-bills" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Clear</a>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-2">Bill</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2 text-right">Total</th>
                                <th class="px-4 py-2 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($customerBills as $bill)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-2"><a href="{{ route('bills.show', $bill) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $bill->bill_number }}</a></td>
                                    <td class="px-4 py-2 text-slate-700">{{ optional($bill->bill_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $bill->status === 'paid' ? 'bg-green-100 text-green-800' : ($bill->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right">Rs. {{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">Rs. {{ number_format($bill->balance_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No bills match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customerBills->hasPages())
                    <div class="border-t px-4 py-3">{{ $customerBills->links() }}</div>
                @endif
            </div>

            <div id="customer-payments" class="scroll-mt-24 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-emerald-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Payments</h2>
                    <p class="text-sm text-emerald-100">{{ $financialSummary['payments'] }} receipts · date &amp; time on each</p>
                </div>
                <form method="GET" action="{{ route('customers.show', $customer) }}#customer-payments" class="grid grid-cols-1 gap-3 border-b border-slate-200 p-4 md:grid-cols-4">
                    @foreach($billFilters as $key => $value)
                        @if($value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    @if(request('bills_page'))
                        <input type="hidden" name="bills_page" value="{{ request('bills_page') }}">
                    @endif
                    @if(request('readings_page'))
                        <input type="hidden" name="readings_page" value="{{ request('readings_page') }}">
                    @endif
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">Search</label>
                        <input type="search" name="payment_q" value="{{ $paymentFilters['payment_q'] }}" placeholder="Receipt, bill, reference" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">Method</label>
                        <select name="payment_method" class="w-full rounded-md border-slate-300 text-sm">
                            <option value="">All</option>
                            @foreach(['cash','bank_transfer','cheque','card','wallet','mobile_payment'] as $method)
                                <option value="{{ $method }}" @selected($paymentFilters['payment_method'] === $method)>{{ str_replace('_', ' ', ucfirst($method)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">From</label>
                        <input type="date" name="payment_from" value="{{ $paymentFilters['payment_from'] }}" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">To</label>
                        <input type="date" name="payment_to" value="{{ $paymentFilters['payment_to'] }}" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button>
                        <a href="{{ route('customers.show', $customer) }}#customer-payments" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Clear</a>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-2">Receipt</th>
                                <th class="px-4 py-2">Date &amp; time</th>
                                <th class="px-4 py-2">Bill</th>
                                <th class="px-4 py-2">Method</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                                <th class="px-4 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($customerPayments as $payment)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium text-slate-900">{{ $payment->receipt_number }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-slate-700">{{ $payment->paidOnDisplay() }}</td>
                                    <td class="px-4 py-2">
                                        @if($payment->bill)
                                            <a href="{{ route('bills.show', $payment->bill) }}" class="text-blue-700 hover:text-blue-900">{{ $payment->bill->bill_number }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-slate-700">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">
                                        Rs. {{ number_format($payment->exactPaidAmount(), 2) }}
                                        @if($payment->walletAddedAmount() > 0)
                                            <div class="text-xs font-normal text-indigo-600">Bill Rs. {{ number_format($payment->amount, 2) }} · Wallet +Rs. {{ number_format($payment->walletAddedAmount(), 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="text-xs font-medium text-slate-700 hover:text-slate-900">Receipt</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No payments match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customerPayments->hasPages())
                    <div class="border-t px-4 py-3">{{ $customerPayments->links() }}</div>
                @endif
            </div>
        </div>

        {{-- 5. Make Payment --}}
        <section id="make-payment" class="mb-8 scroll-mt-24 overflow-hidden rounded-xl border border-teal-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-teal-600 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">Make Payment</h2>
                    <p class="text-sm text-teal-100">Record a payment. Extra cash is added to the wallet.</p>
                </div>
                <a href="{{ route('payments.collect', ['customer' => $customer->id]) }}" class="text-sm font-medium text-white/90 underline hover:text-white">Open full collect page</a>
            </div>
            <form method="POST" action="{{ route('payments.collect.store') }}" class="space-y-4 p-6">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="redirect_to" value="customer">

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-3 py-2">Pay</th>
                                <th class="px-3 py-2">Bill</th>
                                <th class="px-3 py-2">Due</th>
                                <th class="px-3 py-2 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($openBills as $bill)
                                <tr>
                                    <td class="px-3 py-2">
                                        <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}" class="customer-bill-pick rounded border-slate-300 text-teal-600" data-balance="{{ $bill->balance_amount }}" checked>
                                    </td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('bills.show', $bill) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $bill->bill_number }}</a>
                                        <div class="text-xs text-slate-500">{{ ucfirst($bill->status) }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-slate-700">{{ optional($bill->due_date)->format('Y-m-d') }}</td>
                                    <td class="px-3 py-2 text-right font-semibold">Rs. {{ number_format($bill->balance_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-slate-500">No open bills. An amount entered below is added to the wallet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="customer_pay_amount" class="mb-1 block text-sm font-medium text-slate-700">Amount (Rs.)</label>
                        <input type="number" name="amount" id="customer_pay_amount" step="0.01" min="0" value="{{ old('amount', $openBills->sum('balance_amount')) }}" class="w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label for="customer_pay_method" class="mb-1 block text-sm font-medium text-slate-700">Method</label>
                        <select name="payment_method" id="customer_pay_method" class="w-full rounded-md border-slate-300" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div>
                        <label for="customer_pay_date" class="mb-1 block text-sm font-medium text-slate-700">Date &amp; time</label>
                        <input type="datetime-local" name="payment_date" id="customer_pay_date" value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" required class="w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label for="customer_pay_reference" class="mb-1 block text-sm font-medium text-slate-700">Reference</label>
                        <input type="text" name="reference" id="customer_pay_reference" value="{{ old('reference') }}" class="w-full rounded-md border-slate-300">
                    </div>
                </div>

                <div>
                    <label for="customer_pay_notes" class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
                    <textarea name="notes" id="customer_pay_notes" rows="2" class="w-full rounded-md border-slate-300">{{ old('notes') }}</textarea>
                </div>

                @if($customer->wallet_balance > 0)
                    <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                        <input type="checkbox" name="use_wallet" value="1" class="rounded border-slate-300 text-teal-600" {{ old('use_wallet') ? 'checked' : '' }}>
                        Use wallet (Rs. {{ number_format($customer->wallet_balance, 2) }} available)
                    </label>
                @endif

                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700">
                    <i class="fas fa-check mr-2"></i>Save payment
                </button>
            </form>
        </section>

        {{-- 5b. Wallet --}}
        @php($canEditWallet = in_array(auth()->user()->role, ['admin', 'super_admin'], true))
        <section id="wallet" class="mb-8 scroll-mt-24 overflow-hidden rounded-xl border border-teal-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-teal-700 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-white"><i class="fas fa-wallet mr-2"></i>Wallet</h2>
                    <p class="text-sm text-teal-100">Credit that is used on this customer's next bills.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase text-teal-100">Balance</p>
                    <p class="text-2xl font-bold text-white">Rs. {{ number_format($customer->wallet_balance, 2) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 {{ $canEditWallet ? 'lg:grid-cols-3' : '' }}">
                <div class="{{ $canEditWallet ? 'lg:col-span-2' : '' }}">
                    <h3 class="mb-2 text-sm font-semibold text-slate-800">History</h3>
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">What</th>
                                    <th class="px-3 py-2 text-right">Amount</th>
                                    <th class="px-3 py-2">Note</th>
                                    <th class="px-3 py-2">By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($walletEntries as $entry)
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-3 py-2">
                                            <span class="font-medium {{ $entry->is_manual_adjustment ? 'text-teal-700' : 'text-slate-800' }}">{{ $entry->reason_label }}</span>
                                            @if($entry->payment)
                                                <div class="text-xs text-slate-500">Receipt {{ $entry->payment->receipt_number }}</div>
                                            @elseif($entry->bill)
                                                <div class="text-xs text-slate-500">Bill {{ $entry->bill->bill_number }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-2 text-right font-semibold {{ (float) $entry->amount >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                            {{ (float) $entry->amount >= 0 ? '+' : '-' }}Rs. {{ number_format(abs((float) $entry->amount), 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">{{ $entry->notes ?: '—' }}</td>
                                        <td class="whitespace-nowrap px-3 py-2 text-slate-600">{{ $entry->user->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-slate-500">No wallet activity yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($walletEntryCount > $walletEntries->count())
                        <p class="mt-2 text-xs text-slate-500">Showing the latest {{ $walletEntries->count() }} of {{ $walletEntryCount }} entries.</p>
                    @endif
                </div>

                @if($canEditWallet)
                    <form method="POST" action="{{ route('customers.wallet.adjust', $customer) }}" class="space-y-4 rounded-lg border border-teal-200 bg-teal-50/40 p-4"
                          onsubmit="return confirm('Save this wallet change? It is kept in the wallet history and the activity log.');">
                        @csrf
                        <h3 class="text-sm font-semibold text-slate-800">Edit wallet</h3>

                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex cursor-pointer items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                <input type="radio" name="type" value="add" class="text-green-600" {{ old('type', 'add') === 'add' ? 'checked' : '' }}>
                                <span>Add credit</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                <input type="radio" name="type" value="deduct" class="text-red-600" {{ old('type') === 'deduct' ? 'checked' : '' }}>
                                <span>Deduct credit</span>
                            </label>
                        </div>

                        <div>
                            <label for="wallet_amount" class="mb-1 block text-sm font-medium text-slate-700">Amount (Rs.)</label>
                            <input type="number" name="amount" id="wallet_amount" step="0.01" min="0.01" value="{{ old('amount') }}" required class="w-full rounded-md border-slate-300">
                        </div>

                        <div>
                            <label for="wallet_reason" class="mb-1 block text-sm font-medium text-slate-700">Reason (required)</label>
                            <textarea name="reason" id="wallet_reason" rows="2" maxlength="200" required placeholder="e.g. Refund of a wrong bill, correction after audit" class="w-full rounded-md border-slate-300">{{ old('reason') }}</textarea>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-teal-700 px-5 py-2.5 font-semibold text-white hover:bg-teal-800">
                            <i class="fas fa-save mr-2"></i>Save wallet change
                        </button>
                        <p class="text-xs text-slate-500">Past entries are never changed. Each edit is a new line in the history above. Admin changes are not counted as cash received in any report.</p>
                    </form>
                @else
                    <p class="text-xs text-slate-500">Only an Admin or Super Admin can change a wallet.</p>
                @endif
            </div>
        </section>

        {{-- 6. Connection --}}
        <section id="connection" class="mb-8 scroll-mt-24 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Connection</h2>
            <p class="mb-4 text-sm text-slate-600">Status: <span class="font-medium">{{ ucfirst($customer->status) }}</span></p>
            <form method="POST" action="{{ $customer->status === 'disconnected' ? route('customers.reconnect', $customer) : route('customers.disconnect', $customer) }}" class="grid grid-cols-1 items-end gap-3 md:grid-cols-4">
                @csrf
                <div>
                    <label class="block text-xs text-slate-500">Date</label>
                    <input type="date" name="event_date" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded-md border-slate-300 text-sm" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500">Reason</label>
                    <input type="text" name="reason" class="mt-1 w-full rounded-md border-slate-300 text-sm" required>
                </div>
                <button class="rounded-md px-4 py-2 text-sm text-white {{ $customer->status === 'disconnected' ? 'bg-emerald-600' : 'bg-red-600' }}">
                    {{ $customer->status === 'disconnected' ? 'Reconnect' : 'Disconnect' }}
                </button>
            </form>
            @if($customer->connectionEvents->isNotEmpty())
                <ul class="mt-4 space-y-1 text-sm text-slate-700">
                    @foreach($customer->connectionEvents->take(5) as $event)
                        <li>{{ $event->event_date->format('Y-m-d') }} — {{ ucfirst($event->action) }} — {{ $event->reason }} — {{ $event->user?->name ?? 'Office' }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- 7. Profile details --}}
        <section id="profile" class="scroll-mt-24 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="bg-slate-700 px-5 py-3">
                        <h3 class="font-semibold text-white"><i class="fas fa-id-card mr-2"></i>Account &amp; personal</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase text-slate-500">Account number</p>
                            <p class="mt-1 font-semibold text-blue-700">{{ $customer->account_number }}</p>
                        </div>
                        @if($customer->reference_number)
                        <div>
                            <p class="text-xs uppercase text-slate-500">Reference</p>
                            <p class="mt-1 text-slate-900">{{ $customer->reference_number }}</p>
                        </div>
                        @endif
                        @if($customer->meter_number)
                        <div>
                            <p class="text-xs uppercase text-slate-500">Meter number</p>
                            <p class="mt-1 text-slate-900">{{ $customer->meter_number }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-xs uppercase text-slate-500">Connection date</p>
                            <p class="mt-1 text-slate-900">{{ $customer->connection_date?->format('M d, Y') ?? 'Not set' }}</p>
                        </div>
                        @if($customer->email)
                        <div>
                            <p class="text-xs uppercase text-slate-500">Email</p>
                            <p class="mt-1 text-slate-900">{{ $customer->email }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-xs uppercase text-slate-500">Phone</p>
                            <p class="mt-1 text-slate-900">{{ $customer->phone ?: '—' }}</p>
                        </div>
                        @if($customer->phone_two)
                        <div>
                            <p class="text-xs uppercase text-slate-500">Phone (secondary)</p>
                            <p class="mt-1 text-slate-900">{{ $customer->phone_two }}</p>
                        </div>
                        @endif
                        @if($customer->nic)
                        <div>
                            <p class="text-xs uppercase text-slate-500">NIC</p>
                            <p class="mt-1 text-slate-900">{{ $customer->nic }}</p>
                        </div>
                        @endif
                        @if($customer->epf_number)
                        <div>
                            <p class="text-xs uppercase text-slate-500">EPF</p>
                            <p class="mt-1 text-slate-900">{{ $customer->epf_number }}</p>
                        </div>
                        @endif
                        @if($customer->deposit_amount > 0)
                        <div>
                            <p class="text-xs uppercase text-slate-500">Deposit</p>
                            <p class="mt-1 text-slate-900">Rs. {{ number_format($customer->deposit_amount, 2) }}</p>
                        </div>
                        @endif
                        @if($customer->address)
                        <div class="md:col-span-2">
                            <p class="text-xs uppercase text-slate-500">Address</p>
                            <p class="mt-1 text-slate-900">
                                {{ $customer->address }}
                                @if($customer->city), {{ $customer->city }}@endif
                                @if($customer->postal_code) {{ $customer->postal_code }}@endif
                            </p>
                        </div>
                        @endif
                        @if($customer->guarantor)
                        <div class="md:col-span-2">
                            <p class="text-xs uppercase text-slate-500">Guarantor</p>
                            <p class="mt-1 text-slate-900">{{ $customer->guarantor->full_name }} ({{ $customer->guarantor->relationship }})</p>
                        </div>
                        @endif
                        @if($displayNotes !== '')
                        <div class="md:col-span-2">
                            <p class="text-xs uppercase text-slate-500">Notes</p>
                            <p class="mt-1 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">{{ $displayNotes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                @if($activeMeters->count() > 0)
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="bg-violet-600 px-5 py-3">
                        <h3 class="font-semibold text-white"><i class="fas fa-tachometer-alt mr-2"></i>Water meters ({{ $activeMeters->count() }})</h3>
                    </div>
                    <div class="space-y-3 p-5">
                        @foreach($activeMeters as $meter)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 p-4">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $meter->meter_number }}</p>
                                    <p class="text-sm text-slate-500">
                                        {{ $meter->meter_brand }} {{ $meter->meter_model }}
                                        ({{ $meter->meter_size }}mm {{ ucfirst($meter->meter_type) }})
                                    </p>
                                    @if($meter->location_notes)
                                        <p class="mt-1 text-sm text-slate-600">{{ $meter->location_notes }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-violet-700">@units($meter->current_reading)</p>
                                    <p class="text-xs text-slate-500">Current reading</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="space-y-6">
                @if($customer->qr_code_url)
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="bg-indigo-600 px-5 py-3">
                        <h3 class="font-semibold text-white"><i class="fas fa-qrcode mr-2"></i>QR code</h3>
                    </div>
                    <div class="p-5 text-center">
                        <img src="{{ $customer->qr_code_url }}" alt="QR code for {{ $customer->account_number }}" class="mx-auto h-44 w-44 rounded-lg border border-slate-200 bg-white">
                        <p class="mt-3 font-mono text-sm text-blue-800">{{ $customer->meter_number }}</p>
                        <p class="text-xs text-slate-500">{{ $customer->account_number }}</p>
                        @if($activeMeters->first())
                            <a href="{{ route('meters.qr-code.download', $activeMeters->first()) }}" class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                                <i class="fas fa-download mr-2"></i>Download QR
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="bg-slate-700 px-5 py-3">
                        <h3 class="font-semibold text-white"><i class="fas fa-bolt mr-2"></i>Quick actions</h3>
                    </div>
                    <div class="space-y-2 p-4">
                        <a href="#make-payment" class="flex w-full items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700"><i class="fas fa-hand-holding-usd mr-2"></i>Make Payment</a>
                        <a href="#readings" class="flex w-full items-center justify-center rounded-lg bg-violet-600 px-4 py-2 text-white hover:bg-violet-700"><i class="fas fa-tachometer-alt mr-2"></i>Readings ({{ $financialSummary['readings'] }})</a>
                        <a href="#customer-bills" class="flex w-full items-center justify-center rounded-lg bg-orange-500 px-4 py-2 text-white hover:bg-orange-600"><i class="fas fa-file-invoice-dollar mr-2"></i>Bills ({{ $financialSummary['bills'] }})</a>
                        <a href="#customer-payments" class="flex w-full items-center justify-center rounded-lg bg-emerald-500 px-4 py-2 text-white hover:bg-emerald-600"><i class="fas fa-receipt mr-2"></i>Payments ({{ $financialSummary['payments'] }})</a>
                        @if($activeMeters->count() > 0)
                            <a href="{{ route('customers.meters', $customer) }}" class="flex w-full items-center justify-center rounded-lg bg-indigo-500 px-4 py-2 text-white hover:bg-indigo-600"><i class="fas fa-cog mr-2"></i>Manage meters</a>
                        @endif
                        <a href="{{ route('customers.edit', $customer) }}" class="flex w-full items-center justify-center rounded-lg bg-slate-600 px-4 py-2 text-white hover:bg-slate-700"><i class="fas fa-edit mr-2"></i>Edit customer</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('customer_pay_amount');
    const syncAmount = () => {
        if (!amountInput) return;
        const total = [...document.querySelectorAll('.customer-bill-pick:checked')]
            .reduce((sum, box) => sum + Number(box.dataset.balance || 0), 0);
        amountInput.value = total.toFixed(2);
    };
    document.querySelectorAll('.customer-bill-pick').forEach((box) => box.addEventListener('change', syncAmount));

    const months = @json($yearChart);
    const labels = months.map(month => month.label);
    const money = (value) => 'Rs. ' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    new Chart(document.getElementById('customerYearMoneyChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Draft', data: months.map(month => month.draft), backgroundColor: '#F59E0B' },
                { label: 'Billed', data: months.map(month => month.billed), backgroundColor: '#10B981' },
                { label: 'Paid (exact)', data: months.map(month => month.paid ?? (month.collected + (month.overpaid || 0))), backgroundColor: '#3B82F6' },
                { label: 'Overpaid to wallet', data: months.map(month => month.overpaid), backgroundColor: '#8B5CF6' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: {
                legend: { position: 'top' },
                tooltip: { callbacks: { label: (item) => item.dataset.label + ': ' + money(item.parsed.y) } }
            }
        }
    });

    new Chart(document.getElementById('customerYearUsageChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Units',
                    data: months.map(month => month.units),
                    borderColor: '#0891B2',
                    backgroundColor: 'rgba(8, 145, 178, 0.15)',
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'units'
                },
                {
                    label: 'Readings',
                    data: months.map(month => month.readings),
                    backgroundColor: '#6366F1',
                    yAxisID: 'readings'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                units: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'Units' } },
                readings: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Readings' } }
            },
            plugins: { legend: { position: 'top' } }
        }
    });
});
</script>
@endsection
