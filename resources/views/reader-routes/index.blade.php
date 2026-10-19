@extends('layouts.app')

@section('title', 'Reader Routes')

@section('content')
<div class="mx-auto">
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Reader Routes</h1>
                <p class="text-blue-100 mt-1">Assign divisions or individual customers to each meter reader</p>
            </div>
            <a href="{{ route('reports.reader-routes') }}" class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 text-center">
                Completion report
            </a>
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded mb-6">
        A reader with no division and no customer receives an empty list on the phone. Bills can still be generated automatically or by hand in this system.
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reader</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Divisions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Extra customers</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone route</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($readers as $reader)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $reader->name }}</div>
                            <div class="text-sm text-gray-500">{{ $reader->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            @if($reader->routeDivisions->isEmpty())
                                <span class="text-gray-400">None</span>
                            @else
                                {{ $reader->routeDivisions->pluck('name')->join(', ') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $reader->route_customers_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($reader->route_divisions_count === 0 && $reader->route_customers_count === 0)
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">No route</span>
                            @else
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Assigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('reader-routes.edit', $reader) }}" class="text-blue-600 hover:text-blue-800 font-medium">Assign</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No meter readers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
