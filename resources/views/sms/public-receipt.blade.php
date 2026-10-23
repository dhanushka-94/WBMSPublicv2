<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900">
    <main class="max-w-lg mx-auto p-4">
        <h1 class="text-xl font-semibold">Payment receipt</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $payment->customer->full_name }} · {{ $payment->customer->account_number }}</p>
        <section class="mt-4 bg-white rounded-lg shadow p-4 space-y-2">
            <p class="text-sm text-slate-500">Receipt {{ $payment->receipt_number }}</p>
            <p class="text-3xl font-bold">Rs. {{ number_format($payment->amount, 2) }}</p>
            <p class="text-sm">Paid on {{ $payment->paid_on->format('M d, Y') }}</p>
            <p class="text-sm">Bill {{ $payment->bill->bill_number ?? '—' }}</p>
            <p class="text-sm">Method: {{ ucfirst($payment->payment_method) }}</p>
        </section>
    </main>
</body>
</html>
