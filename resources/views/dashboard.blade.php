@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-blue-100 px-6 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-tachometer-alt text-blue-600 mr-2"></i>
                    DN WASSIP Dashboard
                </h1>
                <p class="text-blue-600 font-medium">Dunsinane Estate Water Supply and Management System</p>
                <p class="text-gray-600 text-sm mt-1">Customer Relationship Management</p>
                <div class="mt-2 flex items-center space-x-2 text-xs text-gray-500">
                    <i class="fas fa-code"></i>
                    <span>Developed by <strong class="text-blue-600">Olexto Digital Solutions (Pvt) Ltd</strong></span>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="bg-white rounded-lg shadow-md border border-blue-200 px-4 py-3">
                    <div class="flex flex-col items-center space-y-1">
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span id="current-date">{{ now()->format('l, F j, Y') }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span id="current-time" class="text-lg font-semibold text-blue-700">{{ now()->format('g:i:s A') }}</span>
                        </div>
                        <div class="text-xs text-gray-500">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Sri Lankan Time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="w-full px-6 lg:px-8">
            
            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Customers -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-blue-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Customers</p>
                                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalCustomers ?? 0) }}</p>
                                <p class="text-sm text-blue-600 font-medium">
                                    <span class="inline-flex items-center">
                                        <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $activeCustomers ?? 0 }} active
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Water Meters -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-cyan-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white shadow-lg">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Water Meters</p>
                                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalMeters ?? 0) }}</p>
                                <p class="text-sm text-cyan-600 font-medium">
                                    <span class="inline-flex items-center">
                                        <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $activeMeters ?? 0 }} active
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Revenue -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-emerald-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-lg">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Monthly Revenue</p>
                                <p class="text-3xl font-bold text-gray-900">@rupees($monthlyRevenue ?? 0)</p>
                                <p class="text-sm text-emerald-600 font-medium">{{ $totalBillsThisMonth ?? 0 }} bills generated</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Amount -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-orange-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Outstanding</p>
                                <p class="text-3xl font-bold text-gray-900">@rupees($outstandingAmount ?? 0)</p>
                                <p class="text-sm text-orange-600 font-medium">{{ $overdueBills ?? 0 }} overdue bills</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts Section -->
            @if(($alerts['overdue_bills'] ?? 0) > 0 || ($alerts['maintenance_due'] ?? 0) > 0 || ($alerts['pending_readings'] ?? 0) > 0)
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6 mb-8 shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-bold text-amber-800">⚠️ Attention Required</h3>
                        <div class="mt-2 text-amber-700">
                            <ul class="list-disc list-inside space-y-2">
                                @if(($alerts['overdue_bills'] ?? 0) > 0)
                                    <li class="font-medium">
                                        <a href="{{ route('reports.overdue') }}" class="underline hover:text-amber-900 transition-colors">
                                            {{ $alerts['overdue_bills'] }} overdue bills
                                        </a> need immediate attention
                                    </li>
                                @endif
                                @if(($alerts['maintenance_due'] ?? 0) > 0)
                                    <li class="font-medium">{{ $alerts['maintenance_due'] }} meters require scheduled maintenance</li>
                                @endif
                                @if(($alerts['pending_readings'] ?? 0) > 0)
                                    <li class="font-medium">
                                        <a href="{{ route('readings.index') }}" class="underline hover:text-amber-900 transition-colors">
                                            {{ $alerts['pending_readings'] }} pending readings
                                        </a> awaiting verification
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Billing Countdown Section -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-xl p-6 mb-8 shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-check text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-indigo-800">
                                <i class="fas fa-clock mr-2"></i>Next System Billing Date
                            </h3>
                            <p class="text-indigo-600">{{ $billingCountdown['next_billing_date']->format('l, F j, Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div id="billing-countdown" class="text-center">
                            @if($billingCountdown['days_until_billing'] >= 0)
                                <div class="text-4xl font-bold text-indigo-800" id="countdown-days">
                                    {{ $billingCountdown['days_until_billing'] }}
                                </div>
                                <div class="text-sm text-indigo-600 font-medium">
                                    {{ $billingCountdown['days_until_billing'] == 0 ? 'Billing Today!' : ($billingCountdown['days_until_billing'] == 1 ? 'Day Left' : 'Days Left') }}
                                </div>
                            @else
                                <div class="text-4xl font-bold text-red-600">
                                    {{ abs($billingCountdown['days_until_billing']) }}
                                </div>
                                <div class="text-sm text-red-600 font-medium">Days Overdue</div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Billing Status Info -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-day text-blue-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-600">Default Billing Day</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $billingCountdown['default_billing_day'] }}{{ $billingCountdown['default_billing_day'] == 1 ? 'st' : ($billingCountdown['default_billing_day'] == 2 ? 'nd' : ($billingCountdown['default_billing_day'] == 3 ? 'rd' : 'th')) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-600">Due Today</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $billingCountdown['customers_due_today'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-red-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-600">Overdue</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $billingCountdown['customers_overdue'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('settings.system-billing') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-cog mr-2"></i>Configure Billing
                    </a>
                    <a href="{{ route('settings.billing.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-calendar-alt mr-2"></i>Manage Customer Billing
                    </a>
                    <a href="{{ route('bills.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-file-invoice mr-2"></i>View Bills
                    </a>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Recent Customers -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-blue-100">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-users mr-2"></i>Recent Customers
                            </h3>
                            <a href="{{ route('customers.index') }}" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                                View all →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(isset($recentCustomers) && $recentCustomers->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentCustomers as $customer)
                                    <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-b-0">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-semibold text-gray-900">{{ $customer->full_name }}</p>
                                                <p class="text-sm text-gray-500">{{ $customer->account_number }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">{{ $customer->created_at->format('M d') }}</p>
                                            <span class="text-xs px-2 py-1 rounded-full font-medium
                                                {{ $customer->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ ucfirst($customer->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="mx-auto h-12 w-12 text-blue-400">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5m-2 3v5m-6 1c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm0 0h.01M21 21h.01M10 9a2 2 0 11-4 0 2 2 0 014 0zM26 9a2 2 0 11-4 0 2 2 0 014 0zM19 9a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 mt-4">No customers registered yet.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Bills -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-emerald-100">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-file-invoice mr-2"></i>Recent Bills
                            </h3>
                            <a href="{{ route('bills.index') }}" class="text-emerald-100 hover:text-white text-sm font-medium transition-colors">
                                View all →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(isset($recentBills) && $recentBills->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentBills as $bill)
                                    <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-b-0">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $bill->bill_number }}</p>
                                            <p class="text-sm text-gray-500">{{ $bill->customer->full_name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-gray-900">@rupees($bill->total_amount)</p>
                                            <span class="text-xs px-3 py-1 rounded-full font-medium
                                                {{ $bill->status === 'paid' ? 'bg-green-100 text-green-700' : 
                                                   ($bill->status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                {{ ucfirst($bill->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="mx-auto h-12 w-12 text-emerald-400">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5l3 3m0 0l3-3m-3 3V8M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 mt-4">No bills generated yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-blue-100">
                <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">
                        <i class="fas fa-rocket mr-2"></i>Quick Actions
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('customers.create') }}" 
                           class="group bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-4 px-6 rounded-xl text-center transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span class="text-sm">Add Customer</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('readings.index') }}" 
                           class="group bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white font-bold py-4 px-6 rounded-xl text-center transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="text-sm">Enter Readings</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('bills.index') }}" 
                           class="group bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold py-4 px-6 rounded-xl text-center transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5l3 3m0 0l3-3m-3 3V8M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm">Generate Bills</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('reports.consumption') }}" 
                           class="group bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-bold py-4 px-6 rounded-xl text-center transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="text-sm">View Reports</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time clock
function updateClock() {
    const now = new Date();
    
    // Format date
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        timeZone: 'Asia/Colombo'
    };
    const dateString = now.toLocaleDateString('en-US', options);
    
    // Format time
    const timeOptions = {
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
        timeZone: 'Asia/Colombo'
    };
    const timeString = now.toLocaleTimeString('en-US', timeOptions);
    
    // Update the display
    const dateElement = document.getElementById('current-date');
    const timeElement = document.getElementById('current-time');
    
    if (dateElement) {
        dateElement.textContent = dateString;
    }
    
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// Real-time billing countdown
function updateBillingCountdown() {
    const nextBillingDate = new Date('{{ $billingCountdown['next_billing_date']->format('Y-m-d H:i:s') }}');
    const now = new Date();
    const timeDifference = nextBillingDate.getTime() - now.getTime();
    
    const countdownElement = document.getElementById('countdown-days');
    const countdownContainer = document.getElementById('billing-countdown');
    
    if (!countdownElement || !countdownContainer) return;
    
    if (timeDifference > 0) {
        // Calculate days, hours, minutes, seconds
        const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);
        
        if (days > 0) {
            countdownContainer.innerHTML = `
                <div class="text-4xl font-bold text-indigo-800">${days}</div>
                <div class="text-sm text-indigo-600 font-medium">${days === 1 ? 'Day Left' : 'Days Left'}</div>
                <div class="text-xs text-indigo-500 mt-1">${hours}h ${minutes}m ${seconds}s</div>
            `;
        } else if (hours > 0) {
            countdownContainer.innerHTML = `
                <div class="text-3xl font-bold text-orange-600">${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}</div>
                <div class="text-sm text-orange-600 font-medium">Hours Left</div>
            `;
        } else {
            countdownContainer.innerHTML = `
                <div class="text-3xl font-bold text-red-600">${minutes}:${seconds.toString().padStart(2, '0')}</div>
                <div class="text-sm text-red-600 font-medium">Minutes Left</div>
            `;
        }
    } else {
        // Billing date has passed
        const daysPast = Math.floor(Math.abs(timeDifference) / (1000 * 60 * 60 * 24));
        countdownContainer.innerHTML = `
            <div class="text-4xl font-bold text-red-600">${daysPast}</div>
            <div class="text-sm text-red-600 font-medium">${daysPast === 1 ? 'Day Overdue' : 'Days Overdue'}</div>
            <div class="text-xs text-red-500 mt-1">Billing Due!</div>
        `;
    }
}

// Update countdown and clock every second
document.addEventListener('DOMContentLoaded', function() {
    // Initialize displays
    updateClock();
    updateBillingCountdown();
    
    // Update clock every second
    setInterval(updateClock, 1000);
    
    // Update billing countdown every second
    setInterval(updateBillingCountdown, 1000);
});

// Auto-refresh page every 5 minutes to get latest data
setTimeout(function() {
    window.location.reload();
}, 5 * 60 * 1000);
</script>

@endsection
