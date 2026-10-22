<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\View\View;

class SmsLinkController extends Controller
{
    public function bill(Bill $bill): View
    {
        $bill->load('customer');

        return view('sms.public-bill', [
            'bill' => $bill,
            'customer' => $bill->customer,
        ]);
    }

    public function receipt(Payment $payment): View
    {
        $payment->load(['customer', 'bill']);

        return view('sms.public-receipt', [
            'payment' => $payment,
        ]);
    }

    public function due(Customer $customer): View
    {
        $bills = $customer->bills()
            ->where('balance_amount', '>', 0)
            ->whereNotIn('status', ['cancelled', 'draft', 'paid'])
            ->orderBy('due_date')
            ->get();

        return view('sms.public-due', [
            'customer' => $customer,
            'bills' => $bills,
        ]);
    }
}
