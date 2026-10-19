@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-semibold text-gray-900">Bulk SMS</h1>
    <p class="mt-2 text-sm text-gray-600">One message for interruptions, maintenance, emergencies, or announcements. Sending uses the Notify.lk settings already saved on this server.</p>
    @if(session('success'))
        <div class="mt-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('sms.bulk.send') }}" class="mt-6 bg-white border border-gray-200 rounded-lg p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Send to</label>
            <select name="audience" class="mt-1 w-full border-gray-300 rounded-md">
                <option value="all">All active customers</option>
                <option value="defaulters">Customers with an outstanding balance</option>
                <option value="division">One division</option>
                <option value="disconnected">Disconnected customers</option>
                <option value="selected">Selected customer numbers</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Division</label>
            <select name="division_id" class="mt-1 w-full border-gray-300 rounded-md">
                <option value="">Select a division</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Customer numbers</label>
            <input type="text" name="customer_ids" placeholder="12, 45, 88" class="mt-1 w-full border-gray-300 rounded-md">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Message</label>
            <textarea name="message" rows="5" required class="mt-1 w-full border-gray-300 rounded-md" placeholder="Water supply will be interrupted tomorrow from 8.00 a.m. to 12.00 noon for maintenance."></textarea>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-md">Send</button>
    </form>
</div>
@endsection
