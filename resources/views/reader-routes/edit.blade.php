@extends('layouts.app')

@section('title', 'Assign Route')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $user->name }}</h1>
                <p class="text-blue-100 mt-1">{{ $user->email }} · {{ $routeCount }} active customers on today's route</p>
            </div>
            <a href="{{ route('reader-routes.index') }}" class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30">
                Back to routes
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('reader-routes.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Divisions</h2>
            <p class="text-sm text-gray-500 mb-4">Every active customer in a ticked division is included.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($divisions as $division)
                    <label class="flex items-start gap-3 border border-gray-200 rounded-lg p-3 hover:border-blue-300">
                        <input type="checkbox" name="division_ids[]" value="{{ $division->id }}"
                               class="mt-1 rounded border-gray-300 text-blue-600"
                               {{ in_array($division->id, $selectedDivisionIds) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-medium text-gray-900">{{ $division->name }}</span>
                            <span class="block text-sm text-gray-500">{{ $division->active_customers_count }} active customers</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Individual customers</h2>
            <p class="text-sm text-gray-500 mb-4">Add a customer who should be on this route even when their division is not ticked.</p>

            <input id="customer-search" type="text" placeholder="Search name, account, meter, or phone"
                   class="w-full rounded-md border-gray-300 mb-3">
            <div id="customer-results" class="mb-4 border border-gray-200 rounded-lg hidden"></div>

            <ul id="selected-customers" class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                @foreach($selectedCustomers as $customer)
                    <li class="flex items-center justify-between px-4 py-3" data-id="{{ $customer->id }}">
                        <div>
                            <p class="font-medium text-gray-900">{{ $customer->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $customer->account_number }} · {{ $customer->meter_number }} · {{ $customer->division->name ?? 'No division' }}</p>
                        </div>
                        <button type="button" class="text-red-600 text-sm" onclick="removeCustomer(this)">Remove</button>
                        <input type="hidden" name="customer_ids[]" value="{{ $customer->id }}">
                    </li>
                @endforeach
            </ul>
            <p id="selected-empty" class="text-sm text-gray-400 mt-3 {{ $selectedCustomers->isEmpty() ? '' : 'hidden' }}">No individual customers added.</p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-semibold">
                Save route
            </button>
        </div>
    </form>
</div>

<script>
const searchUrl = @json(route('reader-routes.customers.search'));
const searchInput = document.getElementById('customer-search');
const results = document.getElementById('customer-results');
const selected = document.getElementById('selected-customers');
const emptyNote = document.getElementById('selected-empty');
let timer = null;

searchInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(runSearch, 250);
});

function selectedIds() {
    return Array.from(selected.querySelectorAll('input[name="customer_ids[]"]')).map((input) => input.value);
}

function refreshEmpty() {
    emptyNote.classList.toggle('hidden', selected.children.length > 0);
}

async function runSearch() {
    const term = searchInput.value.trim();
    if (term.length < 2) {
        results.classList.add('hidden');
        results.innerHTML = '';
        return;
    }

    const response = await fetch(searchUrl + '?q=' + encodeURIComponent(term), {
        headers: { 'Accept': 'application/json' }
    });
    const data = await response.json();
    const taken = selectedIds();
    const rows = (data.customers || []).filter((customer) => !taken.includes(String(customer.id)));

    results.replaceChildren();
    if (rows.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'px-4 py-3 text-sm text-gray-500';
        empty.textContent = 'No matching customers.';
        results.appendChild(empty);
    } else {
        rows.forEach((customer) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left px-4 py-3 hover:bg-blue-50 border-b border-gray-100';
            const name = document.createElement('span');
            name.className = 'block font-medium text-gray-900';
            name.textContent = customer.name;
            const meta = document.createElement('span');
            meta.className = 'block text-sm text-gray-500';
            meta.textContent = [customer.account_number, customer.meter_number || 'No meter', customer.division || 'No division'].join(' · ');
            button.append(name, meta);
            button.addEventListener('click', () => {
                addCustomer(customer);
                button.remove();
                if (!results.querySelector('button')) results.classList.add('hidden');
            });
            results.appendChild(button);
        });
    }
    results.classList.remove('hidden');
}

function addCustomer(customer) {
    if (selectedIds().includes(String(customer.id))) return;
    const item = document.createElement('li');
    item.className = 'flex items-center justify-between px-4 py-3';
    item.dataset.id = customer.id;
    item.innerHTML = `
        <div>
            <p class="font-medium text-gray-900"></p>
            <p class="text-sm text-gray-500"></p>
        </div>
        <button type="button" class="text-red-600 text-sm" onclick="removeCustomer(this)">Remove</button>
        <input type="hidden" name="customer_ids[]" value="${customer.id}">
    `;
    item.querySelector('p.font-medium').textContent = customer.name;
    item.querySelector('p.text-sm').textContent = [customer.account_number, customer.meter_number || 'No meter', customer.division || 'No division'].join(' · ');
    selected.appendChild(item);
    refreshEmpty();
}

function removeCustomer(button) {
    button.closest('li').remove();
    refreshEmpty();
}
</script>
@endsection
