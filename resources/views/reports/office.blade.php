@extends('layouts.app')

@section('content')
<div class="py-8 px-6">
    <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
    @if($notes)
        <p class="mt-2 text-sm text-gray-600">{{ $notes }}</p>
    @endif
    <form method="GET" class="mt-4 flex gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500">Month</label>
            <input type="month" name="month" value="{{ $month }}" class="border-gray-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500">Date</label>
            <input type="date" name="date" value="{{ $date }}" class="border-gray-300 rounded-md text-sm">
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Show</button>
    </form>
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
