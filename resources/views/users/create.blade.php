@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-blue-100 px-6 py-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                    Create New User
                </h1>
                <p class="text-blue-600 font-medium">Add a new system user</p>
                <p class="text-gray-600 text-sm mt-1">User Management</p>
            </div>
            <div class="mt-4 md:mt-0">
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
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-blue-100">
                <div class="p-8">
                    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                        @csrf

                        <!-- Basic Information Section -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Basic Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-300 @enderror">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Security Section -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                                Security & Access
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Password -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="password" id="password" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-300 @enderror">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">Minimum 8 characters required</p>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- Role -->
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                        User Role <span class="text-red-500">*</span>
                                    </label>
                                    <select name="role" id="role" required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('role') border-red-300 @enderror">
                                        <option value="">Select Role</option>
                                        <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="meter_reader" {{ old('role') === 'meter_reader' ? 'selected' : '' }}>Meter Reader</option>
                                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('role')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Account Status</label>
                                    <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                        <label for="is_active" class="ml-2 block text-sm text-green-700 font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Active (User can login and access the system)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Descriptions -->
                        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-6 border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Role Descriptions:
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white p-3 rounded-lg border border-blue-100">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-user text-gray-600 mr-2"></i>
                                        <strong class="text-gray-800">Staff:</strong>
                                    </div>
                                    <p class="text-sm text-gray-600">Basic access to view and manage customers, meters, and readings</p>
                                </div>
                                <div class="bg-white p-3 rounded-lg border border-blue-100">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-tachometer-alt text-cyan-600 mr-2"></i>
                                        <strong class="text-gray-800">Meter Reader:</strong>
                                    </div>
                                    <p class="text-sm text-gray-600">Specialized access focused on meter readings and data collection</p>
                                </div>
                                <div class="bg-white p-3 rounded-lg border border-blue-100">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-chart-line text-green-600 mr-2"></i>
                                        <strong class="text-gray-800">Manager:</strong>
                                    </div>
                                    <p class="text-sm text-gray-600">All staff permissions plus billing management and reports</p>
                                </div>
                                <div class="bg-white p-3 rounded-lg border border-blue-100">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-crown text-yellow-600 mr-2"></i>
                                        <strong class="text-gray-800">Admin:</strong>
                                    </div>
                                    <p class="text-sm text-gray-600">Full system access including user management and system settings</p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6">
                            <a href="{{ route('users.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-3 px-6 rounded-lg transition duration-150 ease-in-out flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-150 ease-in-out flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-user-plus mr-2"></i>
                                Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 