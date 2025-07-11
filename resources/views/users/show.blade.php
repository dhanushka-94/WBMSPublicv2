@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-blue-100 px-6 py-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    User Details
                </h1>
                <p class="text-blue-600 font-medium">View user information and access details</p>
                <p class="text-gray-600 text-sm mt-1">User Management</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-2">
                <a href="{{ route('users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit User
                </a>
                <a href="{{ route('users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-blue-100">
                <div class="p-8">
                    <!-- User Header -->
                    <div class="flex items-center space-x-6 pb-6 border-b border-gray-200">
                        <div class="flex-shrink-0">
                            <div class="h-24 w-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-3xl shadow-lg">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-gray-600 text-lg">{{ $user->email }}</p>
                            <div class="flex items-center space-x-4 mt-3">
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                                    @if($user->role === 'admin') bg-red-100 text-red-800
                                    @elseif($user->role === 'manager') bg-purple-100 text-purple-800
                                    @elseif($user->role === 'meter_reader') bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    <i class="fas fa-user-tag mr-2"></i>
                                    {{ $user->role_display }}
                                </span>
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <i class="fas {{ $user->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                    {{ $user->status_display }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                        <!-- Basic Information -->
                        <div class="space-y-6">
                            <h4 class="text-xl font-bold text-gray-900 border-b border-blue-200 pb-3 flex items-center">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Basic Information
                            </h4>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Full Name</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Email Address</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $user->email }}</p>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Email Verified</label>
                                    <div class="flex items-center">
                                        @if($user->email_verified_at)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Verified on {{ $user->email_verified_at->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Not Verified
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Access & Security -->
                        <div class="space-y-6">
                            <h4 class="text-xl font-bold text-gray-900 border-b border-green-200 pb-3 flex items-center">
                                <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                                Access & Security
                            </h4>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Role</label>
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                                            @if($user->role === 'admin') bg-red-100 text-red-800
                                            @elseif($user->role === 'manager') bg-purple-100 text-purple-800
                                            @elseif($user->role === 'meter_reader') bg-green-100 text-green-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                            @if($user->role === 'admin')
                                                <i class="fas fa-crown mr-2"></i>
                                            @elseif($user->role === 'manager')
                                                <i class="fas fa-chart-line mr-2"></i>
                                            @elseif($user->role === 'meter_reader')
                                                <i class="fas fa-tachometer-alt mr-2"></i>
                                            @else
                                                <i class="fas fa-user mr-2"></i>
                                            @endif
                                            {{ $user->role_display }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600">
                                        @if($user->role === 'admin')
                                            Full system access including user management and system settings
                                        @elseif($user->role === 'manager')
                                            All staff permissions plus billing management and reports
                                        @elseif($user->role === 'meter_reader')
                                            Specialized access focused on meter readings and data collection
                                        @else
                                            Basic access to view and manage customers, meters, and readings
                                        @endif
                                    </p>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Account Status</label>
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            <i class="fas {{ $user->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                            {{ $user->status_display }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600">
                                        {{ $user->is_active ? 'User can login and access the system' : 'User account is deactivated' }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Account Created</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-8 mt-8 border-t border-gray-200">
                        <div class="flex space-x-4">
                            <a href="{{ route('users.edit', $user) }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-150 ease-in-out flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-edit mr-2"></i>
                                Edit User
                            </a>
                        </div>

                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                  onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-6 rounded-lg transition duration-150 ease-in-out flex items-center shadow-lg hover:shadow-xl">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete User
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 