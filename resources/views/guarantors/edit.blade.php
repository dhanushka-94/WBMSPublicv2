@extends('layouts.app')

@section('content')
<div class="w-full">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 text-white py-8 px-6 rounded-lg shadow-lg mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold flex items-center">
                    <i class="fas fa-user-edit mr-3"></i>
                    Edit Guarantor
                </h1>
                <p class="text-blue-100 mt-2">Update guarantor information for {{ $guarantor->full_name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('guarantors.show', $guarantor) }}" 
                   class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-700 transform transition hover:scale-105 shadow-md flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    View Guarantor
                </a>
                <a href="{{ route('guarantors.index') }}" 
                   class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transform transition hover:scale-105 shadow-md flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Guarantors
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('guarantors.update', $guarantor) }}">
        @csrf
        @method('PUT')

        <!-- Personal Information Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-user mr-3"></i>
                    Personal Information
                </h2>
                <p class="text-blue-100 text-sm mt-1">Basic guarantor details and identity information</p>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- First Name -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">First Name *</label>
                        <div class="relative">
                            <input type="text" 
                                   name="first_name" 
                                   id="first_name"
                                   value="{{ old('first_name', $guarantor->first_name) }}"
                                   required
                                   placeholder="Enter first name"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors @error('first_name') border-red-300 @enderror">
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                        @error('first_name')
                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Last Name *</label>
                        <div class="relative">
                            <input type="text" 
                                   name="last_name" 
                                   id="last_name"
                                   value="{{ old('last_name', $guarantor->last_name) }}"
                                   required
                                   placeholder="Enter last name"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors @error('last_name') border-red-300 @enderror">
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                        @error('last_name')
                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIC -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">National Identity Card (NIC) *</label>
                        <div class="relative">
                            <input type="text" 
                                   name="nic" 
                                   id="nic"
                                   value="{{ old('nic', $guarantor->nic) }}"
                                   required
                                   placeholder="Enter NIC number (e.g., 199012345678)"
                                   maxlength="12"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors @error('nic') border-red-300 @enderror">
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-id-card text-gray-400"></i>
                            </div>
                        </div>
                        @error('nic')
                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500">Sri Lankan NIC format (12 digits)</p>
                    </div>

                    <!-- Relationship -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Relationship to Customer *</label>
                        <div class="relative">
                            <select name="relationship"
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors appearance-none @error('relationship') border-red-300 @enderror">
                                <option value="">Select Relationship</option>
                                <option value="Father" {{ old('relationship', $guarantor->relationship) == 'Father' ? 'selected' : '' }}>Father</option>
                                <option value="Mother" {{ old('relationship', $guarantor->relationship) == 'Mother' ? 'selected' : '' }}>Mother</option>
                                <option value="Spouse" {{ old('relationship', $guarantor->relationship) == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                                <option value="Brother" {{ old('relationship', $guarantor->relationship) == 'Brother' ? 'selected' : '' }}>Brother</option>
                                <option value="Sister" {{ old('relationship', $guarantor->relationship) == 'Sister' ? 'selected' : '' }}>Sister</option>
                                <option value="Son" {{ old('relationship', $guarantor->relationship) == 'Son' ? 'selected' : '' }}>Son</option>
                                <option value="Daughter" {{ old('relationship', $guarantor->relationship) == 'Daughter' ? 'selected' : '' }}>Daughter</option>
                                <option value="Uncle" {{ old('relationship', $guarantor->relationship) == 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                <option value="Aunt" {{ old('relationship', $guarantor->relationship) == 'Aunt' ? 'selected' : '' }}>Aunt</option>
                                <option value="Friend" {{ old('relationship', $guarantor->relationship) == 'Friend' ? 'selected' : '' }}>Friend</option>
                                <option value="Other" {{ old('relationship', $guarantor->relationship) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                        @error('relationship')
                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-phone mr-3"></i>
                    Contact Information
                </h2>
                <p class="text-amber-100 text-sm mt-1">Phone number, email, and address details</p>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Phone -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Phone Number *</label>
                        <div class="relative">
                            <input type="tel" 
                                   name="phone" 
                                   id="phone"
                                   value="{{ old('phone', $guarantor->phone) }}"
                                   required
                                   placeholder="Enter phone number (e.g., 0771234567)"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none transition-colors @error('phone') border-red-300 @enderror">
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                        </div>
                        @error('phone')
                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Email Address</label>
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   value="{{ old('email', $guarantor->email) }}"
                                   placeholder="Enter email address (optional)"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none transition-colors @error('email') border-red-300 @enderror">
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                        @error('email')
                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">Residential Address *</label>
                        <div class="relative">
                            <textarea name="address" 
                                      id="address" 
                                      rows="4"
                                      required
                                      placeholder="Enter complete address with house number, street, city, and postal code"
                                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none transition-colors resize-none @error('address') border-red-300 @enderror">{{ old('address', $guarantor->address) }}</textarea>
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-map-marker-alt text-gray-400"></i>
                            </div>
                        </div>
                        @error('address')
                            <p class="text-xs text-red-600 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Management Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-toggle-on mr-3"></i>
                    Status Management
                </h2>
                <p class="text-green-100 text-sm mt-1">Control guarantor availability and status</p>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Guarantor Status</label>
                            <div class="space-y-3">
                                <label class="flex items-center space-x-3 cursor-pointer p-3 rounded-lg border-2 border-gray-200 hover:border-green-300 transition-colors">
                                    <input type="radio" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', $guarantor->is_active) == '1' ? 'checked' : '' }}
                                           class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-2xl">🟢</span>
                                        <div>
                                            <span class="text-sm font-semibold text-gray-700">Active</span>
                                            <p class="text-xs text-gray-500">Can be assigned to new customers</p>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-center space-x-3 cursor-pointer p-3 rounded-lg border-2 border-gray-200 hover:border-red-300 transition-colors">
                                    <input type="radio" 
                                           name="is_active" 
                                           value="0" 
                                           {{ old('is_active', $guarantor->is_active) == '0' ? 'checked' : '' }}
                                           class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-2xl">🔴</span>
                                        <div>
                                            <span class="text-sm font-semibold text-gray-700">Inactive</span>
                                            <p class="text-xs text-gray-500">Cannot be assigned to new customers</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Current Customer Associations</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                @if($guarantor->customers && $guarantor->customers->count() > 0)
                                    <p class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-users mr-1"></i>
                                        {{ $guarantor->customers->count() }} customer(s) backed by this guarantor:
                                    </p>
                                    <div class="space-y-1 max-h-32 overflow-y-auto">
                                        @foreach($guarantor->customers as $customer)
                                            <div class="text-xs text-gray-500 flex items-center">
                                                <i class="fas fa-user mr-1"></i>
                                                {{ $customer->full_name }} ({{ $customer->account_number }})
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        No customers currently backed by this guarantor
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-cogs mr-3"></i>
                    System Information
                </h2>
                <p class="text-gray-100 text-sm mt-1">Read-only system generated information</p>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Guarantor ID -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Guarantor ID</label>
                        <div class="relative">
                            <input type="text" 
                                   value="{{ $guarantor->guarantor_id }}" 
                                   disabled
                                   class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 rounded-lg text-gray-600">
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">This ID is automatically generated and cannot be changed</p>
                    </div>

                    <!-- Created Date -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Registration Date</label>
                        <div class="relative">
                            <input type="text" 
                                   value="{{ $guarantor->created_at->format('M d, Y \a\t g:i A') }}" 
                                   disabled
                                   class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 rounded-lg text-gray-600">
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-calendar text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Last Updated -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Last Updated</label>
                        <div class="relative">
                            <input type="text" 
                                   value="{{ $guarantor->updated_at->format('M d, Y \a\t g:i A') }}" 
                                   disabled
                                   class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 rounded-lg text-gray-600">
                            <div class="absolute top-3 right-3">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Current Status Display -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Current Status</label>
                        <div class="relative">
                            <div class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 rounded-lg text-gray-600 flex items-center">
                                @if($guarantor->is_active)
                                    <span class="text-green-600 mr-2">🟢</span>
                                    <span>Active Guarantor</span>
                                @else
                                    <span class="text-red-600 mr-2">🔴</span>
                                    <span>Inactive Guarantor</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <a href="{{ route('guarantors.show', $guarantor) }}" 
                   class="px-8 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transform transition hover:scale-105 shadow-md text-center">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-semibold transform transition hover:scale-105 shadow-md">
                    <i class="fas fa-save mr-2"></i>
                    Update Guarantor
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-format NIC input
document.getElementById('nic').addEventListener('input', function(e) {
    // Remove any non-digit characters
    let value = e.target.value.replace(/\D/g, '');
    // Limit to 12 digits
    if (value.length > 12) {
        value = value.substr(0, 12);
    }
    e.target.value = value;
});

// Auto-format phone input
document.getElementById('phone').addEventListener('input', function(e) {
    // Remove any non-digit characters except + at the beginning
    let value = e.target.value.replace(/[^\d+]/g, '');
    e.target.value = value;
});
</script>
@endsection 