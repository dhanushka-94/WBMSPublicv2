@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100 px-6 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-tachometer-alt text-purple-600 mr-2"></i>
                    Water Meters Management
                </h1>
                <p class="text-purple-600 font-medium">Monitor and manage all water meters in the system</p>
                <p class="text-gray-600 text-sm mt-1">Track readings, maintenance, and performance</p>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="flex space-x-3">
                    <a href="{{ route('meters.map') }}" 
                       class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-map-marked-alt mr-2"></i>
                        Map View
                    </a>
                    <a href="{{ route('meters.create') }}" 
                       class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Meter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="w-full px-6 lg:px-8">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <!-- Total Meters -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-purple-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg">
                                    <i class="fas fa-tachometer-alt text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Meters</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalMeters) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Meters -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-green-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg">
                                    <i class="fas fa-check-circle text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Active</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($activeMeters) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inactive Meters -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-gray-500 to-gray-600 text-white shadow-lg">
                                    <i class="fas fa-pause-circle text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Inactive</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($inactiveMeters) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Faulty Meters -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-red-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg">
                                    <i class="fas fa-exclamation-triangle text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Faulty</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($faultyMeters) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Due -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-orange-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg">
                                    <i class="fas fa-wrench text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Maintenance Due</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($maintenanceDue) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-8">
                <form method="GET" action="{{ route('meters.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-search mr-1"></i>Search
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Meter number, brand, customer..."
                                   class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors">
                        </div>

                        <!-- Status Filter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-filter mr-1"></i>Status
                            </label>
                            <select name="status" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="faulty" {{ request('status') == 'faulty' ? 'selected' : '' }}>Faulty</option>
                                <option value="replaced" {{ request('status') == 'replaced' ? 'selected' : '' }}>Replaced</option>
                            </select>
                        </div>

                        <!-- Meter Type Filter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-cogs mr-1"></i>Type
                            </label>
                            <select name="meter_type" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors">
                                <option value="">All Types</option>
                                <option value="mechanical" {{ request('meter_type') == 'mechanical' ? 'selected' : '' }}>Mechanical</option>
                                <option value="digital" {{ request('meter_type') == 'digital' ? 'selected' : '' }}>Digital</option>
                                <option value="smart" {{ request('meter_type') == 'smart' ? 'selected' : '' }}>Smart</option>
                            </select>
                        </div>

                        <!-- Maintenance Filter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-wrench mr-1"></i>Maintenance
                            </label>
                            <select name="maintenance_due" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors">
                                <option value="">All Meters</option>
                                <option value="1" {{ request('maintenance_due') == '1' ? 'selected' : '' }}>Due for Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <a href="{{ route('meters.index') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Meters Table -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">
                        <i class="fas fa-table mr-2"></i>Water Meters ({{ $meters->total() }})
                    </h3>
                </div>

                @if($meters->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Meter Details</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Specifications</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Current Reading</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Maintenance</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($meters as $meter)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $meter->meter_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $meter->meter_brand }} {{ $meter->meter_model }}</p>
                                        <p class="text-xs text-gray-400">Installed: {{ $meter->installation_date->format('M d, Y') }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $meter->customer->full_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $meter->customer->account_number }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm text-gray-900">{{ $meter->meter_size }}mm</p>
                                        <p class="text-xs text-gray-500">{{ ucfirst($meter->meter_type) }}</p>
                                        <p class="text-xs text-gray-400">×{{ $meter->multiplier }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ number_format($meter->current_reading, 0) }}</p>
                                        @if($meter->meterReadings->count() > 0)
                                        <p class="text-xs text-gray-500">Last: {{ $meter->meterReadings->first()->reading_date->format('M d') }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
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
                                </td>
                                <td class="px-6 py-4">
                                    @if($meter->isDueForMaintenance())
                                        <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                            🔧 Due Now
                                        </span>
                                    @elseif($meter->next_maintenance_date)
                                        <p class="text-xs text-gray-500">{{ $meter->next_maintenance_date->format('M d, Y') }}</p>
                                    @else
                                        <span class="text-xs text-gray-400">Not scheduled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('meters.show', $meter) }}" 
                                           class="text-blue-600 hover:text-blue-800 transition-colors" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('meters.edit', $meter) }}" 
                                           class="text-green-600 hover:text-green-800 transition-colors" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($meter->hasLocation())
                                        <a href="{{ $meter->getGoogleMapsUrl() }}" 
                                           target="_blank"
                                           class="text-purple-600 hover:text-purple-800 transition-colors" 
                                           title="View on Map">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                        @endif
                                        @if($meter->isDueForMaintenance())
                                        <button onclick="showMaintenanceModal({{ $meter->id }})" 
                                                class="text-orange-600 hover:text-orange-800 transition-colors" 
                                                title="Record Maintenance">
                                            <i class="fas fa-wrench"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4">
                    {{ $meters->withQueryString()->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <div class="mx-auto h-24 w-24 text-gray-400">
                        <i class="fas fa-tachometer-alt text-6xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No water meters found</h3>
                    <p class="mt-2 text-gray-500">Get started by adding your first water meter.</p>
                    <div class="mt-6">
                        <a href="{{ route('meters.create') }}" 
                           class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-plus mr-2"></i>
                            Add Water Meter
                        </a>
                    </div>
                </div>
                @endif
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
            <form id="maintenanceForm" method="POST" class="p-6">
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
function showMaintenanceModal(meterId) {
    document.getElementById('maintenanceForm').action = `/meters/${meterId}/maintenance`;
    document.getElementById('maintenanceModal').classList.remove('hidden');
}

function hideMaintenanceModal() {
    document.getElementById('maintenanceModal').classList.add('hidden');
}
</script>
@endsection 