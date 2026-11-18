<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600;700&family=Noto+Sans+Sinhala:wght@400;600;700&family=Noto+Sans+Tamil:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f2a33;
            --muted: #5a7179;
            --line: #d7e4e8;
            --accent: #0e7490;
            --accent-soft: #ecfeff;
            --paper: #ffffff;
            --wash: #f3f8f9;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, #dff7fb 0, transparent 42%),
                linear-gradient(180deg, #eef6f8 0%, #f7fbfc 45%, #eef4f6 100%);
            font-family: "Noto Sans", "Noto Sans Sinhala", "Noto Sans Tamil", sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body.thermal {
            background: #fff;
        }
        .tools {
            max-width: 720px;
            margin: 16px auto 0;
            padding: 0 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .tools a, .tools button {
            appearance: none;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            border-radius: 999px;
            padding: 8px 14px;
            font: inherit;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
        }
        .tools a.active, .tools button.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .sheet {
            max-width: 720px;
            margin: 16px auto 40px;
            padding: 0 16px;
        }
        .receipt {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(15, 42, 51, 0.08);
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .receipt-head {
            background:
                linear-gradient(135deg, rgba(14, 116, 144, 0.12), rgba(14, 116, 144, 0.02)),
                linear-gradient(180deg, #fff, var(--wash));
            border-bottom: 1px solid var(--line);
            padding: 28px 28px 22px;
            position: relative;
        }
        .receipt-head::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 4px;
            background: linear-gradient(90deg, #0891b2, #0e7490 45%, #155e75);
        }
        .brand {
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 700;
            margin: 0 0 8px;
        }
        .title {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
            font-weight: 700;
        }
        .subtitle {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 14px;
        }
        .amount-block {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .amount-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin: 0 0 4px;
        }
        .amount-value {
            margin: 0;
            font-size: 34px;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: -0.02em;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--accent-soft);
            color: #155e75;
            border: 1px solid #a5f3fc;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .body {
            padding: 22px 28px 28px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 24px;
        }
        .field {
            border-top: 1px dashed var(--line);
            padding-top: 10px;
        }
        .field.full { grid-column: 1 / -1; }
        .label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .value {
            font-size: 15px;
            font-weight: 600;
            word-break: break-word;
        }
        .footer {
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 13px;
        }
        .thanks {
            color: var(--ink);
            font-weight: 600;
        }
        .lang-si { font-family: "Noto Sans Sinhala", "Noto Sans", sans-serif; }
        .lang-ta { font-family: "Noto Sans Tamil", "Noto Sans", sans-serif; }

        body.thermal .sheet { max-width: 320px; padding: 0 8px; }
        body.thermal .receipt { border-radius: 0; box-shadow: none; }
        body.thermal .receipt-head,
        body.thermal .body { padding: 16px; }
        body.thermal .title { font-size: 20px; }
        body.thermal .amount-value { font-size: 24px; }
        body.thermal .grid { grid-template-columns: 1fr; gap: 10px; }
        body.thermal .tools { max-width: 320px; }

        @media print {
            body { background: #fff; }
            .tools { display: none !important; }
            .sheet { max-width: none; margin: 0; padding: 0; }
            .receipt {
                box-shadow: none;
                border: 0;
                border-radius: 0;
                margin: 0 0 24px;
                page-break-after: always;
            }
            .receipt:last-child { page-break-after: auto; }
        }

        @media (max-width: 640px) {
            .grid { grid-template-columns: 1fr; }
            .title { font-size: 24px; }
            .amount-value { font-size: 28px; }
        }
    </style>
</head>
@php
    $public = $public ?? false;
    $format = $format ?? 'a4';
    $language = $language ?? 'all';
    $labels = [
        'en' => [
            'scheme' => 'Dunsinane Water Supply Scheme',
            'title' => 'Payment Receipt',
            'paid' => 'Applied to this bill',
            'cash_received' => 'Cash / payment received',
            'wallet_used' => 'Taken from wallet',
            'wallet_added' => 'Added to wallet',
            'wallet_balance' => 'Wallet balance now',
            'confirmed' => 'Payment confirmed',
            'receipt' => 'Receipt number',
            'date' => 'Payment date & time',
            'customer' => 'Customer',
            'account' => 'Account number',
            'bill' => 'Bill number',
            'method' => 'Payment method',
            'received' => 'Received by',
            'reference' => 'Reference',
            'balance' => 'Bill balance after payment',
            'thanks' => 'Thank you for your payment.',
            'contact' => 'Keep this receipt for your records.',
            'methods' => [
                'cash' => 'Cash',
                'bank_transfer' => 'Bank transfer',
                'cheque' => 'Cheque',
                'card' => 'Card',
                'wallet' => 'Wallet',
                'mobile_payment' => 'Mobile payment',
            ],
        ],
        'si' => [
            'scheme' => 'ඩන්සිනේන් ජල සැපයුම් ව්‍යාපෘතිය',
            'title' => 'ගෙවීම් රිසිට්පත',
            'paid' => 'මෙම බිලට යොදන ලද්දේ',
            'cash_received' => 'ලැබුණු මුදල් / ගෙවීම',
            'wallet_used' => 'පසුම්බියෙන් ගත්තේ',
            'wallet_added' => 'පසුම්බියට එකතු කළේ',
            'wallet_balance' => 'දැන් පසුම්බි ශේෂය',
            'confirmed' => 'ගෙවීම තහවුරුයි',
            'receipt' => 'රිසිට් අංකය',
            'date' => 'ගෙවූ දිනය සහ වේලාව',
            'customer' => 'පාරිභෝගිකයා',
            'account' => 'ගිණුම් අංකය',
            'bill' => 'බිල් අංකය',
            'method' => 'ගෙවීමේ ක්‍රමය',
            'received' => 'ලබාගත්තේ',
            'reference' => 'යොමු අංකය',
            'balance' => 'ගෙවීමෙන් පසු බිල් ශේෂය',
            'thanks' => 'ඔබගේ ගෙවීමට ස්තූතියි.',
            'contact' => 'මෙම රිසිට්පත ඔබේ වාර්තා සඳහා තබාගන්න.',
            'methods' => [
                'cash' => 'මුදල්',
                'bank_transfer' => 'බැංකු මාරුව',
                'cheque' => 'චෙක්පත',
                'card' => 'කාඩ්පත',
                'wallet' => 'පසුම්බිය',
                'mobile_payment' => 'ජංගම ගෙවීම',
            ],
        ],
        'ta' => [
            'scheme' => 'டன்சினேன் நீர் வழங்கல் திட்டம்',
            'title' => 'கட்டண ரசீது',
            'paid' => 'இந்த பிலில் பயன்படுத்தியது',
            'cash_received' => 'பெற்ற தொகை',
            'wallet_used' => 'வாலட்டில் இருந்து',
            'wallet_added' => 'வாலட்டில் சேர்க்கப்பட்டது',
            'wallet_balance' => 'தற்போதைய வாலட் இருப்பு',
            'confirmed' => 'கட்டணம் உறுதி',
            'receipt' => 'ரசீது எண்',
            'date' => 'கட்டணத் தேதி & நேரம்',
            'customer' => 'வாடிக்கையாளர்',
            'account' => 'கணக்கு எண்',
            'bill' => 'பில் எண்',
            'method' => 'கட்டண முறை',
            'received' => 'பெற்றவர்',
            'reference' => 'குறிப்பு எண்',
            'balance' => 'கட்டணத்திற்குப் பின் பில் மீதி',
            'thanks' => 'உங்கள் கட்டணத்திற்கு நன்றி.',
            'contact' => 'இந்த ரசீதை உங்கள் பதிவுகளுக்காக வைத்திருங்கள்.',
            'methods' => [
                'cash' => 'ரொக்கம்',
                'bank_transfer' => 'வங்கி பரிமாற்றம்',
                'cheque' => 'காசோலை',
                'card' => 'அட்டை',
                'wallet' => 'வாலட்',
                'mobile_payment' => 'மொபைல் கட்டணம்',
            ],
        ],
    ];
    $languages = $language === 'all' ? ['en', 'si', 'ta'] : [$language];
    $methodKey = $payment->payment_method;
    $settle = $payment->bill?->settlementSummary();
    $billBalance = $settle['bill_balance'] ?? $payment->bill?->balance_amount;
    $walletAdded = (float) ($payment->walletAddedAmount() ?: ($settle['wallet_added'] ?? 0));
    $cashReceived = $payment->exactPaidAmount();
    $walletUsed = (float) ($settle['wallet_used'] ?? 0);
    $walletBalance = (float) ($settle['wallet_balance'] ?? ($payment->customer?->wallet_balance ?? 0));
@endphp
<body class="{{ $format === 'thermal' ? 'thermal' : '' }}">
@unless($public)
<div class="tools">
    <a href="{{ route('payments.index') }}">← Back to payments</a>
    <a href="{{ route('payments.receipt', ['payment' => $payment, 'lang' => 'all', 'format' => $format]) }}" class="{{ $language === 'all' ? 'active' : '' }}">EN + සිං + தமிழ்</a>
    <a href="{{ route('payments.receipt', ['payment' => $payment, 'lang' => 'en', 'format' => $format]) }}" class="{{ $language === 'en' ? 'active' : '' }}">English</a>
    <a href="{{ route('payments.receipt', ['payment' => $payment, 'lang' => 'si', 'format' => $format]) }}" class="{{ $language === 'si' ? 'active' : '' }}">සිංහල</a>
    <a href="{{ route('payments.receipt', ['payment' => $payment, 'lang' => 'ta', 'format' => $format]) }}" class="{{ $language === 'ta' ? 'active' : '' }}">தமிழ்</a>
    <a href="{{ route('payments.receipt', ['payment' => $payment, 'lang' => $language, 'format' => $format === 'thermal' ? 'a4' : 'thermal']) }}">
        {{ $format === 'thermal' ? 'A4' : 'Thermal' }}
    </a>
    <button type="button" class="primary" onclick="window.print()">Print</button>
</div>
@endunless

<div class="sheet">
@foreach($languages as $code)
    @php
        $label = $labels[$code];
        $methodLabel = $label['methods'][$methodKey] ?? str_replace('_', ' ', (string) $methodKey);
        $langClass = $code === 'si' ? 'lang-si' : ($code === 'ta' ? 'lang-ta' : '');
    @endphp
    <article class="receipt {{ $langClass }}">
        <header class="receipt-head">
            <p class="brand">{{ $label['scheme'] }}</p>
            <h1 class="title">{{ $label['title'] }}</h1>
            <p class="subtitle">{{ $payment->receipt_number }} · {{ $payment->paidOnDisplay() }}</p>
            <div class="amount-block">
                <div>
                    <p class="amount-label">{{ $label['cash_received'] }}</p>
                    <p class="amount-value">LKR {{ number_format($cashReceived, 2) }}</p>
                </div>
                <span class="badge">{{ $label['confirmed'] }}</span>
            </div>
        </header>
        <div class="body">
            <div class="grid">
                <div class="field">
                    <span class="label">{{ $label['receipt'] }}</span>
                    <div class="value">{{ $payment->receipt_number }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['date'] }}</span>
                    <div class="value">{{ $payment->paidOnDisplay() }}</div>
                </div>
                <div class="field full">
                    <span class="label">{{ $label['customer'] }}</span>
                    <div class="value">{{ $payment->customer?->full_name }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['account'] }}</span>
                    <div class="value">{{ $payment->customer?->account_number }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['bill'] }}</span>
                    <div class="value">{{ $payment->bill?->bill_number ?? '—' }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['method'] }}</span>
                    <div class="value">{{ $methodLabel }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['paid'] }}</span>
                    <div class="value">LKR {{ number_format((float) $payment->amount, 2) }}</div>
                </div>
                @if($walletAdded > 0)
                <div class="field">
                    <span class="label">{{ $label['wallet_added'] }}</span>
                    <div class="value">LKR {{ number_format($walletAdded, 2) }}</div>
                </div>
                @endif
                @if($walletUsed > 0)
                <div class="field">
                    <span class="label">{{ $label['wallet_used'] }}</span>
                    <div class="value">LKR {{ number_format($walletUsed, 2) }}</div>
                </div>
                @endif
                <div class="field">
                    <span class="label">{{ $label['wallet_balance'] }}</span>
                    <div class="value">LKR {{ number_format($walletBalance, 2) }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['received'] }}</span>
                    <div class="value">{{ $payment->receiver?->name ?? 'Office' }}</div>
                </div>
                @if($payment->reference)
                <div class="field full">
                    <span class="label">{{ $label['reference'] }}</span>
                    <div class="value">{{ $payment->reference }}</div>
                </div>
                @endif
                @if($billBalance !== null)
                <div class="field full">
                    <span class="label">{{ $label['balance'] }}</span>
                    <div class="value">LKR {{ number_format((float) $billBalance, 2) }}</div>
                </div>
                @endif
            </div>
            <div class="footer">
                <div class="thanks">{{ $label['thanks'] }}</div>
                <div>{{ $label['contact'] }}</div>
            </div>
        </div>
    </article>
@endforeach
</div>

@unless($public)
<script>
    // Only auto-print when a single language is chosen (office thermal / quick print).
    @if($language !== 'all' && $format === 'thermal')
    window.addEventListener('load', function () { window.print(); });
    @endif
</script>
@endunless
</body>
</html>
