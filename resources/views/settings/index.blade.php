@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="mx-auto">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-xl shadow-2xl p-8 mb-8 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/20 to-transparent"></div>
            <div class="absolute top-4 right-4 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute bottom-4 left-4 w-24 h-24 bg-white/10 rounded-full blur-lg"></div>
        </div>
        
        <div class="relative z-10 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-white mb-2 flex items-center">
                    <div class="bg-white/20 p-3 rounded-lg mr-4">
                        <i class="fas fa-cogs text-2xl"></i>
                    </div>
                    System Settings
                </h1>
                <p class="text-blue-100 text-lg">Manage customer divisions and types with custom reference IDs</p>
                <div class="flex items-center mt-3 space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white backdrop-blur-sm">
                        <i class="fas fa-database mr-1"></i>
                        {{ $divisions->count() + $customerTypes->count() }} Total Records
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white backdrop-blur-sm">
                        <i class="fas fa-shield-alt mr-1"></i>
                        10 Char Limit
                    </span>
                </div>
            </div>
            <div class="flex space-x-3">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $divisions->count() }}</div>
                    <div class="text-xs text-blue-100">Divisions</div>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $customerTypes->count() }}</div>
                    <div class="text-xs text-blue-100">Types</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Quick Access Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8 animate-fade-in-up">
        <!-- Rate Management Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-purple-200 transition-all duration-300 transform hover:-translate-y-1 group">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 group-hover:from-purple-200 group-hover:to-purple-300 transition-all duration-300">
                            <i class="fas fa-calculator text-purple-600 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Rate Management</h3>
                        <p class="text-sm text-gray-600">Manage unit ranges and billing charges</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('settings.rates.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-cog mr-2"></i>Manage Rates
                    </a>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-chart-line mr-1"></i>
                    <span>{{ App\Models\Rate::active()->count() }} active rates</span>
                </div>
            </div>
        </div>

        <!-- Billing Settings Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-orange-200 transition-all duration-300 transform hover:-translate-y-1 group">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gradient-to-br from-orange-100 to-orange-200 group-hover:from-orange-200 group-hover:to-orange-300 transition-all duration-300">
                            <i class="fas fa-calendar-alt text-orange-600 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Billing Settings</h3>
                        <p class="text-sm text-gray-600">Individual customer billing dates</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('settings.billing.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-calendar mr-2"></i>Manage Billing
                    </a>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    <span>{{ App\Models\Customer::where('auto_billing_enabled', true)->count() }} auto-enabled</span>
                </div>
            </div>
        </div>

        <!-- System Billing Configuration Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-indigo-200 transition-all duration-300 transform hover:-translate-y-1 group">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 group-hover:from-indigo-200 group-hover:to-indigo-300 transition-all duration-300">
                            <i class="fas fa-cogs text-indigo-600 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">System Billing</h3>
                        <p class="text-sm text-gray-600">Default billing configuration</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('settings.system-billing') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-sliders-h mr-2"></i>Configure System
                    </a>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-calendar-day mr-1"></i>
                    @php
                        $defaultDay = \App\Models\SystemConfiguration::getDefaultBillingDay();
                        $suffix = $defaultDay == 1 ? 'st' : ($defaultDay == 2 ? 'nd' : ($defaultDay == 3 ? 'rd' : 'th'));
                    @endphp
                    <span>{{ $defaultDay }}{{ $suffix }} default</span>
                </div>
            </div>
        </div>

        <!-- Division Management Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-blue-200 transition-all duration-300 transform hover:-translate-y-1 group">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 group-hover:from-blue-200 group-hover:to-blue-300 transition-all duration-300">
                            <i class="fas fa-map-marked-alt text-blue-600 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Customer Divisions</h3>
                        <p class="text-sm text-gray-600">Geographical area management</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button onclick="showTab('divisions')" 
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-map mr-2"></i>Manage Divisions
                    </button>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-building mr-1"></i>
                    <span>{{ $divisions->count() }} divisions</span>
                </div>
            </div>
        </div>

        <!-- Customer Types Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-green-200 transition-all duration-300 transform hover:-translate-y-1 group">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gradient-to-br from-green-100 to-green-200 group-hover:from-green-200 group-hover:to-green-300 transition-all duration-300">
                            <i class="fas fa-tags text-green-600 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Customer Types</h3>
                        <p class="text-sm text-gray-600">Customer category management</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button onclick="showTab('customer-types')" 
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-tag mr-2"></i>Manage Types
                    </button>
                </div>
                <div class="mt-3 flex items-center text-sm text-gray-500">
                    <i class="fas fa-users mr-1"></i>
                    <span>{{ $customerTypes->count() }} types</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-lg mb-8 overflow-hidden">
        <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <nav class="flex space-x-8 px-8">
                <button class="tab-btn py-6 px-3 border-b-3 font-semibold text-sm border-blue-500 text-blue-600 flex items-center relative group transition-all duration-300" 
                        onclick="showTab('divisions')">
                    <div class="absolute inset-0 bg-blue-50 rounded-t-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10 flex items-center">
                        <i class="fas fa-map-marked-alt mr-2 text-lg"></i>
                    Customer Divisions
                        <span class="ml-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs px-3 py-1 rounded-full font-medium shadow-sm">{{ $divisions->count() }}</span>
                    </div>
                </button>
                <button class="tab-btn py-6 px-3 border-b-3 font-semibold text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 flex items-center relative group transition-all duration-300" 
                        onclick="showTab('customer-types')">
                    <div class="absolute inset-0 bg-green-50 rounded-t-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10 flex items-center">
                        <i class="fas fa-tags mr-2 text-lg"></i>
                    Customer Types
                        <span class="ml-3 bg-gradient-to-r from-gray-400 to-gray-500 text-white text-xs px-3 py-1 rounded-full font-medium shadow-sm">{{ $customerTypes->count() }}</span>
                    </div>
                </button>
            </nav>
        </div>
    </div>

    <!-- Divisions Tab -->
    <div id="divisions-tab" class="tab-content">
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
            <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-map-marked-alt text-blue-600"></i>
                        </div>
                        Customer Divisions
                </h3>
                    <p class="text-sm text-gray-600 mt-1">Manage geographical areas and regions</p>
                </div>
                <button onclick="openModal('division-modal')" 
                        class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Add Division
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-building mr-2 text-blue-500"></i>
                                    Division
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag mr-2 text-blue-500"></i>
                                    Custom ID
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                    Description
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-toggle-on mr-2 text-blue-500"></i>
                                    Status
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-users mr-2 text-blue-500"></i>
                                    Customers
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-cog mr-2 text-blue-500"></i>
                                    Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($divisions as $division)
                            <tr class="hover:bg-blue-50 transition-colors duration-200">
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-map-marker-alt text-blue-600"></i>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $division->name }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 shadow-sm">
                                        {{ $division->custom_id }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                                    {{ $division->description ?: 'No description' }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    @if($division->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm">
                                            <i class="fas fa-check mr-2"></i>Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow-sm">
                                            <i class="fas fa-times mr-2"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-gray-100 p-2 rounded-lg mr-2">
                                            <i class="fas fa-users text-gray-600"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900">{{ $division->customers()->count() }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editDivision(this)"
                                                class="bg-indigo-100 hover:bg-indigo-200 text-indigo-600 hover:text-indigo-800 p-2 rounded-lg transition-all duration-200 transform hover:scale-105"
                                                data-id="{{ $division->id }}"
                                                data-name="{{ htmlspecialchars($division->name, ENT_QUOTES, 'UTF-8') }}"
                                                data-custom-id="{{ htmlspecialchars($division->custom_id, ENT_QUOTES, 'UTF-8') }}"
                                                data-description="{{ htmlspecialchars($division->description ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                data-is-active="{{ $division->is_active ? 'true' : 'false' }}"
                                                title="Edit Division">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('settings.divisions.destroy', $division) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-800 p-2 rounded-lg transition-all duration-200 transform hover:scale-105"
                                                    onclick="return confirm('Are you sure you want to delete this division?')"
                                                    title="Delete Division">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No divisions found. <button onclick="openModal('division-modal')" class="text-blue-600 hover:text-blue-900">Add the first division</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Customer Types Tab -->
    <div id="customer-types-tab" class="tab-content hidden">
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
            <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-tags text-green-600"></i>
                        </div>
                        Customer Types
                </h3>
                    <p class="text-sm text-gray-600 mt-1">Manage customer categories and classifications</p>
                </div>
                <button onclick="openModal('customer-type-modal')" 
                        class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Add Customer Type
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tag mr-2 text-green-500"></i>
                                    Type
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag mr-2 text-green-500"></i>
                                    Custom ID
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-green-500"></i>
                                    Description
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-toggle-on mr-2 text-green-500"></i>
                                    Status
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-users mr-2 text-green-500"></i>
                                    Customers
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-cog mr-2 text-green-500"></i>
                                    Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($customerTypes as $customerType)
                            <tr class="hover:bg-green-50 transition-colors duration-200">
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-tag text-green-600"></i>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $customerType->name }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm">
                                        {{ $customerType->custom_id }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                                    {{ $customerType->description ?: 'No description' }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    @if($customerType->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm">
                                            <i class="fas fa-check mr-2"></i>Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow-sm">
                                            <i class="fas fa-times mr-2"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-gray-100 p-2 rounded-lg mr-2">
                                            <i class="fas fa-users text-gray-600"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900">{{ $customerType->customers()->count() }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editCustomerType(this)"
                                                class="bg-indigo-100 hover:bg-indigo-200 text-indigo-600 hover:text-indigo-800 p-2 rounded-lg transition-all duration-200 transform hover:scale-105"
                                                data-id="{{ $customerType->id }}"
                                                data-name="{{ htmlspecialchars($customerType->name, ENT_QUOTES, 'UTF-8') }}"
                                                data-custom-id="{{ htmlspecialchars($customerType->custom_id, ENT_QUOTES, 'UTF-8') }}"
                                                data-description="{{ htmlspecialchars($customerType->description ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                data-is-active="{{ $customerType->is_active ? 'true' : 'false' }}"
                                                title="Edit Customer Type">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('settings.customer-types.destroy', $customerType) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-800 p-2 rounded-lg transition-all duration-200 transform hover:scale-105"
                                                    onclick="return confirm('Are you sure you want to delete this customer type?')"
                                                    title="Delete Customer Type">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No customer types found. <button onclick="openModal('customer-type-modal')" class="text-green-600 hover:text-green-900">Add the first customer type</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Division Modal -->
<div id="division-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-2xl rounded-xl bg-white overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white mb-0 flex items-center" id="division-modal-title">
                <div class="bg-white/20 p-2 rounded-lg mr-3">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                Add Division
            </h3>
        </div>
        <div class="p-6">
            <form id="division-form" action="{{ route('settings.divisions.store') }}" method="POST">
                @csrf
                <div id="division-method"></div>
                
                <div class="mb-6">
                    <label for="division_name" class="block text-sm font-semibold text-gray-700 mb-2">Division Name</label>
                    <input type="text" id="division_name" name="name" required 
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                </div>
                
                <div class="mb-6">
                    <label for="division_custom_id" class="block text-sm font-semibold text-gray-700 mb-2">Custom ID (Max 10 chars)</label>
                    <input type="text" id="division_custom_id" name="custom_id" required maxlength="10"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                </div>
                
                <div class="mb-6">
                    <label for="division_description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="division_description" name="description" rows="3"
                              class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 resize-none"></textarea>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="division_is_active" name="is_active" value="1" checked
                               class="w-5 h-5 rounded border-2 border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                        <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('division-modal')"
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all duration-200 transform hover:scale-105">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Customer Type Modal -->
<div id="customer-type-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-2xl rounded-xl bg-white overflow-hidden">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white mb-0 flex items-center" id="customer-type-modal-title">
                <div class="bg-white/20 p-2 rounded-lg mr-3">
                    <i class="fas fa-tags"></i>
                </div>
                Add Customer Type
            </h3>
        </div>
        <div class="p-6">
            <form id="customer-type-form" action="{{ route('settings.customer-types.store') }}" method="POST">
                @csrf
                <div id="customer-type-method"></div>
                
                <div class="mb-6">
                    <label for="customer_type_name" class="block text-sm font-semibold text-gray-700 mb-2">Type Name</label>
                    <input type="text" id="customer_type_name" name="name" required 
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200">
                </div>
                
                <div class="mb-6">
                    <label for="customer_type_custom_id" class="block text-sm font-semibold text-gray-700 mb-2">Custom ID (Max 10 chars)</label>
                    <input type="text" id="customer_type_custom_id" name="custom_id" required maxlength="10"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200">
                </div>
                
                <div class="mb-6">
                    <label for="customer_type_description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="customer_type_description" name="description" rows="3"
                              class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 resize-none"></textarea>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="customer_type_is_active" name="is_active" value="1" checked
                               class="w-5 h-5 rounded border-2 border-gray-300 text-green-600 focus:ring-2 focus:ring-green-200 transition-all duration-200">
                        <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('customer-type-modal')"
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all duration-200 transform hover:scale-105">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tabs with fade effect
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Show selected tab with fade effect
    const selectedTab = document.getElementById(tabName + '-tab');
    selectedTab.classList.remove('hidden');
    
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
        
        // Update badge colors
        const badge = btn.querySelector('span');
        if (badge) {
            badge.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'from-green-500', 'to-green-600');
            badge.classList.add('bg-gradient-to-r', 'from-gray-400', 'to-gray-500');
        }
    });
    
    // Highlight active tab
    const activeBtn = event.target.closest('.tab-btn');
    activeBtn.classList.remove('border-transparent', 'text-gray-500');
    activeBtn.classList.add('border-blue-500', 'text-blue-600');
    
    // Update active tab badge color
    const activeBadge = activeBtn.querySelector('span');
    if (activeBadge) {
        activeBadge.classList.remove('bg-gradient-to-r', 'from-gray-400', 'to-gray-500');
        if (tabName === 'divisions') {
            activeBadge.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600');
        } else if (tabName === 'customer-types') {
            activeBadge.classList.add('bg-gradient-to-r', 'from-green-500', 'to-green-600');
        }
    }
}

// Modal functionality
function openModal(modalId, skipReset = false) {
    document.getElementById(modalId).classList.remove('hidden');
    if (!skipReset) {
    resetForm(modalId);
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function resetForm(modalId) {
    console.log('Resetting form for modal:', modalId);
    
    if (modalId === 'division-modal') {
        document.getElementById('division-form').reset();
        document.getElementById('division-form').action = "{{ route('settings.divisions.store') }}";
        document.getElementById('division-method').innerHTML = '';
        document.getElementById('division-modal-title').innerHTML = '<i class="fas fa-map-marked-alt mr-2"></i>Add Division';
        document.getElementById('division_is_active').checked = true;
    } else if (modalId === 'customer-type-modal') {
        document.getElementById('customer-type-form').reset();
        document.getElementById('customer-type-form').action = "{{ route('settings.customer-types.store') }}";
        document.getElementById('customer-type-method').innerHTML = '';
        document.getElementById('customer-type-modal-title').innerHTML = '<i class="fas fa-tags mr-2"></i>Add Customer Type';
        document.getElementById('customer_type_is_active').checked = true;
    }
}

// Edit functions
function editDivision(button) {
    try {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const customId = button.dataset.customId;
        const description = button.dataset.description;
        const isActive = button.dataset.isActive === 'true';
        
        // Debug log
        console.log('Division edit data:', { id, name, customId, description, isActive });
        
        // Validate required data
        if (!id || !name || !customId) {
            console.error('Missing required data for division edit:', { id, name, customId });
            alert('Error: Missing required data. Please refresh the page and try again.');
            return;
        }
        
    document.getElementById('division_name').value = name;
    document.getElementById('division_custom_id').value = customId;
        document.getElementById('division_description').value = description || '';
    document.getElementById('division_is_active').checked = isActive;
    
        // Debug: Verify values are set
        console.log('Form values set:', {
            name: document.getElementById('division_name').value,
            customId: document.getElementById('division_custom_id').value,
            description: document.getElementById('division_description').value,
            isActive: document.getElementById('division_is_active').checked
        });
    
        document.getElementById('division-form').action = `{{ route('settings.divisions.update', ':id') }}`.replace(':id', id);
    document.getElementById('division-method').innerHTML = '@method("PUT")';
    document.getElementById('division-modal-title').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Division';
    
            openModal('division-modal', true);
    } catch (error) {
        console.error('Error in editDivision:', error);
        alert('Error editing division. Please refresh the page and try again.');
    }
}

function editCustomerType(button) {
    try {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const customId = button.dataset.customId;
        const description = button.dataset.description;
        const isActive = button.dataset.isActive === 'true';
        
        // Debug log
        console.log('Customer type edit data:', { id, name, customId, description, isActive });
        
        // Validate required data
        if (!id || !name || !customId) {
            console.error('Missing required data for customer type edit:', { id, name, customId });
            alert('Error: Missing required data. Please refresh the page and try again.');
            return;
        }
        
    document.getElementById('customer_type_name').value = name;
    document.getElementById('customer_type_custom_id').value = customId;
        document.getElementById('customer_type_description').value = description || '';
    document.getElementById('customer_type_is_active').checked = isActive;
    
        // Debug: Verify values are set
        console.log('Form values set:', {
            name: document.getElementById('customer_type_name').value,
            customId: document.getElementById('customer_type_custom_id').value,
            description: document.getElementById('customer_type_description').value,
            isActive: document.getElementById('customer_type_is_active').checked
        });
    
        document.getElementById('customer-type-form').action = `{{ route('settings.customer-types.update', ':id') }}`.replace(':id', id);
    document.getElementById('customer-type-method').innerHTML = '@method("PUT")';
    document.getElementById('customer-type-modal-title').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Customer Type';
    
            openModal('customer-type-modal', true);
    } catch (error) {
        console.error('Error in editCustomerType:', error);
        alert('Error editing customer type. Please refresh the page and try again.');
    }
}

// Auto-uppercase for custom IDs
document.addEventListener('DOMContentLoaded', function() {
    ['division_custom_id', 'customer_type_custom_id'].forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            });
        }
    });
});
</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

/* Custom scrollbar for tables */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Enhanced focus styles */
input:focus, textarea:focus, select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Smooth transitions for all interactive elements */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Enhanced button hover effects */
button:hover {
    transform: translateY(-1px);
}

button:active {
    transform: translateY(0);
}

/* Loading animation for form submissions */
.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin-top: -10px;
    margin-left: -10px;
    border: 2px solid #ffffff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection 