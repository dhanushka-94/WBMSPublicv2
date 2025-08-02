@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Professional Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Create New Customer</h1>
                    <p class="mt-1 text-sm text-gray-600">Add a new customer to AquaBill - Smart Water Supply, Billing, and Customer Management</p>
                </div>
                <div class="flex items-center space-x-3">
                <a href="{{ route('customers.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Customers
                </a>
                </div>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-0">
                @csrf

                <!-- Account Information Section -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-id-card text-blue-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h2 class="text-lg font-medium text-gray-900">Account Information</h2>
                            <p class="text-sm text-gray-500">System-generated account details</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Account Number -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Account Number</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    value="Auto-generated (e.g., CP/NE/DN/DIV/TYPE/0001)"
                                    readonly
                                    class="w-full px-4 py-3 bg-white border-2 border-blue-200 rounded-lg text-gray-500 cursor-not-allowed focus:outline-none">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-magic text-blue-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-blue-600 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Format: CP/NE/DN/Division/Type/Number, auto-generated on save
                            </p>
                        </div>

                        <!-- Reference Number -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Reference Number</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="reference_number" 
                                    value="{{ old('reference_number') }}"
                                    placeholder="Leave empty to auto-generate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('reference_number') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-hashtag text-gray-400"></i>
                                </div>
                            </div>
                            @error('reference_number')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Customer Classification Section -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tags text-indigo-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h2 class="text-lg font-medium text-gray-900">Customer Classification</h2>
                            <p class="text-sm text-gray-500">Required classification details</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                                        <option value="{{ $type->id }}" {{ old('customer_type_id') == $type->id ? 'selected' : '' }}>
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
                                        <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
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


                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-emerald-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h2 class="text-lg font-medium text-gray-900">Personal Information</h2>
                            <p class="text-sm text-gray-500">Customer's personal details</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 border-b border-gray-200">
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
                                    <option value="Mr" {{ old('title') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                    <option value="Mrs" {{ old('title') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                    <option value="Miss" {{ old('title') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                    <option value="Ms" {{ old('title') == 'Ms' ? 'selected' : '' }}>Ms</option>
                                    <option value="Dr" {{ old('title') == 'Dr' ? 'selected' : '' }}>Dr</option>
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
                                    value="{{ old('first_name') }}"
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
                                    value="{{ old('last_name') }}"
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
                                         src="{{ asset('images/profile.png') }}" 
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
                                </div>
                            </div>
                            @error('profile_photo')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-phone text-amber-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h2 class="text-lg font-medium text-gray-900">Contact Information</h2>
                            <p class="text-sm text-gray-500">Phone numbers and email address</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 border-b border-gray-200">
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
                                    value="{{ old('phone') }}"
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
                                    value="{{ old('phone_two') }}"
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
                                    value="{{ old('email') }}"
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
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-rose-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h2 class="text-lg font-medium text-gray-900">Identity & Address</h2>
                            <p class="text-sm text-gray-500">Location and identification details</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIC -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">NIC (National Identity Card)</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="nic" 
                                    value="{{ old('nic') }}"
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
                                    value="{{ old('epf_number') }}"
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
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-rose-500 focus:outline-none transition-colors resize-none @error('address') border-red-300 @enderror">{{ old('address') }}</textarea>
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
                                    value="{{ old('city') }}"
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
                                    value="{{ old('postal_code') }}"
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
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plug text-violet-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h2 class="text-lg font-medium text-gray-900">Connection & Financial</h2>
                            <p class="text-sm text-gray-500">Service connection and payment details</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 border-b border-gray-200">
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
                                    value="{{ old('connection_date') }}"
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
                                    value="{{ old('deposit_amount') }}"
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
                                        <option value="{{ $guarantor->id }}" {{ old('guarantor_id') == $guarantor->id ? 'selected' : '' }}>
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
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:outline-none transition-colors resize-none @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
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

                <!-- Meter Assignment Section -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tachometer-alt text-green-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h2 class="text-lg font-medium text-gray-900">Meter Assignment</h2>
                            <p class="text-sm text-gray-500">Assign an existing water meter to this customer</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Water Meter Selection -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-tachometer-alt mr-1"></i>
                                Water Meter <span class="text-gray-500">(Optional)</span>
                            </label>
                            
                            <!-- Hidden select for form submission -->
                            <input type="hidden" name="water_meter_id" id="water_meter_id" value="{{ old('water_meter_id') }}">
                            
                            <!-- Custom searchable dropdown -->
                            <div class="relative">
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="meter_search_input"
                                        placeholder="🔍 Search meter number, type, or brand..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('water_meter_id') border-red-300 @enderror"
                                        autocomplete="off">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                </div>
                                
                                <!-- Dropdown results -->
                                <div id="meter_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-80 overflow-y-auto hidden">
                                    @php
                                        $unassignedMeters = \App\Models\WaterMeter::whereNull('customer_id')->orderBy('meter_number')->get();
                                        $assignedMeters = \App\Models\WaterMeter::whereNotNull('customer_id')->with('customer')->orderBy('meter_number')->get();
                                    @endphp
                                    
                                    @if($unassignedMeters->count() > 0)
                                        <div class="p-2 bg-green-50 border-b border-green-200">
                                            <p class="text-sm font-semibold text-green-700 flex items-center">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                📍 Available Meters (Unassigned)
                                            </p>
                                        </div>
                                        @foreach($unassignedMeters as $meter)
                                            <div class="meter-option cursor-pointer p-3 hover:bg-green-50 border-b border-gray-100 available-meter" 
                                                 data-meter-id="{{ $meter->id }}"
                                                 data-meter-number="{{ $meter->meter_number }}"
                                                 data-meter-type="{{ $meter->meter_type }}"
                                                 data-meter-brand="{{ $meter->meter_brand }}"
                                                 data-installation-date="{{ $meter->installation_date ? $meter->installation_date->format('Y-m-d') : '' }}"
                                                 data-current-reading="{{ $meter->current_reading }}"
                                                 data-search-text="{{ strtolower($meter->meter_number . ' ' . $meter->meter_type . ' ' . ($meter->meter_brand ?? '')) }}">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="font-medium text-gray-900">🆓 {{ $meter->meter_number }}</p>
                                                        <p class="text-sm text-gray-500">{{ ucfirst($meter->meter_type) }} | {{ $meter->meter_brand ?? 'Unknown Brand' }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm font-medium text-green-600">Available</p>
                                                        <p class="text-xs text-gray-500">{{ number_format($meter->current_reading, 2) }} units</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    
                                    @if($assignedMeters->count() > 0)
                                        <div class="p-2 bg-orange-50 border-b border-orange-200">
                                            <p class="text-sm font-semibold text-orange-700 flex items-center">
                                                <i class="fas fa-users mr-1"></i>
                                                👤 Assigned Meters (For Reference)
                                            </p>
                                        </div>
                                        @foreach($assignedMeters as $meter)
                                            <div class="meter-option cursor-not-allowed p-3 bg-gray-50 border-b border-gray-100 assigned-meter opacity-60" 
                                                 data-meter-id="{{ $meter->id }}"
                                                 data-meter-number="{{ $meter->meter_number }}"
                                                 data-meter-type="{{ $meter->meter_type }}"
                                                 data-meter-brand="{{ $meter->meter_brand }}"
                                                 data-installation-date="{{ $meter->installation_date ? $meter->installation_date->format('Y-m-d') : '' }}"
                                                 data-current-reading="{{ $meter->current_reading }}"
                                                 data-customer-name="{{ $meter->customer->full_name }}"
                                                 data-search-text="{{ strtolower($meter->meter_number . ' ' . $meter->meter_type . ' ' . ($meter->meter_brand ?? '')) }}">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="font-medium text-gray-600">🔒 {{ $meter->meter_number }}</p>
                                                        <p class="text-sm text-gray-500">{{ ucfirst($meter->meter_type) }} | {{ $meter->meter_brand ?? 'Unknown Brand' }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm font-medium text-orange-600">Assigned</p>
                                                        <p class="text-xs text-gray-500">{{ $meter->customer->full_name }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    
                                    <!-- No results message -->
                                    <div id="no_results" class="p-4 text-center text-gray-500 hidden">
                                        <i class="fas fa-search-minus text-2xl mb-2"></i>
                                        <p>No meters found matching your search</p>
                                    </div>
                                </div>
                            </div>
                            @error('water_meter_id')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                            
                            <!-- Meter Information Display -->
                            <div id="meter-info" class="mt-3 p-4 bg-white rounded-lg border-2 border-green-200 hidden">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-green-500"></i>
                                    Selected Meter Information
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-600">Meter Number:</p>
                                        <p id="meter-number" class="font-medium text-gray-900">-</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Type:</p>
                                        <p id="meter-type" class="font-medium text-gray-900">-</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Brand:</p>
                                        <p id="meter-brand" class="font-medium text-gray-900">-</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Installation Date:</p>
                                        <p id="meter-installation-date" class="font-medium text-gray-900">-</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Current Reading:</p>
                                        <p id="meter-current-reading" class="font-medium text-blue-600">-</p>
                                    </div>
                                    <div id="meter-customer-info" class="hidden">
                                        <p class="text-gray-600">Currently Assigned To:</p>
                                        <p id="meter-customer-name" class="font-medium text-orange-600">-</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Meter Statistics -->
                            <div class="mt-3 p-3 bg-white rounded-lg border border-green-200">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <span><strong>{{ $unassignedMeters->count() }}</strong> unassigned meters available</span>
                                    </div>
                                    <div class="flex items-center text-gray-500">
                                        <i class="fas fa-users mr-1"></i>
                                        <span><strong>{{ $assignedMeters->count() }}</strong> meters already assigned</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Create New Meter Link -->
                            <div class="mt-3">
                                <a href="{{ route('meters.create') }}" 
                                   target="_blank"
                                   class="inline-flex items-center text-sm text-green-600 hover:text-green-800 font-medium">
                                    <i class="fas fa-plus-circle mr-1"></i>
                                    Create New Water Meter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Settings Section -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-orange-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h2 class="text-lg font-medium text-gray-900">Billing Settings</h2>
                            <p class="text-sm text-gray-500">Automated billing configuration</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Billing Day -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Billing Day
                            </label>
                            <div class="relative">
                                <select name="billing_day"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-orange-500 focus:outline-none transition-colors appearance-none @error('billing_day') border-red-300 @enderror">
                                    <option value="">Select Day</option>
                                    @for($day = 1; $day <= 31; $day++)
                                        <option value="{{ $day }}" {{ old('billing_day', 1) == $day ? 'selected' : '' }}>
                                            {{ $day }}{{ $day == 1 || $day == 21 || $day == 31 ? 'st' : ($day == 2 || $day == 22 ? 'nd' : ($day == 3 || $day == 23 ? 'rd' : 'th')) }} of each month
                                        </option>
                                    @endfor
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Day of month when bills are automatically generated
                            </p>
                            @error('billing_day')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Auto Billing Enabled -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Auto Billing Status
                            </label>
                            <div class="relative">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-orange-300 transition-colors cursor-pointer @error('auto_billing_enabled') border-red-300 @enderror">
                                    <input type="checkbox" 
                                           name="auto_billing_enabled" 
                                           value="1" 
                                           {{ old('auto_billing_enabled', true) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Enable Automatic Billing</span>
                                        <p class="text-xs text-gray-500">Bills will be generated automatically on the selected day</p>
                                    </div>
                                    <div class="ml-auto">
                                        <i class="fas fa-robot text-orange-500"></i>
                                    </div>
                                </label>
                            </div>
                            @error('auto_billing_enabled')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Billing Preview -->
                    <div class="mt-6 p-4 bg-white rounded-lg border-2 border-orange-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-eye mr-2 text-orange-500"></i>
                            Billing Schedule Preview
                        </h4>
                        <div id="billing-preview" class="text-sm text-gray-600">
                            <p><i class="fas fa-calendar mr-1"></i> Next billing date will be calculated after customer creation</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span class="text-red-500">*</span> Required fields
                        </div>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('customers.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Create Customer
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

// Handle searchable meter selection
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('meter_search_input');
    const dropdown = document.getElementById('meter_dropdown');
    const hiddenInput = document.getElementById('water_meter_id');
    const meterInfo = document.getElementById('meter-info');
    const noResults = document.getElementById('no_results');
    
    let selectedMeter = null;
    
    // Show dropdown when input is focused
    searchInput.addEventListener('focus', function() {
        dropdown.classList.remove('hidden');
        filterMeters();
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            dropdown.classList.add('hidden');
        }
    });
    
    // Filter meters based on search input
    searchInput.addEventListener('input', function() {
        filterMeters();
    });
    
    function filterMeters() {
        const searchTerm = searchInput.value.toLowerCase();
        const meterOptions = dropdown.querySelectorAll('.meter-option');
        let visibleCount = 0;
        
        meterOptions.forEach(option => {
            const searchText = option.dataset.searchText || '';
            if (searchText.includes(searchTerm)) {
                option.style.display = 'block';
                visibleCount++;
            } else {
                option.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0 && searchTerm.length > 0) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    }
    
    // Handle meter selection
    dropdown.addEventListener('click', function(e) {
        const meterOption = e.target.closest('.meter-option');
        if (meterOption && meterOption.classList.contains('available-meter')) {
            selectMeter(meterOption);
        }
    });
    
    function selectMeter(meterOption) {
        selectedMeter = meterOption;
        
        // Update hidden input
        hiddenInput.value = meterOption.dataset.meterId;
        
        // Update search input display
        searchInput.value = meterOption.dataset.meterNumber + ' | ' + 
                           meterOption.dataset.meterType.charAt(0).toUpperCase() + 
                           meterOption.dataset.meterType.slice(1) + ' | ' + 
                           (meterOption.dataset.meterBrand || 'Unknown Brand');
        
        // Hide dropdown
        dropdown.classList.add('hidden');
        
        // Show meter information
        updateMeterInfo(meterOption);
    }
    
    function updateMeterInfo(meterOption) {
        if (meterOption) {
            meterInfo.classList.remove('hidden');
            
            // Update meter details
            document.getElementById('meter-number').textContent = meterOption.dataset.meterNumber || '-';
            document.getElementById('meter-type').textContent = meterOption.dataset.meterType ? 
                meterOption.dataset.meterType.charAt(0).toUpperCase() + meterOption.dataset.meterType.slice(1) : '-';
            document.getElementById('meter-brand').textContent = meterOption.dataset.meterBrand || 'Unknown Brand';
            document.getElementById('meter-installation-date').textContent = meterOption.dataset.installationDate || '-';
            document.getElementById('meter-current-reading').textContent = meterOption.dataset.currentReading ? 
                parseFloat(meterOption.dataset.currentReading).toFixed(2) + ' units' : '-';
            
            // Show customer info if meter is assigned
            const customerInfo = document.getElementById('meter-customer-info');
            if (meterOption.dataset.customerName) {
                customerInfo.classList.remove('hidden');
                document.getElementById('meter-customer-name').textContent = meterOption.dataset.customerName;
            } else {
                customerInfo.classList.add('hidden');
            }
        } else {
            meterInfo.classList.add('hidden');
        }
    }
    
    // Clear selection when input is cleared
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' || e.key === 'Delete') {
            if (this.value.length <= 1) {
                clearSelection();
            }
        }
    });
    
    function clearSelection() {
        selectedMeter = null;
        hiddenInput.value = '';
        meterInfo.classList.add('hidden');
        dropdown.classList.remove('hidden');
        filterMeters();
    }
    
    // Handle old value on page load
    if (hiddenInput.value) {
        const selectedOption = dropdown.querySelector(`[data-meter-id="${hiddenInput.value}"]`);
        if (selectedOption) {
            selectMeter(selectedOption);
        }
    }
});
</script>
@endsection 