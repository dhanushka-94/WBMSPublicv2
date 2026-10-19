@extends('layouts.app')

@section('title', 'Customer Meters')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $customer->full_name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $customer->account_number }} · Water meters</p>
                </div>
                <a href="{{ route('customers.show', $customer) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Customer
                </a>
            </div>
        </div>

        @forelse($meters as $meter)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6">
                    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-4">
                            @if($meter->getQrCodeUrl())
                                <img src="{{ $meter->getQrCodeUrl() }}" alt="QR code for {{ $meter->meter_number }}" class="h-24 w-24 rounded border border-gray-200 bg-white">
                            @endif
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">{{ $meter->meter_number }}</h2>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $meter->meter_brand }} {{ $meter->meter_model }}
                                    @if($meter->meter_size)
                                        · {{ $meter->meter_size }}mm {{ ucfirst($meter->meter_type) }}
                                    @endif
                                </p>
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-gray-100 text-gray-800',
                                        'faulty' => 'bg-red-100 text-red-800',
                                        'replaced' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                @endphp
                                <span class="mt-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$meter->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($meter->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-2xl font-bold text-purple-600">{{ number_format($meter->current_reading, 2) }}</p>
                            <p class="text-sm text-gray-500">Current reading</p>
                        </div>
                    </div>

                    <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Installed</dt>
                            <dd class="font-medium text-gray-900">{{ $meter->installation_date?->format('M d, Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Initial reading</dt>
                            <dd class="font-medium text-gray-900">{{ number_format($meter->initial_reading, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Location</dt>
                            <dd class="font-medium text-gray-900">{{ $meter->location_notes ?: ($meter->address ?: '—') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('meters.show', $meter) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            <i class="fas fa-eye mr-2"></i>Meter details
                        </a>
                        <a href="{{ route('meters.qr-code', $meter) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-qrcode mr-2"></i>QR code
                        </a>
                    </div>
                </div>

                <div class="border-t border-gray-200">
                    <div class="px-6 py-3 bg-gray-50 text-sm font-medium text-gray-700">Latest readings</div>
                    @if($meter->meterReadings->isEmpty())
                        <p class="px-6 py-4 text-sm text-gray-500">No readings recorded.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($meter->meterReadings as $reading)
                                <li class="px-6 py-3 flex items-center justify-between text-sm">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ number_format($reading->current_reading, 2) }}</p>
                                        <p class="text-gray-500">{{ $reading->reading_date?->format('M d, Y') }}</p>
                                    </div>
                                    <a href="{{ route('readings.show', $reading) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-12 text-center text-gray-500">
                <i class="fas fa-tachometer-alt text-4xl mb-4"></i>
                <p>No water meters for this customer.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
