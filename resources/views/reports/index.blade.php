@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900">Reports</h1>
            <p class="mt-1 text-sm text-slate-600">Open any report for connections, usage, or collections.</p>
        </div>

        <div class="space-y-8">
            @foreach($sections as $section)
                <section>
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $section['title'] }}</h2>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($section['items'] as $item)
                            @php
                                [$label, $desc, $route, $icon, $color] = array_pad($item, 5, null);
                                $param = $item[5] ?? null;
                                $href = $param ? route($route, $param) : route($route);
                            @endphp
                            <a href="{{ $href }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50">
                                        <i class="fas {{ $icon }} {{ $color }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $label }}</p>
                                        <p class="mt-0.5 text-sm text-slate-500">{{ $desc }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
@endsection
