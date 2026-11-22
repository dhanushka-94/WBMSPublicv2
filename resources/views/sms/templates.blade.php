@extends('layouts.app')

@section('title', 'SMS Templates')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4">
        <h1 class="text-2xl font-semibold text-gray-900">SMS Templates</h1>
        <p class="mt-1 text-sm text-gray-600">Edit the message, turn it on or off, and check the sample preview.</p>
        @if(session('success'))
            <div class="mt-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        <div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-600">
            @foreach($placeholders as $placeholder)
                <span class="rounded bg-white px-2 py-1 border">{{ $placeholder }}</span>
            @endforeach
        </div>
        <div class="mt-6 space-y-4">
            @foreach($templates as $template)
                <form method="POST" action="{{ route('sms.templates.update', $template) }}" class="bg-white rounded-lg border p-4">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-gray-900">{{ $template->name }}</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Key: {{ $template->key }}</p>
                        </div>
                        <label class="text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" @checked($template->is_active) class="rounded border-gray-300">
                            Active
                        </label>
                    </div>
                    <textarea name="body" rows="12" maxlength="800" class="mt-3 w-full rounded border-gray-300 text-sm font-mono whitespace-pre-wrap">{{ old('body', $template->body) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Max 800 characters. Use blank lines for spacing. English only. Payment confirmations include date &amp; time, receipt number, and receipt link.</p>
                    <div class="mt-3 rounded border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Customer will receive</p>
                        <pre class="mt-2 whitespace-pre-wrap break-words text-sm text-gray-800 font-sans">{{ $template->preview }}</pre>
                    </div>
                    <button class="mt-3 rounded bg-blue-600 px-4 py-2 text-sm text-white">Save</button>
                </form>
            @endforeach
        </div>
    </div>
</div>
@endsection
