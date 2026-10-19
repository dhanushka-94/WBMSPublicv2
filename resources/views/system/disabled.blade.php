@extends('layouts.app')

@section('title', 'System Temporarily Disabled')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-4">
        
        <!-- Main Disabled Message -->
        <div class="bg-white rounded-xl shadow-lg border border-red-200 p-8 text-center">
            <div class="mb-6">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-red-800 mb-2">System Temporarily Disabled</h1>
                <p class="text-red-600">Some features are currently unavailable</p>
            </div>

            <!-- Disable Information -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-left">
                @if($disableInfo['disabled_at'])
                    <div class="text-sm text-red-700 mb-2">
                        <strong>Disabled Since:</strong> {{ \Carbon\Carbon::parse($disableInfo['disabled_at'])->format('M d, Y h:i A') }}
                    </div>
                @endif
                
                @if($disableInfo['reason'])
                    <div class="text-sm text-red-700">
                        <strong>Reason:</strong> {{ $disableInfo['reason'] }}
                    </div>
                @endif
            </div>

            <!-- Available Features -->
            <div class="text-left mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    Available Features
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 mb-2">Customer Access</h3>
                        <ul class="text-sm text-green-700 space-y-1">
                            <li>• View customer information</li>
                            <li>• Access customer profiles</li>
                            <li>• View customer history</li>
                        </ul>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 mb-2">Payment Management</h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• View all bills</li>
                            <li>• Process payments</li>
                            <li>• View payment history</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Restricted Features -->
            <div class="text-left mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-lock text-red-500 mr-2"></i>
                    Temporarily Restricted
                </h2>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <ul class="text-sm text-gray-600 space-y-1 columns-2">
                        <li>• Customer management</li>
                        <li>• Meter reading</li>
                        <li>• Water meter management</li>
                        <li>• Bill generation</li>
                        <li>• System reports</li>
                        <li>• System settings</li>
                        <li>• User management</li>
                        <li>• Advanced features</li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-150">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Go to Dashboard
                </a>
                <a href="{{ route('customers.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-150">
                    <i class="fas fa-users mr-2"></i>
                    View Customers
                </a>
                <a href="{{ route('bills.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-150">
                    <i class="fas fa-file-invoice mr-2"></i>
                    View Bills
                </a>
            </div>

            <!-- Contact Information -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-phone mr-1"></i>
                    For assistance or to restore full system access, please contact your system administrator.
                </p>
            </div>
        </div>

        <!-- System Information Footer -->
        <div class="text-center mt-6 text-sm text-gray-500">
            <p>AquaBill by olexto v1.0.0</p>
            <p>Powered by olexto Digital Solutions (Pvt) Ltd</p>
        </div>
    </div>
</div>
@endsection