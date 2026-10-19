@extends('layouts.app')

@section('title', 'System Status - AquaBill by olexto')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-cogs text-blue-500 mr-3"></i>
                        System Control Panel
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Manage system access and licensing controls
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Current Status</div>
                    <div class="flex items-center mt-1">
                        <div class="w-3 h-3 rounded-full mr-2 {{ $isEnabled ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        <span class="font-semibold {{ $isEnabled ? 'text-green-600' : 'text-red-600' }}">
                            {{ $isEnabled ? 'ENABLED' : 'DISABLED' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                System Status Information
            </h2>
            
            @if($isEnabled)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-green-800">System Fully Operational</h3>
                            <p class="text-green-700 text-sm">All features and modules are accessible to users.</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <h3 class="font-semibold text-red-800">System Disabled</h3>
                            <p class="text-red-700 text-sm mb-3">Limited functionality - Only viewing and payment features available.</p>
                            
                            @if($disableInfo['disabled_at'])
                                <div class="text-sm text-red-600">
                                    <strong>Disabled At:</strong> {{ \Carbon\Carbon::parse($disableInfo['disabled_at'])->format('M d, Y h:i A') }}
                                </div>
                            @endif
                            
                            @if($disableInfo['reason'])
                                <div class="text-sm text-red-600 mt-1">
                                    <strong>Reason:</strong> {{ $disableInfo['reason'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Control Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-sliders-h text-purple-500 mr-2"></i>
                System Controls
            </h2>
            
            <div class="flex flex-col sm:flex-row gap-4">
                @if($isEnabled)
                    <button onclick="disableSystem()" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-150">
                        <i class="fas fa-power-off mr-2"></i>
                        Disable System
                    </button>
                @else
                    <button onclick="enableSystem()" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-150">
                        <i class="fas fa-power-off mr-2"></i>
                        Enable System
                    </button>
                @endif
                
                <button onclick="refreshStatus()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-150">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh Status
                </button>
            </div>
        </div>

        <!-- Feature Access Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-list-ul text-orange-500 mr-2"></i>
                Feature Access Control
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Enabled Features -->
                <div>
                    <h3 class="font-semibold text-green-800 mb-3">
                        <i class="fas fa-check text-green-500 mr-1"></i>
                        Always Available Features
                    </h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center text-green-700">
                            <i class="fas fa-eye mr-2"></i>View Customers
                        </li>
                        <li class="flex items-center text-green-700">
                            <i class="fas fa-file-invoice mr-2"></i>View Bills
                        </li>
                        <li class="flex items-center text-green-700">
                            <i class="fas fa-credit-card mr-2"></i>Payment Management
                        </li>
                        <li class="flex items-center text-green-700">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard (Limited)
                        </li>
                        <li class="flex items-center text-green-700">
                            <i class="fas fa-user-cog mr-2"></i>Profile Settings
                        </li>
                    </ul>
                </div>
                
                <!-- Restricted Features -->
                <div>
                    <h3 class="font-semibold text-red-800 mb-3">
                        <i class="fas fa-lock text-red-500 mr-1"></i>
                        Disabled When System Off
                    </h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center text-red-700">
                            <i class="fas fa-user-plus mr-2"></i>Customer Management
                        </li>
                        <li class="flex items-center text-red-700">
                            <i class="fas fa-tachometer-alt mr-2"></i>Meter Reading
                        </li>
                        <li class="flex items-center text-red-700">
                            <i class="fas fa-wrench mr-2"></i>Water Meter Management
                        </li>
                        <li class="flex items-center text-red-700">
                            <i class="fas fa-file-plus mr-2"></i>Bill Generation
                        </li>
                        <li class="flex items-center text-red-700">
                            <i class="fas fa-chart-bar mr-2"></i>Reports & Analytics
                        </li>
                        <li class="flex items-center text-red-700">
                            <i class="fas fa-cogs mr-2"></i>System Settings
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable System Modal -->
<div id="disableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Disable System</h3>
            <p class="text-gray-600 mb-4">Please provide a reason for disabling the system:</p>
            
            <textarea id="disableReason" 
                      class="w-full p-3 border border-gray-300 rounded-lg resize-none" 
                      rows="3" 
                      placeholder="e.g., Payment overdue, License expired, Maintenance..."></textarea>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeDisableModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition duration-150">
                    Cancel
                </button>
                <button onclick="confirmDisable()" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-150">
                    Disable System
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function enableSystem() {
    if (confirm('Are you sure you want to enable the system? All features will be restored.')) {
        fetch('{{ route("system.enable") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while enabling the system.');
        });
    }
}

function disableSystem() {
    document.getElementById('disableModal').classList.remove('hidden');
}

function closeDisableModal() {
    document.getElementById('disableModal').classList.add('hidden');
}

function confirmDisable() {
    const reason = document.getElementById('disableReason').value.trim();
    
    if (!reason) {
        alert('Please provide a reason for disabling the system.');
        return;
    }
    
    fetch('{{ route("system.disable") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while disabling the system.');
    });
}

function refreshStatus() {
    location.reload();
}
</script>
@endsection