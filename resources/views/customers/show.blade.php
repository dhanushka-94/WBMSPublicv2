@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        Customer Details
                    </h1>
                    <p class="mt-2 text-gray-600">Complete customer profile and account information</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('customers.edit', $customer) }}" 
                       class="inline-flex items-center px-4 py-2 bg-emerald-500 border border-emerald-600 rounded-lg shadow-sm text-sm font-medium text-white hover:bg-emerald-600 transition-all duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Customer
                    </a>
                    <a href="{{ route('customers.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Customers
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Customer Info -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Customer Profile Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($customer->profile_photo)
                                    <img class="h-20 w-20 rounded-full object-cover border-4 border-white shadow-lg" 
                                         src="{{ $customer->profile_photo_url }}" 
                                         alt="{{ $customer->full_name }}">
                                @else
                                    <div class="h-20 w-20 rounded-full bg-white bg-opacity-20 flex items-center justify-center border-4 border-white shadow-lg">
                                        <i class="fas fa-user text-white text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-6">
                                <h2 class="text-2xl font-bold text-white">{{ $customer->full_name }}</h2>
                                <p class="text-blue-100 text-lg">{{ $customer->customerType->name ?? 'N/A' }} Customer</p>
                                <div class="mt-2 flex items-center space-x-4">
                                    @if($customer->status === 'active')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            🟢 Active
                                        </span>
                                    @elseif($customer->status === 'suspended')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            🟡 Suspended
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            🔴 Inactive
                                        </span>
                                    @endif
                                    <span class="text-blue-100 text-sm">
                                        Member since {{ $customer->connection_date->format('M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Information -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-id-card text-blue-500 mr-2"></i>
                            Account Information
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Account Number</label>
                                <p class="mt-1 text-lg font-bold text-blue-600">{{ $customer->account_number }}</p>
                            </div>
                            @if($customer->reference_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Reference Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->reference_number }}</p>
                            </div>
                            @endif
                            @if($customer->meter_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Meter Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->meter_number }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Division</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->division->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Connection Date</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->connection_date->format('M d, Y') }}</p>
                            </div>
                            @if($customer->deposit_amount > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Deposit Amount</label>
                                <p class="mt-1 text-sm text-gray-900">Rs. {{ number_format($customer->deposit_amount, 2) }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-user-circle mr-2"></i>
                            Personal Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($customer->email)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email Address</label>
                                <p class="mt-1 text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                    {{ $customer->email }}
                                </p>
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Phone Number</label>
                                <p class="mt-1 text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-2"></i>
                                    {{ $customer->phone }}
                                </p>
                            </div>
                            @if($customer->phone_two)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Phone Number (Secondary)</label>
                                <p class="mt-1 text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-2"></i>
                                    {{ $customer->phone_two }}
                                </p>
                            </div>
                            @endif
                            @if($customer->nic)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">NIC Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->nic }}</p>
                            </div>
                            @endif
                            @if($customer->epf_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">EPF Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $customer->epf_number }}</p>
                            </div>
                            @endif
                        </div>
                        
                        @if($customer->address)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-sm text-gray-900 flex items-start">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-2 mt-1"></i>
                                <span>
                                    {{ $customer->address }}
                                    @if($customer->city), {{ $customer->city }}@endif
                                    @if($customer->postal_code) {{ $customer->postal_code }}@endif
                                </span>
                            </p>
                        </div>
                        @endif

                        @if($customer->guarantor)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-500">Guarantor</label>
                            <p class="mt-1 text-sm text-gray-900 flex items-center">
                                <i class="fas fa-user-shield text-gray-400 mr-2"></i>
                                {{ $customer->guarantor->full_name }} ({{ $customer->guarantor->relationship }})
                            </p>
                        </div>
                        @endif

                        @if($customer->notes)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-500">Notes</label>
                            <p class="mt-1 text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $customer->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Water Meters Section -->
                @if($activeMeters->count() > 0)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Water Meters ({{ $activeMeters->count() }})
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($activeMeters as $meter)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $meter->meter_number }}</h4>
                                        <p class="text-sm text-gray-500">
                                            {{ $meter->meter_brand }} {{ $meter->meter_model }} 
                                            ({{ $meter->meter_size }}mm {{ ucfirst($meter->meter_type) }})
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-purple-600">{{ number_format($meter->current_reading, 2) }}</p>
                                        <p class="text-sm text-gray-500">Current Reading</p>
                                    </div>
                                </div>
                                @if($meter->location_notes)
                                <p class="mt-2 text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                    {{ $meter->location_notes }}
                                </p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>

            <!-- Right Column - Quick Stats & Recent Activity -->
            <div class="space-y-8">
                
                <!-- Quick Stats -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-chart-line mr-2"></i>
                            Account Summary
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-green-800">Outstanding Balance</p>
                                <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($outstandingBalance, 2) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                            </div>
                        </div>
                        
                        @if($lastBill)
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-blue-800">Last Bill Amount</p>
                                <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($lastBill->total_amount, 2) }}</p>
                                <p class="text-xs text-blue-600">{{ $lastBill->bill_date->format('M d, Y') }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        @endif

                        @if($currentReading)
                        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-purple-800">Current Reading</p>
                                <p class="text-xl font-bold text-purple-600">{{ number_format($currentReading->current_reading, 2) }}</p>
                                <p class="text-xs text-purple-600">{{ $currentReading->reading_date->format('M d, Y') }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-tachometer-alt text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Bills -->
                @if($customer->bills->count() > 0)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-orange-500 to-red-600 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            Recent Bills
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($customer->bills->take(5) as $bill)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:border-orange-300 transition-colors">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $bill->bill_number }}</p>
                                    <p class="text-sm text-gray-500">{{ $bill->bill_date->format('M d, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900">Rs. {{ number_format($bill->total_amount, 2) }}</p>
                                    @if($bill->status === 'paid')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Paid
                                        </span>
                                    @elseif($bill->status === 'overdue')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($customer->bills->count() > 5)
                        <div class="mt-4 text-center">
                            <a href="{{ route('customers.bills', $customer) }}" 
                               class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                                View All Bills ({{ $customer->bills->count() }})
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-gray-500 to-gray-700 p-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-bolt mr-2"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('customers.edit', $customer) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Customer
                        </a>
                        @if($customer->bills->count() > 0)
                        <a href="{{ route('customers.bills', $customer) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            View All Bills
                        </a>
                        @endif
                        @if($activeMeters->count() > 0)
                        <a href="{{ route('customers.meters', $customer) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Manage Meters
                        </a>
                        @endif
                        <button onclick="window.print()" 
                                class="w-full flex items-center justify-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                            <i class="fas fa-print mr-2"></i>
                            Print Details
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection 