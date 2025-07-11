@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100 px-6 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-tachometer-alt text-purple-600 mr-2"></i>
                    {{ $meter->meter_number }}
                </h1>
                <p class="text-purple-600 font-medium">{{ $meter->meter_brand }} {{ $meter->meter_model }}</p>
                <p class="text-gray-600 text-sm mt-1">Installed: {{ $meter->installation_date->format('F j, Y') }}</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('meters.edit', $meter) }}" 
                   class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Meter
                </a>
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
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($meter->current_reading, 0) }}</p>
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
                                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $meter->customer->full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $meter->customer->account_number }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status</label>
                                    <span class="mt-1 inline-flex px-3 py-1 rounded-full text-sm font-medium
                                        {{ $meter->status === 'active' ? 'bg-green-100 text-green-700' : 
                                           ($meter->status === 'faulty' ? 'bg-red-100 text-red-700' : 
                                           ($meter->status === 'replaced' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700')) }}">
                                        @if($meter->status === 'active')
                                            🟢 Active
                                        @elseif($meter->status === 'faulty')
                                            🔴 Faulty
                                        @elseif($meter->status === 'replaced')
                                            🔵 Replaced
                                        @else
                                            ⚫ Inactive
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Brand & Model</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $meter->meter_brand }} {{ $meter->meter_model }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Size & Type</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $meter->meter_size }}mm {{ ucfirst($meter->meter_type) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Installation Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $meter->installation_date->format('F j, Y') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Multiplier</label>
                                    <p class="mt-1 text-sm text-gray-900">×{{ $meter->multiplier }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Initial Reading</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ number_format($meter->initial_reading, 0) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Current Reading</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ number_format($meter->current_reading, 0) }}</p>
                                </div>
                            </div>
                            
                            @if($meter->location_notes)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-500">Location Notes</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $meter->location_notes }}</p>
                            </div>
                            @endif
                            
                            @if($meter->notes)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-500">General Notes</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $meter->notes }}</p>
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
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $reading->reading_date->format('M d, Y') }}</td>
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
                        
                        @if($meter->bills->count() > 0)
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
                                    @foreach($meter->bills as $bill)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $bill->bill_number }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $bill->bill_date->format('M d, Y') }}</td>
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
                                @if($meter->last_maintenance_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Last Maintenance</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $meter->last_maintenance_date->format('F j, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $meter->last_maintenance_date->diffForHumans() }}</p>
                                </div>
                                @endif
                                
                                @if($meter->next_maintenance_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Next Maintenance</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $meter->next_maintenance_date->format('F j, Y') }}</p>
                                    @if($meter->isDueForMaintenance())
                                        <p class="text-xs text-red-600 font-medium">⚠️ Due for maintenance</p>
                                    @else
                                        <p class="text-xs text-gray-500">{{ $meter->next_maintenance_date->diffForHumans() }}</p>
                                    @endif
                                </div>
                                @endif
                                
                                @if($meter->isDueForMaintenance())
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
                            <a href="{{ route('meters.edit', $meter) }}" 
                               class="w-full flex items-center justify-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Meter
                            </a>
                            <a href="{{ route('customers.show', $meter->customer) }}" 
                               class="w-full flex items-center justify-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                <i class="fas fa-user mr-2"></i>
                                View Customer
                            </a>
                            <a href="{{ route('readings.create') }}?meter_id={{ $meter->id }}" 
                               class="w-full flex items-center justify-center px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Add Reading
                            </a>
                        </div>
                    </div>

                    <!-- Location Information -->
                    @if($meter->hasLocation())
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
                                        src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_MAPS_API_KEY&q={{ $meter->latitude }},{{ $meter->longitude }}&zoom=17"
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
                                        <p class="text-sm text-gray-900 font-mono">{{ number_format($meter->latitude, 6) }}, {{ number_format($meter->longitude, 6) }}</p>
                                    </div>
                                    
                                    @if($meter->address)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Address</label>
                                        <p class="text-sm text-gray-900">{{ $meter->address }}</p>
                                    </div>
                                    @endif
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex space-x-2">
                                        <a href="{{ $meter->getGoogleMapsUrl() }}" 
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
                            <a href="{{ route('meters.edit', $meter) }}" 
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
            <form action="{{ route('meters.maintenance', $meter) }}" method="POST" class="p-6">
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

@if($meter->hasLocation())
function getDirections() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            const meterLat = {{ $meter->latitude }};
            const meterLng = {{ $meter->longitude }};
            
            const directionsUrl = `https://www.google.com/maps/dir/${userLat},${userLng}/${meterLat},${meterLng}`;
            window.open(directionsUrl, '_blank');
        }, function() {
            // Fallback: Open directions without current location
            const meterLat = {{ $meter->latitude }};
            const meterLng = {{ $meter->longitude }};
            const directionsUrl = `https://www.google.com/maps/dir//${meterLat},${meterLng}`;
            window.open(directionsUrl, '_blank');
        });
    } else {
        // Fallback: Open directions without current location
        const meterLat = {{ $meter->latitude }};
        const meterLng = {{ $meter->longitude }};
        const directionsUrl = `https://www.google.com/maps/dir//${meterLat},${meterLng}`;
        window.open(directionsUrl, '_blank');
    }
}
@endif
</script>
@endsection 