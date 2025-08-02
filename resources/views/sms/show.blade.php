@extends('layouts.app')

@section('title', 'SMS Details')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-sms text-white text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-white">SMS Notification Details</h1>
                    </div>
                    <a href="{{ route('sms.index') }}" 
                       class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>

            <!-- Basic Information & Customer Info -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>Basic Information
                        </h2>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">SMS ID:</span>
                                <span class="text-sm text-gray-900">{{ $sms_notification->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">Type:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $sms_notification->getTypeLabelAttribute() }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">Status:</span>
                                @if($sms_notification->status === 'sent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Sent
                                    </span>
                                @elseif($sms_notification->status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">Phone Number:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $sms_notification->phone_number }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">Created:</span>
                                <span class="text-sm text-gray-900">{{ $sms_notification->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            @if($sms_notification->sent_at)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">Sent At:</span>
                                <span class="text-sm text-gray-900">{{ $sms_notification->sent_at->format('M d, Y h:i A') }}</span>
                            </div>
                            @endif
                            @if($sms_notification->scheduled_at)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">Scheduled For:</span>
                                <span class="text-sm text-gray-900">{{ $sms_notification->scheduled_at->format('M d, Y h:i A') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">Retry Count:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sms_notification->retry_count > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $sms_notification->retry_count }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer & Bill Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user text-green-500 mr-2"></i>Customer & Bill Information
                        </h2>
                        @if($sms_notification->customer)
                            <div class="mb-4">
                                <h3 class="text-sm font-medium text-gray-600 mb-2 flex items-center">
                                    <i class="fas fa-user-circle text-blue-500 mr-1"></i>Customer Details
                                </h3>
                                <div class="space-y-2 ml-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Name:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $sms_notification->customer->full_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Account:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $sms_notification->customer->account_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Email:</span>
                                        <span class="text-sm text-gray-900">{{ $sms_notification->customer->email ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Address:</span>
                                        <span class="text-sm text-gray-900">{{ $sms_notification->customer->address ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('customers.show', $sms_notification->customer) }}" 
                                       class="inline-flex items-center px-3 py-1 border border-blue-300 text-sm text-blue-600 rounded-md hover:bg-blue-50 transition duration-200">
                                        <i class="fas fa-external-link-alt mr-1"></i>View Customer
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-gray-500 py-6">
                                <i class="fas fa-user-slash text-3xl mb-2"></i>
                                <p class="text-sm">No customer associated with this SMS</p>
                            </div>
                        @endif

                        @if($sms_notification->bill)
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-medium text-gray-600 mb-2 flex items-center">
                                    <i class="fas fa-file-invoice-dollar text-green-500 mr-1"></i>Bill Details
                                </h3>
                                <div class="space-y-2 ml-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Bill ID:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $sms_notification->bill->id }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Amount:</span>
                                        <span class="text-sm font-medium text-gray-900">Rs. {{ number_format($sms_notification->bill->total_amount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Status:</span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $sms_notification->bill->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($sms_notification->bill->status) }}
                                        </span>
                                    </div>
                                    @if($sms_notification->bill->due_date)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Due Date:</span>
                                        <span class="text-sm text-gray-900">{{ $sms_notification->bill->due_date->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('bills.show', $sms_notification->bill) }}" 
                                       class="inline-flex items-center px-3 py-1 border border-green-300 text-sm text-green-600 rounded-md hover:bg-green-50 transition duration-200">
                                        <i class="fas fa-external-link-alt mr-1"></i>View Bill
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Message Content -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-comment text-purple-500 mr-2"></i>Message Content
                    </h2>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <p class="text-gray-800 whitespace-pre-line leading-relaxed">{{ $sms_notification->message }}</p>
                    </div>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-2"></i>
                        Message Length: {{ strlen($sms_notification->message) }} characters
                        ({{ ceil(strlen($sms_notification->message) / 160) }} SMS{{ ceil(strlen($sms_notification->message) / 160) > 1 ? ' parts' : '' }})
                    </div>
                </div>

                <!-- Technical Details & Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Technical Details -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cog text-gray-500 mr-2"></i>Technical Details
                        </h2>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-600">HTTP Status:</span>
                                @if($sms_notification->http_status)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sms_notification->http_status == 200 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $sms_notification->http_status }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">N/A</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600 block mb-2">Response Data:</span>
                                @if($sms_notification->response_data)
                                    <div class="bg-gray-900 text-green-400 p-3 rounded-md text-xs font-mono overflow-x-auto">
                                        <pre>{{ json_encode($sms_notification->response_data, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">N/A</span>
                                @endif
                            </div>
                            @if($sms_notification->error_message)
                            <div>
                                <span class="text-sm font-medium text-gray-600 block mb-2">Error Message:</span>
                                <div class="bg-red-50 border border-red-200 rounded-md p-3">
                                    <p class="text-sm text-red-800">{{ $sms_notification->error_message }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-tools text-orange-500 mr-2"></i>Actions
                        </h2>
                        <div class="space-y-3">
                            @if($sms_notification->status === 'failed')
                                <form method="POST" action="{{ route('sms.resend', $sms_notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center justify-center" 
                                            onclick="return confirm('Resend this SMS message?')">
                                        <i class="fas fa-redo mr-2"></i>Resend SMS
                                    </button>
                                </form>
                            @endif

                            <button type="button" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center justify-center" 
                                    onclick="copyMessage()">
                                <i class="fas fa-copy mr-2"></i>Copy Message
                            </button>

                            <form method="POST" action="{{ route('sms.destroy', $sms_notification) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center justify-center" 
                                        onclick="return confirm('Delete this SMS record permanently?')">
                                    <i class="fas fa-trash mr-2"></i>Delete Record
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyMessage() {
    const message = `{{ addslashes($sms_notification->message) }}`;
    navigator.clipboard.writeText(message).then(function() {
        // Create and show success notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center';
        notification.innerHTML = `
            <i class="fas fa-check mr-2"></i>
            <span>Message copied to clipboard!</span>
        `;
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                notification.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    notification.parentNode.removeChild(notification);
                }, 300);
            }
        }, 3000);
    }).catch(function() {
        alert('Failed to copy message to clipboard');
    });
}
</script>
@endsection