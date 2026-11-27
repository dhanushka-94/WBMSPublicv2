@extends('layouts.app')

@section('title', $report->title())

@section('content')
@php
    $staffLabels = \App\Support\CustomerSummaryReport::STAFF_LABELS;
    $genderLabels = \App\Support\CustomerSummaryReport::GENDER_LABELS;
    $money = fn ($v) => number_format((float) $v, 2);
    $exportQuery = request()->query();
    $filtersOn = collect($filters)->except('month')->filter(fn ($v) => $v !== '')->isNotEmpty();
@endphp

<div class="py-8 px-6">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('reports.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900"><i class="fas fa-arrow-left mr-1"></i>All reports</a>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">{{ $report->title() }}</h1>
            @if($report->isMissingPhoneView())
                <p class="text-sm text-gray-600">Every customer with no phone number saved, so they cannot receive SMS. Usage and bill columns are for <strong>{{ $report->monthLabel() }}</strong>.</p>
            @else
                <p class="text-sm text-gray-600">Who each customer is, what they used and were billed in <strong>{{ $report->monthLabel() }}</strong>, and what they owe in total.</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.customer-summary.pdf', $exportQuery) }}"
               class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                <i class="fas fa-file-pdf"></i>Export PDF
            </a>
            <a href="{{ route('reports.customer-summary.excel', $exportQuery) }}"
               class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                <i class="fas fa-file-excel"></i>Export Excel
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.customer-summary') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="f-month" class="block text-xs font-medium text-gray-500">Month</label>
                <input id="f-month" type="month" name="month" value="{{ $filters['month'] }}" class="mt-1 w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="sm:col-span-1 lg:col-span-2">
                <label for="f-q" class="block text-xs font-medium text-gray-500">Search</label>
                <input id="f-q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Name, ref no, EPF no, meter no or phone"
                       class="mt-1 w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label for="f-status" class="block text-xs font-medium text-gray-500">Customer status</label>
                <select id="f-status" name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach(['active', 'inactive', 'suspended', 'disconnected'] as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f-division" class="block text-xs font-medium text-gray-500">Division</label>
                <select id="f-division" name="division_id" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">All divisions</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @selected($filters['division_id'] === (string) $division->id)>{{ $division->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-type" class="block text-xs font-medium text-gray-500">Customer type</label>
                <select id="f-type" name="customer_type_id" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">All types</option>
                    @foreach($customerTypes as $type)
                        <option value="{{ $type->id }}" @selected($filters['customer_type_id'] === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-staff" class="block text-xs font-medium text-gray-500">Staff group</label>
                <select id="f-staff" name="staff_type" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach($staffLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['staff_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-gender" class="block text-xs font-medium text-gray-500">Male / Female</label>
                <select id="f-gender" name="gender" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach($genderLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['gender'] === $value)>{{ $label }}</option>
                    @endforeach
                    <option value="unset" @selected($filters['gender'] === 'unset')>Not set</option>
                </select>
            </div>

            <div>
                <label for="f-outstanding" class="block text-xs font-medium text-gray-500">Outstanding</label>
                <select id="f-outstanding" name="outstanding" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">Any</option>
                    <option value="yes" @selected($filters['outstanding'] === 'yes')>Owes money</option>
                    <option value="no" @selected($filters['outstanding'] === 'no')>Nothing owed</option>
                </select>
            </div>
            <div>
                <label for="f-usage" class="block text-xs font-medium text-gray-500">Usage this month</label>
                <select id="f-usage" name="usage" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">Any</option>
                    <option value="yes" @selected($filters['usage'] === 'yes')>Used water</option>
                    <option value="no" @selected($filters['usage'] === 'no')>No usage / not read</option>
                </select>
            </div>
            <div>
                <label for="f-phone" class="block text-xs font-medium text-gray-500">Phone number</label>
                <select id="f-phone" name="phone" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">Any</option>
                    <option value="missing" @selected($filters['phone'] === 'missing')>No phone number</option>
                    <option value="has" @selected($filters['phone'] === 'has')>Has a phone number</option>
                </select>
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
                <button class="rounded-md bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i>Show
                </button>
                @if($filtersOn || $filters['month'] !== now()->format('Y-m'))
                    <a href="{{ route('reports.customer-summary') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Totals for everything the filters match --}}
    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Customers</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totals['customers']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Usage this month (units)</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $money($totals['usage_units']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">This month bill amount</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">Rs. {{ $money($totals['month_bill']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total outstanding</p>
            <p class="mt-1 text-2xl font-semibold text-red-700">Rs. {{ $money($totals['outstanding']) }}</p>
        </div>
    </div>
    <p class="mt-2 text-xs text-gray-500">
        Totals cover every customer the filters match, not only this page. The PDF and Excel files contain the same customers.
        Usage is meter reading consumption dated in the month; the bill amount is bills whose billing period ends in the month (cancelled bills left out);
        outstanding is the unpaid balance on all bills, any month.
    </p>

    {{-- Table --}}
    <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    @foreach($headings as $i => $heading)
                        <th scope="col" class="px-3 py-3 {{ $i >= 9 ? 'text-right' : '' }}">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-500">{{ $row[0] }}</td>
                        <td class="whitespace-nowrap px-3 py-2 font-medium text-gray-900">{{ $row[1] }}</td>
                        <td class="px-3 py-2">{{ $row[2] ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $row[3] ?: '-' }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900">{{ $row[4] }}</td>
                        <td class="whitespace-nowrap px-3 py-2">{{ $row[5] ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $row[6] ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $row[7] ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $row[8] ?: '-' }}</td>
                        <td class="px-3 py-2 text-right tabular-nums">{{ $money($row[9]) }}</td>
                        <td class="px-3 py-2 text-right tabular-nums">{{ $money($row[10]) }}</td>
                        <td class="px-3 py-2 text-right tabular-nums {{ $row[11] > 0 ? 'font-semibold text-red-700' : 'text-gray-500' }}">{{ $money($row[11]) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($headings) }}" class="px-4 py-8 text-center text-gray-500">No customers match these filters.</td></tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot class="bg-gray-50 text-sm font-semibold text-gray-900">
                    <tr>
                        <td colspan="9" class="px-3 py-3">Total, all {{ number_format($totals['customers']) }} matching customers</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $money($totals['usage_units']) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $money($totals['month_bill']) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-red-700">{{ $money($totals['outstanding']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
</div>
@endsection
