@extends('layouts.app')

@section('title', 'Send Custom SMS')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4">
        <h1 class="text-2xl font-semibold text-gray-900">Send Custom SMS</h1>
        <p class="mt-1 text-sm text-gray-600">{{ number_format($eligibleCount) }} active customers have a mobile number. Placeholders: {customer_name} {account_number} {bill_number} {bill_amount} {payment_link}</p>
        @if(session('success'))
            <div class="mt-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
        @endif

        <form method="GET" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3 bg-white border rounded-lg p-4">
            <input name="search" value="{{ request('search') }}" placeholder="Name, account, or mobile" class="rounded border-gray-300 text-sm">
            <select name="filter_division" class="rounded border-gray-300 text-sm">
                <option value="">All divisions</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}" @selected(request('filter_division') == $division->id)>{{ $division->name }}</option>
                @endforeach
            </select>
            <select name="filter_status" class="rounded border-gray-300 text-sm">
                <option value="">Any status</option>
                @foreach(['active', 'suspended', 'inactive', 'disconnected'] as $status)
                    <option value="{{ $status }}" @selected(request('filter_status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="rounded bg-gray-800 text-white text-sm">Search</button>
        </form>

        <form method="POST" action="{{ route('sms.bulk.send') }}" id="customSmsForm" class="mt-4 bg-white border rounded-lg p-4 space-y-4">
            @csrf
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="send_all" value="1" id="sendAll" @checked(old('send_all')) class="rounded border-gray-300">
                Entire active customer base with a mobile number
            </label>
            <div>
                <p class="text-sm font-medium text-gray-700">Divisions</p>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                    @foreach($divisions as $division)
                        <label class="text-sm">
                            <input type="checkbox" name="division_ids[]" value="{{ $division->id }}" class="division-box rounded border-gray-300" @checked(in_array($division->id, old('division_ids', [])))>
                            {{ $division->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Customers from the search ({{ $customers->count() }})</p>
                <div class="mt-2 max-h-64 overflow-auto border rounded">
                    @forelse($customers as $customer)
                        <label class="flex items-center gap-2 px-3 py-2 border-b text-sm">
                            <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" class="customer-box rounded border-gray-300" @checked(in_array($customer->id, old('customer_ids', [])))>
                            <span>{{ $customer->full_name }} · {{ $customer->account_number }} · {{ $customer->phone ?: 'No mobile' }} · {{ $customer->division->name ?? 'No division' }} · {{ ucfirst($customer->status) }}</span>
                        </label>
                    @empty
                        <p class="px-3 py-4 text-sm text-gray-500">Search to pick individual customers.</p>
                    @endforelse
                </div>
            </div>
            <label class="block text-sm">Message
                <textarea name="message" id="smsMessage" rows="4" class="mt-1 w-full rounded border-gray-300">{{ old('message', $starter) }}</textarea>
            </label>
            <p id="smsCount" class="text-sm text-gray-600"></p>
            @if(session('preview'))
                <div class="rounded bg-amber-50 border border-amber-200 p-3 text-sm">
                    <p class="font-medium">Preview for the first selected customer</p>
                    <p class="mt-1">{{ session('preview') }}</p>
                    @if(session('preview_meta'))
                        <p class="mt-2 text-gray-700">{{ session('preview_meta.recipients') }} recipients · {{ session('preview_meta.segments') }} segment(s) each
                            @if($costPerSegment > 0)
                                · about Rs. {{ number_format(session('preview_meta.cost'), 2) }}
                            @else
                                · set a segment cost in SMS Settings to estimate the charge
                            @endif
                        </p>
                    @endif
                </div>
            @endif
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="confirm_send" value="1" class="rounded border-gray-300">
                I confirm this message should be sent
            </label>
            <div class="flex gap-2">
                <button name="preview" value="1" class="rounded border border-gray-300 px-4 py-2 text-sm">Preview</button>
                <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white">Send</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const message = document.getElementById('smsMessage');
    const count = document.getElementById('smsCount');
    const cost = {{ json_encode($costPerSegment) }};
    function refresh() {
        const text = message.value || '';
        const gsm = /^[\n\r\x20-\x7E]*$/.test(text);
        const length = [...text].length;
        const single = gsm ? 160 : 70;
        const multi = gsm ? 153 : 67;
        const segments = length === 0 ? 0 : (length <= single ? 1 : Math.ceil(length / multi));
        const selected = document.querySelectorAll('.customer-box:checked').length + document.querySelectorAll('.division-box:checked').length;
        const all = document.getElementById('sendAll').checked;
        count.textContent = segments + ' SMS segment' + (segments === 1 ? '' : 's') + ' · ' + length + ' characters'
            + (cost > 0 ? ' · about Rs. ' + (segments * cost).toFixed(2) + ' each' : '')
            + (all ? ' · entire active base will be included' : ' · ' + selected + ' division or customer box' + (selected === 1 ? '' : 'es') + ' ticked');
    }
    message.addEventListener('input', refresh);
    document.getElementById('customSmsForm').addEventListener('change', refresh);
    refresh();
});
</script>
@endsection
