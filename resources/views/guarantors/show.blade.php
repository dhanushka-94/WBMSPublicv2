@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 text-white py-8 px-6 rounded-lg shadow-lg mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold flex items-center">
                    <i class="fas fa-user-shield mr-3"></i>
                    Guarantor Details
                </h1>
                <p class="text-blue-100 mt-2">Complete information for {{ $guarantor->full_name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('guarantors.edit', $guarantor) }}" 
                   class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-700 transform transition hover:scale-105 shadow-md flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Guarantor
                </a>
                <a href="{{ route('guarantors.index') }}" 
                   class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transform transition hover:scale-105 shadow-md flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Guarantors
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information Panel -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Guarantor Profile Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-user mr-3"></i>
                        Guarantor Profile
                    </h2>
                    <p class="text-blue-100 text-sm mt-1">Personal information and identification details</p>
                </div>

                <div class="p-8">
                    <div class="flex items-center mb-8">
                        <div class="flex-shrink-0 h-20 w-20 rounded-full bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                            {{ substr($guarantor->first_name, 0, 1) }}{{ substr($guarantor->last_name, 0, 1) }}
                        </div>
                        <div class="ml-6">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $guarantor->full_name }}</h3>
                            <p class="text-lg text-gray-600">{{ $guarantor->guarantor_id }}</p>
                            <div class="mt-2">
                                @if($guarantor->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        🟢 Active Guarantor
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        🔴 Inactive Guarantor
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-id-card mr-1"></i>
                                    National Identity Card
                                </label>
                                <div class="text-lg font-mono bg-gray-100 px-4 py-3 rounded-lg border">
                                    {{ $guarantor->nic }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-heart mr-1"></i>
                                    Relationship
                                </label>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                                        @if($guarantor->relationship == 'Father') bg-blue-100 text-blue-800
                                        @elseif($guarantor->relationship == 'Mother') bg-pink-100 text-pink-800
                                        @elseif($guarantor->relationship == 'Spouse') bg-purple-100 text-purple-800
                                        @elseif($guarantor->relationship == 'Brother' || $guarantor->relationship == 'Sister') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        <i class="fas fa-heart mr-2"></i>
                                        {{ $guarantor->relationship }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Registration Date
                                </label>
                                <div class="text-lg text-gray-900">
                                    {{ $guarantor->created_at->format('F d, Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $guarantor->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    Last Updated
                                </label>
                                <div class="text-lg text-gray-900">
                                    {{ $guarantor->updated_at->format('F d, Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $guarantor->updated_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-phone mr-3"></i>
                        Contact Information
                    </h2>
                    <p class="text-amber-100 text-sm mt-1">Phone, email, and address details</p>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-phone mr-1"></i>
                                    Phone Number
                                </label>
                                <div class="flex items-center space-x-3">
                                    <div class="text-lg text-gray-900">{{ $guarantor->phone }}</div>
                                    <a href="tel:{{ $guarantor->phone }}" 
                                       class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-sm hover:bg-green-200 transition-colors">
                                        <i class="fas fa-phone mr-1"></i>
                                        Call
                                    </a>
                                </div>
                            </div>

                            @if($guarantor->email)
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-envelope mr-1"></i>
                                        Email Address
                                    </label>
                                    <div class="flex items-center space-x-3">
                                        <div class="text-lg text-gray-900">{{ $guarantor->email }}</div>
                                        <a href="mailto:{{ $guarantor->email }}" 
                                           class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm hover:bg-blue-200 transition-colors">
                                            <i class="fas fa-envelope mr-1"></i>
                                            Email
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                Residential Address
                            </label>
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <div class="text-gray-900 whitespace-pre-line">{{ $guarantor->address }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Associated Customers Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-users mr-3"></i>
                        Associated Customers
                    </h2>
                    <p class="text-purple-100 text-sm mt-1">Customers backed by this guarantor</p>
                </div>

                <div class="p-8">
                    @if($guarantor->customers && $guarantor->customers->count() > 0)
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $guarantor->customers->count() }} Customer{{ $guarantor->customers->count() > 1 ? 's' : '' }} Backed
                                </h3>
                                <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                                    Active Guarantorship
                                </span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach($guarantor->customers as $customer)
                                <div class="border border-gray-200 rounded-lg p-6 hover:border-purple-300 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-bold">
                                                {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900">{{ $customer->full_name }}</h4>
                                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                                    <span>
                                                        <i class="fas fa-hashtag mr-1"></i>
                                                        {{ $customer->account_number }}
                                                    </span>
                                                    <span>
                                                        <i class="fas fa-phone mr-1"></i>
                                                        {{ $customer->phone }}
                                                    </span>
                                                    @if($customer->customer_type)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ $customer->customer_type->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            @if($customer->status == 'active')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    🟢 Active
                                                </span>
                                            @elseif($customer->status == 'suspended')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    🟡 Suspended
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    🔴 Inactive
                                                </span>
                                            @endif
                                            <a href="{{ route('customers.show', $customer) }}" 
                                               class="bg-purple-100 text-purple-600 px-3 py-1 rounded-full text-sm hover:bg-purple-200 transition-colors">
                                                <i class="fas fa-eye mr-1"></i>
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Associated Customers</h3>
                            <p class="text-gray-500 mb-6">This guarantor is not currently backing any customers.</p>
                            <a href="{{ route('customers.create') }}" 
                               class="inline-flex items-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transform transition hover:scale-105 shadow-md">
                                <i class="fas fa-plus mr-2"></i>
                                Create Customer
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Quick Actions Panel -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-bolt mr-3"></i>
                        Quick Actions
                    </h2>
                    <p class="text-emerald-100 text-sm mt-1">Common guarantor operations</p>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        <a href="{{ route('guarantors.edit', $guarantor) }}" 
                           class="w-full flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transform transition hover:scale-105 shadow-md">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Information
                        </a>

                        <a href="{{ route('customers.create') }}?guarantor_id={{ $guarantor->id }}" 
                           class="w-full flex items-center justify-center bg-gradient-to-r from-purple-500 to-purple-600 text-white py-3 px-4 rounded-lg hover:from-purple-600 hover:to-purple-700 transform transition hover:scale-105 shadow-md">
                            <i class="fas fa-user-plus mr-2"></i>
                            Add Customer
                        </a>

                        @if($guarantor->phone)
                            <a href="tel:{{ $guarantor->phone }}" 
                               class="w-full flex items-center justify-center bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-4 rounded-lg hover:from-green-600 hover:to-green-700 transform transition hover:scale-105 shadow-md">
                                <i class="fas fa-phone mr-2"></i>
                                Call Guarantor
                            </a>
                        @endif

                        @if($guarantor->email)
                            <a href="mailto:{{ $guarantor->email }}" 
                               class="w-full flex items-center justify-center bg-gradient-to-r from-indigo-500 to-indigo-600 text-white py-3 px-4 rounded-lg hover:from-indigo-600 hover:to-indigo-700 transform transition hover:scale-105 shadow-md">
                                <i class="fas fa-envelope mr-2"></i>
                                Send Email
                            </a>
                        @endif

                        @if(!$guarantor->customers || $guarantor->customers->count() == 0)
                            <form method="POST" action="{{ route('guarantors.destroy', $guarantor) }}" 
                                  onsubmit="return confirm('Are you sure you want to delete this guarantor? This action cannot be undone.')" 
                                  class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full flex items-center justify-center bg-gradient-to-r from-red-500 to-red-600 text-white py-3 px-4 rounded-lg hover:from-red-600 hover:to-red-700 transform transition hover:scale-105 shadow-md">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Guarantor
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics Panel -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-chart-bar mr-3"></i>
                        Statistics
                    </h2>
                    <p class="text-gray-100 text-sm mt-1">Guarantor performance metrics</p>
                </div>

                <div class="p-6">
                    <div class="space-y-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ $guarantor->customers ? $guarantor->customers->count() : 0 }}</div>
                            <div class="text-sm text-gray-600">Customers Backed</div>
                        </div>

                        @if($guarantor->customers && $guarantor->customers->count() > 0)
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Active Customers</span>
                                    <span class="text-sm font-semibold text-green-600">
                                        {{ $guarantor->customers->where('status', 'active')->count() }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Suspended Customers</span>
                                    <span class="text-sm font-semibold text-yellow-600">
                                        {{ $guarantor->customers->where('status', 'suspended')->count() }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Inactive Customers</span>
                                    <span class="text-sm font-semibold text-red-600">
                                        {{ $guarantor->customers->where('status', 'inactive')->count() }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Member Since</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ $guarantor->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 