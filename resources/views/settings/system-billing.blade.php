@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header with Gradient -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    <i class="fas fa-cog mr-3"></i>System Billing Configuration
                </h1>
                <p class="text-blue-100">Configure default billing settings for all new customers</p>
            </div>
            <div class="text-right">
                <div class="text-blue-100 text-sm">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ now()->format('M d, Y') }}
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- System Configuration Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-sliders-h mr-2 text-blue-600"></i>Default Settings
            </h2>

            <form action="{{ route('settings.system-billing.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="default_billing_day" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day mr-1"></i>Default Billing Day (1-31)
                        </label>
                        <input type="number" 
                               id="default_billing_day" 
                               name="default_billing_day" 
                               value="{{ $config['default_billing_day'] }}"
                               min="1" 
                               max="31" 
                               required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <p class="text-xs text-gray-500 mt-1">This will be the default billing day assigned to all new customers</p>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="auto_billing_enabled_default" 
                                   value="1"
                                   {{ $config['auto_billing_enabled_default'] ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-robot mr-1"></i>Enable automatic billing by default for new customers
                            </span>
                        </label>
                    </div>

                    <div>
                        <label for="billing_cycle_type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sync-alt mr-1"></i>Billing Cycle Type
                        </label>
                        <select id="billing_cycle_type" 
                                name="billing_cycle_type" 
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <option value="monthly" {{ $config['billing_cycle_type'] === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ $config['billing_cycle_type'] === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="semi-annual" {{ $config['billing_cycle_type'] === 'semi-annual' ? 'selected' : '' }}>Semi-Annual</option>
                            <option value="annual" {{ $config['billing_cycle_type'] === 'annual' ? 'selected' : '' }}>Annual</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">How often bills are generated for customers</p>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                            <i class="fas fa-save mr-2"></i>Save Configuration
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Current Settings & Actions -->
        <div class="space-y-6">
            <!-- Current Settings Display -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">
                    <i class="fas fa-info-circle mr-2 text-green-600"></i>Current Settings
                </h2>

                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium text-gray-700">Default Billing Day:</span>
                        <span class="text-sm font-semibold text-blue-600">
                            {{ $config['default_billing_day'] }}{{ $config['default_billing_day'] == 1 ? 'st' : ($config['default_billing_day'] == 2 ? 'nd' : ($config['default_billing_day'] == 3 ? 'rd' : 'th')) }} of each month
                        </span>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium text-gray-700">Auto-Billing Default:</span>
                        <span class="text-sm font-semibold {{ $config['auto_billing_enabled_default'] ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas {{ $config['auto_billing_enabled_default'] ? 'fa-check' : 'fa-times' }} mr-1"></i>
                            {{ $config['auto_billing_enabled_default'] ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium text-gray-700">Billing Cycle:</span>
                        <span class="text-sm font-semibold text-purple-600">
                            <i class="fas fa-sync-alt mr-1"></i>
                            {{ ucfirst(str_replace('-', ' ', $config['billing_cycle_type'])) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">
                    <i class="fas fa-users-cog mr-2 text-orange-600"></i>Bulk Actions
                </h2>

                <div class="space-y-4">
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
                        <h3 class="font-medium text-yellow-800 mb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Apply to All Customers
                        </h3>
                        <p class="text-sm text-yellow-700 mb-3">
                            This will update ALL existing customers to use the current system default settings.
                        </p>
                        <form action="{{ route('settings.system-billing.apply-all') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('This will change billing settings for ALL customers. Are you sure?')"
                                    class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                <i class="fas fa-users mr-2"></i>Apply to All Customers
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-external-link-alt mr-2 text-indigo-600"></i>Quick Links
                </h2>

                <div class="space-y-2">
                    <a href="{{ route('settings.billing.index') }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition duration-200">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Manage Individual Billing Settings
                    </a>
                    <a href="{{ route('settings.index') }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition duration-200">
                        <i class="fas fa-cog mr-2"></i>
                        Back to Settings
                    </a>
                    <a href="{{ route('customers.index') }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition duration-200">
                        <i class="fas fa-users mr-2"></i>
                        Manage Customers
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 