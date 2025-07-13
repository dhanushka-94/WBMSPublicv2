@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100 px-6 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-tachometer-alt text-purple-600 mr-2"></i>
                    {{ $water_meter->meter_number }}
                </h1>
                <p class="text-purple-600 font-medium">{{ $water_meter->meter_brand }} {{ $water_meter->meter_model }}</p>
                <p class="text-gray-600 text-sm mt-1">Installed: {{ $water_meter->installation_date ? $water_meter->installation_date->format('F j, Y') : 'Date not set' }}</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('meters.edit', $water_meter) }}" 
                   class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Meter
                </a>
                <form method="POST" action="{{ route('meters.destroy', $water_meter) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this meter? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-trash mr-2"></i>
                        Delete Meter
                    </button>
                </form>
                <a href="{{ route('meters.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Meters
                </a>
            </div>
        </div>
    </div>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="w-full px-6 lg:px-8">
            
            <!-- Meter Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Current Reading -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-purple-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg">
                                    <i class="fas fa-gauge text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Current Reading</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($water_meter->current_reading, 0) }}</p>
                                <p class="text-xs text-gray-500">Cubic meters</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Consumption -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-blue-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg">
                                    <i class="fas fa-chart-line text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Consumption</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalConsumption, 0) }}</p>
                                <p class="text-xs text-gray-500">Since installation</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Average -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-green-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg">
                                    <i class="fas fa-chart-bar text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Monthly Average</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($averageConsumption, 0) }}</p>
                                <p class="text-xs text-gray-500">Last 12 months</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Month -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-orange-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg">
                                    <i class="fas fa-calendar-month text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">This Month</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($monthlyConsumption, 0) }}</p>
                                <p class="text-xs text-gray-500">{{ now()->format('F Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Meter Details -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-info-circle mr-2"></i>Meter Details
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Customer</label>
                                    @if($water_meter->customer)
                                        <div class="text-center">
                                            <img class="mx-auto h-24 w-24 rounded-full object-cover" 
                                                 src="{{ $water_meter->customer->profile_photo_url }}" 
                                                 alt="{{ $water_meter->customer->full_name }}">
                                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $water_meter->customer->full_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $water_meter->customer->account_number }}</p>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            <div class="mx-auto h-24 w-24 rounded-full bg-gray-300 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-500 text-3xl"></i>
                                            </div>
                                            <p class="mt-1 text-lg font-semibold text-gray-900">Unassigned Customer</p>
                                        <p class="text-sm text-gray-500">No customer assigned</p>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status</label>
                                    <span class="mt-1 inline-flex px-3 py-1 rounded-full text-sm font-medium
                                        {{ $water_meter->status === 'active' ? 'bg-green-100 text-green-700' : 
                                           ($water_meter->status === 'faulty' ? 'bg-red-100 text-red-700' : 
                                           ($water_meter->status === 'replaced' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700')) }}">
                                        @if($water_meter->status === 'active')
                                            🟢 Active
                                        @elseif($water_meter->status === 'faulty')
                                            🔴 Faulty
                                        @elseif($water_meter->status === 'replaced')
                                            🔵 Replaced
                                        @else
                                            ⚫ Inactive
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Brand & Model</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $water_meter->meter_brand }} {{ $water_meter->meter_model }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Size & Type</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $water_meter->meter_size }}mm {{ ucfirst($water_meter->meter_type) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Installation Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $water_meter->installation_date ? $water_meter->installation_date->format('F j, Y') : 'Date not set' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Multiplier</label>
                                    <p class="mt-1 text-sm text-gray-900">×{{ $water_meter->multiplier }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Initial Reading</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ number_format($water_meter->initial_reading, 0) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Current Reading</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ number_format($water_meter->current_reading, 0) }}</p>
                                </div>
                            </div>
                            
                            @if($water_meter->location_notes)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-500">Location Notes</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $water_meter->location_notes }}</p>
                            </div>
                            @endif
                            
                            @if($water_meter->notes)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-500">General Notes</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $water_meter->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Reading History -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-history mr-2"></i>Reading History
                            </h3>
                        </div>
                        
                        @if($readingHistory->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reading</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Consumption</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($readingHistory as $reading)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $reading->reading_date ? $reading->reading_date->format('M d, Y') : 'No date' }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ number_format($reading->current_reading, 0) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($reading->consumption, 0) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                {{ $reading->reading_type === 'actual' ? 'bg-green-100 text-green-700' : 
                                                   ($reading->reading_type === 'estimated' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                                                {{ ucfirst($reading->reading_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                {{ $reading->status === 'verified' ? 'bg-green-100 text-green-700' : 
                                                   ($reading->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                                                {{ ucfirst($reading->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <i class="fas fa-chart-line text-4xl"></i>
                            </div>
                            <p class="text-gray-500 mt-4">No readings recorded yet.</p>
                        </div>
                        @endif
                    </div>

                    <!-- Recent Bills -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-file-invoice mr-2"></i>Recent Bills
                            </h3>
                        </div>
                        
                        @if($water_meter->bills->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Bill Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($water_meter->bills as $bill)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $bill->bill_number }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $bill->bill_date ? $bill->bill_date->format('M d, Y') : 'No date' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">@rupees($bill->total_amount)</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                {{ $bill->status === 'paid' ? 'bg-green-100 text-green-700' : 
                                                   ($bill->status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                {{ ucfirst($bill->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <i class="fas fa-file-invoice text-4xl"></i>
                            </div>
                            <p class="text-gray-500 mt-4">No bills generated yet.</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    
                    <!-- Maintenance Information -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-500 to-red-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-wrench mr-2"></i>Maintenance
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @if($water_meter->last_maintenance_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Last Maintenance</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $water_meter->last_maintenance_date ? $water_meter->last_maintenance_date->format('F j, Y') : 'Not performed' }}</p>
                                    @if($water_meter->last_maintenance_date)
                                        <p class="text-xs text-gray-500">{{ $water_meter->last_maintenance_date->diffForHumans() }}</p>
                                    @endif
                                </div>
                                @endif
                                
                                @if($water_meter->next_maintenance_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Next Maintenance</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $water_meter->next_maintenance_date ? $water_meter->next_maintenance_date->format('F j, Y') : 'Not scheduled' }}</p>
                                    @if($water_meter->next_maintenance_date)
                                        @if($water_meter->isDueForMaintenance())
                                        <p class="text-xs text-red-600 font-medium">⚠️ Due for maintenance</p>
                                    @else
                                            <p class="text-xs text-gray-500">{{ $water_meter->next_maintenance_date->diffForHumans() }}</p>
                                        @endif
                                    @endif
                                </div>
                                @endif
                                
                                @if($water_meter->isDueForMaintenance())
                                <div class="pt-4 border-t border-gray-200">
                                    <button onclick="showMaintenanceModal()" 
                                            class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                                        <i class="fas fa-wrench mr-2"></i>Record Maintenance
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-500 to-gray-700 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-bolt mr-2"></i>Quick Actions
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('meters.edit', $water_meter) }}" 
                               class="w-full flex items-center justify-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Meter
                            </a>
                            @if($water_meter->customer)
                            <a href="{{ route('customers.show', $water_meter->customer) }}" 
                               class="w-full flex items-center justify-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                <i class="fas fa-user mr-2"></i>
                                View Customer
                            </a>
                            @else
                            <div class="w-full flex items-center justify-center px-4 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                                <i class="fas fa-user-slash mr-2"></i>
                                No Customer Assigned
                            </div>
                            @endif
                            <a href="{{ route('readings.create') }}?meter_id={{ $water_meter->id }}" 
                               class="w-full flex items-center justify-center px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Add Reading
                            </a>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-qrcode mr-2"></i>QR Code
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="text-center space-y-4">
                                <!-- QR Code Display -->
                                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 inline-block">
                                    <div id="qr-code-container" class="w-48 h-48 mx-auto flex items-center justify-center">
                                        <div class="text-gray-400">
                                            <i class="fas fa-spinner fa-spin text-2xl"></i>
                                            <p class="text-sm mt-2">Generating QR Code...</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Meter Information -->
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-700">{{ $water_meter->meter_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $water_meter->customer ? $water_meter->customer->full_name : 'Unassigned' }}</p>
                                </div>
                                
                                <!-- Download Options -->
                                <div class="space-y-2">
                                    <div class="flex space-x-2">
                                        <button onclick="downloadQrCode('svg', 300)" 
                                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-download mr-1"></i>
                                            Download SVG
                                        </button>
                                        <button onclick="downloadQrCode('svg', 600)" 
                                                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-3 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-download mr-1"></i>
                                            Large SVG
                                        </button>
                                    </div>
                                    
                                    <button onclick="printQrCode()" 
                                            class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-print mr-2"></i>
                                        Print QR Code
                                    </button>
                                    
                                    <button onclick="showQrCodeInfo()" 
                                            class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-barcode mr-2"></i>
                                        Show QR Content
                                    </button>
                                </div>
                                
                                <!-- QR Code Instructions -->
                                <div class="text-left bg-blue-50 rounded-lg p-4 border border-blue-200">
                                    <h4 class="font-semibold text-blue-700 mb-2">
                                        <i class="fas fa-mobile-alt mr-2"></i>Simple QR Code
                                    </h4>
                                    <div class="text-sm text-blue-600 space-y-1 mb-3">
                                        <p class="font-medium">Contains: <span class="font-mono bg-white px-2 py-1 rounded text-blue-800">{{ $water_meter->meter_number }}</span></p>
                                    </div>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                        <li>• Scan to get meter number: <strong>{{ $water_meter->meter_number }}</strong></li>
                                        <li>• Perfect for mobile apps and manual entry</li>
                                        <li>• Simple and fast to scan</li>
                                        <li>• No complex data - just the meter number</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    @if($water_meter->hasLocation())
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-map-marked-alt mr-2"></i>Location
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <!-- Google Maps Embed -->
                                <div class="w-full h-64 border-2 border-gray-200 rounded-lg overflow-hidden">
                                    <iframe 
                                        src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_MAPS_API_KEY&q={{ $water_meter->latitude }},{{ $water_meter->longitude }}&zoom=17"
                                        width="100%" 
                                        height="100%" 
                                        style="border:0;" 
                                        allowfullscreen="" 
                                        loading="lazy">
                                    </iframe>
                                </div>
                                
                                <!-- Location Details -->
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">GPS Coordinates</label>
                                        <p class="text-sm text-gray-900 font-mono">{{ number_format($water_meter->latitude, 6) }}, {{ number_format($water_meter->longitude, 6) }}</p>
                                    </div>
                                    
                                    @if($water_meter->address)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Address</label>
                                        <p class="text-sm text-gray-900">{{ $water_meter->address }}</p>
                                    </div>
                                    @endif
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex space-x-2">
                                        <a href="{{ $water_meter->getGoogleMapsUrl() }}" 
                                           target="_blank"
                                           class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-external-link-alt mr-1"></i>
                                            Open in Maps
                                        </a>
                                        <button onclick="getDirections()" 
                                                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-3 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-directions mr-1"></i>
                                            Get Directions
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-400 to-gray-500 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-map-marked-alt mr-2"></i>Location
                            </h3>
                        </div>
                        <div class="p-6 text-center">
                            <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                                <i class="fas fa-map-marker-alt text-4xl"></i>
                            </div>
                            <p class="text-gray-500 mb-4">No location data available for this meter.</p>
                            <a href="{{ route('meters.edit', $water_meter) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg text-sm transition-colors">
                                <i class="fas fa-map-pin mr-1"></i>
                                Add Location
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Modal -->
<div id="maintenanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="bg-gradient-to-r from-orange-500 to-red-600 px-6 py-4 rounded-t-xl">
                <h3 class="text-lg font-bold text-white">
                    <i class="fas fa-wrench mr-2"></i>Record Maintenance
                </h3>
            </div>
            <form action="{{ route('meters.maintenance', $water_meter) }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Maintenance Date</label>
                        <input type="date" name="maintenance_date" value="{{ date('Y-m-d') }}" 
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-orange-500 focus:outline-none transition-colors" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Notes</label>
                        <textarea name="maintenance_notes" rows="3" 
                                  class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-orange-500 focus:outline-none transition-colors"
                                  placeholder="Maintenance details..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Next Maintenance (months)</label>
                        <select name="next_maintenance_months" 
                                class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-orange-500 focus:outline-none transition-colors" required>
                            <option value="3">3 months</option>
                            <option value="6" selected>6 months</option>
                            <option value="12">12 months</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-4 mt-6">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>Record
                    </button>
                    <button type="button" onclick="hideMaintenanceModal()" 
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showMaintenanceModal() {
    document.getElementById('maintenanceModal').classList.remove('hidden');
}

function hideMaintenanceModal() {
    document.getElementById('maintenanceModal').classList.add('hidden');
}

// QR Code Functionality
let qrCodeData = null;

// Load QR code when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadQrCode();
});

async function loadQrCode() {
    try {
        // Use SVG format which works without Imagick extension
        const qrCodeUrl = '{{ $water_meter->getQrCodeUrl(200, "svg") }}';
        const meterNumber = '{{ $water_meter->meter_number }}';
        
        // Set QR code data for other functions
        qrCodeData = {
            meter_id: {{ $water_meter->id }},
            meter_number: meterNumber,
            qr_code_data: meterNumber
        };
        
        // Display QR code (SVG can be loaded as img src)
        const qrContainer = document.getElementById('qr-code-container');
        qrContainer.innerHTML = `
            <img src="${qrCodeUrl}" 
                 alt="QR Code for ${meterNumber}" 
                 class="w-full h-full object-contain">
        `;
    } catch (error) {
        console.error('Error loading QR code:', error);
        const qrContainer = document.getElementById('qr-code-container');
        qrContainer.innerHTML = `
            <div class="text-red-400 text-center">
                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                <p class="text-sm">Failed to load QR code</p>
                <button onclick="loadQrCode()" class="text-blue-500 hover:text-blue-700 text-xs mt-1">
                    Try again
                </button>
            </div>
        `;
    }
}

function downloadQrCode(format, size) {
    if (!qrCodeData) {
        alert('QR code not loaded yet. Please wait and try again.');
        return;
    }
    
    // Use the existing web route for QR code download
    const downloadUrl = '{{ route("meters.qr-code.download", $water_meter) }}';
    
    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `meter_${qrCodeData.meter_number}_qr_code.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printQrCode() {
    if (!qrCodeData) {
        alert('QR code not loaded yet. Please wait and try again.');
        return;
    }
    
    // Create a print window with QR code
    const printWindow = window.open('', '_blank');
    const qrImage = document.querySelector('#qr-code-container img');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code - ${qrCodeData.meter_number}</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    text-align: center; 
                    padding: 20px; 
                    margin: 0;
                }
                .qr-container { 
                    border: 2px solid #333; 
                    padding: 20px; 
                    margin: 20px auto;
                    max-width: 400px;
                    border-radius: 10px;
                }
                .qr-code { 
                    width: 300px; 
                    height: 300px; 
                    margin: 20px auto;
                }
                .meter-info {
                    margin-top: 20px;
                    font-size: 14px;
                }
                .instructions {
                    margin-top: 20px;
                    font-size: 12px;
                    color: #666;
                    text-align: left;
                    max-width: 300px;
                    margin-left: auto;
                    margin-right: auto;
                }
            </style>
        </head>
        <body>
            <div class="qr-container">
                <h2>Water Meter QR Code</h2>
                <div class="qr-code">
                    <img src="${qrImage.src}" alt="QR Code" style="width: 100%; height: 100%;">
                </div>
                <div class="meter-info">
                    <strong>Meter Number:</strong> {{ $water_meter->meter_number }}<br>
                    <strong>Customer:</strong> {{ $water_meter->customer ? $water_meter->customer->full_name : 'Unassigned' }}<br>
                    <strong>Generated:</strong> ${new Date().toLocaleDateString()}
                </div>
                <div class="instructions">
                    <strong>Simple QR Code:</strong><br>
                    • Contains only: <strong>{{ $water_meter->meter_number }}</strong><br>
                    • Scan to get meter number instantly<br>
                    • Perfect for mobile apps<br>
                    • Fast and reliable scanning<br>
                    • SVG format for crisp printing
                </div>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    // Wait for the page to load then print
    setTimeout(() => {
        printWindow.print();
    }, 500);
}

function showQrCodeInfo() {
    const meterNumber = '{{ $water_meter->meter_number }}';
    
    alert(`✅ Simple QR Code Content:

📱 Contains: ${meterNumber}

ℹ️ Description:
This QR code contains only the meter number (${meterNumber}). 

When scanned:
• Mobile apps get the meter number directly
• Easy to read and process
• Fast scanning with no complex data
• Perfect for meter identification

🎯 Much simpler than before!`);
}

@if($water_meter->hasLocation())
function getDirections() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            const meterLat = {{ $water_meter->latitude }};
            const meterLng = {{ $water_meter->longitude }};
            
            const directionsUrl = `https://www.google.com/maps/dir/${userLat},${userLng}/${meterLat},${meterLng}`;
            window.open(directionsUrl, '_blank');
        }, function() {
            // Fallback: Open directions without current location
            const meterLat = {{ $water_meter->latitude }};
            const meterLng = {{ $water_meter->longitude }};
            const directionsUrl = `https://www.google.com/maps/dir//${meterLat},${meterLng}`;
            window.open(directionsUrl, '_blank');
        });
    } else {
        // Fallback: Open directions without current location
        const meterLat = {{ $water_meter->latitude }};
        const meterLng = {{ $water_meter->longitude }};
        const directionsUrl = `https://www.google.com/maps/dir//${meterLat},${meterLng}`;
        window.open(directionsUrl, '_blank');
    }
}
@endif
</script>
@endsection 
