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
                            <i class="fas fa-user-edit text-white"></i>
                        </div>
                        Edit Customer
                    </h1>
                    <p class="mt-2 text-gray-600">Update customer information - {{ $customer->full_name }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('customers.show', $customer) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-500 border border-green-600 rounded-lg shadow-sm text-sm font-medium text-white hover:bg-green-600 transition-all duration-200">
                        <i class="fas fa-eye mr-2"></i>
                        View Customer
                    </a>
                    <a href="{{ route('customers.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Customers
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <form action="{{ route('customers.update', $customer) }}" method="POST" enctype="multipart/form-data" class="space-y-0">
                @csrf
                @method('PUT')

                <!-- Account Information Section -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-id-card mr-3"></i>
                        Account Information
                    </h2>
                    <p class="text-blue-100 mt-1">System-generated account details</p>
                </div>

                <div class="p-6 bg-blue-50 border-b border-blue-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Account Number (Read Only) -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Account Number</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    value="{{ $customer->account_number }}"
                                    readonly
                                    class="w-full px-4 py-3 bg-white border-2 border-blue-200 rounded-lg text-gray-700 cursor-not-allowed focus:outline-none">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-lock text-blue-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-blue-600 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Account number cannot be changed after creation
                            </p>
                        </div>

                        <!-- Reference Number -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Reference Number</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="reference_number" 
                                    value="{{ old('reference_number', $customer->reference_number) }}"
                                    placeholder="System generated"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors @error('reference_number') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-hashtag text-gray-400"></i>
                                </div>
                            </div>
                            @error('reference_number')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Status</label>
                            <div class="relative">
                                <select name="status" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors appearance-none @error('status') border-red-300 @enderror">
                                    <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>
                                        🟢 Active
                                    </option>
                                    <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>
                                        🔴 Inactive
                                    </option>
                                    <option value="suspended" {{ old('status', $customer->status) == 'suspended' ? 'selected' : '' }}>
                                        🟡 Suspended
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('status')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Customer Classification Section -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-tags mr-3"></i>
                        Customer Classification
                    </h2>
                    <p class="text-indigo-100 mt-1">Required classification details</p>
                </div>

                <div class="p-6 bg-indigo-50 border-b border-indigo-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Type -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Customer Type <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="customer_type_id" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:outline-none transition-colors appearance-none @error('customer_type_id') border-red-300 @enderror">
                                    <option value="">Choose Type</option>
                                    @foreach($customerTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('customer_type_id', $customer->customer_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} ({{ $type->custom_id }})
                                            @if($type->description) - {{ $type->description }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('customer_type_id')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Division -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Division <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="division_id" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:outline-none transition-colors appearance-none @error('division_id') border-red-300 @enderror">
                                    <option value="">Choose Division</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ old('division_id', $customer->division_id) == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
                                            @if($division->custom_id) ({{ $division->custom_id }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('division_id')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Meter Number -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">Meter Number</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="meter_number" 
                                    value="{{ old('meter_number', $customer->meter_number) }}"
                                    placeholder="Water meter number"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:outline-none transition-colors @error('meter_number') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-tachometer-alt text-gray-400"></i>
                                </div>
                            </div>
                            @error('meter_number')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-6">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-user mr-3"></i>
                        Personal Information
                    </h2>
                    <p class="text-emerald-100 mt-1">Customer's personal details</p>
                </div>

                <div class="p-6 bg-emerald-50 border-b border-emerald-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Title -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="title" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-emerald-500 focus:outline-none transition-colors appearance-none @error('title') border-red-300 @enderror">
                                    <option value="">Select Title</option>
                                    <option value="Mr" {{ old('title', $customer->title) == 'Mr' ? 'selected' : '' }}>Mr</option>
                                    <option value="Mrs" {{ old('title', $customer->title) == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                    <option value="Miss" {{ old('title', $customer->title) == 'Miss' ? 'selected' : '' }}>Miss</option>
                                    <option value="Ms" {{ old('title', $customer->title) == 'Ms' ? 'selected' : '' }}>Ms</option>
                                    <option value="Dr" {{ old('title', $customer->title) == 'Dr' ? 'selected' : '' }}>Dr</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('title')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- First Name -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="first_name" 
                                    value="{{ old('first_name', $customer->first_name) }}"
                                    required
                                    placeholder="Enter first name"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-emerald-500 focus:outline-none transition-colors @error('first_name') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            </div>
                            @error('first_name')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Last Name</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="last_name" 
                                    value="{{ old('last_name', $customer->last_name) }}"
                                    placeholder="Enter last name"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-emerald-500 focus:outline-none transition-colors @error('last_name') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            </div>
                            @error('last_name')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Profile Photo -->
                        <div class="space-y-2 md:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-semibold text-gray-700">Profile Photo</label>
                            <div class="flex items-center space-x-6">
                                <div class="flex-shrink-0">
                                    <img id="profile_preview" 
                                         src="{{ $customer->profile_photo_url }}" 
                                         alt="Profile Preview" 
                                         class="h-24 w-24 rounded-full object-cover border-4 border-emerald-200 shadow-lg">
                                </div>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="file" 
                                               name="profile_photo" 
                                               id="profile_photo"
                                               accept="image/*"
                                               onchange="previewImage(this)"
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 file:cursor-pointer cursor-pointer @error('profile_photo') border-red-300 @enderror">
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        PNG, JPG, GIF up to 2MB
                                    </p>
                                    @if($customer->profile_photo)
                                        <p class="mt-1 text-xs text-green-600 flex items-center">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Current photo will be replaced if you upload a new one
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @error('profile_photo')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-6">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-phone mr-3"></i>
                        Contact Information
                    </h2>
                    <p class="text-amber-100 mt-1">Phone numbers and email address</p>
                </div>

                <div class="p-6 bg-amber-50 border-b border-amber-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Phone Number One -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Phone Number One <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="phone" 
                                    value="{{ old('phone', $customer->phone) }}"
                                    required
                                    placeholder="Primary phone number"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none transition-colors @error('phone') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                            </div>
                            @error('phone')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number Two -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Phone Number Two</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="phone_two" 
                                    value="{{ old('phone_two', $customer->phone_two) }}"
                                    placeholder="Secondary phone number"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none transition-colors @error('phone_two') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-mobile-alt text-gray-400"></i>
                                </div>
                            </div>
                            @error('phone_two')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Email Address</label>
                            <div class="relative">
                                <input 
                                    type="email" 
                                    name="email" 
                                    value="{{ old('email', $customer->email) }}"
                                    placeholder="customer@example.com"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none transition-colors @error('email') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                            </div>
                            @error('email')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Identity & Address Section -->
                <div class="bg-gradient-to-r from-rose-500 to-pink-600 p-6">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-map-marker-alt mr-3"></i>
                        Identity & Address
                    </h2>
                    <p class="text-rose-100 mt-1">Location and identification details</p>
                </div>

                <div class="p-6 bg-rose-50 border-b border-rose-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIC -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">NIC (National Identity Card)</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="nic" 
                                    value="{{ old('nic', $customer->nic) }}"
                                    placeholder="e.g., 199012345678"
                                    maxlength="12"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-rose-500 focus:outline-none transition-colors @error('nic') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                            </div>
                            @error('nic')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- EPF Number -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">EPF Number</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="epf_number" 
                                    value="{{ old('epf_number', $customer->epf_number) }}"
                                    placeholder="Employees' Provident Fund Number"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-rose-500 focus:outline-none transition-colors @error('epf_number') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-briefcase text-gray-400"></i>
                                </div>
                            </div>
                            @error('epf_number')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">Address</label>
                            <div class="relative">
                                <textarea 
                                    name="address" 
                                    rows="3"
                                    placeholder="Enter full address"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-rose-500 focus:outline-none transition-colors resize-none @error('address') border-red-300 @enderror">{{ old('address', $customer->address) }}</textarea>
                                <div class="absolute top-3 right-3">
                                    <i class="fas fa-map-marked-alt text-gray-400"></i>
                                </div>
                            </div>
                            @error('address')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">City</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="city" 
                                    value="{{ old('city', $customer->city) }}"
                                    placeholder="Enter city"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-rose-500 focus:outline-none transition-colors @error('city') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-city text-gray-400"></i>
                                </div>
                            </div>
                            @error('city')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Postal Code -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Postal Code</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="postal_code" 
                                    value="{{ old('postal_code', $customer->postal_code) }}"
                                    placeholder="Enter postal code"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-rose-500 focus:outline-none transition-colors @error('postal_code') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-mail-bulk text-gray-400"></i>
                                </div>
                            </div>
                            @error('postal_code')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Connection & Financial Section -->
                <div class="bg-gradient-to-r from-violet-500 to-purple-600 p-6">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-plug mr-3"></i>
                        Connection & Financial
                    </h2>
                    <p class="text-violet-100 mt-1">Service connection and payment details</p>
                </div>

                <div class="p-6 bg-violet-50 border-b border-violet-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Connection Date -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Connection Date <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="date" 
                                    name="connection_date" 
                                    value="{{ old('connection_date', $customer->connection_date?->format('Y-m-d')) }}"
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:outline-none transition-colors @error('connection_date') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                            </div>
                            @error('connection_date')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Deposit Amount -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Deposit Amount</label>
                            <div class="relative">
                                <input 
                                    type="number" 
                                    name="deposit_amount" 
                                    value="{{ old('deposit_amount', $customer->deposit_amount) }}"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:outline-none transition-colors @error('deposit_amount') border-red-300 @enderror">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 text-sm">LKR</span>
                                </div>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-coins text-gray-400"></i>
                                </div>
                            </div>
                            @error('deposit_amount')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Guarantor -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">Guarantor</label>
                            <div class="relative">
                                <select name="guarantor_id"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:outline-none transition-colors appearance-none @error('guarantor_id') border-red-300 @enderror">
                                    <option value="">Select Guarantor (Optional)</option>
                                    @foreach($guarantors as $guarantor)
                                        <option value="{{ $guarantor->id }}" {{ old('guarantor_id', $customer->guarantor_id) == $guarantor->id ? 'selected' : '' }}>
                                            {{ $guarantor->full_name }} ({{ $guarantor->nic }}) - {{ $guarantor->relationship }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('guarantor_id')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                            <div class="mt-2">
                                <a href="{{ route('guarantors.create') }}" 
                                   target="_blank"
                                   class="inline-flex items-center text-sm text-violet-600 hover:text-violet-800 font-medium">
                                    <i class="fas fa-plus-circle mr-1"></i>
                                    Create New Guarantor
                                </a>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">Notes</label>
                            <div class="relative">
                                <textarea 
                                    name="notes" 
                                    rows="3"
                                    placeholder="Additional notes or comments"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:outline-none transition-colors resize-none @error('notes') border-red-300 @enderror">{{ old('notes', $customer->notes) }}</textarea>
                                <div class="absolute top-3 right-3">
                                    <i class="fas fa-sticky-note text-gray-400"></i>
                                </div>
                            </div>
                            @error('notes')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-gray-50 px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span class="text-red-500">*</span> Required fields
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('customers.show', $customer) }}" 
                               class="px-6 py-3 bg-white border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                Update Customer
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('profile_preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection 