<nav x-data="{ open: false }" class="bg-gradient-to-r from-blue-600 to-blue-800 border-b border-blue-700 shadow-lg">
    <!-- Primary Navigation Menu -->
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-white bg-opacity-20 p-1.5">
                            <img src="{{ asset('images/wassip-logo-only.png') }}" alt="WASSIP Logo" class="h-full w-full object-contain">
                        </div>
                        <div class="text-white">
                            <div class="font-bold text-lg leading-tight">DN WASSIP</div>
                            <div class="text-xs text-blue-200">Water Supply & Management</div>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex sm:items-center sm:h-16">
                    <!-- Dashboard -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" 
                                class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 {{ request()->routeIs('dashboard') ? 'bg-white bg-opacity-20 text-white' : '' }} flex items-center h-16">
                        <i class="fas fa-tachometer-alt mr-2"></i>{{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Customer Management Dropdown -->
                    <div class="relative flex items-center h-16" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-3 py-2 h-10 text-sm font-medium text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 rounded-md transition duration-150 ease-in-out {{ request()->routeIs('customers.*', 'guarantors.*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-users mr-2"></i>
                            <span>Customer Management</span>
                            <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 top-full">
                            <div class="py-1">
                                <a href="{{ route('customers.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-user-circle mr-3 text-blue-600"></i>Customers
                                </a>
                                <a href="{{ route('guarantors.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-handshake mr-3 text-green-600"></i>Guarantors
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Meter & Readings Dropdown -->
                    <div class="relative flex items-center h-16" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-3 py-2 h-10 text-sm font-medium text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 rounded-md transition duration-150 ease-in-out {{ request()->routeIs('meters.*', 'readings.*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            <span>Meter & Readings</span>
                            <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 top-full">
                            <div class="py-1">
                                <a href="{{ route('meters.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-gauge mr-3 text-purple-600"></i>Water Meters
                                </a>
                                <a href="{{ route('readings.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-clipboard-list mr-3 text-cyan-600"></i>Meter Readings
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="{{ route('readings.bulk') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-list mr-3 text-orange-600"></i>Bulk Entry
                                </a>
                                <a href="{{ route('readings.schedule') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-calendar-alt mr-3 text-indigo-600"></i>Reading Schedule
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Billing & Finance Dropdown -->
                    <div class="relative flex items-center h-16" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-3 py-2 h-10 text-sm font-medium text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 rounded-md transition duration-150 ease-in-out {{ request()->routeIs('bills.*', 'settings.rates.*', 'reports.*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            <span>Billing & Finance</span>
                            <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 top-full">
                            <div class="py-1">
                                <a href="{{ route('bills.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-file-invoice mr-3 text-green-600"></i>Bills Management
                                </a>
                                <a href="{{ route('settings.rates.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-dollar-sign mr-3 text-yellow-600"></i>Rate Management
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Reports</div>
                                <a href="{{ route('reports.consumption') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-chart-line mr-3 text-purple-600"></i>Consumption Report
                                </a>
                                <a href="{{ route('reports.revenue') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-chart-bar mr-3 text-indigo-600"></i>Revenue Report
                                </a>
                                <a href="{{ route('reports.overdue') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-exclamation-triangle mr-3 text-red-600"></i>Overdue Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- System Management Dropdown -->
                    <div class="relative flex items-center h-16" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-3 py-2 h-10 text-sm font-medium text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 rounded-md transition duration-150 ease-in-out {{ request()->routeIs('users.*', 'settings.*', 'activity-logs.*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-cogs mr-2"></i>
                            <span>System</span>
                            <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 top-full">
                            <div class="py-1">
                                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-users-cog mr-3 text-blue-600"></i>User Management
                                </a>
                                <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-sliders-h mr-3 text-gray-600"></i>System Settings
                                </a>
                                <a href="{{ route('activity-logs.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-history mr-3 text-orange-600"></i>Activity Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-white bg-opacity-10 hover:bg-opacity-20 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <div class="flex items-center space-x-2">
                                <div class="h-8 w-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <div class="font-medium text-sm">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-blue-200">{{ ucfirst(Auth::user()->role) }}</div>
                                </div>
                            </div>
                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                            <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                            <div class="text-xs text-blue-600 font-medium mt-1">{{ ucfirst(Auth::user()->role) }} User</div>
                        </div>
                        
                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center">
                            <i class="fas fa-user-edit mr-3 text-gray-500"></i>
                            {{ __('Profile Settings') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('dashboard')" class="flex items-center">
                            <i class="fas fa-tachometer-alt mr-3 text-blue-500"></i>
                            {{ __('Dashboard') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="flex items-center text-red-600 hover:text-red-700 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-3"></i>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 focus:outline-none focus:bg-white focus:bg-opacity-10 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-blue-700">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10">
                <i class="fas fa-tachometer-alt mr-2"></i>{{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            <!-- Customer Management -->
            <div class="px-3 py-2 text-xs font-semibold text-blue-200 uppercase tracking-wide">Customer Management</div>
            <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-user-circle mr-2"></i>{{ __('Customers') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('guarantors.index')" :active="request()->routeIs('guarantors.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-handshake mr-2"></i>{{ __('Guarantors') }}
            </x-responsive-nav-link>
            
            <!-- Meter & Readings -->
            <div class="px-3 py-2 text-xs font-semibold text-blue-200 uppercase tracking-wide mt-4">Meter & Readings</div>
            <x-responsive-nav-link :href="route('meters.index')" :active="request()->routeIs('meters.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-gauge mr-2"></i>{{ __('Water Meters') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('readings.index')" :active="request()->routeIs('readings.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-clipboard-list mr-2"></i>{{ __('Meter Readings') }}
            </x-responsive-nav-link>
            
            <!-- Billing & Finance -->
            <div class="px-3 py-2 text-xs font-semibold text-blue-200 uppercase tracking-wide mt-4">Billing & Finance</div>
            <x-responsive-nav-link :href="route('bills.index')" :active="request()->routeIs('bills.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-file-invoice mr-2"></i>{{ __('Bills') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.rates.index')" :active="request()->routeIs('settings.rates.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-dollar-sign mr-2"></i>{{ __('Rates') }}
            </x-responsive-nav-link>
            
            <!-- System Management -->
            <div class="px-3 py-2 text-xs font-semibold text-blue-200 uppercase tracking-wide mt-4">System Management</div>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-users-cog mr-2"></i>{{ __('Users') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-sliders-h mr-2"></i>{{ __('Settings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')"
                                   class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10 ml-4">
                <i class="fas fa-history mr-2"></i>{{ __('Activity Logs') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-blue-600">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-blue-200">{{ Auth::user()->email }}</div>
                <div class="text-xs text-blue-300 mt-1">{{ ucfirst(Auth::user()->role) }} User</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')"
                                       class="text-white hover:text-blue-200 hover:bg-white hover:bg-opacity-10">
                    <i class="fas fa-user-edit mr-2"></i>{{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();"
                            class="text-red-200 hover:text-red-100 hover:bg-red-600 hover:bg-opacity-20">
                        <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
