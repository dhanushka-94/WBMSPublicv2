@foreach([
    'success' => ['border-green-300 bg-green-50 text-green-800', 'fa-check-circle'],
    'warning' => ['border-yellow-300 bg-yellow-50 text-yellow-800', 'fa-exclamation-triangle'],
    'error' => ['border-red-300 bg-red-50 text-red-800', 'fa-times-circle'],
] as $key => [$classes, $icon])
    @if(session($key))
        <div class="mb-4 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm {{ $classes }}" role="alert">
            <i class="fas {{ $icon }} mt-0.5"></i>
            <div>{{ session($key) }}</div>
        </div>
    @endif
@endforeach
@if($errors->any())
    <div class="mb-4 flex items-start gap-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        <i class="fas fa-times-circle mt-0.5"></i>
        <div>{{ $errors->first() }}</div>
    </div>
@endif
