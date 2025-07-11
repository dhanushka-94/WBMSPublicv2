@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 text-white py-8 px-6 rounded-lg shadow-lg mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold flex items-center">
                    <i class="fas fa-user-shield mr-3"></i>
                    Guarantor Management
                </h1>
                <p class="text-blue-100 mt-2">Manage guarantors and their customer relationships</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('guarantors.create') }}" 
                   class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transform transition hover:scale-105 shadow-md flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Guarantor
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Guarantors</p>
                    <p class="text-3xl font-bold">{{ $guarantors->total() }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-user-shield text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Active Guarantors</p>
                    <p class="text-3xl font-bold">{{ $guarantors->where('is_active', true)->count() }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Inactive Guarantors</p>
                    <p class="text-3xl font-bold">{{ $guarantors->where('is_active', false)->count() }}</p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-pause-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">With Customers</p>
                    <p class="text-3xl font-bold">{{ $guarantors->filter(function($g) { return $g->customers && $g->customers->count() > 0; })->count() }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-search text-blue-600 mr-2"></i>
            <h3 class="text-lg font-semibold text-gray-800">Search & Filter Guarantors</h3>
        </div>
        
        <form method="GET" action="{{ route('guarantors.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search by ID, name, NIC, or phone..." 
                       class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
            
            <div class="relative">
                <select name="status" class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors appearance-none">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-filter text-gray-400"></i>
                </div>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
            
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transform transition hover:scale-105 shadow-md flex items-center justify-center">
                <i class="fas fa-search mr-2"></i>
                Search
            </button>
            
            @if(request('search') || request('status'))
                <a href="{{ route('guarantors.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transform transition hover:scale-105 shadow-md flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Guarantors Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-list mr-2 text-blue-600"></i>
                    Guarantor Directory
                </h3>
                <p class="text-sm text-gray-600">{{ $guarantors->total() }} total guarantors</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-user mr-1"></i>Guarantor
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-phone mr-1"></i>Contact
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-id-card mr-1"></i>NIC
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-heart mr-1"></i>Relationship
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-users mr-1"></i>Customers
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-toggle-on mr-1"></i>Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-cogs mr-1"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($guarantors as $guarantor)
                        <tr class="hover:bg-blue-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                                            {{ substr($guarantor->first_name, 0, 1) }}{{ substr($guarantor->last_name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $guarantor->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 flex items-center">
                                            <i class="fas fa-hashtag mr-1 text-xs"></i>
                                            {{ $guarantor->guarantor_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-2"></i>
                                    {{ $guarantor->phone }}
                                </div>
                                @if($guarantor->email)
                                    <div class="text-sm text-gray-500 flex items-center mt-1">
                                        <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                        {{ $guarantor->email }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">
                                    {{ $guarantor->nic }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($guarantor->relationship == 'Father') bg-blue-100 text-blue-800
                                    @elseif($guarantor->relationship == 'Mother') bg-pink-100 text-pink-800
                                    @elseif($guarantor->relationship == 'Spouse') bg-purple-100 text-purple-800
                                    @elseif($guarantor->relationship == 'Brother' || $guarantor->relationship == 'Sister') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    <i class="fas fa-heart mr-1"></i>
                                    {{ $guarantor->relationship }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $guarantor->customers ? $guarantor->customers->count() : 0 }} customers
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($guarantor->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        🟢 Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        🔴 Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('guarantors.show', $guarantor) }}" 
                                       class="text-blue-600 hover:text-blue-900 hover:bg-blue-100 px-2 py-1 rounded transition-colors"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('guarantors.edit', $guarantor) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 px-2 py-1 rounded transition-colors"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$guarantor->customers || $guarantor->customers->count() == 0)
                                        <form method="POST" action="{{ route('guarantors.destroy', $guarantor) }}" 
                                              onsubmit="return confirm('Are you sure you want to delete this guarantor?')" 
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 hover:bg-red-100 px-2 py-1 rounded transition-colors"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-user-shield text-6xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No guarantors found</h3>
                                    <p class="text-gray-500 mb-4">Get started by creating your first guarantor.</p>
                                    <a href="{{ route('guarantors.create') }}" 
                                       class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-2 rounded-lg font-semibold transform transition hover:scale-105 shadow-md">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create Guarantor
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($guarantors->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $guarantors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 