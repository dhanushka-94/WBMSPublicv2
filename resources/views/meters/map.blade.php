@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="bg-gradient-to-r from-emerald-50 to-cyan-50 border-b border-emerald-100 px-4 sm:px-6 py-4">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-map-marked-alt text-emerald-600 mr-2"></i>
                    Water Meters Map View
                </h1>
                <p class="text-emerald-700 font-medium text-sm mt-1">
                    {{ $meters->count() }} meters with location · pin colour = customer outstanding
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('meters.create') }}"
                   class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg text-sm">
                    <i class="fas fa-plus mr-1"></i>Add Meter
                </a>
                <a href="{{ route('meters.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg text-sm">
                    <i class="fas fa-list mr-1"></i>List View
                </a>
            </div>
        </div>
    </div>

    <div class="px-3 sm:px-4 lg:px-6 py-3 bg-slate-100">
        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center gap-3 justify-between">
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
                    @foreach($legend as $item)
                        <span class="inline-flex items-center gap-1.5">
                            <img src="https://maps.google.com/mapfiles/ms/icons/{{ $item['pin'] }}-dot.png" alt="" class="h-4 w-4">
                            {{ $item['label'] }}
                        </span>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="showAllMeters()"
                            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg text-sm">
                        <i class="fas fa-expand mr-1"></i>Show all
                    </button>
                    <button type="button" onclick="getCurrentLocation()"
                            class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-3 rounded-lg text-sm">
                        <i class="fas fa-location-arrow mr-1"></i>My location
                    </button>
                </div>
            </div>

            {{-- Inline heights: production CSS may lag Vite rebuild; map must never collapse to 0px. --}}
            <div class="meter-map-layout" style="display:grid;grid-template-columns:minmax(0,1fr);gap:0;">
                <style>
                    @media (min-width: 1280px) {
                        .meter-map-layout { grid-template-columns: minmax(0, 3fr) minmax(280px, 1fr) !important; }
                        .meter-map-side { border-top: 0 !important; border-left: 1px solid #e5e7eb; max-height: calc(100vh - 11rem) !important; }
                        .meter-map-canvas { height: calc(100vh - 11rem) !important; }
                    }
                </style>
                <div class="relative">
                    <div id="map" class="meter-map-canvas w-full" style="width:100%;height:70vh;min-height:520px;"></div>
                    @if(\App\Models\SystemConfiguration::googleMapsKey() === '')
                        <div class="absolute inset-0 flex items-center justify-center bg-amber-50/95 p-6">
                            <div class="max-w-md rounded-lg border border-amber-300 bg-white p-4 text-sm text-amber-900 shadow">
                                Add the Google Maps API key in System Settings to show this map.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="meter-map-side border-t border-gray-200 flex flex-col" style="max-height:70vh;">
                    <div class="p-3 border-b border-gray-100 space-y-2">
                        <label for="meterSearch" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Search meters</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="search" id="meterSearch" autocomplete="off"
                                   placeholder="Meter no, owner, account…"
                                   class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                   oninput="filterMeters(this.value)">
                        </div>
                        <p id="searchCount" class="text-xs text-gray-500">{{ $meters->count() }} meters</p>
                    </div>

                    <div id="meterList" class="flex-1 overflow-y-auto p-2 space-y-2">
                        @forelse($meters as $meter)
                            <button type="button"
                                    class="meter-card w-full text-left border border-gray-200 rounded-lg p-3 hover:bg-emerald-50 hover:border-emerald-300 transition"
                                    data-meter-id="{{ $meter['id'] }}"
                                    data-search="{{ $meter['search_text'] }}"
                                    onclick="focusOnMeter({{ $meter['id'] }})">
                                <div class="flex items-start gap-2">
                                    <img src="https://maps.google.com/mapfiles/ms/icons/{{ $meter['pin_color'] }}-dot.png" alt="" class="mt-0.5 h-5 w-5 shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $meter['meter_number'] }}</p>
                                            <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-medium {{ $meter['status'] === 'active' ? 'bg-green-100 text-green-800' : ($meter['status'] === 'faulty' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700') }}">
                                                {{ ucfirst($meter['status']) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-700 truncate mt-0.5">
                                            {{ $meter['owner_name'] ?? 'Unassigned' }}
                                            @if($meter['account_number'])
                                                <span class="text-gray-400">· {{ $meter['account_number'] }}</span>
                                            @endif
                                        </p>
                                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-[11px] text-gray-500">
                                            <span>
                                                Usage:
                                                @if($meter['water_usage'] !== null)
                                                    {{ number_format($meter['water_usage'], 0) }} units
                                                @else
                                                    —
                                                @endif
                                            </span>
                                            <span class="font-medium {{ $meter['outstanding'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                                Due: Rs.{{ number_format($meter['outstanding'], 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @empty
                            <p class="text-sm text-gray-500 p-4">No meters have map coordinates yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(\App\Models\SystemConfiguration::googleMapsKey() !== '')
<script>
window.gm_authFailure = function () {
    const el = document.getElementById('map');
    if (!el) return;
    el.innerHTML = `
        <div class="flex h-full items-center justify-center p-6 bg-amber-50">
            <div class="max-w-lg rounded-lg border border-amber-300 bg-white p-4 text-sm text-amber-950 shadow">
                <p class="font-semibold">Google Maps rejected this API key</p>
                <p class="mt-2">In Google Cloud Console, enable <strong>Maps JavaScript API</strong>, turn on billing, and allow these HTTP referrers:</p>
                <ul class="mt-2 list-disc pl-5 space-y-1">
                    <li><code>http://127.0.0.1:8000/*</code></li>
                    <li><code>http://localhost:8000/*</code></li>
                    <li><code>https://erp.dunwassip.com/*</code></li>
                </ul>
            </div>
        </div>`;
};
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\SystemConfiguration::googleMapsKey() }}&callback=initMap" async defer></script>
@endif

<script>
let map;
let markers = [];
let markerById = {};
let infoWindow;
let userMarker = null;

const meterData = @json($meters);

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function money(value) {
    return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function initMap() {
    const defaultLocation = { lat: 7.8731, lng: 80.7718 };

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 10,
        center: defaultLocation,
        mapTypeId: 'roadmap',
        fullscreenControl: true,
        streetViewControl: false,
        mapTypeControl: true,
    });

    infoWindow = new google.maps.InfoWindow();

    meterData.forEach(function (meter) {
        if (meter.latitude && meter.longitude) {
            addMeterMarker(meter);
        }
    });

    showAllMeters();
}

function addMeterMarker(meter) {
    const position = {
        lat: parseFloat(meter.latitude),
        lng: parseFloat(meter.longitude)
    };

    const pin = meter.pin_color || 'green';
    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: meter.meter_number,
        icon: `https://maps.google.com/mapfiles/ms/icons/${pin}-dot.png`,
        meterId: meter.id
    });

    marker.addListener('click', function () {
        infoWindow.setContent(buildInfoContent(meter));
        infoWindow.open(map, marker);
        highlightCard(meter.id);
    });

    markers.push(marker);
    markerById[meter.id] = marker;
}

function buildInfoContent(meter) {
    const owner = meter.owner_name || 'Unassigned';
    const account = meter.account_number || '—';
    const usage = meter.water_usage !== null && meter.water_usage !== undefined
        ? `${Number(meter.water_usage).toLocaleString()} units`
        : '—';
    const dial = meter.current_reading !== null && meter.current_reading !== undefined
        ? Number(meter.current_reading).toLocaleString()
        : '—';
    const dueClass = Number(meter.outstanding) > 0 ? 'color:#b91c1c;font-weight:700;' : 'color:#047857;font-weight:700;';
    const status = (meter.status || 'unknown');
    const customerLink = meter.customer_id
        ? `<a href="/customers/${meter.customer_id}" style="color:#1d4ed8;text-decoration:underline;">${escapeHtml(owner)}</a>`
        : escapeHtml(owner);

    return `
        <div style="min-width:240px;max-width:300px;font-family:system-ui,sans-serif;padding:2px 4px;">
            <div style="font-size:16px;font-weight:700;color:#111827;">${escapeHtml(meter.meter_number)}</div>
            <div style="margin-top:6px;font-size:13px;color:#374151;line-height:1.45;">
                <div><strong>Owner:</strong> ${customerLink}</div>
                <div><strong>Account:</strong> ${escapeHtml(account)}</div>
                <div><strong>Status:</strong> ${escapeHtml(status.charAt(0).toUpperCase() + status.slice(1))}</div>
                <div><strong>Water usage:</strong> ${escapeHtml(usage)}${meter.usage_date ? ` <span style="color:#6b7280;">(${escapeHtml(meter.usage_date)})</span>` : ''}</div>
                <div><strong>Current dial:</strong> ${escapeHtml(dial)}</div>
                <div style="margin-top:4px;"><strong>Outstanding:</strong> <span style="${dueClass}">Rs.${money(meter.outstanding)}</span>
                    <span style="margin-left:6px;font-size:11px;color:#6b7280;">${escapeHtml(meter.outstanding_label || '')}</span>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;gap:10px;font-size:12px;">
                <a href="/meters/${meter.id}" style="color:#2563eb;">View meter</a>
                <a href="/meters/${meter.id}/edit" style="color:#059669;">Edit</a>
            </div>
        </div>
    `;
}

function highlightCard(meterId) {
    document.querySelectorAll('.meter-card').forEach(function (card) {
        card.classList.toggle('ring-2', Number(card.dataset.meterId) === Number(meterId));
        card.classList.toggle('ring-emerald-500', Number(card.dataset.meterId) === Number(meterId));
        card.classList.toggle('bg-emerald-50', Number(card.dataset.meterId) === Number(meterId));
    });

    const active = document.querySelector(`.meter-card[data-meter-id="${meterId}"]`);
    if (active) {
        active.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
}

function focusOnMeter(meterId) {
    const marker = markerById[meterId];
    if (!marker || !map) {
        return;
    }

    map.setCenter(marker.getPosition());
    map.setZoom(17);
    google.maps.event.trigger(marker, 'click');
}

function showAllMeters() {
    if (!map || markers.length === 0) {
        return;
    }

    const bounds = new google.maps.LatLngBounds();
    markers.forEach(function (marker) {
        if (marker.getMap()) {
            bounds.extend(marker.getPosition());
        }
    });
    map.fitBounds(bounds);

    const listener = google.maps.event.addListener(map, 'idle', function () {
        if (map.getZoom() > 16) map.setZoom(16);
        google.maps.event.removeListener(listener);
    });
}

function filterMeters(query) {
    const q = String(query || '').trim().toLowerCase();
    let visible = 0;

    document.querySelectorAll('.meter-card').forEach(function (card) {
        const match = !q || (card.dataset.search || '').includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible += 1;

        const marker = markerById[card.dataset.meterId];
        if (marker) {
            marker.setMap(match ? map : null);
        }
    });

    const countEl = document.getElementById('searchCount');
    if (countEl) {
        countEl.textContent = q
            ? `${visible} of ${meterData.length} meters`
            : `${meterData.length} meters`;
    }

    if (q && visible > 0) {
        const bounds = new google.maps.LatLngBounds();
        let any = false;
        markers.forEach(function (marker) {
            if (marker.getMap()) {
                bounds.extend(marker.getPosition());
                any = true;
            }
        });
        if (any) {
            map.fitBounds(bounds);
            if (visible === 1) {
                map.setZoom(Math.max(map.getZoom(), 16));
            }
        }
    } else if (!q) {
        showAllMeters();
    }
}

function getCurrentLocation() {
    if (!navigator.geolocation) {
        alert("Your browser doesn't support geolocation.");
        return;
    }

    navigator.geolocation.getCurrentPosition(function (position) {
        const userLocation = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
        };

        map.setCenter(userLocation);
        map.setZoom(15);

        if (userMarker) {
            userMarker.setMap(null);
        }

        userMarker = new google.maps.Marker({
            position: userLocation,
            map: map,
            title: 'Your Location',
            icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        });
    }, function () {
        alert('Could not get your current location.');
    });
}
</script>
@endsection
