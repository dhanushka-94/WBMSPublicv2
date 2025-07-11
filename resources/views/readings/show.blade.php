@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-eye mr-3"></i>Meter Reading Details
                    </h1>
                    <p class="text-blue-100 mt-2">View and manage meter reading information</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('readings.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Readings
                    </a>
                    @if($reading->status !== 'billed')
                        <a href="{{ route('readings.edit', $reading) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-yellow-600 transition duration-200">
                            <i class="fas fa-edit mr-2"></i>Edit Reading
                        </a>
                    @endif
                    @if($reading->status === 'pending')
                        <form action="{{ route('readings.verify', $reading) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition duration-200" onclick="return confirm('Verify this reading?')">
                                <i class="fas fa-check mr-2"></i>Verify Reading
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Reading Status Banner -->
            <div class="mb-8">
                @php
                    $statusConfig = [
                        'pending' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-800', 'icon' => 'fas fa-hourglass-half'],
                        'verified' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800', 'icon' => 'fas fa-check-circle'],
                        'billed' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' => 'fas fa-file-invoice']
                    ];
                    $config = $statusConfig[$reading->status] ?? $statusConfig['pending'];
                @endphp
                
                <div class="{{ $config['bg'] }} {{ $config['border'] }} border rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="{{ $config['icon'] }} {{ $config['text'] }} text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold {{ $config['text'] }}">
                                Reading Status: {{ ucfirst($reading->status) }}
                            </h3>
                            <p class="text-sm {{ $config['text'] }} opacity-75">
                                @if($reading->status === 'pending')
                                    This reading is awaiting verification by a supervisor.
                                @elseif($reading->status === 'verified')
                                    This reading has been verified and is ready for billing.
                                @elseif($reading->status === 'billed')
                                    This reading has been included in a bill and cannot be modified.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Reading Information -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Reading Details Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-tachometer-alt mr-2"></i>Reading Information
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reading Date</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $reading->reading_date->format('F d, Y') }}</p>
                                    <p class="text-sm text-gray-500">{{ $reading->reading_date->diffForHumans() }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reading Type</label>
                                    @php
                                        $typeColors = [
                                            'actual' => 'bg-green-100 text-green-800',
                                            'estimated' => 'bg-yellow-100 text-yellow-800',
                                            'customer_read' => 'bg-blue-100 text-blue-800'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $typeColors[$reading->reading_type] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $reading->reading_type)) }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Reading</label>
                                    <p class="text-2xl font-bold text-blue-600">{{ number_format($reading->current_reading) }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Previous Reading</label>
                                    @php
                                        $previousReading = $reading->waterMeter->meterReadings()
                                            ->where('reading_date', '<', $reading->reading_date)
                                            ->latest('reading_date')
                                            ->first();
                                        $prevValue = $previousReading ? $previousReading->current_reading : $reading->waterMeter->initial_reading;
                                    @endphp
                                    <p class="text-lg font-semibold text-gray-900">{{ number_format($prevValue) }}</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Consumption</label>
                                    @php
                                        $consumption = max(0, $reading->current_reading - $prevValue);
                                    @endphp
                                    <div class="flex items-center space-x-4">
                                        <p class="text-3xl font-bold text-green-600">{{ number_format($consumption) }} units</p>
                                        @if($consumption > 100)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>High Consumption
                                            </span>
                                        @elseif($consumption == 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-info-circle mr-1"></i>No Consumption
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reader Name</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $reading->reader_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Recorded On</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $reading->created_at->format('M d, Y H:i') }}</p>
                                    <p class="text-sm text-gray-500">{{ $reading->created_at->diffForHumans() }}</p>
                                </div>

                                @if($reading->notes)
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <p class="text-gray-900">{{ $reading->notes }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Billing Information -->
                    @if($reading->status === 'billed')
                        @php
                            $bill = \App\Models\Bill::where('meter_reading_id', $reading->id)->first();
                        @endphp
                        @if($bill)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <i class="fas fa-file-invoice-dollar mr-2"></i>Associated Bill
                                    </h3>
                                </div>
                                
                                <div class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Bill Number</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ $bill->bill_number }}</p>
                                            <p class="text-sm text-gray-500">Generated on {{ $bill->bill_date->format('M d, Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-700">Total Amount</p>
                                            <p class="text-2xl font-bold text-green-600">@lkr($bill->total_amount)</p>
                                            <a href="{{ route('bills.show', $bill) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                View Bill Details →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Customer Information -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-user mr-2"></i>Customer Details
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <img class="h-16 w-16 rounded-full object-cover" 
                                         src="{{ $reading->waterMeter->customer->profile_photo_url }}" 
                                         alt="{{ $reading->waterMeter->customer->full_name }}">
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $reading->waterMeter->customer->full_name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $reading->waterMeter->customer->account_number }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Phone</p>
                                    <p class="text-sm text-gray-900">{{ $reading->waterMeter->customer->phone }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Address</p>
                                    <p class="text-sm text-gray-900">{{ $reading->waterMeter->customer->address }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Customer Type</p>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $reading->waterMeter->customer->customerType->type ?? 'Standard' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('customers.show', $reading->waterMeter->customer) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Customer Profile →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Water Meter Information -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-tachometer-alt mr-2"></i>Meter Details
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Meter Number</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $reading->waterMeter->meter_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Meter Type</p>
                                    <p class="text-sm text-gray-900">{{ ucfirst($reading->waterMeter->meter_type) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Brand & Model</p>
                                    <p class="text-sm text-gray-900">{{ $reading->waterMeter->meter_brand }} {{ $reading->waterMeter->meter_model }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Installation Date</p>
                                    <p class="text-sm text-gray-900">{{ $reading->waterMeter->installation_date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Location</p>
                                    <p class="text-sm text-gray-900">{{ $reading->waterMeter->location_notes ?? 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Status</p>
                                    @php
                                        $meterStatusColors = [
                                            'active' => 'bg-green-100 text-green-800',
                                            'inactive' => 'bg-red-100 text-red-800',
                                            'faulty' => 'bg-yellow-100 text-yellow-800',
                                            'replaced' => 'bg-blue-100 text-blue-800'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $meterStatusColors[$reading->waterMeter->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($reading->waterMeter->status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('meters.show', $reading->waterMeter) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Meter Details →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Reading History -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-history mr-2"></i>Recent Readings
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            @php
                                $recentReadings = $reading->waterMeter->meterReadings()
                                    ->where('id', '!=', $reading->id)
                                    ->latest('reading_date')
                                    ->take(5)
                                    ->get();
                            @endphp
                            
                            @if($recentReadings->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentReadings as $recentReading)
                                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ number_format($recentReading->current_reading) }}</p>
                                                <p class="text-xs text-gray-500">{{ $recentReading->reading_date->format('M d, Y') }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusConfig[$recentReading->status]['bg'] ?? 'bg-gray-100' }} {{ $statusConfig[$recentReading->status]['text'] ?? 'text-gray-800' }}">
                                                {{ ucfirst($recentReading->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No previous readings found.</p>
                            @endif
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('readings.index', ['meter_id' => $reading->waterMeter->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View All Readings →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 