@extends('layouts.app')

@section('title', 'SMS Settings')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4">
        <h1 class="text-2xl font-semibold text-gray-900">SMS Settings</h1>
        <p class="mt-1 text-sm text-gray-600">Notify.lk credentials stay in the database. Leave the API key blank to keep the saved key.</p>
        @if(session('success'))
            <div class="mt-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('sms.settings.update') }}" class="mt-6 bg-white rounded-lg border p-4 space-y-4">
            @csrf
            @method('PUT')
            <label class="block text-sm">User ID
                <input name="user_id" value="{{ old('user_id', $userId) }}" class="mt-1 w-full rounded border-gray-300">
            </label>
            <label class="block text-sm">API key
                <input type="password" name="api_key" class="mt-1 w-full rounded border-gray-300" placeholder="{{ $hasApiKey ? 'Saved key is hidden' : 'Enter the Notify.lk API key' }}">
            </label>
            <label class="block text-sm">Sender ID
                <input name="sender_id" value="{{ old('sender_id', $senderId) }}" class="mt-1 w-full rounded border-gray-300">
            </label>
            <div class="text-sm">
                <label class="block text-gray-700" for="cost_per_segment">Cost per SMS segment (LKR)</label>
                <div class="mt-1 flex items-center gap-2">
                    <span class="text-gray-500">Rs.</span>
                    <input type="number" id="cost_per_segment" name="cost_per_segment" step="0.01" min="0" max="1000" required
                           value="{{ old('cost_per_segment', number_format($cost, 2, '.', '')) }}"
                           class="w-32 rounded border-gray-300 @error('cost_per_segment') border-red-400 @enderror">
                </div>
                @error('cost_per_segment')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <input name="rate_note" value="{{ old('rate_note') }}" maxlength="255" placeholder="Reason for a rate change (optional)"
                       class="mt-2 w-full rounded border-gray-300 text-sm">
                <p class="mt-1 text-xs text-gray-500">Only the Super Admin can change this. Each change is saved below with its date and time. New messages use the new rate; messages already sent keep the rate they were sent at.</p>
            </div>
            <label class="text-sm">
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $enabled)) class="rounded border-gray-300">
                Sending is enabled
            </label>
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white">Save settings</button>
        </form>

        <div class="mt-6 rounded-lg border bg-white">
            <div class="border-b px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900"><i class="fas fa-history mr-2 text-gray-500"></i>SMS cost history</h2>
                <p class="text-xs text-gray-500">Every change to the cost per segment, newest first.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">Date &amp; time</th>
                            <th class="px-4 py-2 text-right">Was</th>
                            <th class="px-4 py-2 text-right">New rate</th>
                            <th class="px-4 py-2 text-left">Changed by</th>
                            <th class="px-4 py-2 text-left">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($rateHistory as $row)
                            <tr class="align-top">
                                <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $row->created_at->format('d M Y, h:i A') }}</td>
                                <td class="whitespace-nowrap px-4 py-2 text-right text-gray-500">{{ $row->previous_rate !== null ? 'Rs. '.number_format($row->previous_rate, 2) : '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-2 text-right font-semibold text-gray-900">Rs. {{ number_format($row->rate, 2) }}@if($loop->first) <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Current</span>@endif</td>
                                <td class="px-4 py-2 text-gray-700">{{ $row->changed_by_name ?: 'System' }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $row->note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No changes recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rateHistoryCount > $rateHistory->count())
                <div class="border-t px-4 py-2 text-xs text-gray-500">Showing the latest {{ $rateHistory->count() }} of {{ $rateHistoryCount }} changes.</div>
            @endif
        </div>
    </div>
</div>
@endsection
