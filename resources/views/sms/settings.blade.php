@extends('layouts.app')

@section('title', 'SMS Settings')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-xl mx-auto px-4">
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
            <label class="block text-sm">Estimated cost per segment (LKR)
                <input type="number" step="0.01" min="0" name="cost_per_segment" value="{{ old('cost_per_segment', $cost) }}" class="mt-1 w-full rounded border-gray-300">
            </label>
            <label class="text-sm">
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $enabled)) class="rounded border-gray-300">
                Sending is enabled
            </label>
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white">Save settings</button>
        </form>
    </div>
</div>
@endsection
