<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Payment;
use App\Services\BillPaymentService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
    use LogsActivity;

    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));

        $payments = Payment::query()
            ->with(['customer:id,account_number,first_name,last_name,title', 'bill:id,bill_number', 'receiver:id,name'])
            ->when($query !== '', function ($rows) use ($query) {
                $like = '%'.$query.'%';
                $rows->where(function ($search) use ($like) {
                    $search->where('receipt_number', 'like', $like)
                        ->orWhere('reference', 'like', $like)
                        ->orWhereHas('customer', function ($customer) use ($like) {
                            $customer->where('account_number', 'like', $like)
                                ->orWhere('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like);
                        })
                        ->orWhereHas('bill', function ($bill) use ($like) {
                            $bill->where('bill_number', 'like', $like);
                        });
                });
            })
            ->orderByDesc('paid_on')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('payments.index', compact('payments', 'query'));
    }

    public function edit(Payment $payment): View
    {
        abort_unless($this->canEditPayments(), 403);

        $payment->load(['customer', 'bill', 'receiver']);

        $this->logActivity('payment_edit_viewed', 'Opened payment '.$payment->receipt_number.' for editing', $payment, [
            'receipt_number' => $payment->receipt_number,
            'amount' => (float) $payment->amount,
        ]);

        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment, BillPaymentService $payments): RedirectResponse
    {
        abort_unless($this->canEditPayments(), 403);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,card,wallet,mobile_payment',
            'paid_on' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['paid_on'] = Payment::parsePaidOn($validated['paid_on'])->toDateTimeString();

        try {
            $updated = $payments->updatePayment($payment, $validated, $request->user()?->id);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('payments.edit', $updated)
            ->with('success', 'Payment '.$updated->receipt_number.' updated.');
    }

    public function destroy(Request $request, Payment $payment, BillPaymentService $payments): RedirectResponse
    {
        $receipt = $payment->receipt_number;
        $billId = $payment->bill_id;

        try {
            $payments->deletePayment($payment, $request->user()?->id);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $redirect = $request->input('redirect_to') === 'bill' && $billId
            ? redirect()->route('bills.show', $billId)
            : redirect()->route('payments.index');

        return $redirect->with('success', 'Payment '.$receipt.' deleted and the bill balance was restored.');
    }

    public function print(Request $request, Payment $payment): View
    {
        $payment->load(['bill', 'customer', 'receiver']);
        $language = in_array($request->input('lang'), ['en', 'si', 'ta', 'all'], true)
            ? $request->input('lang')
            : 'all';
        $format = $request->input('format') === 'thermal' ? 'thermal' : 'a4';

        try {
            ActivityLog::logPaymentReceiptViewed($payment);
        } catch (\Throwable $exception) {
            // Receipt printing must not fail if logging fails.
        }

        return view('payments.receipt', compact('payment', 'language', 'format'));
    }

    private function canEditPayments(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }
}
