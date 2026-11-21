@extends('layouts.app')

@section('content')
<div class="py-8 px-6">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
            @if($notes)
                <p class="mt-2 text-sm text-gray-600">{{ $notes }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(!empty($canExportCsv) && $report === 'daily-collection')
                <a href="{{ route('reports.office.daily-collection.export', ['date' => $date]) }}"
                   class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                    <i class="fas fa-file-csv mr-2"></i>Export CSV
                </a>
            @endif
            <a href="{{ route('operations.index') }}" class="text-sm font-medium text-teal-700 hover:text-teal-900">Open Operations hub</a>
        </div>
    </div>

    @if($usesMonth || $usesDate)
        <form method="GET" class="mt-4 flex flex-wrap gap-3 items-end">
            @if($usesMonth)
                <div>
                    <label class="block text-xs text-gray-500">Month</label>
                    <input type="month" name="month" value="{{ $month }}" class="border-gray-300 rounded-md text-sm">
                </div>
            @endif
            @if($usesDate)
                <div>
                    <label class="block text-xs text-gray-500">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="border-gray-300 rounded-md text-sm">
                </div>
            @endif
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Show</button>
            @if(!empty($canExportCsv) && $report === 'daily-collection')
                <a href="{{ route('reports.office.daily-collection.export', ['date' => $date]) }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                    Export CSV
                </a>
            @endif
        </form>
    @endif

    <div class="mt-6 bg-white border border-gray-200 rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $column)
                        <th class="px-4 py-2 text-left">{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-t">
                        @foreach($row as $cell)
                            <td class="px-4 py-2">
                                @if(is_array($cell) && !empty($cell['url']))
                                    <a href="{{ $cell['url'] }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $cell['text'] }}</a>
                                @else
                                    {{ is_array($cell) ? ($cell['text'] ?? '') : $cell }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-gray-500" colspan="{{ count($columns) }}">Nothing to show.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rows instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div class="mt-4">{{ $rows->links() }}</div>
    @endif
</div>
@endsection
