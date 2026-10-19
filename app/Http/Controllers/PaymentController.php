<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function print(Payment $payment): View
    {
        $payment->load(['bill', 'customer', 'receiver']);

        return view('payments.receipt', compact('payment'));
    }
}
