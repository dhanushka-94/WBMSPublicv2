@extends('layouts.app')

@section('title', 'Changelog - AquaBill by olexto')

@section('content')
<div class="py-8 px-6 max-w-4xl">
    <h1 class="text-2xl font-semibold text-gray-900">
        <i class="fas fa-list-ul text-blue-600 mr-2"></i>Changelog
    </h1>
    <p class="mt-2 text-sm text-gray-600">AquaBill by olexto. The current version code is {{ config('changelog.0.version') }}.</p>

    <div class="mt-8 space-y-6">
        @foreach(config('changelog') as $release)
            <article class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">
                        Version code {{ $release['version'] }}
                    </span>
                    @if($loop->first)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Current</span>
                    @endif
                    <span class="text-sm text-gray-500">{{ $release['date'] }}</span>
                </div>
                <h2 class="mt-3 text-lg font-semibold text-gray-900">{{ $release['title'] }}</h2>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-gray-700">
                    @foreach($release['changes'] as $change)
                        <li>{{ $change }}</li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </div>
</div>
@endsection
