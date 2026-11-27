{{--
    A settings page that lives somewhere else. The whole card is the link.

    Needs: $href, $icon, $tone, $title, $text, $stat, $statIcon
    Tones: purple, orange, indigo, teal
--}}
@php
    $tones = [
        'purple' => ['icon' => 'bg-purple-50 text-purple-600', 'border' => 'hover:border-purple-300', 'link' => 'text-purple-700'],
        'orange' => ['icon' => 'bg-orange-50 text-orange-600', 'border' => 'hover:border-orange-300', 'link' => 'text-orange-700'],
        'indigo' => ['icon' => 'bg-indigo-50 text-indigo-600', 'border' => 'hover:border-indigo-300', 'link' => 'text-indigo-700'],
        'teal' => ['icon' => 'bg-teal-50 text-teal-600', 'border' => 'hover:border-teal-300', 'link' => 'text-teal-700'],
    ];
    $t = $tones[$tone];
@endphp
<a href="{{ $href }}"
   class="group flex h-full flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 {{ $t['border'] }}">
    <div class="mb-4 flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $t['icon'] }}">
            <i class="fas {{ $icon }} text-lg"></i>
        </span>
        <div class="min-w-0">
            <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
            <p class="mt-0.5 text-sm text-gray-600">{{ $text }}</p>
        </div>
    </div>
    <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-3 text-sm">
        <span class="inline-flex items-center gap-1.5 text-gray-500"><i class="fas {{ $statIcon }} text-xs"></i>{{ $stat }}</span>
        <span class="inline-flex items-center gap-1 font-medium {{ $t['link'] }}">Open <i class="fas fa-arrow-right text-xs transition group-hover:translate-x-0.5"></i></span>
    </div>
</a>
