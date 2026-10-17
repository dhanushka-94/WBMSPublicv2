@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-slate-800 to-cyan-800 shadow-lg">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-cyan-200 text-sm font-semibold uppercase tracking-wide">Admin only</p>
                    <h1 class="text-3xl font-bold text-white mt-1">
                        <i class="fas fa-mobile-alt mr-3"></i>Meter Reader Mobile API Guide
                    </h1>
                    <p class="text-slate-200 mt-2">Complete guide for building the Android / iOS meter reader app against AquaBill <code class="text-cyan-100">/api/v1</code></p>
                </div>
                <div class="bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white text-sm">
                    <div class="opacity-80">Base URL</div>
                    <code class="font-mono text-base break-all">{{ $baseUrl }}</code>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3">Before the first request</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>Send <code class="bg-gray-100 px-1 rounded">Accept: application/json</code> on every call.</li>
                <li>Send <code class="bg-gray-100 px-1 rounded">Content-Type: application/json</code> unless the call includes a photo. Photo calls use <code class="bg-gray-100 px-1 rounded">multipart/form-data</code>.</li>
                <li>After login, send <code class="bg-gray-100 px-1 rounded">Authorization: Bearer {token}</code> on every protected call.</li>
                <li>Roles that can log in: <strong>admin</strong>, <strong>meter_reader</strong>, <strong>supervisor</strong>. Manager and staff receive HTTP 403.</li>
                <li>Currency is LKR. Dates are <code class="bg-gray-100 px-1 rounded">YYYY-MM-DD</code>. Server timezone is Asia/Colombo.</li>
            </ul>
            <p class="mt-4 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Test meter reader: <strong>reader1@aquabill.olexto.com</strong> / <strong>password</strong>.
                Readers 2 to 5 use the same password. Admin web login is <strong>admin@aquabill.olexto.com</strong> / <strong>password</strong>.
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3">App flow</h2>
            <ol class="list-decimal list-inside text-gray-700 space-y-2">
                <li><code class="bg-gray-100 px-1 rounded">POST /login</code> and store <code class="bg-gray-100 px-1 rounded">data.token</code>.</li>
                <li><code class="bg-gray-100 px-1 rounded">GET /meter-reading/rates</code> and store the tariff slabs for offline receipts.</li>
                <li><code class="bg-gray-100 px-1 rounded">GET /meter-reading/route/today</code> for the day’s customer list.</li>
                <li>Open a customer with <code class="bg-gray-100 px-1 rounded">GET /meter-reading/customers/{id}</code>, or find one with search or QR scan.</li>
                <li>Price the new reading with the stored slabs, or call <code class="bg-gray-100 px-1 rounded">POST /meter-reading/quote</code> while online. The receipt shows previous reading, new reading, consumption, water charges, other charges, and the LKR total.</li>
                <li><code class="bg-gray-100 px-1 rounded">POST /meter-reading/submit</code> for one reading while online. <code class="bg-gray-100 px-1 rounded">receipt_data.charges</code> repeats those amounts.</li>
                <li>If the phone was offline, keep the readings on the device and upload them with <code class="bg-gray-100 px-1 rounded">POST /meter-reading/bulk-sync</code>.</li>
                <li>Optional: show unpaid bills and record a cash payment.</li>
                <li><code class="bg-gray-100 px-1 rounded">POST /logout</code> when the reader finishes.</li>
            </ol>
            <p class="mt-3 text-sm text-gray-600"><code class="bg-gray-100 px-1 rounded">GET /sync/pending</code> and <code class="bg-gray-100 px-1 rounded">POST /sync/force</code> do not upload anything. The offline upload is bulk-sync.</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3">Rules the app must follow</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>Do not submit a reading below <code class="bg-gray-100 px-1 rounded">meter.current_reading</code>. The server’s cumulative check does not run, so a lower number can be saved.</li>
                <li>One reading per meter per calendar date. A second submit for the same meter and <code class="bg-gray-100 px-1 rounded">reading_date</code> fails.</li>
                <li>QR sticker text is the meter number, for example <code class="bg-gray-100 px-1 rounded">WM-00118</code>. The field app only calls scan. Generate, download, and batch-generate are for printing stickers.</li>
                <li>Customer search accepts <code class="bg-gray-100 px-1 rounded">q</code> or <code class="bg-gray-100 px-1 rounded">search</code>. Payment search accepts only <code class="bg-gray-100 px-1 rounded">search</code>.</li>
                <li>Payment amount minimum is 0.01, and it cannot be higher than the bill balance. Amount 0 saves nothing.</li>
                <li>A bulk-sync response of HTTP 200 can still contain failed rows. Keep those rows on the phone.</li>
                <li>Areas, routes, and the sync pending/force calls are placeholders. Build the route list from <code class="bg-gray-100 px-1 rounded">route/today</code>.</li>
                <li>Dashboard <code class="bg-gray-100 px-1 rounded">accuracy_rate</code> is a fixed 98.5, not a live score.</li>
                <li>Price a reading from the customer’s <code class="bg-gray-100 px-1 rounded">customer_type</code> slabs. Consumption of 0 uses only the 0–0 slab’s fixed charge. Consumption above 0 skips that slab. A null <code class="bg-gray-100 px-1 rounded">tier_to</code> takes the remaining units. Other charges are the fixed charge, and the total is water charges plus other charges.</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3">How to authenticate</h2>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-2">Login body</p>
                    <pre class="bg-slate-900 text-green-300 text-xs p-4 rounded-lg overflow-x-auto">{{ json_encode($loginExample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-2">Submit reading body</p>
                    <pre class="bg-slate-900 text-cyan-200 text-xs p-4 rounded-lg overflow-x-auto">{{ json_encode($submitExample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600 mb-2">Offline bulk-sync body</p>
                    <pre class="bg-slate-900 text-amber-200 text-xs p-4 rounded-lg overflow-x-auto">{{ json_encode($bulkExample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-sm text-gray-700">
            <h2 class="text-xl font-bold text-gray-900 mb-3">Response shape</h2>
            <pre class="bg-slate-900 text-slate-100 text-xs p-4 rounded-lg overflow-x-auto">{
  "success": true,
  "message": "...",
  "data": { }
}</pre>
            <ul class="list-disc list-inside mt-3 space-y-1">
                <li>Validation errors are HTTP 422 with <code class="bg-gray-100 px-1 rounded">errors</code> keyed by field name.</li>
                <li>A missing or expired token is HTTP 401 <code class="bg-gray-100 px-1 rounded">Unauthenticated.</code></li>
                <li>An unknown path is HTTP 404 <code class="bg-gray-100 px-1 rounded">API endpoint not found</code>.</li>
            </ul>
        </div>

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
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Query / path</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        @foreach($endpoint['params'] as $key => $rule)
                                            <li><code class="bg-gray-100 px-1 rounded">{{ $key }}</code> — {{ $rule }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($endpoint['body']))
                                <div class="mt-3">
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Body</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        @foreach($endpoint['body'] as $key => $rule)
                                            <li><code class="bg-gray-100 px-1 rounded">{{ $key }}</code> — {{ $rule }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($endpoint['response']))
                                <div class="mt-3">
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Response fields</p>
                                    <p class="text-sm text-gray-700">{{ implode(', ', $endpoint['response']) }}</p>
                                </div>
                            @endif

                            @if(!empty($endpoint['statuses']))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($endpoint['statuses'] as $status)
                                        <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">{{ $status }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($endpoint['notes']))
                                <p class="mt-3 text-sm text-gray-600">{{ $endpoint['notes'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
