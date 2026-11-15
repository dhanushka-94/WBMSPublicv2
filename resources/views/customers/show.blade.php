@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Customer Details</h1>
                    <p class="mt-1 text-sm text-gray-600">Complete customer profile and account information</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="#make-payment"
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700">
                        <i class="fas fa-hand-holding-usd mr-2"></i>
                        Make Payment
                    </a>
                    <a href="{{ route('customers.edit', $customer) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Customer
                    </a>
                    @if(auth()->user()->isSuperAdmin())
                        <form method="POST" action="{{ route('customers.destroy', $customer) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Customer
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('customers.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Customers
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-8 bg-white border border-gray-200 rounded-lg p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Connection</h2>
            <p class="text-sm text-gray-600 mb-3">Status: {{ ucfirst($customer->status) }}</p>
            <form method="POST" action="{{ $customer->status === 'disconnected' ? route('customers.reconnect', $customer) : route('customers.disconnect', $customer) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500">Date</label>
                    <input type="date" name="event_date" value="{{ now()->toDateString() }}" class="mt-1 w-full border-gray-300 rounded-md text-sm" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500">Reason</label>
                    <input type="text" name="reason" class="mt-1 w-full border-gray-300 rounded-md text-sm" required>
                </div>
                <button class="px-4 py-2 rounded-md text-sm text-white {{ $customer->status === 'disconnected' ? 'bg-emerald-600' : 'bg-red-600' }}">
                    {{ $customer->status === 'disconnected' ? 'Reconnect' : 'Disconnect' }}
                </button>
            </form>
            @if($customer->connectionEvents->isNotEmpty())
                <ul class="mt-4 text-sm text-gray-700 space-y-1">
                    @foreach($customer->connectionEvents->take(5) as $event)
                        <li>{{ $event->event_date->format('Y-m-d') }} — {{ ucfirst($event->action) }} — {{ $event->reason }} — {{ $event->user?->name ?? 'Office' }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mb-8 bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-emerald-600 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Financial Summary</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 p-6">
                <div class="rounded-lg bg-blue-50 p-4">
                    <p class="text-xs font-medium uppercase text-blue-700">Total billed</p>
                    <p class="mt-1 text-2xl font-bold text-blue-900">Rs. {{ number_format($financialSummary['billed'], 2) }}</p>
                    <p class="text-xs text-blue-700">{{ $financialSummary['bills'] }} bills</p>
                </div>
                <div class="rounded-lg bg-green-50 p-4">
                    <p class="text-xs font-medium uppercase text-green-700">Total paid</p>
                    <p class="mt-1 text-2xl font-bold text-green-900">Rs. {{ number_format($financialSummary['paid'], 2) }}</p>
                    @if($financialSummary['last_payment'])
                        <p class="text-xs text-green-700">Last payment {{ $financialSummary['last_payment']->paidOnDisplay('M d, Y H:i') }} · Rs. {{ number_format($financialSummary['last_payment']->amount, 2) }}</p>
                    @else
                        <p class="text-xs text-green-700">{{ $financialSummary['payments'] }} receipts</p>
                    @endif
                </div>
                <div class="rounded-lg bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase text-amber-700">Outstanding</p>
                    <p class="mt-1 text-2xl font-bold text-amber-900">Rs. {{ number_format($financialSummary['outstanding'], 2) }}</p>
                </div>
                <div class="rounded-lg bg-red-50 p-4">
                    <p class="text-xs font-medium uppercase text-red-700">Overdue</p>
                    <p class="mt-1 text-2xl font-bold text-red-900">Rs. {{ number_format($financialSummary['overdue'], 2) }}</p>
                </div>
                <div class="rounded-lg bg-teal-50 p-4">
                    <p class="text-xs font-medium uppercase text-teal-700">Wallet</p>
                    <p class="mt-1 text-2xl font-bold text-teal-900">Rs. {{ number_format($customer->wallet_balance, 2) }}</p>
                    <a href="#make-payment" class="text-xs font-medium text-teal-700 hover:text-teal-900">Make payment</a>
                </div>
            </div>
        </div>

        <div id="make-payment" class="mb-8 bg-white border border-teal-200 rounded-xl shadow-sm overflow-hidden scroll-mt-24">
            <div class="bg-teal-600 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">Make Payment</h2>
                    <p class="text-teal-100 text-sm">Record a payment for this customer. Date and time are stored on the receipt.</p>
                </div>
                <a href="{{ route('payments.collect', ['customer' => $customer->id]) }}" class="text-sm font-medium text-white/90 hover:text-white underline">Open full collect page</a>
            </div>
            <form method="POST" action="{{ route('payments.collect.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="redirect_to" value="customer">

                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
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
                                        <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}" class="customer-bill-pick rounded border-gray-300 text-teal-600" data-balance="{{ $bill->balance_amount }}" checked>
                                    </td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('bills.show', $bill) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $bill->bill_number }}</a>
                                        <div class="text-xs text-gray-500">{{ ucfirst($bill->status) }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-gray-700">{{ optional($bill->due_date)->format('Y-m-d') }}</td>
                                    <td class="px-3 py-2 text-right font-semibold">Rs. {{ number_format($bill->balance_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">No open bills. An amount entered below is added to the wallet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="customer_pay_amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (Rs.)</label>
                        <input type="number" name="amount" id="customer_pay_amount" step="0.01" min="0" value="{{ old('amount', $openBills->sum('balance_amount')) }}" class="w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label for="customer_pay_method" class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                        <select name="payment_method" id="customer_pay_method" class="w-full rounded-md border-gray-300" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div>
                        <label for="customer_pay_date" class="block text-sm font-medium text-gray-700 mb-1">Date &amp; time</label>
                        <input type="datetime-local" name="payment_date" id="customer_pay_date" value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" required class="w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label for="customer_pay_reference" class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                        <input type="text" name="reference" id="customer_pay_reference" value="{{ old('reference') }}" class="w-full rounded-md border-gray-300">
                    </div>
                </div>

                <div>
                    <label for="customer_pay_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="customer_pay_notes" rows="2" class="w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
                </div>

                @if($customer->wallet_balance > 0)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                        <input type="checkbox" name="use_wallet" value="1" class="rounded border-gray-300 text-teal-600" {{ old('use_wallet') ? 'checked' : '' }}>
                        Use wallet (Rs. {{ number_format($customer->wallet_balance, 2) }} available)
                    </label>
                @endif

                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700">
                    <i class="fas fa-check mr-2"></i>Save payment
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
            <div id="customer-bills" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden scroll-mt-24">
                <div class="bg-orange-500 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Bills</h2>
                    <p class="text-orange-100 text-sm">{{ $financialSummary['bills'] }} total · filter below</p>
                </div>
                <form method="GET" action="{{ route('customers.show', $customer) }}#customer-bills" class="p-4 border-b grid grid-cols-1 md:grid-cols-4 gap-3">
                    @foreach($paymentFilters as $key => $value)
                        @if($value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    @if(request('payments_page'))
                        <input type="hidden" name="payments_page" value="{{ request('payments_page') }}">
                    @endif
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Search</label>
                        <input type="search" name="bill_q" value="{{ $billFilters['bill_q'] }}" placeholder="Bill number" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="bill_status" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">All</option>
                            @foreach(['draft','generated','sent','partial','paid','overdue','cancelled'] as $status)
                                <option value="{{ $status }}" @selected($billFilters['bill_status'] === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">From</label>
                        <input type="date" name="bill_from" value="{{ $billFilters['bill_from'] }}" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">To</label>
                        <input type="date" name="bill_to" value="{{ $billFilters['bill_to'] }}" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Filter</button>
                        <a href="{{ route('customers.show', $customer) }}#customer-bills" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Clear</a>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
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
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('bills.show', $bill) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $bill->bill_number }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">{{ optional($bill->bill_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ $bill->status === 'paid' ? 'bg-green-100 text-green-800' : ($bill->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right">Rs. {{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">Rs. {{ number_format($bill->balance_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No bills match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customerBills->hasPages())
                    <div class="px-4 py-3 border-t">{{ $customerBills->links() }}</div>
                @endif
            </div>

            <div id="customer-payments" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden scroll-mt-24">
                <div class="bg-emerald-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Payments</h2>
                    <p class="text-emerald-100 text-sm">{{ $financialSummary['payments'] }} receipts · date &amp; time on each</p>
                </div>
                <form method="GET" action="{{ route('customers.show', $customer) }}#customer-payments" class="p-4 border-b grid grid-cols-1 md:grid-cols-4 gap-3">
                    @foreach($billFilters as $key => $value)
                        @if($value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    @if(request('bills_page'))
                        <input type="hidden" name="bills_page" value="{{ request('bills_page') }}">
                    @endif
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Search</label>
                        <input type="search" name="payment_q" value="{{ $paymentFilters['payment_q'] }}" placeholder="Receipt, bill, reference" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Method</label>
                        <select name="payment_method" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">All</option>
                            @foreach(['cash','bank_transfer','cheque','card','wallet','mobile_payment'] as $method)
                                <option value="{{ $method }}" @selected($paymentFilters['payment_method'] === $method)>{{ str_replace('_', ' ', ucfirst($method)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">From</label>
                        <input type="date" name="payment_from" value="{{ $paymentFilters['payment_from'] }}" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">To</label>
                        <input type="date" name="payment_to" value="{{ $paymentFilters['payment_to'] }}" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button>
                        <a href="{{ route('customers.show', $customer) }}#customer-payments" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Clear</a>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
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
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $payment->receipt_number }}</td>
                                    <td class="px-4 py-2 text-gray-700 whitespace-nowrap">{{ $payment->paidOnDisplay() }}</td>
                                    <td class="px-4 py-2">
                                        @if($payment->bill)
                                            <a href="{{ route('bills.show', $payment->bill) }}" class="text-blue-700 hover:text-blue-900">{{ $payment->bill->bill_number }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">Rs. {{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="text-xs font-medium text-gray-700 hover:text-gray-900">Receipt</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No payments match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customerPayments->hasPages())
                    <div class="px-4 py-3 border-t">{{ $customerPayments->links() }}</div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-emerald-100">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">
                        <i class="fas fa-chart-bar mr-2"></i>{{ $chartYear }} bills by month
                    </h3>
                    <p class="text-emerald-100 text-sm mt-1">Draft, billed, and collected amounts</p>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="customerYearMoneyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-cyan-100">
                <div class="bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">
                        <i class="fas fa-chart-line mr-2"></i>{{ $chartYear }} water use by month
                    </h3>
                    <p class="text-cyan-100 text-sm mt-1">Units consumed and readings taken</p>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="customerYearUsageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Customer Info -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Customer Profile Card -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                    <div class="bg-blue-600 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($customer->profile_photo)
                                    <img class="h-20 w-20 rounded-full object-cover border-4 border-white shadow-lg cursor-pointer hover:opacity-90 transition-opacity" 
                                         src="{{ $customer->profile_photo_url }}" 
                                         alt="{{ $customer->full_name }}"
                                         data-lightbox
                                         title="View photo">
                                @else
                                    <div class="h-20 w-20 rounded-full bg-white bg-opacity-20 flex items-center justify-center border-4 border-white shadow-lg">
                                        <i class="fas fa-user text-white text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-6">
                                <h2 class="text-2xl font-bold text-white">{{ $customer->full_name }}</h2>
                                <p class="text-blue-100 text-lg">{{ $customer->customerType->name ?? 'N/A' }} Customer</p>
                                <div class="mt-2 flex items-center space-x-4">
                                    @if($customer->status === 'active')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            🟢 Active
                                        </span>
                                    @elseif($customer->status === 'suspended')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            🟡 Suspended
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            🔴 Inactive
                                        </span>
                                    @endif
                                    @if($customer->connection_date)
                                    <span class="text-blue-100 text-sm">
                                        Member since {{ $customer->connection_date->format('M Y') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Information -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-id-card text-blue-500 mr-2"></i>
                            Account Information
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Account Number</label>
                                <p class="mt-1 text-lg font-bold text-blue-600">{{ $customer->account_number }}</p>
                            </div>
                            @if($customer->reference_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Reference Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->reference_number }}</p>
                            </div>
                            @endif
                            @if($customer->meter_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Meter Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->meter_number }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Division</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->division->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Connection Date</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->connection_date?->format('M d, Y') ?? 'Not set' }}</p>
                            </div>
                            @if($customer->deposit_amount > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Deposit Amount</label>
                                <p class="mt-1 text-sm text-gray-900">Rs. {{ number_format($customer->deposit_amount, 2) }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-user-circle mr-2"></i>
                            Personal Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($customer->email)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email Address</label>
                                <p class="mt-1 text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                    {{ $customer->email }}
                                </p>
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Phone Number</label>
                                <p class="mt-1 text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-2"></i>
                                    {{ $customer->phone }}
                                </p>
                            </div>
                            @if($customer->phone_two)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Phone Number (Secondary)</label>
                                <p class="mt-1 text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-2"></i>
                                    {{ $customer->phone_two }}
                                </p>
                            </div>
                            @endif
                            @if($customer->nic)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">NIC Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->nic }}</p>
                            </div>
                            @endif
                            @if($customer->epf_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">EPF Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->epf_number }}</p>
                            </div>
                            @endif
                        </div>
                        
                        @if($customer->address)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-sm text-gray-900 flex items-start">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-2 mt-1"></i>
                                <span>
                                    {{ $customer->address }}
                                    @if($customer->city), {{ $customer->city }}@endif
                                    @if($customer->postal_code) {{ $customer->postal_code }}@endif
                                </span>
                            </p>
                        </div>
                        @endif

                        @if($customer->guarantor)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-500">Guarantor</label>
                            <p class="mt-1 text-sm text-gray-900 flex items-center">
                                <i class="fas fa-user-shield text-gray-400 mr-2"></i>
                                {{ $customer->guarantor->full_name }} ({{ $customer->guarantor->relationship }})
                            </p>
                        </div>
                        @endif

                        @php
                            $displayNotes = trim(preg_replace('/\s*Imported from customer-readings-payments\.xlsx\.\s*Sheet name:.*$/i', '', (string) $customer->notes));
                        @endphp
                        @if($displayNotes !== '')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-500">Notes</label>
                            <p class="mt-1 text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $displayNotes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Water Meters Section -->
                @if($activeMeters->count() > 0)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Water Meters ({{ $activeMeters->count() }})
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($activeMeters as $meter)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-300 transition-colors">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        @if($customer->qr_code_url && $customer->meter_number === $meter->meter_number)
                                        <img src="{{ $customer->qr_code_url }}" alt="QR code for {{ $customer->full_name }}" class="h-20 w-20 rounded border border-gray-200 bg-white">
                                        @endif
                                        <div>
                                        <h4 class="font-medium text-gray-900">{{ $meter->meter_number }}</h4>
                                        <p class="text-sm text-gray-500">
                                            {{ $meter->meter_brand }} {{ $meter->meter_model }} 
                                            ({{ $meter->meter_size }}mm {{ ucfirst($meter->meter_type) }})
                                        </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-purple-600">{{ number_format($meter->current_reading, 2) }}</p>
                                        <p class="text-sm text-gray-500">Current Reading</p>
                                    </div>
                                </div>
                                @if($meter->location_notes)
                                <p class="mt-2 text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                    {{ $meter->location_notes }}
                                </p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>

            <!-- Right Column - Quick Stats & Recent Activity -->
            <div class="space-y-8">

                @if($customer->qr_code_url)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-indigo-500 to-blue-600 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-qrcode mr-2"></i>
                            Customer QR Code
                        </h3>
                    </div>
                    <div class="p-6 text-center">
                        <img src="{{ $customer->qr_code_url }}" alt="QR code for {{ $customer->account_number }}" class="mx-auto h-48 w-48 rounded-lg border border-gray-200 bg-white">
                        <p class="mt-4 font-mono text-sm text-blue-800">{{ $customer->meter_number }}</p>
                        <p class="text-xs text-gray-500">{{ $customer->account_number }}</p>
                        @if($activeMeters->first())
                        <a href="{{ route('meters.qr-code.download', $activeMeters->first()) }}"
                           class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Download QR
                        </a>
                        @endif
                    </div>
                </div>
                @endif
                
                <!-- Quick Stats -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-chart-line mr-2"></i>
                            Account Summary
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-green-800">Outstanding Balance</p>
                                <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($outstandingBalance, 2) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                            </div>
                        </div>
                        
                        @if($lastBill)
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-blue-800">Last Bill Amount</p>
                                <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($lastBill->total_amount, 2) }}</p>
                                <p class="text-xs text-blue-600">{{ $lastBill->bill_date->format('M d, Y') }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        @endif

                        @if($currentReading)
                        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-purple-800">Current Reading</p>
                                <p class="text-xl font-bold text-purple-600">{{ number_format($currentReading->current_reading, 2) }}</p>
                                <p class="text-xs text-purple-600">{{ $currentReading->reading_date->format('M d, Y') }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-tachometer-alt text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-gray-500 to-gray-700 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-bolt mr-2"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="#make-payment"
                           class="w-full flex items-center justify-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                            <i class="fas fa-hand-holding-usd mr-2"></i>
                            Make Payment
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Customer
                        </a>
                        <a href="#customer-bills"
                           class="w-full flex items-center justify-center px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            View Bills ({{ $financialSummary['bills'] }})
                        </a>
                        <a href="#customer-payments"
                           class="w-full flex items-center justify-center px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors">
                            <i class="fas fa-receipt mr-2"></i>
                            View Payments ({{ $financialSummary['payments'] }})
                        </a>
                        @if($activeMeters->count() > 0)
                        <a href="{{ route('customers.meters', $customer) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Manage Meters
                        </a>
                        @endif
                        <button onclick="window.print()" 
                                class="w-full flex items-center justify-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                            <i class="fas fa-print mr-2"></i>
                            Print Details
                        </button>
                    </div>
                </div>

            </div>
        </div>
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
                { label: 'Collected', data: months.map(month => month.collected), backgroundColor: '#3B82F6' }
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