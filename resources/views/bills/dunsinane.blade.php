<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill {{ $bill->bill_number }}</title>
    <style>
        body { font-family: "Noto Sans Tamil", Arial, sans-serif; color: #111; margin: 24px; }
        body.thermal { width: 280px; margin: 8px auto; font-size: 12px; }
        h1 { font-size: 18px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td { padding: 4px 0; vertical-align: top; }
        .right { text-align: right; }
        .tools { margin-bottom: 16px; }
        .tools a { margin-right: 8px; }
        @media print { .tools { display: none; } }
    </style>
</head>
<body class="{{ $format === 'thermal' ? 'thermal' : '' }}">
@php
    $labels = [
        'en' => [
            'scheme' => 'Dunsinane Water Supply Scheme',
            'title' => 'Water bill',
            'customer' => 'Customer',
            'account' => 'Account',
            'meter' => 'Meter',
            'previous' => 'Previous reading',
            'current' => 'Current reading',
            'consumption' => 'Consumption',
            'water' => 'Water charges',
            'fixed' => 'Fixed charge',
            'outstanding' => 'Outstanding',
            'total' => 'Total payable',
            'due' => 'Due date',
            'contact' => 'Contact the estate office for payment queries.',
        ],
        'ta' => [
            'scheme' => 'டன்சினேன் நீர் வழங்கல் திட்டம்',
            'title' => 'நீர் கட்டண பட்டியல்',
            'customer' => 'வாடிக்கையாளர்',
            'account' => 'கணக்கு எண்',
            'meter' => 'மீட்டர் எண்',
            'previous' => 'முந்தைய அளவு',
            'current' => 'தற்போதைய அளவு',
            'consumption' => 'நுகர்வு',
            'water' => 'நீர் கட்டணம்',
            'fixed' => 'நிலையான கட்டணம்',
            'outstanding' => 'நிலுவை',
            'total' => 'செலுத்த வேண்டிய மொத்தம்',
            'due' => 'செலுத்த வேண்டிய தினம்',
            'contact' => 'கட்டண வினவல்களுக்கு தோட்ட அலுவலகத்தை அணுகவும்.',
        ],
    ];
    $languages = $language === 'both' ? ['en', 'ta'] : [$language];
@endphp
<div class="tools">
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => 'both']) }}">English + Tamil</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => 'en']) }}">English</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => 'ta']) }}">Tamil</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'format' => 'thermal', 'lang' => $language]) }}">Thermal</a>
</div>
@foreach($languages as $code)
    @php $label = $labels[$code]; @endphp
    <h1>{{ $label['scheme'] }}</h1>
    <p>{{ $label['title'] }} {{ $bill->bill_number }}</p>
    <table>
        <tr><td>{{ $label['customer'] }}</td><td class="right">{{ $bill->customer?->full_name }}</td></tr>
        <tr><td>{{ $label['account'] }}</td><td class="right">{{ $bill->customer?->account_number }}</td></tr>
        <tr><td>{{ $label['meter'] }}</td><td class="right">{{ $bill->waterMeter?->meter_number }}</td></tr>
        <tr><td>{{ $label['previous'] }}</td><td class="right">{{ number_format($bill->previous_reading, 0) }}</td></tr>
        <tr><td>{{ $label['current'] }}</td><td class="right">{{ number_format($bill->current_reading, 0) }}</td></tr>
        <tr><td>{{ $label['consumption'] }}</td><td class="right">{{ number_format($bill->consumption, 0) }}</td></tr>
        <tr><td>{{ $label['water'] }}</td><td class="right">LKR {{ number_format($bill->water_charges, 2) }}</td></tr>
        <tr><td>{{ $label['fixed'] }}</td><td class="right">LKR {{ number_format($bill->fixed_charges, 2) }}</td></tr>
        <tr><td>{{ $label['outstanding'] }}</td><td class="right">LKR {{ number_format($bill->balance_amount, 2) }}</td></tr>
        <tr><td>{{ $label['total'] }}</td><td class="right">LKR {{ number_format($bill->total_amount, 2) }}</td></tr>
        <tr><td>{{ $label['due'] }}</td><td class="right">{{ $bill->due_date?->format('Y-m-d') }}</td></tr>
    </table>
    <p>{{ $label['contact'] }}</p>
    @if(!$loop->last)<hr>@endif
@endforeach
</body>
</html>
