@extends('layouts.app')

@section('title', 'Take Payment')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-lg shadow-lg p-6 mb-6">
        <h1 class="text-3xl font-bold text-white">Take Payment</h1>
        <p class="text-emerald-100 mt-1">Filter the customers, search a name, account, or bill number, then record the payment. Any extra amount is kept in the customer wallet.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('payments.collect') }}" id="filter-form" class="bg-white rounded-lg shadow p-4 mb-6">
        @if($customer)
            <input type="hidden" name="customer" value="{{ $customer->id }}">
        @endif
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2 relative">
                <label for="payment-search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="search" name="q" id="payment-search" value="{{ $query }}" autocomplete="off" placeholder="Name, account, phone, or bill number" class="w-full rounded-md border-gray-300">
                <ul id="payment-suggest" class="absolute z-20 mt-1 hidden w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg"></ul>
            </div>
            <div>
                <label for="division_id" class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                <select name="division_id" id="division_id" class="w-full rounded-md border-gray-300">
                    <option value="">All divisions</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @selected((string) $filters['division_id'] === (string) $division->id)>{{ $division->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="customer_type_id" class="block text-sm font-medium text-gray-700 mb-1">Customer type</label>
                <select name="customer_type_id" id="customer_type_id" class="w-full rounded-md border-gray-300">
                    <option value="">All types</option>
                    @foreach($customerTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) $filters['customer_type_id'] === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300">
                    <option value="">All statuses</option>
                    @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'disconnected' => 'Disconnected'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3 flex items-end gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 font-semibold text-white hover:bg-emerald-700">Apply filters</button>
                <a href="{{ route('payments.collect') }}" class="rounded-lg bg-gray-100 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-200">Clear</a>
                <p id="match-count" class="text-sm text-gray-600">Showing {{ $customers->firstItem() ?? 0 }}–{{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }}</p>
            </div>
        </div>
    </form>

    @if($customer)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        <a href="{{ route('customers.show', $customer) }}" class="text-blue-700 hover:text-blue-900">{{ $customer->full_name }}</a>
                    </h2>
                    <p class="text-sm text-gray-500">{{ $customer->account_number }}</p>
                </div>
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-right">
                    <p class="text-xs uppercase text-emerald-700">Wallet</p>
                    <p class="text-2xl font-bold text-emerald-900">Rs. {{ number_format($customer->wallet_balance, 2) }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('payments.collect.store') }}" id="collect-form">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="q" value="{{ $query }}">
                <input type="hidden" name="division_id" value="{{ $filters['division_id'] }}">
                <input type="hidden" name="customer_type_id" value="{{ $filters['customer_type_id'] }}">
                <input type="hidden" name="status" value="{{ $filters['status'] }}">

                <div class="overflow-x-auto mb-4">
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
                            @forelse($bills as $bill)
                                <tr>
                                    <td class="px-3 py-3">
                                        <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}" class="bill-pick rounded border-gray-300 text-emerald-600" data-balance="{{ $bill->balance_amount }}" checked>
                                    </td>
                                    <td class="px-3 py-3">
                                        <a href="{{ route('bills.show', $bill) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $bill->bill_number }}</a>
                                    </td>
                                    <td class="px-3 py-3 text-gray-700">{{ optional($bill->due_date)->format('d M Y') }}</td>
                                    <td class="px-3 py-3 text-right font-medium text-gray-900">Rs. {{ number_format($bill->balance_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">No open bills. An amount entered below is added to the wallet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $bills->links() }}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount received (Rs.)</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount', $bills->sum('balance_amount')) }}" class="w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                        <select name="payment_method" id="payment_method" class="w-full rounded-md border-gray-300">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required class="w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                        <input type="text" name="reference" id="reference" value="{{ old('reference') }}" class="w-full rounded-md border-gray-300">
                    </div>
                </div>

                @if($customer->wallet_balance > 0)
                    <label class="mt-4 inline-flex items-center gap-2 text-sm text-gray-800">
                        <input type="checkbox" name="use_wallet" value="1" id="use_wallet" class="rounded border-gray-300 text-emerald-600" {{ old('use_wallet') ? 'checked' : '' }}>
                        Use wallet (Rs. {{ number_format($customer->wallet_balance, 2) }} available)
                    </label>
                @endif

                <p id="payment-preview" class="mt-3 text-sm text-gray-700"></p>

                <button type="submit" class="mt-4 rounded-lg bg-emerald-600 px-5 py-2 font-semibold text-white hover:bg-emerald-700">Save payment</button>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b text-sm font-medium text-gray-700">Customers</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-3 py-2">Account</th>
                        <th class="px-3 py-2">Customer</th>
                        <th class="px-3 py-2">Division</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Phone</th>
                        <th class="px-3 py-2 text-right">Open balance</th>
                        <th class="px-3 py-2 text-right">Wallet</th>
                    </tr>
                </thead>
                <tbody id="customer-rows" class="divide-y">
                    @forelse($customers as $row)
                        <tr class="customer-row cursor-pointer hover:bg-emerald-50 {{ $customer && $customer->id === $row->id ? 'bg-emerald-50' : '' }}"
                            data-id="{{ $row->id }}">
                            <td class="px-3 py-3 text-gray-700">{{ $row->account_number }}</td>
                            <td class="px-3 py-3 font-medium text-gray-900">{{ $row->full_name }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $row->division?->name ?: '—' }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $row->customerType?->name ?: '—' }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $row->phone ?: '—' }}</td>
                            <td class="px-3 py-3 text-right font-medium text-gray-900">Rs. {{ number_format((float) $row->open_balance, 2) }}</td>
                            <td class="px-3 py-3 text-right text-emerald-700">Rs. {{ number_format((float) $row->wallet_balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">No customer matches that search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $customers->links() }}</div>
    </div>
</div>
<script>
    const searchInput = document.getElementById('payment-search');
    const suggest = document.getElementById('payment-suggest');
    let activeSuggestion = -1;
    let suggestTimer;

    function customerUrl(id) {
        const params = new URLSearchParams(new FormData(document.getElementById('filter-form')));
        [...params.keys()].forEach((key) => {
            if (!params.get(key)) params.delete(key);
        });
        params.delete('suggest');
        params.delete('page');
        params.set('customer', id);
        params.set('q', searchInput.value.trim());
        return '{{ route('payments.collect') }}?' + params.toString();
    }

    function showSuggestions(customers) {
        suggest.innerHTML = '';
        activeSuggestion = -1;
        customers.forEach((customer) => {
            const choice = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-emerald-50';
            button.dataset.id = customer.id;
            button.innerHTML = '<span class="font-medium text-gray-900"></span><span class="block text-gray-500"></span>';
            button.querySelector('span').textContent = customer.name;
            button.querySelectorAll('span')[1].textContent = customer.account;
            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
                window.location = customerUrl(customer.id);
            });
            choice.appendChild(button);
            suggest.appendChild(choice);
        });
        suggest.classList.toggle('hidden', customers.length === 0);
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(suggestTimer);
        const query = searchInput.value.trim();
        if (query.length < 1) {
            suggest.classList.add('hidden');
            return;
        }
        suggestTimer = setTimeout(() => {
            const params = new URLSearchParams(new FormData(document.getElementById('filter-form')));
            params.set('suggest', '1');
            params.set('q', query);
            params.delete('page');
            params.delete('customer');
            fetch('{{ route('payments.collect') }}?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then((response) => response.json()).then((data) => showSuggestions(data.customers || [])).catch(() => suggest.classList.add('hidden'));
        }, 200);
    });

    searchInput.addEventListener('keydown', (event) => {
        const buttons = [...suggest.querySelectorAll('button')];
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (!buttons.length) return;
            activeSuggestion += event.key === 'ArrowDown' ? 1 : -1;
            if (activeSuggestion < 0) activeSuggestion = buttons.length - 1;
            if (activeSuggestion >= buttons.length) activeSuggestion = 0;
            buttons.forEach((button, index) => button.classList.toggle('bg-emerald-50', index === activeSuggestion));
        }
        if (event.key === 'Enter' && activeSuggestion >= 0 && buttons[activeSuggestion]) {
            event.preventDefault();
            window.location = customerUrl(buttons[activeSuggestion].dataset.id);
        }
    });
    document.addEventListener('click', (event) => {
        if (!suggest.contains(event.target) && event.target !== searchInput) {
            suggest.classList.add('hidden');
        }
    });
    document.querySelectorAll('.customer-row').forEach((row) => {
        row.addEventListener('click', () => {
            window.location = customerUrl(row.dataset.id);
        });
    });
    document.getElementById('filter-form').addEventListener('submit', () => {
        document.querySelectorAll('#filter-form input, #filter-form select').forEach((field) => {
            if (!field.value) field.disabled = true;
        });
    });
    ['division_id', 'customer_type_id', 'status'].forEach((id) => {
        document.getElementById(id).addEventListener('change', () => document.getElementById('filter-form').requestSubmit());
    });

    const wallet = {{ $customer ? (float) $customer->wallet_balance : 0 }};
    const amountInput = document.getElementById('amount');
    const walletBox = document.getElementById('use_wallet');
    const preview = document.getElementById('payment-preview');

    function refreshPreview() {
        if (!preview || !amountInput) return;
        const selected = [...document.querySelectorAll('.bill-pick:checked')].reduce((sum, box) => sum + Number(box.dataset.balance || 0), 0);
        const cash = Number(amountInput.value || 0);
        const fromWallet = walletBox && walletBox.checked ? Math.min(wallet, selected) : 0;
        const applied = Math.min(selected, cash + fromWallet);
        const added = Math.max(0, cash - Math.max(0, selected - fromWallet));
        preview.textContent = 'Rs. ' + applied.toFixed(2) + ' will be applied to the selected bills. Rs. ' + added.toFixed(2) + ' will be added to the wallet.';
    }

    document.querySelectorAll('.bill-pick, #amount, #use_wallet').forEach((el) => el.addEventListener('input', refreshPreview));
    document.querySelectorAll('.bill-pick, #use_wallet').forEach((el) => el.addEventListener('change', refreshPreview));
    refreshPreview();
</script>
@endsection
