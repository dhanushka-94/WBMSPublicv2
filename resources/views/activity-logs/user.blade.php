@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 shadow-lg">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <img class="h-16 w-16 rounded-full border-4 border-white shadow-lg" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $user->name }}'s Activity</h1>
                        <p class="text-indigo-100 mt-1">{{ $user->email }} • {{ ucfirst($user->role ?? 'User') }}</p>
                        <p class="text-indigo-100 text-sm">{{ $activities->total() }} total activities</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('activity-logs.index') }}" class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-medium hover:bg-indigo-50 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>All Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-day text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Today's Activities</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['today'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-week text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">This Week</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['week'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Last Login</p>
                        <p class="text-sm font-bold text-gray-900">{{ $lastLogin ?? 'Never' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-orange-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Activities</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activities->total() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-filter mr-2"></i>Filters
                </h3>
            </div>

            <div class="p-6">
                <form method="GET" action="{{ route('activity-logs.user', $user) }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Action Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                            <select name="action" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Actions</option>
                                @foreach($availableActions as $action)
                                <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Module Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                            <select name="module" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Modules</option>
                                @foreach($availableModules as $module)
                                <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>
                                    {{ ucfirst($module) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Status</option>
                                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                            <select name="date_range" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Time</option>
                                <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="yesterday" {{ request('date_range') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="flex space-x-4">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search in descriptions, IP addresses..." class="flex-1 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition duration-200">
                                <i class="fas fa-search mr-2"></i>Filter
                            </button>
                            @if(request()->hasAny(['action', 'module', 'status', 'date_range', 'search']))
                            <a href="{{ route('activity-logs.user', $user) }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-200">
                                <i class="fas fa-times mr-2"></i>Clear
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-history mr-2"></i>Activity Timeline
                    </h3>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">
                            Showing {{ $activities->firstItem() }}-{{ $activities->lastItem() }} of {{ $activities->total() }}
                        </span>
                    </div>
                </div>
            </div>

            @if($activities->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($activities as $activity)
                <div class="p-6 hover:bg-gray-50 transition duration-200">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full {{ $activity->status === 'failed' ? 'bg-red-100' : ($activity->status === 'success' ? 'bg-green-100' : 'bg-blue-100') }} flex items-center justify-center">
                                <i class="{{ $activity->action_icon }} {{ $activity->status === 'failed' ? 'text-red-600' : ($activity->status === 'success' ? 'text-green-600' : 'text-blue-600') }}"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                        @if($activity->module !== 'general')
                                            <span class="text-gray-500">in {{ ucfirst($activity->module) }}</span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $activity->description }}</p>
                                    
                                    @if($activity->subject_name)
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-tag mr-1"></i>{{ $activity->subject_name }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    {!! $activity->status_badge !!}
                                    
                                    @if($activity->duration_ms)
                                        <span class="text-xs text-gray-500">{{ $activity->duration_ms }}ms</span>
                                    @endif
                                    
                                    <a href="{{ route('activity-logs.show', $activity) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Additional Info -->
                            <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                <span>
                                    <i class="fas fa-clock mr-1"></i>{{ $activity->formatted_created_at }}
                                </span>
                                
                                @if($activity->ip_address)
                                <span>
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $activity->ip_address }}
                                </span>
                                @endif
                                
                                @if($activity->method && $activity->url)
                                <span>
                                    <i class="fas fa-globe mr-1"></i>{{ $activity->method }} {{ Str::limit($activity->url, 50) }}
                                </span>
                                @endif
                            </div>

                            <!-- Error Message -->
                            @if($activity->error_message)
                            <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i>{{ $activity->error_message }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $activities->links() }}
            </div>
            @else
            <div class="p-12 text-center">
                <i class="fas fa-history text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Activities Found</h3>
                <p class="text-gray-500">This user hasn't performed any activities yet or no activities match your filters.</p>
                
                @if(request()->hasAny(['action', 'module', 'status', 'date_range', 'search']))
                <a href="{{ route('activity-logs.user', $user) }}" class="inline-flex items-center mt-4 text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 