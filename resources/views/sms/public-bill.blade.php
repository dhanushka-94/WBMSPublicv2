<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Bill {{ $bill->bill_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900">
    <main class="max-w-lg mx-auto p-4">
        <h1 class="text-xl font-semibold">AquaBill</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $customer->full_name }} · {{ $customer->account_number }}</p>
        <section class="mt-4 bg-white rounded-lg shadow p-4 space-y-2">
            <p class="text-sm text-slate-500">Bill {{ $bill->bill_number }}</p>
            <p class="text-3xl font-bold">Rs. {{ number_format($bill->balance_amount, 2) }}</p>
            <p class="text-sm">Outstanding of Rs. {{ number_format($bill->total_amount, 2) }} billed.</p>
            <p class="text-sm">Period: {{ $bill->billing_period_from->format('M d, Y') }} – {{ $bill->billing_period_to->format('M d, Y') }}</p>
            <p class="text-sm">Due: {{ $bill->due_date->format('M d, Y') }}</p>
            <p class="text-sm">Status: {{ ucfirst($bill->status) }}</p>
        </section>
        <p class="mt-4 text-sm text-slate-600">Pay this bill at the Dunsinane Water Supply Scheme office and keep your receipt.</p>
    </main>
</body>
</html>
