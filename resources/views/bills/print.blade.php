<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bill - {{ $bill->bill_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .company-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .bill-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }

        .bill-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .bill-info-left,
        .bill-info-right {
            width: 48%;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
        }

        .customer-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .consumption-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .consumption-table th,
        .consumption-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        .consumption-table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .consumption-table .number {
            text-align: right;
        }

        .rate-breakdown {
            margin-bottom: 20px;
        }

        .rate-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .rate-table th,
        .rate-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
        }

        .rate-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 11px;
        }

        .rate-table .number {
            text-align: right;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-table .label {
            width: 70%;
            font-weight: bold;
        }

        .summary-table .amount {
            width: 30%;
            text-align: right;
            font-weight: bold;
        }

        .total-row {
            background-color: #f3f4f6;
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-generated {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-sent {
            background-color: #fef3c7;
            color: #92400e;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 11px;
        }

        .payment-info {
            background-color: #f0f9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .important-note {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 10px;
            margin-bottom: 20px;
        }

        @media print {
            .container {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">DN WASSIP</div>
            <div class="company-subtitle">Water Billing Management System</div>
            <div class="bill-title">WATER BILL</div>
        </div>

        <!-- Bill Information -->
        <div class="bill-info">
            <div class="bill-info-left">
                <div class="info-group">
                    <div class="info-label">Bill Number:</div>
                    <div class="info-value">{{ $bill->bill_number }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Bill Date:</div>
                    <div class="info-value">{{ $bill->bill_date->format('F d, Y') }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Due Date:</div>
                    <div class="info-value">{{ $bill->due_date->format('F d, Y') }}</div>
                </div>
            </div>
            <div class="bill-info-right">
                <div class="info-group">
                    <div class="info-label">Billing Period:</div>
                    <div class="info-value">{{ $bill->billing_period_from->format('M d') }} - {{ $bill->billing_period_to->format('M d, Y') }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $bill->status }}">{{ ucfirst($bill->status) }}</span>
                    </div>
                </div>
                @if($bill->isOverdue())
                <div class="info-group">
                    <div class="info-label">Days Overdue:</div>
                    <div class="info-value" style="color: #ef4444; font-weight: bold;">{{ $bill->getDaysOverdue() }} days</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div class="section-title">CUSTOMER INFORMATION</div>
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 48%;">
                    <div class="info-group">
                        <div class="info-label">Name:</div>
                        <div class="info-value">{{ $bill->customer ? $bill->customer->full_name : 'Unknown Customer' }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Account Number:</div>
                        <div class="info-value">{{ $bill->customer->account_number }}</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Customer Type:</div>
                        <div class="info-value">{{ $bill->customer->customerType->name ?? 'Residential' }}</div>
                    </div>
                </div>
                <div style="width: 48%;">
                    <div class="info-group">
                        <div class="info-label">Address:</div>
                        <div class="info-value">
                            {{ $bill->customer->address }}<br>
                            {{ $bill->customer->city }}, {{ $bill->customer->postal_code }}
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone:</div>
                        <div class="info-value">{{ $bill->customer->phone }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meter Information -->
        <div class="section-title">METER INFORMATION</div>
        <table class="consumption-table">
            <tr>
                <th>Meter Number</th>
                <th>Meter Type</th>
                <th>Previous Reading</th>
                <th>Current Reading</th>
                <th>Consumption</th>
            </tr>
            <tr>
                <td>{{ $bill->waterMeter->meter_number }}</td>
                <td>{{ ucfirst($bill->waterMeter->meter_type) }}</td>
                <td class="number">{{ number_format($bill->previous_reading) }}</td>
                <td class="number">{{ number_format($bill->current_reading) }}</td>
                <td class="number">{{ number_format($bill->consumption) }} units</td>
            </tr>
        </table>

        <!-- Rate Breakdown -->
        @if($bill->rate_breakdown && count($bill->rate_breakdown) > 0)
        <div class="rate-breakdown">
            <div class="section-title">RATE BREAKDOWN</div>
            <table class="rate-table">
                <thead>
                    <tr>
                        <th>Tier Description</th>
                        <th>Unit Range</th>
                        <th>Units Used</th>
                        <th>Rate per Unit</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->rate_breakdown as $tier)
                    <tr>
                        <td>{{ $tier['tier_name'] }}</td>
                        <td>{{ $tier['tier_from'] }}{{ $tier['tier_to'] ? ' - ' . $tier['tier_to'] : '+' }}</td>
                        <td class="number">{{ number_format($tier['consumption']) }}</td>
                        <td class="number">Rs. {{ number_format($tier['rate_per_unit'], 2) }}</td>
                        <td class="number">Rs. {{ number_format($tier['charge'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Bill Summary -->
        <div class="section-title">BILL SUMMARY</div>
        <table class="summary-table">
            <tr>
                <td class="label">Water Charges</td>
                <td class="amount">Rs. {{ number_format($bill->water_charges, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Fixed Charges</td>
                <td class="amount">Rs. {{ number_format($bill->fixed_charges, 2) }}</td>
            </tr>
            @if($bill->service_charges > 0)
            <tr>
                <td class="label">Service Charges</td>
                <td class="amount">Rs. {{ number_format($bill->service_charges, 2) }}</td>
            </tr>
            @endif
            @if($bill->taxes > 0)
            <tr>
                <td class="label">Taxes</td>
                <td class="amount">Rs. {{ number_format($bill->taxes, 2) }}</td>
            </tr>
            @endif
            @if($bill->late_fees > 0)
            <tr>
                <td class="label">Late Fees</td>
                <td class="amount" style="color: #ef4444;">Rs. {{ number_format($bill->late_fees, 2) }}</td>
            </tr>
            @endif
            @if($bill->adjustments != 0)
            <tr>
                <td class="label">Adjustments</td>
                <td class="amount" style="color: {{ $bill->adjustments > 0 ? '#10b981' : '#ef4444' }};">
                    Rs. {{ number_format($bill->adjustments, 2) }}
                </td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label">TOTAL AMOUNT</td>
                <td class="amount">Rs. {{ number_format($bill->total_amount, 2) }}</td>
            </tr>
            @if($bill->paid_amount > 0)
            <tr>
                <td class="label">Paid Amount</td>
                <td class="amount" style="color: #10b981;">Rs. {{ number_format($bill->paid_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label">BALANCE AMOUNT</td>
                <td class="amount" style="color: {{ $bill->balance_amount > 0 ? '#ef4444' : '#10b981' }};">
                    Rs. {{ number_format($bill->balance_amount, 2) }}
                </td>
            </tr>
        </table>

        <!-- Payment Information -->
        @if($bill->balance_amount > 0)
        <div class="payment-info">
            <div class="section-title">PAYMENT INFORMATION</div>
            <p><strong>Amount Due:</strong> Rs. {{ number_format($bill->balance_amount, 2) }}</p>
            <p><strong>Due Date:</strong> {{ $bill->due_date->format('F d, Y') }}</p>
            @if($bill->isOverdue())
            <p style="color: #ef4444; font-weight: bold;">
                <strong>OVERDUE:</strong> This bill is {{ $bill->getDaysOverdue() }} days overdue. 
                Please pay immediately to avoid additional late fees.
            </p>
            @endif
        </div>
        @endif

        <!-- Important Notes -->
        @if($bill->isOverdue() || $bill->notes)
        <div class="important-note">
            <strong>Important Notes:</strong>
            @if($bill->isOverdue())
            <p>• This bill is overdue. Late fees may apply.</p>
            @endif
            @if($bill->notes)
            <p>• {{ $bill->notes }}</p>
            @endif
            <p>• Please pay your bill on time to avoid service disconnection.</p>
            <p>• For any queries, please contact our customer service.</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated bill. No signature required.</p>
            <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
            <p>Thank you for using DN WASSIP Water Billing Management System</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html> 