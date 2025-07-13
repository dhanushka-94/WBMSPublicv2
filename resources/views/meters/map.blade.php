@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100 px-6 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-map-marked-alt text-purple-600 mr-2"></i>
                    Water Meters Map View
                </h1>
                <p class="text-purple-600 font-medium">Interactive map showing all water meter locations</p>
                <p class="text-gray-600 text-sm mt-1">{{ $meters->count() }} meters with location data</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('meters.create') }}" 
                   class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Meter
                </a>
                <a href="{{ route('meters.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-list mr-2"></i>
                    List View
                </a>
            </div>
        </div>
    </div>

    <div class="py-4 bg-gray-50 min-h-screen">
        <div class="w-full px-6 lg:px-8">
            
            <!-- Map Container -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">
                        <i class="fas fa-map mr-2"></i>All Water Meters
                    </h3>
                    <p class="text-blue-100 text-sm">Click on markers to view meter details</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <!-- Map -->
                        <div class="lg:col-span-3">
                            <div id="map" class="w-full h-96 border-2 border-gray-200 rounded-lg"></div>
                        </div>
                        
                        <!-- Meter List -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-list mr-2"></i>Meters with Location
                            </h4>
                            
                            <div class="max-h-96 overflow-y-auto space-y-2">
                                @foreach($meters as $meter)
                                <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer" 
                                     onclick="focusOnMeter({{ $meter->id }})">
                                    <div class="p-3">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @if($meter->customer)
                                                    <img class="h-10 w-10 rounded-full object-cover" 
                                                         src="{{ $meter->customer->profile_photo_url }}" 
                                                         alt="{{ $meter->customer->full_name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-500"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $meter->meter_number }}</p>
                                                <p class="text-xs text-gray-500">{{ $meter->customer ? $meter->customer->full_name : 'Unassigned Customer' }}</p>
                                        </div>
                                        </div>
                                    </div>
                                    @if($meter->address)
                                    <p class="text-xs text-gray-400 mt-1">{{ Str::limit($meter->address, 40) }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Map Controls -->
                            <div class="space-y-2 pt-4 border-t border-gray-200">
                                <button onclick="showAllMeters()" 
                                        class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-expand mr-1"></i>
                                    Show All Meters
                                </button>
                                <button onclick="getCurrentLocation()" 
                                        class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-3 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-location-arrow mr-1"></i>
                                    My Location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>

<script>
let map;
let markers = [];
let infoWindow;

const meterData = @json($meters);

function initMap() {
    // Default location (center of Sri Lanka)
    const defaultLocation = { lat: 7.8731, lng: 80.7718 };
    
    // Initialize map
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 8,
        center: defaultLocation,
        mapTypeId: 'roadmap'
    });

    // Initialize info window
    infoWindow = new google.maps.InfoWindow();

    // Add markers for each meter
    meterData.forEach(function(meter) {
        if (meter.latitude && meter.longitude) {
            addMeterMarker(meter);
        }
    });

    // Fit map to show all markers
    showAllMeters();
}

function addMeterMarker(meter) {
    const position = {
        lat: parseFloat(meter.latitude),
        lng: parseFloat(meter.longitude)
    };

    // Choose marker color based on status
    let markerColor = 'green'; // active
    if (meter.status === 'faulty') markerColor = 'red';
    else if (meter.status === 'inactive') markerColor = 'gray';
    else if (meter.status === 'replaced') markerColor = 'blue';

    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: meter.meter_number,
        icon: `https://maps.google.com/mapfiles/ms/icons/${markerColor}-dot.png`,
        meterId: meter.id
    });

    // Create info window content
    const infoContent = `
        <div class="p-2">
            <h3 class="font-bold text-lg text-gray-900">${meter.meter_number}</h3>
            <p class="text-sm text-gray-600">${meter.customer ? meter.customer.full_name : 'Unassigned Customer'}</p>
            <p class="text-xs text-gray-500">${meter.customer ? meter.customer.account_number : 'No Account'}</p>
            ${meter.address ? `<p class="text-xs text-gray-400 mt-1">${meter.address}</p>` : ''}
            <div class="flex items-center justify-between mt-2">
                <span class="px-2 py-1 rounded-full text-xs font-medium ${getStatusClass(meter.status)}">
                    ${meter.status.charAt(0).toUpperCase() + meter.status.slice(1)}
                </span>
                <div class="space-x-1">
                    <a href="/meters/${meter.id}" class="text-blue-600 hover:text-blue-800 text-xs">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="/meters/${meter.id}/edit" class="text-green-600 hover:text-green-800 text-xs">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    `;

    // Add click listener
    marker.addListener('click', function() {
        infoWindow.setContent(infoContent);
        infoWindow.open(map, marker);
    });

    markers.push(marker);
}

function getStatusClass(status) {
    switch(status) {
        case 'active': return 'bg-green-100 text-green-700';
        case 'faulty': return 'bg-red-100 text-red-700';
        case 'replaced': return 'bg-blue-100 text-blue-700';
        default: return 'bg-gray-100 text-gray-700';
    }
}

function focusOnMeter(meterId) {
    const marker = markers.find(m => m.meterId === meterId);
    if (marker) {
        map.setCenter(marker.getPosition());
        map.setZoom(17);
        
        // Trigger click event to show info window
        google.maps.event.trigger(marker, 'click');
    }
}

function showAllMeters() {
    if (markers.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        markers.forEach(function(marker) {
            bounds.extend(marker.getPosition());
        });
        map.fitBounds(bounds);
        
        // Ensure minimum zoom level
        const listener = google.maps.event.addListener(map, 'idle', function() {
            if (map.getZoom() > 15) map.setZoom(15);
            google.maps.event.removeListener(listener);
        });
    }
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            
            map.setCenter(userLocation);
            map.setZoom(15);
            
            // Add user location marker
            new google.maps.Marker({
                position: userLocation,
                map: map,
                title: 'Your Location',
                icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            });
        }, function() {
            alert('Error: The Geolocation service failed.');
        });
    } else {
        alert('Error: Your browser doesn\'t support geolocation.');
    }
}
</script>
@endsection 