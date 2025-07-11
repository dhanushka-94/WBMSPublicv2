@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100 px-6 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus text-purple-600 mr-2"></i>
                    Add New Water Meter
                </h1>
                <p class="text-purple-600 font-medium">Install and configure a new water meter</p>
                <p class="text-gray-600 text-sm mt-1">Complete all required information below</p>
            </div>
            <div class="mt-4 md:mt-0">
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
            <form action="{{ route('meters.store') }}" method="POST" class="max-w-4xl mx-auto">
                @csrf

                <!-- Customer Selection Section -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">
                            <i class="fas fa-user mr-2"></i>Customer Assignment
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-user-circle mr-1"></i>Select Customer
                            </label>
                            <select name="customer_id" 
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors @error('customer_id') border-red-300 @enderror">
                                <option value="">Choose a customer (optional)...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->full_name }} ({{ $customer->account_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Meter Information Section -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">
                            <i class="fas fa-tachometer-alt mr-2"></i>Meter Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Meter Number -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-barcode mr-1"></i>Meter Number *
                                </label>
                                <input type="text" 
                                       name="meter_number" 
                                       value="{{ old('meter_number') }}"
                                       placeholder="e.g., WM25000001"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors @error('meter_number') border-red-300 @enderror" 
                                       required>
                                @error('meter_number')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Meter Brand -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-industry mr-1"></i>Brand
                                </label>
                                <input type="text" 
                                       name="meter_brand" 
                                       value="{{ old('meter_brand') }}"
                                       placeholder="e.g., Sensus, Itron, Neptune"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors @error('meter_brand') border-red-300 @enderror">
                                @error('meter_brand')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Meter Model -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-tag mr-1"></i>Model
                                </label>
                                <input type="text" 
                                       name="meter_model" 
                                       value="{{ old('meter_model') }}"
                                       placeholder="e.g., 620M, E-Series"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors @error('meter_model') border-red-300 @enderror">
                                @error('meter_model')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Meter Size -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-ruler mr-1"></i>Size (mm)
                                </label>
                                <select name="meter_size" 
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors @error('meter_size') border-red-300 @enderror">
                                    <option value="">Select size (optional)...</option>
                                    <option value="15" {{ old('meter_size') == '15' ? 'selected' : '' }}>15mm</option>
                                    <option value="20" {{ old('meter_size') == '20' ? 'selected' : '' }}>20mm</option>
                                    <option value="25" {{ old('meter_size') == '25' ? 'selected' : '' }}>25mm</option>
                                    <option value="32" {{ old('meter_size') == '32' ? 'selected' : '' }}>32mm</option>
                                    <option value="40" {{ old('meter_size') == '40' ? 'selected' : '' }}>40mm</option>
                                    <option value="50" {{ old('meter_size') == '50' ? 'selected' : '' }}>50mm</option>
                                    <option value="65" {{ old('meter_size') == '65' ? 'selected' : '' }}>65mm</option>
                                    <option value="80" {{ old('meter_size') == '80' ? 'selected' : '' }}>80mm</option>
                                    <option value="100" {{ old('meter_size') == '100' ? 'selected' : '' }}>100mm</option>
                                </select>
                                @error('meter_size')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Meter Type -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-cogs mr-1"></i>Type *
                                </label>
                                <select name="meter_type" 
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors @error('meter_type') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select type...</option>
                                    <option value="mechanical" {{ old('meter_type') == 'mechanical' ? 'selected' : '' }}>Mechanical</option>
                                    <option value="digital" {{ old('meter_type') == 'digital' ? 'selected' : '' }}>Digital</option>
                                    <option value="smart" {{ old('meter_type') == 'smart' ? 'selected' : '' }}>Smart</option>
                                </select>
                                @error('meter_type')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Multiplier -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-calculator mr-1"></i>Multiplier
                                </label>
                                <input type="number" 
                                       name="multiplier" 
                                       value="{{ old('multiplier', '1') }}"
                                       step="0.0001"
                                       min="0.0001"
                                       max="10000"
                                       placeholder="1"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors @error('multiplier') border-red-300 @enderror">
                                @error('multiplier')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Installation Details Section -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">
                            <i class="fas fa-calendar-alt mr-2"></i>Installation Details
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Installation Date -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-calendar mr-1"></i>Installation Date *
                                </label>
                                <input type="date" 
                                       name="installation_date" 
                                       value="{{ old('installation_date', date('Y-m-d')) }}"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:outline-none transition-colors @error('installation_date') border-red-300 @enderror" 
                                       required>
                                @error('installation_date')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-toggle-on mr-1"></i>Status *
                                </label>
                                <select name="status" 
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:outline-none transition-colors @error('status') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select status...</option>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="faulty" {{ old('status') == 'faulty' ? 'selected' : '' }}>Faulty</option>
                                    <option value="replaced" {{ old('status') == 'replaced' ? 'selected' : '' }}>Replaced</option>
                                </select>
                                @error('status')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Initial Reading -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-tachometer-alt mr-1"></i>Initial Reading *
                                </label>
                                <input type="number" 
                                       name="initial_reading" 
                                       value="{{ old('initial_reading', '0') }}"
                                       step="1"
                                       min="0"
                                       placeholder="0"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:outline-none transition-colors @error('initial_reading') border-red-300 @enderror" 
                                       required>
                                @error('initial_reading')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Current Reading -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-gauge mr-1"></i>Current Reading *
                                </label>
                                <input type="number" 
                                       name="current_reading" 
                                       value="{{ old('current_reading', '0') }}"
                                       step="1"
                                       min="0"
                                       placeholder="0"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:outline-none transition-colors @error('current_reading') border-red-300 @enderror" 
                                       required>
                                @error('current_reading')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">
                            <i class="fas fa-info-circle mr-2"></i>Additional Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Location Notes -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Location Notes
                                </label>
                                <textarea name="location_notes" 
                                          rows="3"
                                          placeholder="Describe the meter location (e.g., Front yard, near main gate, basement)"
                                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none transition-colors @error('location_notes') border-red-300 @enderror">{{ old('location_notes') }}</textarea>
                                @error('location_notes')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- General Notes -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-sticky-note mr-1"></i>General Notes
                                </label>
                                <textarea name="notes" 
                                          rows="3"
                                          placeholder="Any additional notes about this meter"
                                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none transition-colors @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location & Maps Section -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">
                            <i class="fas fa-map-marked-alt mr-2"></i>Location & Maps
                        </h3>
                        <p class="text-blue-100 text-sm">Pin the exact location of this water meter</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Map Container -->
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-map mr-1"></i>Click on map to set location
                                    </label>
                                    <div id="map" class="w-full h-64 border-2 border-gray-200 rounded-lg"></div>
                                </div>
                                
                                <!-- Address Search -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-search mr-1"></i>Search Address
                                    </label>
                                    <input type="text" 
                                           id="address-search"
                                           placeholder="Type address to search..."
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors">
                                </div>

                                <!-- Current Location Button -->
                                <button type="button" 
                                        onclick="getCurrentLocation()"
                                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105">
                                    <i class="fas fa-location-arrow mr-2"></i>Use Current Location
                                </button>
                            </div>

                            <!-- Location Details -->
                            <div class="space-y-4">
                                <!-- GPS Coordinates -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700">
                                            <i class="fas fa-crosshairs mr-1"></i>Latitude
                                        </label>
                                        <input type="number" 
                                               name="latitude" 
                                               id="latitude"
                                               value="{{ old('latitude') }}"
                                               step="0.00000001"
                                               placeholder="7.8731"
                                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors @error('latitude') border-red-300 @enderror">
                                        @error('latitude')
                                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700">
                                            <i class="fas fa-crosshairs mr-1"></i>Longitude
                                        </label>
                                        <input type="number" 
                                               name="longitude" 
                                               id="longitude"
                                               value="{{ old('longitude') }}"
                                               step="0.00000001"
                                               placeholder="80.7718"
                                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors @error('longitude') border-red-300 @enderror">
                                        @error('longitude')
                                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <i class="fas fa-home mr-1"></i>Address
                                    </label>
                                    <input type="text" 
                                           name="address" 
                                           id="address"
                                           value="{{ old('address') }}"
                                           placeholder="Full address will be auto-filled"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors @error('address') border-red-300 @enderror">
                                    @error('address')
                                        <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Google Place ID (Hidden) -->
                                <input type="hidden" name="google_place_id" id="google_place_id" value="{{ old('google_place_id') }}">

                                <!-- Location Info -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-blue-800 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>Location Information
                                    </h4>
                                    <div id="location-info" class="text-sm text-blue-700">
                                        <p>Click on the map or use current location to set meter coordinates</p>
                                    </div>
                                </div>

                                <!-- Clear Location Button -->
                                <button type="button" 
                                        onclick="clearLocation()"
                                        class="w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-times mr-2"></i>Clear Location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span>Required fields: Meter Number, Type, Installation Date, Status, Initial Reading, Current Reading</span>
                            </div>
                            <div class="flex space-x-4">
                                <a href="{{ route('meters.index') }}" 
                                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </a>
                                <button type="submit" 
                                        class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                    <i class="fas fa-save mr-2"></i>Create Meter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generate meter number suggestion
document.addEventListener('DOMContentLoaded', function() {
    const meterNumberInput = document.querySelector('input[name="meter_number"]');
    if (meterNumberInput && !meterNumberInput.value) {
        const year = new Date().getFullYear().toString().substr(-2);
        const randomNumber = Math.floor(Math.random() * 900000) + 100000;
        meterNumberInput.value = `WM${year}${randomNumber}`;
    }
});
</script>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places&callback=initMap" async defer></script>

<script>
let map;
let marker;
let geocoder;
let autocomplete;

function initMap() {
    // Default location (Colombo, Sri Lanka)
    const defaultLocation = { lat: 6.9271, lng: 79.8612 };
    
    // Initialize map
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 13,
        center: defaultLocation,
        mapTypeId: 'roadmap'
    });

    // Initialize geocoder
    geocoder = new google.maps.Geocoder();

    // Initialize autocomplete
    const searchInput = document.getElementById('address-search');
    autocomplete = new google.maps.places.Autocomplete(searchInput, {
        componentRestrictions: { country: 'lk' }, // Restrict to Sri Lanka
        fields: ['place_id', 'geometry', 'formatted_address']
    });

    // Listen for place selection
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            const location = place.geometry.location;
            setLocation(location.lat(), location.lng(), place.formatted_address, place.place_id);
            map.setCenter(location);
            map.setZoom(17);
        }
    });

    // Listen for map clicks
    map.addListener('click', function(event) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();
        
        // Reverse geocode to get address
        geocoder.geocode({ location: event.latLng }, function(results, status) {
            if (status === 'OK' && results[0]) {
                const address = results[0].formatted_address;
                const placeId = results[0].place_id;
                setLocation(lat, lng, address, placeId);
            } else {
                setLocation(lat, lng, `${lat.toFixed(6)}, ${lng.toFixed(6)}`, null);
            }
        });
    });

    // Set initial location if coordinates exist
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    if (lat && lng) {
        setLocation(parseFloat(lat), parseFloat(lng));
    }
}

function setLocation(lat, lng, address = '', placeId = '') {
    // Update form fields
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
    document.getElementById('address').value = address;
    document.getElementById('google_place_id').value = placeId || '';

    // Update map marker
    if (marker) {
        marker.setMap(null);
    }
    
    marker = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map,
        title: 'Water Meter Location',
        draggable: true
    });

    // Listen for marker drag
    marker.addListener('dragend', function(event) {
        const newLat = event.latLng.lat();
        const newLng = event.latLng.lng();
        
        geocoder.geocode({ location: event.latLng }, function(results, status) {
            if (status === 'OK' && results[0]) {
                const newAddress = results[0].formatted_address;
                const newPlaceId = results[0].place_id;
                setLocation(newLat, newLng, newAddress, newPlaceId);
            } else {
                setLocation(newLat, newLng, `${newLat.toFixed(6)}, ${newLng.toFixed(6)}`, null);
            }
        });
    });

    // Update location info
    updateLocationInfo(lat, lng, address);
}

function updateLocationInfo(lat, lng, address) {
    const locationInfo = document.getElementById('location-info');
    locationInfo.innerHTML = `
        <p><strong>Coordinates:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}</p>
        ${address ? `<p><strong>Address:</strong> ${address}</p>` : ''}
        <p><strong>Google Maps:</strong> <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="text-blue-600 hover:underline">View on Google Maps</a></p>
    `;
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // Reverse geocode to get address
            geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    const address = results[0].formatted_address;
                    const placeId = results[0].place_id;
                    setLocation(lat, lng, address, placeId);
                } else {
                    setLocation(lat, lng, `${lat.toFixed(6)}, ${lng.toFixed(6)}`, null);
                }
            });
            
            map.setCenter({ lat: lat, lng: lng });
            map.setZoom(17);
        }, function() {
            alert('Error: The Geolocation service failed.');
        });
    } else {
        alert('Error: Your browser doesn\'t support geolocation.');
    }
}

function clearLocation() {
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';
    document.getElementById('address').value = '';
    document.getElementById('google_place_id').value = '';
    document.getElementById('address-search').value = '';
    
    if (marker) {
        marker.setMap(null);
        marker = null;
    }
    
    document.getElementById('location-info').innerHTML = '<p>Click on the map or use current location to set meter coordinates</p>';
}

// Update coordinates when manually entered
document.getElementById('latitude').addEventListener('input', function() {
    const lat = parseFloat(this.value);
    const lng = parseFloat(document.getElementById('longitude').value);
    
    if (!isNaN(lat) && !isNaN(lng)) {
        setLocation(lat, lng);
        map.setCenter({ lat: lat, lng: lng });
    }
});

document.getElementById('longitude').addEventListener('input', function() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(this.value);
    
    if (!isNaN(lat) && !isNaN(lng)) {
        setLocation(lat, lng);
        map.setCenter({ lat: lat, lng: lng });
    }
});
</script>
@endsection 