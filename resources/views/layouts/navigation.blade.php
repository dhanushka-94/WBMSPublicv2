@php
    $isSystemEnabled = \App\Helpers\SystemConfig::isSystemEnabled();
    $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();
    $canControlSystem = $isSuperAdmin;
    $officeReports = [
        'customer-register' => ['Customer Register', 'fa-address-book', 'text-blue-600'],
        'contacts' => ['Contact List', 'fa-address-card', 'text-cyan-600'],
        'new-connections' => ['New Connections', 'fa-plug', 'text-green-600'],
        'disconnected' => ['Disconnected Customers', 'fa-unlink', 'text-red-600'],
        'usage-alerts' => ['Usage Alerts', 'fa-bell', 'text-amber-600'],
        'meter-faults' => ['Meter Faults', 'fa-tools', 'text-orange-600'],
        'billing-summary' => ['Billing Summary', 'fa-file-invoice-dollar', 'text-emerald-600'],
        'daily-collection' => ['Daily Collection', 'fa-coins', 'text-yellow-600'],
        'monthly-collection' => ['Monthly Collection', 'fa-calendar-alt', 'text-indigo-600'],
        'collection-efficiency' => ['Collection Efficiency', 'fa-percentage', 'text-purple-600'],
        'age-analysis' => ['Age Analysis', 'fa-hourglass-half', 'text-rose-600'],
    ];
    $onCustomers = request()->routeIs('customers.*', 'guarantors.*');
    $onMeters = request()->routeIs('meters.*', 'readings.*');
    $onBilling = request()->routeIs('bills.*', 'payments.*', 'settings.rates.*');
    $onReports = request()->routeIs('reports.*');
    $onSms = request()->routeIs('sms.*');
    $onSystem = request()->routeIs('users.*', 'reader-routes.*', 'activity-logs.*', 'mobile-api.*', 'about', 'changelog')
        || (request()->routeIs('settings.*') && ! request()->routeIs('settings.rates.*'));
    $mobileSection = match (true) {
        $onCustomers => 'customers',
        $onMeters => 'meters',
        $onBilling => 'billing',
        $onReports => 'reports',
        $onSms => 'sms',
        $onSystem => 'system',
        default => '',
    };
    $bar = 'inline-flex items-center gap-2 whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium text-blue-50 hover:bg-white/15 hover:text-white';
    $barOn = 'inline-flex items-center gap-2 whitespace-nowrap rounded-md bg-white/20 px-3 py-2 text-sm font-medium text-white';
    $link = 'flex items-center gap-3 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800';
    $mobileLink = 'flex items-center gap-3 rounded-md px-3 py-2.5 text-sm text-blue-50 hover:bg-white/10';
    $mobileOn = 'flex items-center gap-3 rounded-md bg-white/15 px-3 py-2.5 text-sm font-medium text-white';
@endphp

<nav x-data="{ open: false, menu: null }" class="bg-blue-800 shadow-lg">
    <div class="bg-gradient-to-r from-blue-700 to-blue-900">
        <div class="flex h-14 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/20 p-1.5">
                    <img src="{{ asset('images/aquabill-logo-color.png') }}" alt="AquaBill Logo" class="h-full w-full object-contain">
                </span>
                <span class="min-w-0 text-white">
                    <span class="block truncate text-base font-bold leading-tight sm:text-lg">AquaBill by olexto</span>
                    <span class="hidden text-xs text-blue-200 sm:block">Smart Water Management</span>
                </span>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                @if($canControlSystem)
                    <div class="hidden items-center gap-2 md:flex">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-100">
                            <span class="h-2 w-2 rounded-full {{ $isSystemEnabled ? 'bg-green-400' : 'bg-red-400' }}"></span>
                            {{ $isSystemEnabled ? 'Active' : 'Disabled' }}
                        </span>
                        <button type="button" onclick="toggleSystemStatus()"
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium text-white {{ $isSystemEnabled ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                            <i class="fas fa-power-off"></i>
                            {{ $isSystemEnabled ? 'Disable' : 'Enable' }}
                        </button>
                        <a href="{{ route('system.status') }}" class="rounded p-1.5 text-blue-100 hover:bg-white/10 hover:text-white" title="System status">
                            <i class="fas fa-cogs"></i>
                        </a>
                    </div>
                @endif

                <div class="hidden lg:block">
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-2.5 py-1.5 text-sm text-white hover:bg-white/20">
                                <img src="{{ Auth::user()->profile_photo_url }}" alt="" class="h-8 w-8 rounded-full object-cover">
                                <span class="hidden text-left xl:block">
                                    <span class="block text-sm font-medium leading-tight">{{ Auth::user()->name }}</span>
                                    <span class="block text-xs text-blue-200">{{ Auth::user()->role_display }}</span>
                                </span>
                                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="border-b border-gray-100 px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                                <div class="mt-1 text-xs font-medium text-blue-600">{{ Auth::user()->role_display }} User</div>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="flex items-center">
                                <i class="fas fa-user-edit mr-3 text-gray-500"></i>{{ __('Profile Settings') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('dashboard')" class="flex items-center">
                                <i class="fas fa-tachometer-alt mr-3 text-blue-500"></i>{{ __('Dashboard') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('about')" class="flex items-center">
                                <i class="fas fa-info-circle mr-3 text-green-500"></i>{{ __('About System') }}
                            </x-dropdown-link>
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="flex items-center text-red-600 hover:bg-red-50 hover:text-red-700">
                                    <i class="fas fa-sign-out-alt mr-3"></i>{{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <button type="button" @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-white hover:bg-white/10 lg:hidden" aria-label="Open menu">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="hidden border-t border-white/10 bg-blue-700 lg:block">
        <div class="flex flex-wrap items-center gap-1 px-4 py-1.5 sm:px-6 lg:px-8" @click.outside="menu = null">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $barOn : $bar }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <div class="relative">
                <button type="button" @click="menu = menu === 'customers' ? null : 'customers'" class="{{ $onCustomers ? $barOn : $bar }}">
                    <i class="fas fa-users"></i> Customers
                    <i class="fas fa-chevron-down text-[10px] opacity-80"></i>
                </button>
                <div x-show="menu === 'customers'" x-cloak class="absolute left-0 top-full z-50 mt-1 w-56 rounded-lg bg-white p-1.5 shadow-lg ring-1 ring-black/5" style="display: none;">
                    <a href="{{ route('customers.index') }}" class="{{ $link }}"><i class="fas fa-user-circle w-4 text-blue-600"></i>Customers</a>
                    <a href="{{ route('guarantors.index') }}" class="{{ $link }}"><i class="fas fa-handshake w-4 text-green-600"></i>Guarantors</a>
                </div>
            </div>

            <div class="relative">
                <button type="button" @click="menu = menu === 'meters' ? null : 'meters'" class="{{ $onMeters ? $barOn : $bar }}">
                    <i class="fas fa-gauge"></i> Meters
                    <i class="fas fa-chevron-down text-[10px] opacity-80"></i>
                </button>
                <div x-show="menu === 'meters'" x-cloak class="absolute left-0 top-full z-50 mt-1 w-60 rounded-lg bg-white p-1.5 shadow-lg ring-1 ring-black/5" style="display: none;">
                    <a href="{{ route('meters.index') }}" class="{{ $link }}"><i class="fas fa-gauge w-4 text-purple-600"></i>Water Meters</a>
                    <a href="{{ route('readings.index') }}" class="{{ $link }}"><i class="fas fa-clipboard-list w-4 text-cyan-600"></i>Meter Readings</a>
                    <div class="my-1 border-t border-gray-100"></div>
                    <a href="{{ route('readings.bulk') }}" class="{{ $link }}"><i class="fas fa-list w-4 text-orange-600"></i>Bulk Entry</a>
                    <a href="{{ route('readings.initial') }}" class="{{ $link }}"><i class="fas fa-tachometer-alt w-4 text-amber-600"></i>Initial Readings</a>
                    <a href="{{ route('readings.schedule') }}" class="{{ $link }}"><i class="fas fa-calendar-alt w-4 text-indigo-600"></i>Reading Schedule</a>
                    <a href="{{ route('meters.map') }}" class="{{ $link }}"><i class="fas fa-map-marked-alt w-4 text-emerald-600"></i>Meter Map</a>
                </div>
            </div>

            <div class="relative">
                <button type="button" @click="menu = menu === 'billing' ? null : 'billing'" class="{{ $onBilling ? $barOn : $bar }}">
                    <i class="fas fa-file-invoice-dollar"></i> Billing
                    <i class="fas fa-chevron-down text-[10px] opacity-80"></i>
                </button>
                <div x-show="menu === 'billing'" x-cloak class="absolute left-0 top-full z-50 mt-1 w-56 rounded-lg bg-white p-1.5 shadow-lg ring-1 ring-black/5" style="display: none;">
                    <a href="{{ route('bills.index') }}" class="{{ $link }}"><i class="fas fa-file-invoice w-4 text-green-600"></i>Bills</a>
                    <a href="{{ route('payments.collect') }}" class="{{ $link }}"><i class="fas fa-hand-holding-usd w-4 text-emerald-600"></i>Take Payment</a>
                    <a href="{{ route('settings.rates.index') }}" class="{{ $link }}"><i class="fas fa-dollar-sign w-4 text-yellow-600"></i>Rates</a>
                </div>
            </div>

            <div class="relative">
                <button type="button" @click="menu = menu === 'reports' ? null : 'reports'" class="{{ $onReports ? $barOn : $bar }}">
                    <i class="fas fa-chart-bar"></i> Reports
                    <i class="fas fa-chevron-down text-[10px] opacity-80"></i>
                </button>
                <div x-show="menu === 'reports'" x-cloak class="absolute left-0 top-full z-50 mt-1 w-[34rem] max-w-[calc(100vw-2rem)] rounded-lg bg-white p-2 shadow-lg ring-1 ring-black/5" style="display: none;">
                    <div class="grid grid-cols-2 gap-x-2">
                        <div>
                            <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Connections</p>
                            <a href="{{ route('reports.monthly-active') }}" class="{{ $link }}"><i class="fas fa-users-cog w-4 text-green-600"></i>Monthly Active</a>
                            @foreach(['customer-register', 'contacts', 'new-connections', 'disconnected'] as $key)
                                @php [$label, $icon, $color] = $officeReports[$key]; @endphp
                                <a href="{{ route('reports.office', $key) }}" class="{{ $link }}"><i class="fas {{ $icon }} w-4 {{ $color }}"></i>{{ $label }}</a>
                            @endforeach
                            <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Usage</p>
                            <a href="{{ route('reports.consumption') }}" class="{{ $link }}"><i class="fas fa-chart-line w-4 text-purple-600"></i>Consumption</a>
                            <a href="{{ route('reports.reader-routes') }}" class="{{ $link }}"><i class="fas fa-clipboard-check w-4 text-blue-600"></i>Reader Routes</a>
                            <a href="{{ route('reports.reader-statistics') }}" class="{{ $link }}"><i class="fas fa-chart-pie w-4 text-cyan-600"></i>Reader Statistics</a>
                            @foreach(['usage-alerts', 'meter-faults'] as $key)
                                @php [$label, $icon, $color] = $officeReports[$key]; @endphp
                                <a href="{{ route('reports.office', $key) }}" class="{{ $link }}"><i class="fas {{ $icon }} w-4 {{ $color }}"></i>{{ $label }}</a>
                            @endforeach
                        </div>
                        <div>
                            <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Collections</p>
                            <a href="{{ route('reports.revenue') }}" class="{{ $link }}"><i class="fas fa-chart-bar w-4 text-indigo-600"></i>Revenue</a>
                            <a href="{{ route('reports.overdue') }}" class="{{ $link }}"><i class="fas fa-exclamation-triangle w-4 text-red-600"></i>Overdue</a>
                            @foreach(['billing-summary', 'daily-collection', 'monthly-collection', 'collection-efficiency', 'age-analysis'] as $key)
                                @php [$label, $icon, $color] = $officeReports[$key]; @endphp
                                <a href="{{ route('reports.office', $key) }}" class="{{ $link }}"><i class="fas {{ $icon }} w-4 {{ $color }}"></i>{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <button type="button" @click="menu = menu === 'sms' ? null : 'sms'" class="{{ $onSms ? $barOn : $bar }}">
                    <i class="fas fa-sms"></i> SMS
                    <i class="fas fa-chevron-down text-[10px] opacity-80"></i>
                </button>
                <div x-show="menu === 'sms'" x-cloak class="absolute left-0 top-full z-50 mt-1 w-56 rounded-lg bg-white p-1.5 shadow-lg ring-1 ring-black/5" style="display: none;">
                    <a href="{{ route('sms.statistics') }}" class="{{ $link }}"><i class="fas fa-chart-bar w-4 text-green-600"></i>SMS Dashboard</a>
                    <a href="{{ route('sms.templates') }}" class="{{ $link }}"><i class="fas fa-file-alt w-4 text-blue-600"></i>SMS Templates</a>
                    <a href="{{ route('sms.bulk') }}" class="{{ $link }}"><i class="fas fa-paper-plane w-4 text-blue-600"></i>Send Custom SMS</a>
                    <a href="{{ route('sms.index') }}" class="{{ $link }}"><i class="fas fa-list w-4 text-blue-600"></i>SMS Logs</a>
                    @if($isSuperAdmin)
                    <a href="{{ route('sms.settings') }}" class="{{ $link }}"><i class="fas fa-cog w-4 text-gray-600"></i>SMS Settings</a>
                    @endif
                </div>
            </div>

            <div class="relative">
                <button type="button" @click="menu = menu === 'system' ? null : 'system'" class="{{ $onSystem ? $barOn : $bar }}">
                    <i class="fas fa-cogs"></i> System
                    <i class="fas fa-chevron-down text-[10px] opacity-80"></i>
                </button>
                <div x-show="menu === 'system'" x-cloak class="absolute right-0 top-full z-50 mt-1 w-56 rounded-lg bg-white p-1.5 shadow-lg ring-1 ring-black/5" style="display: none;">
                    <a href="{{ route('users.index') }}" class="{{ $link }}"><i class="fas fa-users-cog w-4 text-blue-600"></i>Users</a>
                    <a href="{{ route('reader-routes.index') }}" class="{{ $link }}"><i class="fas fa-route w-4 text-indigo-600"></i>Reader Routes</a>
                    @if($isSuperAdmin)
                    <a href="{{ route('settings.index') }}" class="{{ $link }}"><i class="fas fa-sliders-h w-4 text-gray-600"></i>Settings</a>
                    @endif
                    <a href="{{ route('activity-logs.index') }}" class="{{ $link }}"><i class="fas fa-history w-4 text-orange-600"></i>Activity Logs</a>
                    @if($isSuperAdmin)
                        <a href="{{ route('mobile-api.index') }}" class="{{ $link }}"><i class="fas fa-mobile-alt w-4 text-cyan-600"></i>Mobile API Docs</a>
                    @endif
                    <div class="my-1 border-t border-gray-100"></div>
                    <a href="{{ route('about') }}" class="{{ $link }}"><i class="fas fa-info-circle w-4 text-green-600"></i>About Us</a>
                    <a href="{{ route('changelog') }}" class="{{ $link }}"><i class="fas fa-list-ul w-4 text-blue-600"></i>Changelog</a>
                </div>
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak class="max-h-[calc(100vh-3.5rem)] overflow-y-auto border-t border-white/10 bg-blue-800 lg:hidden" style="display: none;" x-data="{ section: @js($mobileSection) }">
        <div class="space-y-1 px-3 py-3">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $mobileOn : $mobileLink }}">
                <i class="fas fa-tachometer-alt w-4"></i> Dashboard
            </a>

            @foreach([
                'customers' => ['Customers', 'fa-users', $onCustomers],
                'meters' => ['Meters', 'fa-gauge', $onMeters],
                'billing' => ['Billing', 'fa-file-invoice-dollar', $onBilling],
                'reports' => ['Reports', 'fa-chart-bar', $onReports],
                'sms' => ['SMS', 'fa-sms', $onSms],
                'system' => ['System', 'fa-cogs', $onSystem],
            ] as $key => [$label, $icon, $active])
                <button type="button" @click="section = section === '{{ $key }}' ? '' : '{{ $key }}'"
                        class="flex w-full items-center justify-between rounded-md px-3 py-2.5 text-sm font-medium {{ $active ? 'bg-white/15 text-white' : 'text-blue-50 hover:bg-white/10' }}">
                    <span class="inline-flex items-center gap-3"><i class="fas {{ $icon }} w-4"></i>{{ $label }}</span>
                    <i class="fas fa-chevron-down text-[10px] transition" :class="section === '{{ $key }}' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="section === '{{ $key }}'" x-cloak class="space-y-0.5 pb-1 ps-4" style="display: none;">
                    @if($key === 'customers')
                        <a href="{{ route('customers.index') }}" class="{{ $mobileLink }}"><i class="fas fa-user-circle w-4"></i>Customers</a>
                        <a href="{{ route('guarantors.index') }}" class="{{ $mobileLink }}"><i class="fas fa-handshake w-4"></i>Guarantors</a>
                    @elseif($key === 'meters')
                        <a href="{{ route('meters.index') }}" class="{{ $mobileLink }}"><i class="fas fa-gauge w-4"></i>Water Meters</a>
                        <a href="{{ route('readings.index') }}" class="{{ $mobileLink }}"><i class="fas fa-clipboard-list w-4"></i>Meter Readings</a>
                        <a href="{{ route('readings.bulk') }}" class="{{ $mobileLink }}"><i class="fas fa-list w-4"></i>Bulk Entry</a>
                        <a href="{{ route('readings.initial') }}" class="{{ $mobileLink }}"><i class="fas fa-tachometer-alt w-4"></i>Initial Readings</a>
                        <a href="{{ route('readings.schedule') }}" class="{{ $mobileLink }}"><i class="fas fa-calendar-alt w-4"></i>Reading Schedule</a>
                        <a href="{{ route('meters.map') }}" class="{{ $mobileLink }}"><i class="fas fa-map-marked-alt w-4"></i>Meter Map</a>
                    @elseif($key === 'billing')
                        <a href="{{ route('bills.index') }}" class="{{ $mobileLink }}"><i class="fas fa-file-invoice w-4"></i>Bills</a>
                        <a href="{{ route('payments.collect') }}" class="{{ $mobileLink }}"><i class="fas fa-hand-holding-usd w-4"></i>Take Payment</a>
                        <a href="{{ route('settings.rates.index') }}" class="{{ $mobileLink }}"><i class="fas fa-dollar-sign w-4"></i>Rates</a>
                    @elseif($key === 'reports')
                        <a href="{{ route('reports.consumption') }}" class="{{ $mobileLink }}"><i class="fas fa-chart-line w-4"></i>Consumption</a>
                        <a href="{{ route('reports.revenue') }}" class="{{ $mobileLink }}"><i class="fas fa-chart-bar w-4"></i>Revenue</a>
                        <a href="{{ route('reports.overdue') }}" class="{{ $mobileLink }}"><i class="fas fa-exclamation-triangle w-4"></i>Overdue</a>
                        <a href="{{ route('reports.monthly-active') }}" class="{{ $mobileLink }}"><i class="fas fa-users-cog w-4"></i>Monthly Active</a>
                        <a href="{{ route('reports.reader-routes') }}" class="{{ $mobileLink }}"><i class="fas fa-clipboard-check w-4"></i>Reader Routes</a>
                        <a href="{{ route('reports.reader-statistics') }}" class="{{ $mobileLink }}"><i class="fas fa-chart-pie w-4"></i>Reader Statistics</a>
                        @foreach($officeReports as $reportKey => [$label, $icon])
                            <a href="{{ route('reports.office', $reportKey) }}" class="{{ $mobileLink }}"><i class="fas {{ $icon }} w-4"></i>{{ $label }}</a>
                        @endforeach
                    @elseif($key === 'sms')
                        <a href="{{ route('sms.statistics') }}" class="{{ $mobileLink }}"><i class="fas fa-chart-bar w-4"></i>SMS Dashboard</a>
                        <a href="{{ route('sms.templates') }}" class="{{ $mobileLink }}"><i class="fas fa-file-alt w-4"></i>SMS Templates</a>
                        <a href="{{ route('sms.bulk') }}" class="{{ $mobileLink }}"><i class="fas fa-paper-plane w-4"></i>Send Custom SMS</a>
                        <a href="{{ route('sms.index') }}" class="{{ $mobileLink }}"><i class="fas fa-list w-4"></i>SMS Logs</a>
                        @if($isSuperAdmin)
                        <a href="{{ route('sms.settings') }}" class="{{ $mobileLink }}"><i class="fas fa-cog w-4"></i>SMS Settings</a>
                        @endif
                    @else
                        <a href="{{ route('users.index') }}" class="{{ $mobileLink }}"><i class="fas fa-users-cog w-4"></i>Users</a>
                        <a href="{{ route('reader-routes.index') }}" class="{{ $mobileLink }}"><i class="fas fa-route w-4"></i>Reader Routes</a>
                        @if($isSuperAdmin)
                        <a href="{{ route('settings.index') }}" class="{{ $mobileLink }}"><i class="fas fa-sliders-h w-4"></i>Settings</a>
                        @endif
                        <a href="{{ route('activity-logs.index') }}" class="{{ $mobileLink }}"><i class="fas fa-history w-4"></i>Activity Logs</a>
                        @if($isSuperAdmin)
                            <a href="{{ route('mobile-api.index') }}" class="{{ $mobileLink }}"><i class="fas fa-mobile-alt w-4"></i>Mobile API Docs</a>
                        @endif
                        <a href="{{ route('about') }}" class="{{ $mobileLink }}"><i class="fas fa-info-circle w-4"></i>About Us</a>
                        <a href="{{ route('changelog') }}" class="{{ $mobileLink }}"><i class="fas fa-list-ul w-4"></i>Changelog</a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="border-t border-white/10 px-4 py-4">
            <div class="text-sm font-medium text-white">{{ Auth::user()->name }}</div>
            <div class="text-sm text-blue-200">{{ Auth::user()->email }}</div>
            <div class="mt-1 text-xs text-blue-300">{{ Auth::user()->role_display }} User</div>
            @if($canControlSystem)
                <div class="mt-3 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs text-blue-100">
                        <span class="h-2 w-2 rounded-full {{ $isSystemEnabled ? 'bg-green-400' : 'bg-red-400' }}"></span>
                        {{ $isSystemEnabled ? 'Active' : 'Disabled' }}
                    </span>
                    <button type="button" onclick="toggleSystemStatus()" class="rounded-full px-2.5 py-1 text-xs font-medium text-white {{ $isSystemEnabled ? 'bg-red-600' : 'bg-green-600' }}">
                        {{ $isSystemEnabled ? 'Disable' : 'Enable' }}
                    </button>
                </div>
            @endif
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="{{ $mobileLink }}"><i class="fas fa-user-edit w-4"></i>Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm text-red-200 hover:bg-red-600/20">
                        <i class="fas fa-sign-out-alt w-4"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleSystemStatus() {
    @if($canControlSystem)
        const isEnabled = {{ $isSystemEnabled ? 'true' : 'false' }};
        const action = isEnabled ? 'disable' : 'enable';

        if (action === 'disable') {
            const reason = prompt('Please provide a reason for disabling the system:', 'Payment overdue - System temporarily disabled');
            if (!reason) return;

            if (confirm('Are you sure you want to disable the system? This will restrict access to most features.')) {
                performToggle(action, reason);
            }
        } else if (confirm('Are you sure you want to enable the system? All features will be restored.')) {
            performToggle(action);
        }
    @endif
}

function performToggle(action, reason = null) {
    const url = action === 'enable' ? '{{ route("system.enable") }}' : '{{ route("system.disable") }}';
    const data = { _token: '{{ csrf_token() }}' };

    if (reason) {
        data.reason = reason;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => {
        alert('An error occurred while toggling system status.');
    });
}
</script>
