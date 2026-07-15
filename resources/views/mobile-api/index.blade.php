@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-slate-800 to-cyan-800 shadow-lg">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-cyan-200 text-sm font-semibold uppercase tracking-wide">Admin only</p>
                    <h1 class="text-3xl font-bold text-white mt-1">
                        <i class="fas fa-mobile-alt mr-3"></i>Mobile App API Reference
                    </h1>
                    <p class="text-slate-200 mt-2">Complete endpoints for building the meter reader Android / iOS app</p>
                </div>
                <div class="bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white text-sm">
                    <div class="opacity-80">Base URL</div>
                    <code class="font-mono text-base break-all">{{ $baseUrl }}</code>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        {{-- Auth quick start --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3">How to authenticate</h2>
            <ol class="list-decimal list-inside text-gray-700 space-y-2 mb-4">
                <li>Call <code class="bg-gray-100 px-1 rounded">POST {{ $baseUrl }}/login</code> with email, password, device_name.</li>
                <li>Save <code class="bg-gray-100 px-1 rounded">data.token</code> from the response.</li>
                <li>Send header on every protected request: <code class="bg-gray-100 px-1 rounded">Authorization: Bearer {token}</code></li>
                <li>Also send <code class="bg-gray-100 px-1 rounded">Accept: application/json</code></li>
            </ol>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-2">Login body example</p>
                    <pre class="bg-slate-900 text-green-300 text-xs p-4 rounded-lg overflow-x-auto">{{ json_encode($loginExample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-2">Submit reading body example</p>
                    <pre class="bg-slate-900 text-cyan-200 text-xs p-4 rounded-lg overflow-x-auto">{{ json_encode($submitExample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
            <p class="mt-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Test meter reader: <strong>reader1@aquabill.olexto.com</strong> / <strong>password</strong>
                (roles allowed for login: admin, meter_reader, supervisor)
            </p>
        </div>

        {{-- Suggested mobile flow --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3">Suggested mobile flow</h2>
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800">1. Login</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800">2. Route today / Search</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800">3. Customer detail + history</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800">4. Submit reading</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800">5. Optional payment</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800">6. Bulk sync when offline</span>
            </div>
        </div>

        {{-- Endpoint groups --}}
        @foreach($grouped as $group => $items)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-900">{{ $group }}</h2>
                    <p class="text-sm text-gray-500">{{ $items->count() }} endpoints</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($items as $endpoint)
                        <div class="p-6">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                @php
                                    $methodColor = match($endpoint['method']) {
                                        'GET' => 'bg-emerald-100 text-emerald-800',
                                        'POST' => 'bg-blue-100 text-blue-800',
                                        'PUT' => 'bg-amber-100 text-amber-800',
                                        'DELETE' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $methodColor }}">{{ $endpoint['method'] }}</span>
                                @if($endpoint['auth'])
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-violet-100 text-violet-800">Bearer token</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700">Public</span>
                                @endif
                            </div>
                            <code class="block font-mono text-sm text-slate-800 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 break-all">
                                {{ $baseUrl }}{{ $endpoint['path'] }}
                            </code>
                            <p class="mt-2 text-gray-800 font-medium">{{ $endpoint['summary'] }}</p>

                            @if(!empty($endpoint['params']))
                                <div class="mt-3">
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Query / path params</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        @foreach($endpoint['params'] as $key => $rule)
                                            <li><code class="bg-gray-100 px-1 rounded">{{ $key }}</code> — {{ $rule }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($endpoint['body']))
                                <div class="mt-3">
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Body fields</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        @foreach($endpoint['body'] as $key => $rule)
                                            <li><code class="bg-gray-100 px-1 rounded">{{ $key }}</code> — {{ $rule }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($endpoint['notes']))
                                <p class="mt-3 text-sm text-gray-600 italic">{{ $endpoint['notes'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-sm text-gray-600">
            <p class="font-semibold text-gray-800 mb-2">Response shape</p>
            <p>Most endpoints return JSON like:</p>
            <pre class="bg-slate-900 text-slate-100 text-xs p-4 rounded-lg overflow-x-auto mt-2">{
  "success": true,
  "message": "...",
  "data": { }
}</pre>
            <p class="mt-3">Errors use HTTP 4xx/5xx with <code class="bg-gray-100 px-1 rounded">success: false</code> and optional <code class="bg-gray-100 px-1 rounded">errors</code> validation object.</p>
        </div>
    </div>
</div>
@endsection
