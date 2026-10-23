<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; width: 320px; margin: 16px auto; color: #111; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 0; }
        p { font-size: 13px; margin: 4px 0; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>Dunsinane Water Supply Scheme</h1>
    <p class="center">Payment receipt</p>
    <p>Receipt: {{ $payment->receipt_number }}</p>
    <p>Date: {{ $payment->paid_on->format('Y-m-d') }}</p>
    <p>Customer: {{ $payment->customer?->full_name }}</p>
    <p>Account: {{ $payment->customer?->account_number }}</p>
    <p>Bill: {{ $payment->bill?->bill_number }}</p>
    <p>Amount: LKR {{ number_format($payment->amount, 2) }}</p>
    <p>Method: {{ str_replace('_', ' ', $payment->payment_method) }}</p>
    <p>Received by: {{ $payment->receiver?->name ?? 'Office' }}</p>
    @if($payment->reference)<p>Reference: {{ $payment->reference }}</p>@endif
    <p class="center">Thank you for your payment.</p>
    <script>window.print()</script>
</body>
</html>
