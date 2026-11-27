{{--
    One on/off setting. Flipping the switch asks a specific question first, then saves straight away.

    Needs: $action, $name, $checked, $title, $text, $state, $badgeOn, $badgeOff, $toneOn, $toneOff, $confirmOn, $confirmOff
    Optional: $disabled (locked switch), $note (extra line shown under the text)
    Tones: green, amber, blue, gray
--}}
@php
    $disabled = $disabled ?? false;
    $note = $note ?? null;

    $badges = [
        'green' => 'bg-green-50 text-green-700 ring-green-600/20',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'blue' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'gray' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    ];
    $dots = [
        'green' => 'bg-green-500',
        'amber' => 'bg-amber-500',
        'blue' => 'bg-blue-500',
        'gray' => 'bg-gray-400',
    ];
    $tracks = [
        'green' => 'peer-checked:bg-green-600',
        'amber' => 'peer-checked:bg-amber-500',
        'blue' => 'peer-checked:bg-blue-600',
        'gray' => 'peer-checked:bg-gray-500',
    ];

    $tone = $checked ? $toneOn : $toneOff;
    $cfg = ['on' => $confirmOn, 'off' => $confirmOff];
@endphp
<form method="POST" action="{{ $action }}" data-skip-confirm="yes" class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
    @csrf
    @method('PUT')
    <input type="hidden" name="{{ $name }}" value="0">

    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badges[$tone] }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $dots[$tone] }}"></span>{{ $checked ? $badgeOn : $badgeOff }}
            </span>
            @if($disabled)
                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                    <i class="fas fa-lock text-[10px]"></i>Locked
                </span>
            @endif
        </div>
        <p class="mt-1 text-sm text-gray-600">{{ $text }}</p>
        <p class="mt-1 text-xs text-gray-500">{{ $state }}</p>
        @if($note)
            <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                <i class="fas fa-info-circle mr-1"></i>{{ $note }}
            </p>
        @endif
    </div>

    <label class="relative inline-flex shrink-0 items-center {{ $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
        <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only"
               {{ $checked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}
               aria-label="{{ $title }}"
               @change="toggleAsk($event, @js($cfg))">
        <span class="h-6 w-11 rounded-full bg-gray-300 transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-blue-400 peer-focus-visible:ring-offset-2 {{ $tracks[$toneOn] }}"></span>
        <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
    </label>
</form>
