<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="color-scheme" content="light only">
    <title>Bill {{ $bill->bill_number }}</title>
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
            --warn: #b45309;
            --ok: #047857;
        }
        * { box-sizing: border-box; }
        html { color-scheme: light only; background: #eef6f8; }
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
        body.thermal { background: #fff; }
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
        .tools a.active, .tools a.primary, .tools button.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .sheet {
            max-width: 720px;
            margin: 16px auto 40px;
            padding: 0 16px;
        }
        .bill {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(15, 42, 51, 0.08);
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .bill-head {
            background:
                linear-gradient(135deg, rgba(14, 116, 144, 0.12), rgba(14, 116, 144, 0.02)),
                linear-gradient(180deg, #fff, var(--wash));
            border-bottom: 1px solid var(--line);
            padding: 28px 28px 22px;
            position: relative;
        }
        .bill-head::after {
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
            color: var(--ink);
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
            background: var(--accent-soft);
            color: #155e75;
            border: 1px solid #a5f3fc;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.paid {
            background: #ecfdf5;
            color: var(--ok);
            border-color: #a7f3d0;
        }
        .badge.due {
            background: #fff7ed;
            color: var(--warn);
            border-color: #fed7aa;
        }
        .body { padding: 22px 28px 28px; }
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
            color: var(--ink);
            word-break: break-word;
        }
        .charges {
            margin-top: 18px;
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
        }
        .charge-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 16px;
            border-top: 1px solid var(--line);
            font-size: 14px;
        }
        .charge-row:first-child { border-top: 0; }
        .charge-row span:first-child { color: var(--muted); }
        .charge-row strong { color: var(--ink); font-weight: 700; }
        .charge-row.total {
            background: var(--wash);
            font-size: 15px;
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
        .thanks { color: var(--ink); font-weight: 600; }
        .lang-si { font-family: "Noto Sans Sinhala", "Noto Sans", sans-serif; }
        .lang-ta { font-family: "Noto Sans Tamil", "Noto Sans", sans-serif; }

        body.thermal .sheet { max-width: 320px; padding: 0 8px; }
        body.thermal .bill { border-radius: 0; box-shadow: none; }
        body.thermal .bill-head,
        body.thermal .body { padding: 16px; }
        body.thermal .title { font-size: 20px; }
        body.thermal .amount-value { font-size: 24px; }
        body.thermal .grid { grid-template-columns: 1fr; gap: 10px; }
        body.thermal .tools { max-width: 320px; }

        @media print {
            html, body { background: #fff !important; color: #000; }
            .tools { display: none !important; }
            .sheet { max-width: none; margin: 0; padding: 0; }
            .bill {
                box-shadow: none;
                border: 0;
                border-radius: 0;
                margin: 0 0 24px;
                page-break-after: always;
            }
            .bill:last-child { page-break-after: auto; }
        }

        @media (max-width: 640px) {
            .grid { grid-template-columns: 1fr; }
            .title { font-size: 24px; }
            .amount-value { font-size: 28px; }
        }
    </style>
</head>
@php
    $language = $language ?? 'both';
    $format = $format ?? 'a4';
    $otherCharges = round(
        (float) $bill->service_charges
        + (float) $bill->taxes
        + (float) $bill->late_fees
        + (float) max(0, (float) $bill->adjustments),
        2
    );
    $labels = [
        'en' => [
            'scheme' => 'Dunsinane Water Supply Scheme',
            'title' => 'Water Bill',
            'payable' => 'Total payable',
            'status_paid' => 'Paid',
            'status_due' => 'Amount due',
            'bill' => 'Bill number',
            'bill_date' => 'Bill date',
            'due' => 'Due date',
            'customer' => 'Customer',
            'account' => 'Account number',
            'meter' => 'Meter number',
            'previous' => 'Previous reading',
            'current' => 'Current reading',
            'consumption' => 'Consumption',
            'water' => 'Water charges',
            'fixed' => 'Fixed charge',
            'other' => 'Other charges',
            'damage' => 'Damage charges',
            'adjustments' => 'Adjustments / opening',
            'paid' => 'Applied to this bill',
            'cash_received' => 'Cash / payment received',
            'wallet_used' => 'Taken from wallet',
            'wallet_added' => 'Added to wallet',
            'balance' => 'Outstanding on this bill',
            'wallet' => 'Wallet balance now',
            'receipts' => 'Receipt number(s)',
            'thanks' => 'Please pay on or before the due date.',
            'contact' => 'Contact the estate office for payment queries.',
        ],
        'si' => [
            'scheme' => 'ඩන්සිනේන් ජල සැපයුම් ව්‍යාපෘතිය',
            'title' => 'ජල බිල්පත',
            'payable' => 'ගෙවිය යුතු මුළු මුදල',
            'status_paid' => 'ගෙවා ඇත',
            'status_due' => 'ගෙවිය යුතුයි',
            'bill' => 'බිල් අංකය',
            'bill_date' => 'බිල් දිනය',
            'due' => 'ගෙවීමේ අවසාන දිනය',
            'customer' => 'පාරිභෝගිකයා',
            'account' => 'ගිණුම් අංකය',
            'meter' => 'මීටර් අංකය',
            'previous' => 'පෙර කියවීම',
            'current' => 'වත්මන් කියවීම',
            'consumption' => 'පරිභෝජනය',
            'water' => 'ජල ගාස්තු',
            'fixed' => 'ස්ථිර ගාස්තුව',
            'other' => 'වෙනත් ගාස්තු',
            'damage' => 'හානි ගාස්තු',
            'adjustments' => 'සීරුමාරු / ආරම්භක',
            'paid' => 'මෙම බිලට යොදන ලද්දේ',
            'cash_received' => 'ලැබුණු මුදල් / ගෙවීම',
            'wallet_used' => 'පසුම්බියෙන් ගත්තේ',
            'wallet_added' => 'පසුම්බියට එකතු කළේ',
            'balance' => 'මෙම බිලේ නිෂ්කාසිතය',
            'wallet' => 'දැන් පසුම්බි ශේෂය',
            'receipts' => 'රිසිට් අංක',
            'thanks' => 'කරුණාකර නියමිත දිනට පෙර ගෙවන්න.',
            'contact' => 'ගෙවීම් විමසීම් සඳහා වතු කාර්යාලය අමතන්න.',
        ],
        'ta' => [
            'scheme' => 'டன்சினேன் நீர் வழங்கல் திட்டம்',
            'title' => 'நீர் கட்டண பட்டியல்',
            'payable' => 'செலுத்த வேண்டிய மொத்தம்',
            'status_paid' => 'செலுத்தப்பட்டது',
            'status_due' => 'நிலுவை',
            'bill' => 'பில் எண்',
            'bill_date' => 'பில் தேதி',
            'due' => 'செலுத்த வேண்டிய தினம்',
            'customer' => 'வாடிக்கையாளர்',
            'account' => 'கணக்கு எண்',
            'meter' => 'மீட்டர் எண்',
            'previous' => 'முந்தைய அளவு',
            'current' => 'தற்போதைய அளவு',
            'consumption' => 'நுகர்வு',
            'water' => 'நீர் கட்டணம்',
            'fixed' => 'நிலையான கட்டணம்',
            'other' => 'பிற கட்டணம்',
            'damage' => 'சேதக் கட்டணம்',
            'adjustments' => 'சரிசெய்தல் / தொடக்கத் தொகை',
            'paid' => 'இந்த பிலில் பயன்படுத்தியது',
            'cash_received' => 'பெற்ற தொகை',
            'wallet_used' => 'வாலட்டில் இருந்து',
            'wallet_added' => 'வாலட்டில் சேர்க்கப்பட்டது',
            'balance' => 'இந்த பில் நிலுவை',
            'wallet' => 'தற்போதைய வாலட் இருப்பு',
            'receipts' => 'ரசீது எண்(கள்)',
            'thanks' => 'தயவுசெய்து நிலுவைத் தேதிக்குள் செலுத்தவும்.',
            'contact' => 'கட்டண வினவல்களுக்கு தோட்ட அலுவலகத்தை அணுகவும்.',
        ],
    ];
    if ($language === 'both') {
        $languages = ['en', 'ta'];
    } elseif ($language === 'all') {
        $languages = ['en', 'si', 'ta'];
    } else {
        $languages = [$language];
    }
    $isPaid = (float) $bill->balance_amount <= 0 && (float) $bill->paid_amount > 0;
    $settle = $bill->settlementSummary();
@endphp
<body class="{{ $format === 'thermal' ? 'thermal' : '' }}">
<div class="tools">
    <a href="{{ route('bills.show', $bill) }}">← Back to bill</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => 'both', 'format' => $format]) }}" class="{{ $language === 'both' ? 'active' : '' }}">English + Tamil</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => 'all', 'format' => $format]) }}" class="{{ $language === 'all' ? 'active' : '' }}">EN + සිං + தமிழ்</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => 'en', 'format' => $format]) }}" class="{{ $language === 'en' ? 'active' : '' }}">English</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => 'si', 'format' => $format]) }}" class="{{ $language === 'si' ? 'active' : '' }}">සිංහල</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => 'ta', 'format' => $format]) }}" class="{{ $language === 'ta' ? 'active' : '' }}">தமிழ்</a>
    <a href="{{ route('bills.print', ['bill' => $bill, 'lang' => $language, 'format' => $format === 'thermal' ? 'a4' : 'thermal']) }}">
        {{ $format === 'thermal' ? 'A4' : 'Thermal' }}
    </a>
    <button type="button" class="primary" onclick="window.print()">Print</button>
    <a href="{{ route('bills.pdf', $bill) }}" class="primary">Download PDF</a>
</div>

<div class="sheet">
@foreach($languages as $code)
    @php
        $label = $labels[$code];
        $langClass = $code === 'si' ? 'lang-si' : ($code === 'ta' ? 'lang-ta' : '');
    @endphp
    <article class="bill {{ $langClass }}">
        <header class="bill-head">
            <p class="brand">{{ $label['scheme'] }}</p>
            <h1 class="title">{{ $label['title'] }}</h1>
            <p class="subtitle">{{ $bill->bill_number }} · {{ optional($bill->bill_date)->format('d M Y') }}</p>
            <div class="amount-block">
                <div>
                    <p class="amount-label">{{ $label['payable'] }}</p>
                    <p class="amount-value">LKR {{ number_format((float) $bill->total_amount, 2) }}</p>
                </div>
                <span class="badge {{ $isPaid ? 'paid' : 'due' }}">
                    {{ $isPaid ? $label['status_paid'] : $label['status_due'] }}
                    @unless($isPaid)
                        · LKR {{ number_format((float) $bill->balance_amount, 2) }}
                    @endunless
                </span>
            </div>
        </header>
        <div class="body">
            <div class="grid">
                <div class="field">
                    <span class="label">{{ $label['bill'] }}</span>
                    <div class="value">{{ $bill->bill_number }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['due'] }}</span>
                    <div class="value">{{ optional($bill->due_date)->format('d M Y') }}</div>
                </div>
                <div class="field full">
                    <span class="label">{{ $label['customer'] }}</span>
                    <div class="value">{{ $bill->customer?->full_name }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['account'] }}</span>
                    <div class="value">{{ $bill->customer?->account_number }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['meter'] }}</span>
                    <div class="value">{{ $bill->waterMeter?->meter_number ?? '—' }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['previous'] }}</span>
                    <div class="value">{{ number_format((float) $bill->previous_reading, 0) }}</div>
                </div>
                <div class="field">
                    <span class="label">{{ $label['current'] }}</span>
                    <div class="value">{{ number_format((float) $bill->current_reading, 0) }}</div>
                </div>
                <div class="field full">
                    <span class="label">{{ $label['consumption'] }}</span>
                    <div class="value">{{ number_format((float) $bill->consumption, 0) }}</div>
                </div>
            </div>

            <div class="charges">
                <div class="charge-row">
                    <span>{{ $label['water'] }}</span>
                    <strong>LKR {{ number_format((float) $bill->water_charges, 2) }}</strong>
                </div>
                <div class="charge-row">
                    <span>{{ $label['fixed'] }}</span>
                    <strong>LKR {{ number_format((float) $bill->fixed_charges, 2) }}</strong>
                </div>
                @if((float) $bill->damage_charges > 0)
                <div class="charge-row">
                    <span>{{ $label['damage'] }}</span>
                    <strong>LKR {{ number_format((float) $bill->damage_charges, 2) }}</strong>
                </div>
                @endif
                @if($otherCharges > 0 || (float) $bill->adjustments != 0)
                <div class="charge-row">
                    <span>{{ (float) $bill->adjustments != 0 ? $label['adjustments'] : $label['other'] }}</span>
                    <strong>LKR {{ number_format((float) $bill->adjustments != 0 ? (float) $bill->adjustments : $otherCharges, 2) }}</strong>
                </div>
                @endif
                @if($settle['cash_received'] > 0)
                <div class="charge-row">
                    <span>{{ $label['cash_received'] }}</span>
                    <strong>LKR {{ number_format($settle['cash_received'], 2) }}</strong>
                </div>
                @endif
                <div class="charge-row">
                    <span>{{ $label['paid'] }}</span>
                    <strong>LKR {{ number_format($settle['applied_to_bill'], 2) }}</strong>
                </div>
                @if($settle['wallet_used'] > 0)
                <div class="charge-row">
                    <span>{{ $label['wallet_used'] }}</span>
                    <strong>LKR {{ number_format($settle['wallet_used'], 2) }}</strong>
                </div>
                @endif
                @if($settle['wallet_added'] > 0)
                <div class="charge-row">
                    <span>{{ $label['wallet_added'] }}</span>
                    <strong>LKR {{ number_format($settle['wallet_added'], 2) }}</strong>
                </div>
                @endif
                <div class="charge-row">
                    <span>{{ $label['balance'] }}</span>
                    <strong>LKR {{ number_format($settle['bill_balance'], 2) }}</strong>
                </div>
                <div class="charge-row">
                    <span>{{ $label['wallet'] }}</span>
                    <strong>LKR {{ number_format($settle['wallet_balance'], 2) }}</strong>
                </div>
                @if($settle['receipts'] !== '')
                <div class="charge-row">
                    <span>{{ $label['receipts'] }}</span>
                    <strong>{{ $settle['receipts'] }}</strong>
                </div>
                @endif
                <div class="charge-row total">
                    <span>{{ $label['payable'] }}</span>
                    <strong>LKR {{ number_format((float) $bill->total_amount, 2) }}</strong>
                </div>
            </div>

            <div class="footer">
                <div class="thanks">{{ $label['thanks'] }}</div>
                <div>{{ $label['contact'] }}</div>
            </div>
        </div>
    </article>
@endforeach
</div>

<script>
    @if(in_array($language, ['en', 'si', 'ta'], true) && $format === 'thermal')
    window.addEventListener('load', function () { window.print(); });
    @endif
</script>
</body>
</html>
