@extends('layouts.app')

@section('title', 'Failed SMS')

@php
    $field = 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm';
    $rows = $notifications->getCollection();
    $pageIds = $rows->pluck('id')->values();
    $segmentMap = $rows->mapWithKeys(fn ($n) => [$n->id => max(1, (int) $n->segments)]);
    $hasFilters = request()->hasAny(['search', 'customer', 'phone', 'type', 'date_from', 'date_to', 'reason']);
    $lastDigits = fn ($value) => substr(preg_replace('/\D+/', '', (string) $value), -9);
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-exclamation-circle mr-2 text-red-600"></i>Failed SMS</h1>
                <p class="mt-1 text-sm text-gray-600">Messages the customer did not receive. Resend them here once the cause is fixed.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sms.index') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-list text-blue-600"></i>All SMS Logs</a>
                <a href="{{ route('sms.export', array_merge(request()->query(), ['status' => 'failed'])) }}" class="inline-flex items-center gap-2 rounded-md border border-green-600 bg-white px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-50"><i class="fas fa-download"></i>Export CSV</a>
            </div>
        </div>

        @include('sms.partials.flash')

        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-gray-500">Failed messages</div>
                <div class="mt-1 text-2xl font-bold text-red-600">{{ number_format($summary->total) }}</div>
                <div class="text-xs text-gray-400">{{ $hasFilters ? 'matching filters' : 'in total' }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-gray-500">Last 24 hours</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($last24h) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-gray-500">Customers affected</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($summary->customers) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-gray-500">Cost to resend all</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format($summary->segments * $rate, 2) }}</div>
                <div class="text-xs text-gray-400">{{ number_format($summary->segments) }} segments at Rs. {{ number_format($rate, 2) }}, charged only if delivered</div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            @if($reasons->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 px-4 py-3 text-xs">
                    <span class="text-gray-500">Reason:</span>
                    <a href="{{ request()->fullUrlWithQuery(['reason' => null, 'page' => null]) }}" class="rounded-full border px-3 py-1 {{ request('reason') ? 'border-gray-300 text-gray-700 hover:border-blue-400' : 'border-blue-600 bg-blue-50 text-blue-700' }}">All</a>
                    @foreach($reasons as $reason => $count)
                        <a href="{{ request()->fullUrlWithQuery(['reason' => $reason, 'page' => null]) }}" title="{{ $reason }}"
                           class="inline-flex max-w-sm items-center gap-2 rounded-full border px-3 py-1 {{ request('reason') === $reason ? 'border-red-600 bg-red-50 text-red-700' : 'border-gray-300 text-gray-700 hover:border-red-400' }}">
                            <span class="truncate">{{ $reason }}</span>
                            <span class="shrink-0 rounded-full bg-gray-200 px-1.5 font-semibold text-gray-700">{{ $count }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('sms.failed') }}" class="border-b border-gray-200 bg-gray-50 p-4">
                @if(request('reason'))<input type="hidden" name="reason" value="{{ request('reason') }}">@endif
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Search</label>
                        <div class="relative">
                            <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Customer, account, phone or message" class="{{ $field }} pl-8">
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Type</label>
                        <select name="type" class="{{ $field }}" onchange="this.form.submit()">
                            <option value="">All types</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="{{ $field }}" onchange="this.form.submit()">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="{{ $field }}" onchange="this.form.submit()">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Rows per page</label>
                        <select name="per_page" class="{{ $field }}" onchange="this.form.submit()">
                            @foreach([25, 50] as $size)
                                <option value="{{ $size }}" @selected((int) request('per_page', 25) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><i class="fas fa-search mr-2"></i>Apply</button>
                    @if($hasFilters)
                        <a href="{{ route('sms.failed') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-times mr-2"></i>Clear filters</a>
                    @endif
                </div>
            </form>

            @if($notifications->count() > 0)
                <div x-data='{
                        ids: [],
                        all: @json($pageIds),
                        segments: @json($segmentMap),
                        rate: {{ $rate }},
                        get allChecked() { return this.all.length > 0 && this.ids.length === this.all.length },
                        get cost() { return this.ids.reduce((sum, id) => sum + (this.segments[id] || 1) * this.rate, 0) },
                        toggleAll(on) { this.ids = on ? [...this.all] : [] }
                    }'>
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2 text-xs text-gray-500">
                        <span>Showing {{ $notifications->firstItem() }}-{{ $notifications->lastItem() }} of {{ number_format($notifications->total()) }}</span>
                        <button type="button" @click="toggleAll(!allChecked)" class="font-medium text-blue-600 hover:text-blue-800" x-text="allChecked ? 'Clear selection' : 'Select all {{ $notifications->count() }} on this page'"></button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="w-10 px-4 py-3"><input type="checkbox" :checked="allChecked" @change="toggleAll($event.target.checked)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select all on this page"></th>
                                    <th class="px-4 py-3 text-left">Last attempt</th>
                                    <th class="px-4 py-3 text-left">Customer</th>
                                    <th class="px-4 py-3 text-left">Type</th>
                                    <th class="px-4 py-3 text-left">Why it failed</th>
                                    <th class="px-4 py-3 text-left">Message</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($notifications as $n)
                                    @php
                                        $customerPhone = $n->customer?->phone;
                                        $phoneChanged = $customerPhone && $lastDigits($customerPhone) !== $lastDigits($n->phone_number);
                                    @endphp
                                    <tr class="align-top hover:bg-gray-50">
                                        <td class="px-4 py-3"><input type="checkbox" x-model="ids" :value="{{ $n->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select SMS {{ $n->id }}"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            <div>{{ $n->updated_at->format('d M Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $n->updated_at->format('h:i A') }} &middot; #{{ $n->id }}</div>
                                            <div class="text-xs text-gray-400">Failed {{ max(1, (int) $n->retry_count) }}x</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($n->customer)
                                                <a href="{{ route('customers.show', $n->customer) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $n->customer->full_name }}</a>
                                                <div class="text-xs text-gray-500">{{ $n->customer->account_number }}</div>
                                            @else
                                                <span class="text-gray-400">No customer</span>
                                            @endif
                                            <div class="mt-0.5 font-mono text-xs text-gray-600">{{ $n->formatted_phone }}</div>
                                            @if($phoneChanged)
                                                <div class="mt-1 max-w-[14rem] text-xs text-amber-700"><i class="fas fa-info-circle mr-1"></i>Customer's number is now {{ $customerPhone }}. Resend will use it.</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $n->type_label }}</span>
                                            @if($n->bill)
                                                <div><a href="{{ route('bills.show', $n->bill) }}" class="text-xs text-blue-700 hover:text-blue-900">{{ $n->bill->bill_number }}</a></div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3" style="max-width: 16rem;">
                                            <div class="text-red-700">{{ $n->failure_reason }}</div>
                                            @if($n->failure_hint)<div class="mt-1 text-xs text-gray-500">{{ $n->failure_hint }}</div>@endif
                                        </td>
                                        <td class="px-4 py-3" style="min-width: 16rem; max-width: 24rem;">
                                            <div x-data="{ open: false }">
                                                <p x-show="!open" class="break-words text-gray-800">{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s*\n\s*/', ' - ', $n->message)), 110) }}</p>
                                                <p x-show="open" x-cloak style="display: none;" class="whitespace-pre-line break-words text-gray-800">{{ $n->message }}</p>
                                                @if(mb_strlen($n->message) > 90 || substr_count($n->message, "\n") > 1)
                                                    <button type="button" @click="open = !open" class="mt-1 text-xs text-blue-600 hover:text-blue-800" x-text="open ? 'Show less' : 'Show full message'"></button>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-3">
                                                <form method="POST" action="{{ route('sms.resend', $n) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-md bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600"
                                                            onclick="return confirm('Resend this SMS to {{ $phoneChanged ? $customerPhone : $n->phone_number }}?')">
                                                        <i class="fas fa-redo mr-1"></i>Resend
                                                    </button>
                                                </form>
                                                <a href="{{ route('sms.show', $n) }}" class="text-blue-600 hover:text-blue-800" title="View details"><i class="fas fa-eye"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 px-4 py-3">{{ $notifications->links() }}</div>

                    <div x-show="ids.length" x-cloak style="display: none;" class="fixed inset-x-0 bottom-4 z-40 flex justify-center px-4">
                        <form method="POST" action="{{ route('sms.bulk-resend') }}"
                              class="flex flex-wrap items-center gap-3 rounded-xl bg-gray-900 px-5 py-3 text-sm text-white shadow-2xl"
                              @submit="if (!confirm('Resend ' + ids.length + ' failed SMS? Estimated cost Rs. ' + cost.toFixed(2) + ' (charged only if delivered).')) $event.preventDefault()">
                            @csrf
                            <template x-for="id in ids" :key="id"><input type="hidden" name="selected_ids[]" :value="id"></template>
                            <span><strong x-text="ids.length"></strong> selected &middot; about Rs. <span x-text="cost.toFixed(2)"></span></span>
                            <button type="submit" class="rounded-md bg-amber-500 px-3 py-1.5 font-medium text-white hover:bg-amber-600"><i class="fas fa-redo mr-1"></i>Resend selected</button>
                            <button type="button" @click="ids = []" class="text-gray-300 hover:text-white">Clear</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-check-circle mb-4 text-5xl text-green-400"></i>
                    <h3 class="text-lg font-medium text-gray-900">{{ $hasFilters ? 'No failed SMS match these filters' : 'No failed SMS' }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $hasFilters ? 'Try clearing the filters.' : 'Every message was delivered to the SMS provider.' }}</p>
                    <a href="{{ $hasFilters ? route('sms.failed') : route('sms.index') }}" class="mt-4 inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">{{ $hasFilters ? 'Clear filters' : 'Open SMS Logs' }}</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
