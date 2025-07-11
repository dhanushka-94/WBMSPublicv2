@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-info-circle mr-3"></i>Activity Log Details
                    </h1>
                    <p class="text-purple-100 mt-2">Detailed information about activity #{{ $activity->id }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('activity-logs.index') }}" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-medium hover:bg-purple-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Activity Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-clipboard-list mr-2"></i>Activity Information
                            </h3>
                            <div class="flex items-center space-x-3">
                                <i class="{{ $activity->action_icon }}"></i>
                                {!! $activity->status_badge !!}
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Activity ID</label>
                                <p class="text-gray-900 font-mono text-sm">#{{ $activity->id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Timestamp</label>
                                <p class="text-gray-900">{{ $activity->formatted_created_at }}</p>
                                <p class="text-sm text-gray-500">{{ $activity->time_ago }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                                <p class="text-gray-900 font-medium">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                                <span class="px-3 py-1 text-sm font-semibold bg-blue-100 text-blue-800 rounded-full">
                                    {{ ucfirst($activity->module) }}
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <p class="text-gray-900 p-4 bg-gray-50 rounded-lg">{{ $activity->description }}</p>
                        </div>

                        <!-- Subject Information -->
                        @if($activity->subject_type || $activity->subject_name)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                @if($activity->subject_name)
                                    <p class="text-gray-900 font-medium">{{ $activity->subject_name }}</p>
                                @endif
                                @if($activity->subject_type)
                                    <p class="text-sm text-gray-600">Type: {{ class_basename($activity->subject_type) }}</p>
                                @endif
                                @if($activity->subject_id)
                                    <p class="text-sm text-gray-600">ID: {{ $activity->subject_id }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Performance Info -->
                        @if($activity->duration_ms)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Performance</label>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-blue-600 mr-2"></i>
                                    <span class="text-gray-900">{{ $activity->duration_ms }}ms</span>
                                </div>
                                @if($activity->duration_ms > 1000)
                                    <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                        Slow Response
                                    </span>
                                @elseif($activity->duration_ms < 100)
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                        Fast Response
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Error Information -->
                        @if($activity->error_message)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Error Details</label>
                            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-2 mt-1"></i>
                                    <p class="text-red-800 font-mono text-sm">{{ $activity->error_message }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Data Changes -->
                @if($activity->old_values || $activity->new_values)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-8">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-exchange-alt mr-2"></i>Data Changes
                        </h3>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($activity->old_values)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Previous Values</label>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <pre class="text-sm text-red-800 whitespace-pre-wrap overflow-x-auto">{{ json_encode($activity->old_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                            @endif

                            @if($activity->new_values)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">New Values</label>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <pre class="text-sm text-green-800 whitespace-pre-wrap overflow-x-auto">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Additional Properties -->
                @if($activity->properties && count($activity->properties) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-8">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-cog mr-2"></i>Additional Properties
                        </h3>
                    </div>

                    <div class="p-6">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <pre class="text-sm text-gray-800 whitespace-pre-wrap overflow-x-auto">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- User Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-user mr-2"></i>User Details
                        </h3>
                    </div>

                    <div class="p-6">
                        @if($activity->user)
                            <div class="flex items-center space-x-4 mb-4">
                                <img class="h-12 w-12 rounded-full" src="{{ $activity->user->profile_photo_url }}" alt="{{ $activity->user_name }}">
                                <div>
                                    <p class="text-gray-900 font-medium">{{ $activity->user_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $activity->user_email }}</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role</label>
                                    <span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded-full">
                                        {{ ucfirst($activity->user_role) }}
                                    </span>
                                </div>
                                <div>
                                    <a href="{{ route('activity-logs.user', $activity->user) }}" class="inline-flex items-center text-sm text-purple-600 hover:text-purple-800">
                                        <i class="fas fa-history mr-1"></i>View User's Activity
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-user-slash text-gray-400 text-2xl mb-2"></i>
                                <p class="text-gray-500">User information not available</p>
                                @if($activity->user_name || $activity->user_email)
                                    <div class="mt-2 text-sm">
                                        @if($activity->user_name)
                                            <p class="text-gray-600">Name: {{ $activity->user_name }}</p>
                                        @endif
                                        @if($activity->user_email)
                                            <p class="text-gray-600">Email: {{ $activity->user_email }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Request Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-globe mr-2"></i>Request Details
                        </h3>
                    </div>

                    <div class="p-6 space-y-4">
                        @if($activity->ip_address)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                            <p class="text-gray-900 font-mono text-sm">{{ $activity->ip_address }}</p>
                        </div>
                        @endif

                        @if($activity->method)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">HTTP Method</label>
                            <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded">
                                {{ $activity->method }}
                            </span>
                        </div>
                        @endif

                        @if($activity->url)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                            <p class="text-gray-900 text-sm break-all">{{ $activity->url }}</p>
                        </div>
                        @endif

                        @if($activity->route_name)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Route Name</label>
                            <p class="text-gray-900 font-mono text-sm">{{ $activity->route_name }}</p>
                        </div>
                        @endif

                        @if($activity->session_id)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Session ID</label>
                            <p class="text-gray-900 font-mono text-xs break-all">{{ $activity->session_id }}</p>
                        </div>
                        @endif

                        @if($activity->user_agent)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User Agent</label>
                            <p class="text-gray-600 text-xs break-words">{{ $activity->user_agent }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-bolt mr-2"></i>Quick Actions
                        </h3>
                    </div>

                    <div class="p-6 space-y-3">
                        @if($activity->user_id)
                        <a href="{{ route('activity-logs.index', ['user_id' => $activity->user_id]) }}" class="block w-full text-center bg-blue-50 text-blue-700 py-2 px-4 rounded-lg hover:bg-blue-100 transition duration-200">
                            <i class="fas fa-filter mr-2"></i>Filter by this User
                        </a>
                        @endif

                        <a href="{{ route('activity-logs.index', ['action' => $activity->action]) }}" class="block w-full text-center bg-green-50 text-green-700 py-2 px-4 rounded-lg hover:bg-green-100 transition duration-200">
                            <i class="fas fa-search mr-2"></i>Filter by Action
                        </a>

                        <a href="{{ route('activity-logs.index', ['module' => $activity->module]) }}" class="block w-full text-center bg-purple-50 text-purple-700 py-2 px-4 rounded-lg hover:bg-purple-100 transition duration-200">
                            <i class="fas fa-layer-group mr-2"></i>Filter by Module
                        </a>

                        @if($activity->ip_address)
                        <a href="{{ route('activity-logs.index', ['search' => $activity->ip_address]) }}" class="block w-full text-center bg-orange-50 text-orange-700 py-2 px-4 rounded-lg hover:bg-orange-100 transition duration-200">
                            <i class="fas fa-map-marker-alt mr-2"></i>Filter by IP
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 