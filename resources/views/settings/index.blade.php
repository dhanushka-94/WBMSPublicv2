@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
@php
    // Admins see Rates, Download Mobile App, Customer Divisions and Customer Types.
    // Everything else is Super Admin only.
    $isSuper = auth()->user()->isSuperAdmin();
    $systemOff = ! \App\Helpers\SystemConfig::isSystemEnabled();

    $defaultDay = (int) \App\Models\SystemConfiguration::getDefaultBillingDay();
    $suffix = in_array($defaultDay % 100, [11, 12, 13], true)
        ? 'th'
        : (['1' => 'st', '2' => 'nd', '3' => 'rd'][$defaultDay % 10] ?? 'th');
    $mobileRelease = \App\Models\SystemConfiguration::mobileAppRelease();

    $sections = [];
    if ($isSuper) {
        $sections[] = ['id' => 'sec-readings', 'label' => 'Meter readings', 'icon' => 'fa-tachometer-alt'];
    }
    $sections[] = ['id' => 'sec-reader-app', 'label' => 'Reader app', 'icon' => 'fa-mobile-alt'];
    $sections[] = ['id' => 'sec-billing', 'label' => 'Billing & rates', 'icon' => 'fa-file-invoice-dollar'];
    if ($isSuper) {
        $sections[] = ['id' => 'sec-maps', 'label' => 'Maps', 'icon' => 'fa-map-marker-alt'];
    }
    $sections[] = ['id' => 'customer-data', 'label' => 'Customer data', 'icon' => 'fa-database'];

    // State handed to the page script. After a failed save, the form reopens with what was typed and the errors.
    $oldKind = old('_form');
    $boot = [
        'tab' => $oldKind === 'customer-type' ? 'customer-types' : 'divisions',
        'modal' => in_array($oldKind, ['division', 'customer-type'], true) ? $oldKind : null,
        'mode' => old('_edit_id') ? 'edit' : 'create',
        'form' => [
            'id' => (string) old('_edit_id', ''),
            'name' => (string) old('name', ''),
            'custom_id' => (string) old('custom_id', ''),
            'description' => (string) old('description', ''),
            'is_active' => $oldKind ? (bool) old('is_active') : true,
        ],
        'errors' => [
            'name' => $oldKind ? (string) $errors->first('name') : '',
            'custom_id' => $oldKind ? (string) $errors->first('custom_id') : '',
            'description' => $oldKind ? (string) $errors->first('description') : '',
        ],
        'urls' => [
            'division' => [
                'store' => route('settings.divisions.store'),
                'update' => route('settings.divisions.update', ':id'),
            ],
            'customer-type' => [
                'store' => route('settings.customer-types.store'),
                'update' => route('settings.customer-types.update', ':id'),
            ],
        ],
        'counts' => [
            'divisions' => $divisions->count(),
            'customer-types' => $customerTypes->count(),
        ],
    ];
@endphp

<style>[x-cloak] { display: none !important; }</style>

<div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8" x-data="settingsPage(@js($boot))"
     @keydown.escape.window="escapePressed()">

    {{-- Header --}}
    <div class="mb-6 rounded-xl bg-gradient-to-r from-blue-700 to-indigo-700 p-6 text-white shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-white/15">
                    <i class="fas fa-cogs text-xl"></i>
                </span>
            <div>
                    <h1 class="text-2xl font-bold">System Settings</h1>
                    <p class="text-sm text-blue-100">Control how AquaBill works for the office and the reader app.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-medium">
                @if($isSuper)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full {{ $systemOff ? 'bg-red-400' : 'bg-green-400' }}"></span>
                        System {{ $systemOff ? 'disabled' : 'active' }}
                    </span>
                    <a href="#sec-reader-app" @click.prevent="go('sec-reader-app')" class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 hover:bg-white/25">
                        <span class="h-2 w-2 rounded-full {{ $mobileAppMaintenance ? 'bg-amber-400' : 'bg-green-400' }}"></span>
                        Reader app {{ $mobileAppMaintenance ? 'in maintenance' : 'open' }}
                    </a>
                @endif
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5">
                    <i class="fas fa-map-marked-alt"></i>{{ $divisions->total() }} {{ \Illuminate\Support\Str::plural('division', $divisions->total()) }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5">
                    <i class="fas fa-tags"></i>{{ $customerTypes->total() }} customer {{ \Illuminate\Support\Str::plural('type', $customerTypes->total()) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity role="status"
             class="mb-4 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <i class="fas fa-check-circle mt-0.5"></i>
            <span class="flex-1">{{ session('success') }}</span>
            <button type="button" @click="show = false" class="text-green-700 hover:text-green-900" aria-label="Dismiss"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition.opacity role="alert"
             class="mb-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <i class="fas fa-exclamation-circle mt-0.5"></i>
            <span class="flex-1">{{ session('error') }}</span>
            <button type="button" @click="show = false" class="text-red-700 hover:text-red-900" aria-label="Dismiss"><i class="fas fa-times"></i></button>
        </div>
    @endif

    {{-- Jump to a section --}}
    <nav class="mb-8 flex gap-2 overflow-x-auto pb-1" aria-label="Settings sections">
        @foreach($sections as $section)
            <a href="#{{ $section['id'] }}" @click.prevent="go('{{ $section['id'] }}')"
               :class="active === '{{ $section['id'] }}' ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'"
               class="inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium shadow-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                <i class="fas {{ $section['icon'] }} text-xs"></i>{{ $section['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- Meter readings (Super Admin) --}}
    @if($isSuper)
        <section id="sec-readings" data-section class="mb-10 scroll-mt-6">
            <div class="mb-3">
                <h2 class="text-base font-semibold text-gray-900">Meter readings</h2>
                <p class="text-sm text-gray-500">Rules for when and by whom readings can be entered.</p>
                </div>
            <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm">
                @include('settings.partials.toggle-row', [
                    'action' => route('settings.reading-limit'),
                    'name' => 'one_reading_per_month',
                    'checked' => $oneReadingPerMonth,
                    'title' => 'One reading per meter each month',
                    'text' => 'Allow only one reading for each meter in a calendar month. A second reading on the same day is always blocked.',
                    'state' => $oneReadingPerMonth
                        ? 'On: the office form and the phone reject a second reading in the same month.'
                        : 'Off: a meter can be read again later in the same month.',
                    'badgeOn' => 'One per month', 'badgeOff' => 'Several allowed',
                    'toneOn' => 'blue', 'toneOff' => 'gray',
                    'confirmOn' => ['title' => 'Allow only one reading per month?', 'message' => 'The office form and the reader app will reject a second reading for the same meter in the same month.', 'yes' => 'Yes, limit it'],
                    'confirmOff' => ['title' => 'Allow several readings per month?', 'message' => 'A meter can be read again later in the same month. A second reading on the same day is still blocked.', 'yes' => 'Yes, allow it'],
                ])
                @include('settings.partials.toggle-row', [
                    'action' => route('settings.initial-readings-access'),
                    'name' => 'initial_readings_open',
                    'checked' => $initialReadingsOpen,
                    'title' => 'Initial Readings page',
                    'text' => 'Choose whether other users can open the Initial Meter Readings page and save on it. The Super Admin can always open it.',
                    'state' => $initialReadingsOpen
                        ? 'On: users with access to meter readings can open Initial Readings.'
                        : 'Off: only the Super Admin can open Initial Readings. The menu links are hidden for everyone else, Admin included.',
                    'badgeOn' => 'Open to others', 'badgeOff' => 'Closed',
                    'toneOn' => 'green', 'toneOff' => 'amber',
                    'confirmOn' => ['title' => 'Open Initial Readings to other users?', 'message' => 'Users who could open it before will be able to open it and save again.', 'yes' => 'Yes, open it'],
                    'confirmOff' => ['title' => 'Close Initial Readings?', 'message' => 'Only the Super Admin will be able to open it. The menu links are hidden for everyone else, Admin included.', 'yes' => 'Yes, close it', 'tone' => 'amber'],
                ])
            </div>
        </section>
    @endif

    {{-- Reader mobile app --}}
    <section id="sec-reader-app" data-section class="mb-10 scroll-mt-6">
        <div class="mb-3">
            <h2 class="text-base font-semibold text-gray-900">Reader mobile app</h2>
            <p class="text-sm text-gray-500">Availability of the meter reader app and where to get it.</p>
        </div>
        <div class="space-y-4">
            @if($isSuper)
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    @include('settings.partials.toggle-row', [
                        'action' => route('settings.mobile-maintenance'),
                        'name' => 'mobile_app_maintenance',
                        'checked' => $mobileAppMaintenance,
                        'title' => 'Reader app maintenance',
                        'text' => 'Readers can still sign in, but reading and payment calls are refused with a maintenance message. The office website stays open.',
                        'state' => $mobileAppMaintenance
                            ? 'On: the reader app is in maintenance.'
                            : 'Off: the reader app can sign in and submit readings.',
                        'badgeOn' => 'In maintenance', 'badgeOff' => 'Open',
                        'toneOn' => 'amber', 'toneOff' => 'green',
                        'disabled' => $systemOff,
                        'note' => $systemOff
                            ? 'The system is disabled, so the reader app is held in maintenance automatically. It goes back to its previous state when you turn the system on.'
                            : null,
                        'confirmOn' => ['title' => 'Put the reader app in maintenance?', 'message' => 'Readers can sign in but cannot submit readings or payments until you turn this off.', 'yes' => 'Yes, start maintenance', 'tone' => 'amber'],
                        'confirmOff' => ['title' => 'Open the reader app again?', 'message' => 'Readers can submit readings and payments again.', 'yes' => 'Yes, open it'],
                    ])
                </div>
            @endif
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @include('settings.partials.link-card', [
                    'href' => route('settings.mobile-app'),
                    'icon' => 'fa-mobile-alt',
                    'tone' => 'teal',
                    'title' => 'Download Mobile App',
                    'text' => 'Reader APK for admins. The Super Admin uploads new versions.',
                    'stat' => $mobileRelease['version'] ? 'Version '.$mobileRelease['version'] : 'No APK uploaded yet',
                    'statIcon' => 'fa-tag',
                ])
            </div>
        </div>
    </section>

    {{-- Billing and rates --}}
    <section id="sec-billing" data-section class="mb-10 scroll-mt-6">
        <div class="mb-3">
            <h2 class="text-base font-semibold text-gray-900">Billing & rates</h2>
            <p class="text-sm text-gray-500">Tariffs and when customers are billed.</p>
                        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @include('settings.partials.link-card', [
                'href' => route('settings.rates.index'),
                'icon' => 'fa-calculator',
                'tone' => 'purple',
                'title' => 'Rate Management',
                'text' => 'Unit ranges and billing charges.',
                'stat' => \App\Models\Rate::active()->count().' active rates',
                'statIcon' => 'fa-chart-line',
            ])
            @if($isSuper)
                @include('settings.partials.link-card', [
                    'href' => route('settings.billing.index'),
                    'icon' => 'fa-calendar-alt',
                    'tone' => 'orange',
                    'title' => 'Billing Settings',
                    'text' => 'Billing day and auto-billing for each customer.',
                    'stat' => \App\Models\Customer::where('auto_billing_enabled', true)->count().' auto-enabled',
                    'statIcon' => 'fa-clock',
                ])
                @include('settings.partials.link-card', [
                    'href' => route('settings.system-billing'),
                    'icon' => 'fa-sliders-h',
                    'tone' => 'indigo',
                    'title' => 'System Billing',
                    'text' => 'Default billing configuration for new customers.',
                    'stat' => 'Default billing day: '.$defaultDay.$suffix,
                    'statIcon' => 'fa-calendar-day',
                ])
            @endif
                    </div>
    </section>

    {{-- Maps (Super Admin) --}}
    @if($isSuper)
        <section id="sec-maps" data-section class="mb-10 scroll-mt-6">
            <div class="mb-3">
                <h2 class="text-base font-semibold text-gray-900">Maps</h2>
                <p class="text-sm text-gray-500">Used by the meter map, meter location, and meter details pages.</p>
            </div>
            <form method="POST" action="{{ route('settings.map-key') }}" x-data="{ show: false }" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PUT')
                <div class="flex flex-wrap items-center gap-2">
                    <label for="google_maps_api_key" class="text-sm font-semibold text-gray-900">Google Maps API key</label>
                    @if($mapKeySaved)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                            <i class="fas fa-check text-[10px]"></i>Key saved
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20">
                            <i class="fas fa-exclamation text-[10px]"></i>No key yet
                        </span>
                    @endif
                </div>
                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start">
                    <div class="flex-1">
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="google_maps_api_key" id="google_maps_api_key" autocomplete="off"
                                   placeholder="{{ $mapKeySaved ? 'Saved key is hidden. Type a new one to replace it.' : 'Paste the API key' }}"
                                   class="w-full rounded-lg border-gray-300 pr-10 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <button type="button" @click="show = !show" class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1.5 text-gray-400 hover:text-gray-600"
                                    :aria-label="show ? 'Hide key' : 'Show key'">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                        </div>
                        @error('google_maps_api_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-gray-500">Leave the box blank to keep the saved key.</p>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 focus-visible:ring-offset-2">
                        <i class="fas fa-save text-xs"></i>Save key
                    </button>
                </div>
            </form>
        </section>
    @endif

    {{-- Customer data: divisions and customer types --}}
    <section id="customer-data" data-section class="mb-10 scroll-mt-6">
        <div class="mb-3">
            <h2 class="text-base font-semibold text-gray-900">Customer data</h2>
            <p class="text-sm text-gray-500">Divisions and customer types, each with a short reference ID.</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex gap-1 overflow-x-auto overflow-y-hidden border-b border-gray-200 px-2 sm:px-4" role="tablist" aria-label="Customer data">
                <button type="button" role="tab" id="tab-divisions" :aria-selected="tab === 'divisions'" aria-controls="panel-divisions" @click="setTab('divisions')"
                        :class="tab === 'divisions' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="-mb-px inline-flex shrink-0 items-center gap-2 border-b-2 px-3 py-3.5 text-sm font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                    <i class="fas fa-map-marked-alt"></i>Divisions
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $divisions->total() }}</span>
                </button>
                <button type="button" role="tab" id="tab-customer-types" :aria-selected="tab === 'customer-types'" aria-controls="panel-customer-types" @click="setTab('customer-types')"
                        :class="tab === 'customer-types' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="-mb-px inline-flex shrink-0 items-center gap-2 border-b-2 px-3 py-3.5 text-sm font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                    <i class="fas fa-tags"></i>Customer types
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $customerTypes->total() }}</span>
                </button>
            </div>
            
            @include('settings.partials.reference-table', [
                'kind' => 'division',
                'tabKey' => 'divisions',
                'items' => $divisions,
                'label' => 'Division',
                'plural' => 'divisions',
                'icon' => 'fa-map-marked-alt',
                'emptyHelp' => 'Add one to group customers by area.',
                'destroyRoute' => 'settings.divisions.destroy',
                'deleteWarning' => 'They will be left with no division, and any reader routes for it are removed.',
            ])
            @include('settings.partials.reference-table', [
                'kind' => 'customer-type',
                'tabKey' => 'customer-types',
                'items' => $customerTypes,
                'label' => 'Customer type',
                'plural' => 'customer types',
                'icon' => 'fa-tags',
                'emptyHelp' => 'Add one to group customers by category.',
                'destroyRoute' => 'settings.customer-types.destroy',
                'deleteWarning' => 'They will be left with no customer type, and the customer type decides which tariff a customer is billed on.',
            ])
        </div>
    </section>

    {{-- Add / edit a division or customer type --}}
    <div x-show="modal" x-cloak x-transition.opacity
         class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 p-4 sm:items-center"
         @click.self="closeModal()" role="dialog" aria-modal="true" :aria-label="modalTitle">
        <div class="my-8 w-full max-w-md rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="flex items-center gap-3 text-lg font-semibold text-gray-900">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <i class="fas" :class="modal === 'division' ? 'fa-map-marked-alt' : 'fa-tags'"></i>
                    </span>
                    <span x-text="modalTitle"></span>
                </h3>
                <button type="button" @click="closeModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            
            <form method="POST" :action="actionUrl" @submit="saving = true" class="px-6 py-5">
                @csrf
                <input type="hidden" name="_form" :value="modal">
                <input type="hidden" name="_edit_id" :value="mode === 'edit' ? form.id : ''">
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                <div class="mb-4">
                    <label for="ref_name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="ref_name" name="name" x-model="form.name" x-ref="nameInput" required maxlength="255"
                           :class="errors.name ? 'border-red-400' : 'border-gray-300'"
                           class="w-full rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                    <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600"></p>
                </div>
                
                <div class="mb-4">
                    <label for="ref_custom_id" class="mb-1 flex items-center justify-between text-sm font-medium text-gray-700">
                        <span>Reference ID</span>
                        <span class="text-xs font-normal text-gray-400"><span x-text="form.custom_id.length"></span>/10</span>
                    </label>
                    <input type="text" id="ref_custom_id" name="custom_id" x-model="form.custom_id" required maxlength="10" autocomplete="off"
                           @input="form.custom_id = form.custom_id.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                           :class="errors.custom_id ? 'border-red-400' : 'border-gray-300'"
                           class="w-full rounded-lg font-mono text-sm uppercase focus:border-blue-500 focus:ring-blue-500">
                    <p x-show="errors.custom_id" x-text="errors.custom_id" class="mt-1 text-sm text-red-600"></p>
                    <p x-show="!errors.custom_id" class="mt-1 text-xs text-gray-500">A short code. Letters and numbers only, up to 10.</p>
                </div>
                
                <div class="mb-4">
                    <label for="ref_description" class="mb-1 flex items-center justify-between text-sm font-medium text-gray-700">
                        <span>Description <span class="font-normal text-gray-400">(optional)</span></span>
                        <span class="text-xs font-normal text-gray-400"><span x-text="form.description.length"></span>/500</span>
                    </label>
                    <textarea id="ref_description" name="description" rows="3" maxlength="500" x-model="form.description"
                              :class="errors.description ? 'border-red-400' : 'border-gray-300'"
                              class="w-full resize-none rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <p x-show="errors.description" x-text="errors.description" class="mt-1 text-sm text-red-600"></p>
                </div>
                
                <label class="mb-5 flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-4 py-3">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-800">Active</span>
                        <span class="block text-xs text-gray-500">Inactive items stay on record but are not offered for new customers.</span>
                    </span>
                </label>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <button type="button" @click="closeModal()" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Cancel</button>
                    <button type="submit" :disabled="saving"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        <span x-text="saving ? 'Saving...' : (mode === 'edit' ? 'Save changes' : 'Add')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Yes / No question for switches and deletes --}}
    <div x-show="confirmBox.open" x-cloak x-transition.opacity
         class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/50 p-4"
         @click.self="confirmNo()" role="alertdialog" aria-modal="true" aria-labelledby="settings-confirm-title">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                      :class="confirmBox.tone === 'red' ? 'bg-red-100 text-red-600' : (confirmBox.tone === 'amber' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600')">
                    <i class="fas" :class="confirmBox.tone === 'red' ? 'fa-trash' : (confirmBox.tone === 'amber' ? 'fa-exclamation-triangle' : 'fa-question')"></i>
                </span>
                <div class="min-w-0">
                    <h2 id="settings-confirm-title" class="text-lg font-semibold text-gray-900" x-text="confirmBox.title"></h2>
                    <p class="mt-1 text-sm text-gray-600" x-text="confirmBox.message"></p>
                </div>
                </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-ref="noBtn" @click="confirmNo()" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Cancel</button>
                <button type="button" @click="confirmYes()" x-text="confirmBox.yes"
                        :class="confirmBox.tone === 'red' ? 'bg-red-600 hover:bg-red-700' : (confirmBox.tone === 'amber' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700')"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-white"></button>
                </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('settingsPage', (boot) => ({
        // Customer data tabs and the add / edit form
        tab: boot.tab,
        modal: boot.modal,            // null, 'division' or 'customer-type'
        mode: boot.mode,              // 'create' or 'edit'
        form: boot.form,
        errors: boot.errors,
        urls: boot.urls,
        saving: false,
        q: { 'divisions': '', 'customer-types': '' },
        visible: { 'divisions': boot.counts['divisions'], 'customer-types': boot.counts['customer-types'] },

        // Section jump bar
        active: '',
        holdUntil: 0,

        // Yes / No question
        confirmBox: { open: false, title: '', message: '', yes: 'Yes', tone: 'blue', apply: null, cancel: null },

        init() {
            const hash = window.location.hash.replace('#', '');
            if (hash === 'divisions' || hash === 'customer-types') {
                this.tab = hash;
                this.active = 'customer-data';
                this.$nextTick(() => document.getElementById('customer-data')?.scrollIntoView());
            }

            this.$watch('modal', (open) => document.body.classList.toggle('overflow-hidden', !!open));
            if (this.modal) {
                document.body.classList.add('overflow-hidden');
                this.$nextTick(() => this.$refs.nameInput?.focus());
            }

            // Highlight the last section whose top has passed the line near the top of the screen.
            let queued = false;
            const spy = () => {
                queued = false;
                if (Date.now() < this.holdUntil) return;
                let current = null;
                document.querySelectorAll('[data-section]').forEach((el) => {
                    if (el.getBoundingClientRect().top <= 120) current = el.id;
                });
                if (current) this.active = current;
            };
            window.addEventListener('scroll', () => {
                if (!queued) { queued = true; requestAnimationFrame(spy); }
            }, { passive: true });
            this.active = this.active || document.querySelector('[data-section]')?.id || '';
            spy();
        },

        go(id) {
            this.active = id;
            this.holdUntil = Date.now() + 900; // keep the clicked pill lit while the page scrolls there
            document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        setTab(name) {
            this.tab = name;
            history.replaceState(null, '', '#' + name);
        },

        // Add / edit
        get modalTitle() {
            const what = this.modal === 'division' ? 'division' : 'customer type';
            return (this.mode === 'edit' ? 'Edit ' : 'Add ') + what;
        },
        get actionUrl() {
            const u = this.urls[this.modal];
            if (!u) return '';
            return this.mode === 'edit' ? u.update.replace(':id', this.form.id) : u.store;
        },
        openCreate(kind) {
            this.mode = 'create';
            this.form = { id: '', name: '', custom_id: '', description: '', is_active: true };
            this.openModal(kind);
        },
        openEdit(kind, data) {
            this.mode = 'edit';
            this.form = {
                id: data.id,
                name: data.name || '',
                custom_id: data.customId || '',
                description: data.description || '',
                is_active: data.isActive === 'true',
            };
            this.openModal(kind);
        },
        openModal(kind) {
            this.errors = { name: '', custom_id: '', description: '' };
            this.saving = false;
            this.tab = kind === 'division' ? 'divisions' : 'customer-types';
            this.modal = kind;
            this.$nextTick(() => this.$refs.nameInput?.focus());
        },
        closeModal() {
            this.modal = null;
        },

        // Search inside the table on this page
        filter(key) {
            const needle = this.q[key].trim().toLowerCase();
            let shown = 0;
            document.querySelectorAll('[data-kind="' + key + '"] tr[data-row]').forEach((row) => {
                const match = !needle || row.dataset.search.includes(needle);
                row.classList.toggle('hidden', !match);
                if (match) shown++;
            });
            this.visible[key] = shown;
        },

        // Yes / No question
        ask(opts) {
            this.confirmBox = {
                open: true,
                title: opts.title,
                message: opts.message || '',
                yes: opts.yes || 'Yes',
                tone: opts.tone || 'blue',
                apply: opts.apply || null,
                cancel: opts.cancel || null,
            };
            this.$nextTick(() => this.$refs.noBtn?.focus());
        },
        confirmYes() {
            const apply = this.confirmBox.apply;
            this.confirmBox.open = false;
            if (apply) apply();
        },
        confirmNo() {
            const cancel = this.confirmBox.cancel;
            this.confirmBox.open = false;
            if (cancel) cancel();
        },
        escapePressed() {
            if (this.confirmBox.open) return this.confirmNo();
            if (this.modal) this.closeModal();
        },

        // A switch was flipped: put it back, ask, and only keep the change on Yes.
        toggleAsk(event, cfg) {
            const box = event.target;
            const want = box.checked;
            const form = box.form;
            const c = want ? cfg.on : cfg.off;
            box.checked = !want;
            this.ask({
                title: c.title,
                message: c.message,
                yes: c.yes || 'Yes',
                tone: c.tone || 'blue',
                apply: () => {
                    box.checked = want;
                    form.classList.add('opacity-60', 'pointer-events-none');
                    form.requestSubmit();
                },
            });
        },

        // Delete with a question that names the record and any customers using it
        askDelete(event) {
            const btn = event.currentTarget;
            const d = btn.dataset;
            const form = btn.form;
            const count = parseInt(d.count || '0', 10);
            this.ask({
                title: 'Delete ' + d.label + ' "' + d.name + '"?',
                message: count > 0
                    ? count + ' customer(s) use it. ' + d.warning + ' This cannot be undone.'
                    : 'No customers use it. This cannot be undone.',
                yes: 'Yes, delete it',
                tone: 'red',
                apply: () => form.requestSubmit(),
            });
        },
    }));
});
</script>
@endsection 
