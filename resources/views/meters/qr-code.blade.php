@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="md:flex md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-qrcode text-white mr-3"></i>
                    QR Code for Meter #{{ $water_meter->meter_number }}
                </h1>
                <p class="text-blue-100 font-medium">Download or view QR code for this water meter</p>
            </div>
            <div class="mt-4 md:mt-0 space-x-3">
                <a href="{{ route('meters.qr-code.download', $water_meter) }}" 
                   class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-download mr-2"></i>
                    Download QR Code
                </a>
                <a href="{{ route('meters.show', $water_meter) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Meter
                </a>
            </div>
        </div>
    </div>
</div>

<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        
        <!-- QR Code Display Section -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white">
                    <i class="fas fa-qrcode mr-2"></i>QR Code
                </h3>
            </div>
            <div class="p-8 text-center">
                <div class="mb-6">
                    <img src="{{ $qrCodeUrl }}" alt="QR Code for Meter {{ $water_meter->meter_number }}" 
                         class="mx-auto border-4 border-gray-200 rounded-lg shadow-md">
                </div>
                <div class="text-gray-600 text-sm">
                    <p class="mb-2 font-medium">Scan this QR code to get meter number: <span class="font-mono bg-blue-100 px-2 py-1 rounded text-blue-800">{{ $qrCodeData }}</span></p>
                    <p class="text-xs text-gray-500">QR Code size: 300x300px | Format: SVG | Content: Simple meter number only</p>
                </div>
            </div>
        </div>

        <!-- Meter Information Section -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white">
                    <i class="fas fa-info-circle mr-2"></i>Meter Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-barcode text-blue-500 mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Meter Number</p>
                                <p class="text-lg text-gray-900">{{ $water_meter->meter_number }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-user text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Customer</p>
                                <p class="text-lg text-gray-900">{{ $water_meter->customer ? $water_meter->customer->full_name : 'Unassigned' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-purple-500 mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Installation Date</p>
                                <p class="text-lg text-gray-900">{{ $water_meter->installation_date ? $water_meter->installation_date->format('M d, Y') : 'Date not set' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-cogs text-orange-500 mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Meter Type</p>
                                <p class="text-lg text-gray-900 capitalize">{{ $water_meter->meter_type }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-toggle-on text-{{ $water_meter->status === 'active' ? 'green' : 'red' }}-500 mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Status</p>
                                <p class="text-lg text-gray-900 capitalize">{{ $water_meter->status }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-tachometer-alt text-blue-500 mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Current Reading</p>
                                <p class="text-lg text-gray-900">{{ number_format($water_meter->current_reading, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Data Section -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white">
                    <i class="fas fa-barcode mr-2"></i>Simple QR Code Content
                </h3>
            </div>
            <div class="p-6">
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 text-center">
                    <div class="text-3xl font-mono font-bold text-blue-800 mb-3">{{ $qrCodeData }}</div>
                    <p class="text-sm text-blue-600 font-medium">✅ QR Code contains only the meter number above</p>
                </div>
                <div class="mt-6 text-sm text-gray-600">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="font-semibold text-green-800 mb-2">✨ Simplified QR Code Benefits:</p>
                        <ul class="list-disc list-inside space-y-1 text-green-700">
                            <li><strong>Simple:</strong> Contains only meter number ({{ $qrCodeData }})</li>
                            <li><strong>Fast:</strong> Quick scanning with minimal data</li>
                            <li><strong>Reliable:</strong> Less chance of scanning errors</li>
                            <li><strong>Mobile-friendly:</strong> Perfect for mobile apps</li>
                            <li><strong>Human-readable:</strong> Can be read manually if needed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Section -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white">
                    <i class="fas fa-tools mr-2"></i>Actions
                </h3>
            </div>
            <div class="p-6">
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('meters.qr-code.download', $water_meter) }}" 
                       class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-download mr-2"></i>
                        Download as SVG
                    </a>
                    
                    <button onclick="window.print()" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-print mr-2"></i>
                        Print QR Code
                    </button>
                    
                    <a href="{{ route('meters.edit', $water_meter) }}" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Meter
                    </a>
                    
                    <a href="{{ route('meters.show', $water_meter) }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-eye mr-2"></i>
                        View Meter Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .bg-gradient-to-r,
    .bg-gray-50 {
        background: white !important;
    }
    
    .shadow-lg,
    .shadow-md {
        box-shadow: none !important;
    }
    
    .border {
        border: 1px solid #ddd !important;
    }
}
</style>
@endsection 
