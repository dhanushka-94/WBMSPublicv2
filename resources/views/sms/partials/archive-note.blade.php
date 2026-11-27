@php($archiveMonths = \App\Models\SmsNotification::archiveMonths())
<div class="mb-6 rounded-lg border border-blue-100 bg-white px-4 py-3 text-sm text-gray-700">
    This list keeps the last {{ \App\Models\SmsNotification::DATABASE_RETENTION_DAYS }} days. Every SMS is also saved in a monthly text file.
    @if(!empty($usageNote))
        <span class="text-gray-500">Costs on this page only cover the days still in the list; older months are in the files.</span>
    @endif
    @forelse($archiveMonths as $month)
        <a href="{{ route('sms.archive', $month) }}" class="ml-2 font-medium text-blue-700 underline">{{ $month }}</a>
    @empty
        <span class="ml-2 text-gray-500">The first file is created when the next SMS is sent.</span>
    @endforelse
</div>
