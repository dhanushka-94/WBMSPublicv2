<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amount due</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900">
    <main class="max-w-lg mx-auto p-4">
        <h1 class="text-xl font-semibold">Amount due</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $customer->full_name }} · {{ $customer->account_number }}</p>
        <div class="mt-4 space-y-3">
            @forelse($bills as $bill)
                <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('sms.bill', ['bill' => $bill->id]) }}" class="block bg-white rounded-lg shadow p-4">
                    <p class="font-medium">{{ $bill->bill_number }}</p>
                    <p class="text-2xl font-bold">Rs. {{ number_format($bill->balance_amount, 2) }}</p>
                    <p class="text-sm text-slate-600">Due {{ $bill->due_date->format('M d, Y') }}</p>
                </a>
            @empty
                <p class="bg-white rounded-lg shadow p-4">No outstanding bills.</p>
            @endforelse
        </div>
    </main>
</body>
</html>
