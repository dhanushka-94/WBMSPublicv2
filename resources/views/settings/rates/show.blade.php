@extends('layouts.app')

@section('title', 'Rate Details')

@section('content')
<div class="mx-auto">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $rate->name }}</h1>
                <p class="text-blue-100 mt-1">Rate details and configuration</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('settings.rates.edit', $rate) }}" 
                   class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit Rate
                </a>
                <a href="{{ route('settings.rates.duplicate', $rate) }}" 
                   class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors">
                    <i class="fas fa-copy mr-2"></i>Duplicate
                </a>
                <a href="{{ route('settings.rates.index') }}" 
                   class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Rates
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Rate Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Rate Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Details -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Rate Name</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $rate->name }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Customer Type</label>
                                <p class="text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $rate->customer_type }}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Unit Range</label>
                                <p class="text-lg text-gray-900">
                                    @if($rate->tier_to)
                                        {{ $rate->tier_from }} - {{ $rate->tier_to }} units
                                    @else
                                        {{ $rate->tier_from }}+ units
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Status</label>
                                <p class="text-gray-900">
                                    @if($rate->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Inactive
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Charges -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Rate per Unit</label>
                                <p class="text-2xl font-bold text-green-600">
                                    Rs. {{ number_format($rate->rate_per_unit, 2) }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Fixed Charge</label>
                                <p class="text-lg text-gray-900">
                                    @if($rate->fixed_charge > 0)
                                        Rs. {{ number_format($rate->fixed_charge, 2) }}
                                    @else
                                        <span class="text-gray-500">No fixed charge</span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Effective From</label>
                                <p class="text-gray-900">{{ $rate->effective_from->format('F d, Y') }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Effective To</label>
                                <p class="text-gray-900">
                                    @if($rate->effective_to)
                                        {{ $rate->effective_to->format('F d, Y') }}
                                    @else
                                        <span class="text-gray-500">No end date</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($rate->description)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500">Description</label>
                            <p class="mt-1 text-gray-900">{{ $rate->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="bg-white rounded-lg shadow-md mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Usage Statistics</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Bills Generated</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $rate->bills()->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        Rs. {{ number_format($rate->bills()->sum('total_amount'), 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-calendar text-purple-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Last Used</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        @if($rate->bills()->exists())
                                            {{ $rate->bills()->latest()->first()->created_at->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-500 text-sm">Never used</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rate Calculation Examples -->
            <div class="bg-white rounded-lg shadow-md mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rate Calculation Examples</h3>
                    
                    <div class="space-y-4">
                        @php
                            $examples = [];
                            
                            // Generate example calculations
                            if ($rate->tier_to) {
                                $midPoint = intval(($rate->tier_from + $rate->tier_to) / 2);
                                $examples[] = $midPoint;
                                $examples[] = $rate->tier_to;
                            } else {
                                $examples[] = $rate->tier_from + 5;
                                $examples[] = $rate->tier_from + 15;
                            }
                        @endphp

                        @foreach($examples as $consumption)
                            @php
                                if ($consumption >= $rate->tier_from && ($rate->tier_to === null || $consumption <= $rate->tier_to)) {
                                    $unitsInTier = $consumption - $rate->tier_from + 1;
                                    if ($rate->tier_to !== null) {
                                        $unitsInTier = min($unitsInTier, $rate->tier_to - $rate->tier_from + 1);
                                    }
                                    $charge = $unitsInTier * $rate->rate_per_unit;
                                } else {
                                    $charge = 0;
                                    $unitsInTier = 0;
                                }
                            @endphp
                            
                            @if($unitsInTier > 0)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <div>
                                        <span class="font-medium">{{ $consumption }} units consumption</span>
                                        <p class="text-sm text-gray-600">
                                            {{ $unitsInTier }} units @ Rs. {{ number_format($rate->rate_per_unit, 2) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-semibold text-green-600">
                                            Rs. {{ number_format($charge, 2) }}
                                        </span>
                                        @if($rate->fixed_charge > 0)
                                            <p class="text-sm text-gray-600">
                                                + Rs. {{ number_format($rate->fixed_charge, 2) }} fixed
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('settings.rates.edit', $rate) }}" 
                       class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-edit mr-2"></i>Edit Rate
                    </a>

                    <a href="{{ route('settings.rates.duplicate', $rate) }}" 
                       class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-copy mr-2"></i>Duplicate Rate
                    </a>

                    <form action="{{ route('settings.rates.toggle-status', $rate) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full {{ $rate->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-md transition-colors flex items-center justify-center"
                                onclick="return confirm('Are you sure you want to {{ $rate->is_active ? 'deactivate' : 'activate' }} this rate?')">
                            <i class="fas fa-{{ $rate->is_active ? 'pause' : 'play' }} mr-2"></i>
                            {{ $rate->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    @if(!$rate->bills()->exists())
                        <form action="{{ route('settings.rates.destroy', $rate) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors flex items-center justify-center"
                                    onclick="return confirm('Are you sure you want to delete this rate? This action cannot be undone.')">
                                <i class="fas fa-trash mr-2"></i>Delete Rate
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Rate Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Created:</span>
                        <span class="text-gray-900">{{ $rate->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Last Updated:</span>
                        <span class="text-gray-900">{{ $rate->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Rate ID:</span>
                        <span class="text-gray-900">#{{ $rate->id }}</span>
                    </div>
                </div>

                @if($rate->bills()->exists())
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center text-sm text-yellow-600">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span>Rate is in use and cannot be deleted</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 