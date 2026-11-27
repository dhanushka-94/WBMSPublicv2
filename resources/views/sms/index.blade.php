@extends('layouts.app')

@section('title', 'SMS Logs')

@php
    $field = 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm';
    $status = request('status');
    $rows = $notifications->getCollection();
    $pageIds = $rows->pluck('id')->values();
    $failedMap = $rows->where('status', 'failed')->mapWithKeys(fn ($n) => [$n->id => max(1, (int) $n->segments)]);
    $tabs = [
        ['', 'All', $counts->sum(), 'bg-gray-100 text-gray-700'],
        ['sent', 'Sent', $counts['sent'] ?? 0, 'bg-green-100 text-green-700'],
        ['failed', 'Failed', $counts['failed'] ?? 0, 'bg-red-100 text-red-700'],
        ['pending', 'Pending', $counts['pending'] ?? 0, 'bg-yellow-100 text-yellow-700'],
    ];
    $hasFilters = request()->hasAny(['search', 'customer', 'phone', 'type', 'date_from', 'date_to', 'status']);
    $presets = [
        'Today' => ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()],
        'Last 7 days' => ['date_from' => today()->subDays(6)->toDateString(), 'date_to' => today()->toDateString()],
        'This month' => ['date_from' => today()->startOfMonth()->toDateString(), 'date_to' => today()->toDateString()],
        'All time' => ['date_from' => null, 'date_to' => null],
    ];
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-sms mr-2 text-blue-600"></i>SMS Logs</h1>
                <p class="mt-1 text-sm text-gray-600">Every SMS the system has sent or tried to send.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sms.failed') }}" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    <i class="fas fa-exclamation-circle"></i>Failed SMS
                    @if($stats['failed'] > 0)<span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-red-700">{{ number_format($stats['failed']) }}</span>@endif
                </a>
                <a href="{{ route('sms.usage') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-coins text-amber-600"></i>Usage &amp; Cost</a>
                <a href="{{ route('sms.bulk') }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><i class="fas fa-paper-plane"></i>Send Custom SMS</a>
            </div>
        </div>

        @include('sms.partials.flash')
        @include('sms.partials.archive-note')

        @if($stats['failed'] > 0 && $status !== 'failed')
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <span><i class="fas fa-exclamation-triangle mr-2"></i><strong>{{ number_format($stats['failed']) }}</strong> {{ Str::plural('message', $stats['failed']) }} failed to send and the customers have not received them.</span>
                <a href="{{ route('sms.failed') }}" class="font-semibold underline hover:text-red-900">Review and resend</a>
            </div>
        @endif

        <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
            @foreach([
                ['Total SMS', $stats['total'], route('sms.index'), 'fa-envelope', 'text-blue-600'],
                ['Sent', $stats['sent'], route('sms.index', ['status' => 'sent']), 'fa-check-circle', 'text-green-600'],
                ['Failed', $stats['failed'], route('sms.failed'), 'fa-times-circle', 'text-red-600'],
                ['Pending', $stats['pending'], route('sms.index', ['status' => 'pending']), 'fa-clock', 'text-yellow-600'],
                ['Today', $stats['today'], route('sms.index', $presets['Today']), 'fa-calendar-day', 'text-purple-600'],
                ['This month', $stats['this_month'], route('sms.index', $presets['This month']), 'fa-calendar-alt', 'text-indigo-600'],
            ] as [$label, $value, $url, $icon, $color])
                <a href="{{ $url }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow">
                    <div class="flex items-center justify-between text-xs uppercase tracking-wide text-gray-500">{{ $label }}<i class="fas {{ $icon }} {{ $color }}"></i></div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($value) }}</div>
                </a>
            @endforeach
        </div>

        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 px-4 pt-3">
                @foreach($tabs as [$key, $label, $count, $badge])
                    @php $active = (string) $status === (string) $key; @endphp
                    <a href="{{ request()->fullUrlWithQuery(['status' => $key ?: null, 'page' => null]) }}"
                       class="-mb-px inline-flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium {{ $active ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
                        {{ $label }}
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $badge }}">{{ number_format($count) }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('sms.index') }}" class="border-b border-gray-200 bg-gray-50 p-4">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
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
                            @foreach([25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) request('per_page', 25) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-gray-500">Quick range:</span>
                        @foreach($presets as $label => $range)
                            <a href="{{ request()->fullUrlWithQuery($range + ['page' => null]) }}" class="rounded-full border border-gray-300 bg-white px-3 py-1 text-gray-700 hover:border-blue-400 hover:text-blue-700">{{ $label }}</a>
                        @endforeach
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><i class="fas fa-search mr-2"></i>Apply</button>
                        @if($hasFilters)
                            <a href="{{ route('sms.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-times mr-2"></i>Clear filters</a>
                        @endif
                        <a href="{{ route('sms.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-green-600 bg-white px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-50"><i class="fas fa-download mr-2"></i>Export CSV</a>
                    </div>
                </div>
            </form>

            @if($notifications->count() > 0)
                <div x-data='{
                        ids: [],
                        all: @json($pageIds),
                        failed: @json($failedMap),
                        rate: {{ $rate }},
                        get allChecked() { return this.all.length > 0 && this.ids.length === this.all.length },
                        get failedIds() { return this.ids.filter(id => this.failed[id] !== undefined) },
                        get failedCost() { return this.failedIds.reduce((sum, id) => sum + this.failed[id] * this.rate, 0) },
                        toggleAll(on) { this.ids = on ? [...this.all] : [] }
                    }'>
                    <div class="flex items-center justify-between px-4 py-2 text-xs text-gray-500">
                        <span>Showing {{ $notifications->firstItem() }}-{{ $notifications->lastItem() }} of {{ number_format($notifications->total()) }}</span>
                        <span x-show="ids.length" x-cloak><span x-text="ids.length"></span> selected on this page</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="w-10 px-4 py-3"><input type="checkbox" :checked="allChecked" @change="toggleAll($event.target.checked)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select all on this page"></th>
                                    <th class="px-4 py-3 text-left">Date</th>
                                    <th class="px-4 py-3 text-left">Customer</th>
                                    <th class="px-4 py-3 text-left">Type</th>
                                    <th class="px-4 py-3 text-left">Message</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-right">Cost</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($notifications as $n)
                                    <tr class="align-top hover:bg-gray-50 {{ $n->status === 'failed' ? 'bg-red-50/40' : '' }}">
                                        <td class="px-4 py-3"><input type="checkbox" x-model="ids" :value="{{ $n->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select SMS {{ $n->id }}"></td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            <div>{{ $n->created_at->format('d M Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $n->created_at->format('h:i A') }} &middot; #{{ $n->id }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($n->customer)
                                                <a href="{{ route('customers.show', $n->customer) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $n->customer->full_name }}</a>
                                                <div class="text-xs text-gray-500">{{ $n->customer->account_number }}</div>
                                            @else
                                                <span class="text-gray-400">No customer</span>
                                            @endif
                                            <div class="mt-0.5 font-mono text-xs text-gray-500">{{ $n->formatted_phone }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $n->type_label }}</span>
                                            <div class="mt-1 text-xs text-gray-400">{{ ucfirst($n->source ?? 'automated') }}</div>
                                            @if($n->bill)
                                                <a href="{{ route('bills.show', $n->bill) }}" class="text-xs text-blue-700 hover:text-blue-900">{{ $n->bill->bill_number }}</a>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3" style="min-width: 18rem; max-width: 26rem;">
                                            <div x-data="{ open: false }">
                                                <p x-show="!open" class="break-words text-gray-800">{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s*\n\s*/', ' - ', $n->message)), 110) }}</p>
                                                <p x-show="open" x-cloak style="display: none;" class="whitespace-pre-line break-words text-gray-800">{{ $n->message }}</p>
                                                @if(mb_strlen($n->message) > 90 || substr_count($n->message, "\n") > 1)
                                                    <button type="button" @click="open = !open" class="mt-1 text-xs text-blue-600 hover:text-blue-800" x-text="open ? 'Show less' : 'Show full message'"></button>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            {!! $n->status_badge !!}
                                            @if($n->status === 'failed')
                                                <div class="mt-1 max-w-[14rem] text-xs text-red-700">{{ $n->failure_reason }}</div>
                                                @if($n->failure_hint)<div class="max-w-[14rem] text-xs text-gray-500">{{ $n->failure_hint }}</div>@endif
                                            @endif
                                            @if($n->retry_count > 0)
                                                <div class="mt-1 text-xs text-gray-400">Failed attempts: {{ $n->retry_count }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">
                                            @if($n->status === 'sent')
                                                Rs. {{ number_format($n->charged_amount, 2) }}
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                            <div class="text-xs text-gray-400">{{ max(1, (int) $n->segments) }} {{ Str::plural('segment', max(1, (int) $n->segments)) }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-3">
                                                <a href="{{ route('sms.show', $n) }}" class="text-blue-600 hover:text-blue-800" title="View details"><i class="fas fa-eye"></i></a>
                                                @if($n->status === 'failed')
                                                    <form method="POST" action="{{ route('sms.resend', $n) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-amber-600 hover:text-amber-800" title="Resend SMS" onclick="return confirm('Resend this SMS to {{ $n->phone_number }}?')"><i class="fas fa-redo"></i></button>
                                                    </form>
                                                @elseif($n->status === 'sent')
                                                    <form method="POST" action="{{ route('sms.send-again', $n) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-800" title="Send again"
                                                                onclick="return confirm('Send this SMS again to {{ $n->phone_number }}?\n\nIt was already delivered, so the customer will receive it twice and it is charged again (about Rs. {{ number_format(max(1, (int) $n->segments) * $rate, 2) }}).')"><i class="fas fa-paper-plane"></i></button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('sms.destroy', $n) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Delete record"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 px-4 py-3">{{ $notifications->links() }}</div>

                    <div x-show="ids.length" x-cloak style="display: none;" class="fixed inset-x-0 bottom-4 z-40 flex justify-center px-4">
                        <div class="flex flex-wrap items-center gap-3 rounded-xl bg-gray-900 px-5 py-3 text-sm text-white shadow-2xl">
                            <span><strong x-text="ids.length"></strong> selected</span>

                            <form method="POST" action="{{ route('sms.bulk-resend') }}" x-show="failedIds.length" class="inline"
                                  @submit="if (!confirm('Resend ' + failedIds.length + ' failed SMS? Estimated cost Rs. ' + failedCost.toFixed(2) + ' (charged only if delivered).')) $event.preventDefault()">
                                @csrf
                                <template x-for="id in failedIds" :key="id"><input type="hidden" name="selected_ids[]" :value="id"></template>
                                <button type="submit" class="rounded-md bg-amber-500 px-3 py-1.5 font-medium text-white hover:bg-amber-600">
                                    <i class="fas fa-redo mr-1"></i>Resend <span x-text="failedIds.length"></span> failed
                                </button>
                            </form>

                            <form method="POST" action="{{ route('sms.bulk-delete') }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <template x-for="id in ids" :key="id"><input type="hidden" name="selected_ids[]" :value="id"></template>
                                <button type="submit" class="rounded-md bg-red-600 px-3 py-1.5 font-medium text-white hover:bg-red-700"><i class="fas fa-trash mr-1"></i>Delete</button>
                            </form>

                            <button type="button" @click="ids = []" class="text-gray-300 hover:text-white">Clear</button>
                        </div>
                    </div>
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-sms mb-4 text-5xl text-gray-300"></i>
                    <h3 class="text-lg font-medium text-gray-900">No SMS found</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $hasFilters ? 'Nothing matches these filters.' : 'Messages will appear here once they are sent.' }}</p>
                    @if($hasFilters)
                        <a href="{{ route('sms.index') }}" class="mt-4 inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Clear filters</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
