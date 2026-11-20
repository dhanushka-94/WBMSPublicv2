<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\WalletEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class BillPaymentService
{
    /**
     * Apply cash and wallet credit to the selected open bills.
     * Cash left after the bills are cleared is stored on the customer wallet.
     */
    public function collect(
        Customer $customer,
        array $billIds,
        float $cash,
        bool $useWallet,
        string $method,
        string $paidOn,
        ?string $reference,
        ?string $notes,
        ?int $userId
    ): array {
        $cash = round($cash, 2);
        if ($cash < 0) {
            throw new InvalidArgumentException('The payment amount cannot be negative.');
        }

        return DB::transaction(function () use ($customer, $billIds, $cash, $useWallet, $method, $paidOn, $reference, $notes, $userId) {
            $customer = Customer::query()->lockForUpdate()->findOrFail($customer->id);
            $bills = Bill::query()
                ->where('customer_id', $customer->id)
                ->whereIn('id', $billIds ?: [0])
                ->where('balance_amount', '>', 0)
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->orderBy('due_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $due = round((float) $bills->sum('balance_amount'), 2);
            $walletBalance = round((float) $customer->wallet_balance, 2);
            $walletUse = $useWallet ? round(min($walletBalance, $due), 2) : 0.0;

            if ($cash + $walletUse <= 0) {
                throw new InvalidArgumentException('Enter an amount, or use the wallet balance.');
            }

            $cashLeft = $cash;
            $walletLeft = $walletUse;
            $applied = [];
            $details = [
                'paid_on' => $paidOn,
                'reference' => $reference,
                'notes' => $notes,
                'received_by' => $userId,
                // SMS is sent once below with the exact cash received (bill + wallet overpayment).
                'skip_sms' => true,
            ];

            foreach ($bills as $bill) {
                $walletLeft = $this->apply($bill, $walletLeft, 'wallet', $details, $applied);
                $bill->refresh();
                $cashLeft = $this->apply($bill, $cashLeft, $method, $details, $applied);
            }

            $walletAdded = round($cashLeft, 2);
            $customer->wallet_balance = round($walletBalance - $walletUse + $walletAdded, 2);
            $customer->save();

            $lastCash = collect($applied)->where('method', '!=', 'wallet')->last();
            $lastApplied = collect($applied)->last();
            $primaryPaymentId = $lastCash['payment_id'] ?? ($lastApplied['payment_id'] ?? null);
            $primaryBillId = $lastCash['bill_id'] ?? ($lastApplied['bill_id'] ?? ((!empty($billIds) ? (int) end($billIds) : null) ?: null));

            if ($walletUse > 0) {
                WalletEntry::create([
                    'customer_id' => $customer->id,
                    'bill_id' => $primaryBillId,
                    'payment_id' => $primaryPaymentId,
                    'user_id' => $userId,
                    'amount' => round(-1 * $walletUse, 2),
                    'reason' => 'applied',
                    'notes' => 'Used on '.collect($applied)->pluck('bill_number')->unique()->implode(', '),
                ]);
            }

            if ($walletAdded > 0) {
                WalletEntry::create([
                    'customer_id' => $customer->id,
                    'bill_id' => $primaryBillId,
                    'payment_id' => $primaryPaymentId,
                    'user_id' => $userId,
                    'amount' => $walletAdded,
                    'reason' => 'overpayment',
                    'notes' => $notes ?: 'Amount left after the selected bills were paid',
                ]);
            }

            $cashOnBills = round((float) collect($applied)->where('method', '!=', 'wallet')->sum('amount'), 2);
            $exactCashReceived = round($cashOnBills + $walletAdded, 2);
            $smsSent = 0;
            $smsSkipped = 0;

            if ($exactCashReceived > 0 && $primaryPaymentId) {
                $payment = Payment::with(['customer', 'bill', 'receiver'])->find($primaryPaymentId);
                $billForSms = $payment?->bill ?? ($primaryBillId ? Bill::find($primaryBillId) : null);
                if ($payment && $billForSms) {
                    $sent = $billForSms->sendPaymentConfirmation($exactCashReceived, $payment);
                    $smsSent = $sent ? 1 : 0;
                    $smsSkipped = $sent ? 0 : 1;
                } else {
                    $smsSkipped = 1;
                }
            } elseif ($walletUse > 0 && $primaryPaymentId) {
                $payment = Payment::with(['customer', 'bill', 'receiver'])->find($primaryPaymentId);
                $billForSms = $payment?->bill;
                if ($payment && $billForSms) {
                    $sent = $billForSms->sendPaymentConfirmation($walletUse, $payment);
                    $smsSent = $sent ? 1 : 0;
                    $smsSkipped = $sent ? 0 : 1;
                } else {
                    $smsSkipped = 1;
                }
            }

            $result = [
                'applied' => $applied,
                'cash_received' => $exactCashReceived,
                'wallet_used' => $walletUse,
                'wallet_added' => $walletAdded,
                'wallet_balance' => (float) $customer->wallet_balance,
                'sms_sent' => $smsSent,
                'sms_skipped' => $smsSkipped,
            ];

            try {
                ActivityLog::logPaymentCollection($customer->fresh(), $result, [
                    'payment_method' => $method,
                    'paid_on' => $paidOn,
                    'reference' => $reference,
                    'notes' => $notes,
                    'received_by' => $userId,
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Payment collection activity log failed: '.$exception->getMessage());
            }

            return $result;
        });
    }

    private function apply(Bill $bill, float $pool, string $method, array $details, array &$applied): float
    {
        $take = round(min((float) $bill->balance_amount, $pool), 2);
        if ($take <= 0) {
            return round($pool, 2);
        }

        $saved = $bill->recordPayment($take, $method, array_merge($details, [
            'source' => $details['source'] ?? 'office_collect',
        ]));
        if (!$saved) {
            throw new InvalidArgumentException('The payment could not be saved on '.$bill->bill_number.'.');
        }

        $applied[] = [
            'bill_id' => $bill->id,
            'bill_number' => $bill->bill_number,
            'amount' => $take,
            'method' => $method,
            'sms_sent' => (bool) $bill->lastPaymentSmsSent,
            'payment_id' => $bill->lastRecordedPayment?->id,
            'receipt_number' => $bill->lastRecordedPayment?->receipt_number,
        ];

        return round($pool - $take, 2);
    }

    /**
     * Pay as much of a new/open bill as the customer wallet allows, and reduce the wallet.
     *
     * @return array{wallet_used: float, wallet_balance: float, bill_balance: float, payment_id: ?int}
     */
    public function applyWalletCredit(Bill $bill, ?int $userId = null, array $options = []): array
    {
        return DB::transaction(function () use ($bill, $userId, $options) {
            $bill = Bill::query()->lockForUpdate()->findOrFail($bill->id);
            $customer = Customer::query()->lockForUpdate()->findOrFail($bill->customer_id);

            $due = round((float) $bill->balance_amount, 2);
            $wallet = round(max(0, (float) $customer->wallet_balance), 2);
            $use = round(min($wallet, $due), 2);

            if ($use <= 0 || in_array($bill->status, ['cancelled', 'draft'], true)) {
                return [
                    'wallet_used' => 0.0,
                    'wallet_balance' => $wallet,
                    'bill_balance' => $due,
                    'payment_id' => null,
                ];
            }

            $saved = $bill->recordPayment($use, 'wallet', [
                'paid_on' => $options['paid_on'] ?? now()->toDateTimeString(),
                'received_by' => $userId,
                'notes' => $options['notes'] ?? 'Auto-applied wallet credit when the bill was created',
                'source' => $options['source'] ?? 'wallet_auto_apply',
                'skip_sms' => array_key_exists('skip_sms', $options) ? (bool) $options['skip_sms'] : true,
            ]);

            if (!$saved) {
                throw new InvalidArgumentException('Wallet credit could not be applied to '.$bill->bill_number.'.');
            }

            $customer->wallet_balance = round($wallet - $use, 2);
            $customer->save();

            WalletEntry::create([
                'customer_id' => $customer->id,
                'bill_id' => $bill->id,
                'payment_id' => $bill->lastRecordedPayment?->id,
                'user_id' => $userId,
                'amount' => round(-1 * $use, 2),
                'reason' => 'applied',
                'notes' => 'Auto-applied on '.$bill->bill_number,
            ]);

            $bill->refresh();

            return [
                'wallet_used' => $use,
                'wallet_balance' => (float) $customer->wallet_balance,
                'bill_balance' => round((float) $bill->balance_amount, 2),
                'payment_id' => $bill->lastRecordedPayment?->id,
            ];
        });
    }

    /**
     * Update an existing payment and keep the bill / wallet balances in sync.
     */
    public function updatePayment(Payment $payment, array $data, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($payment, $data, $userId) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $bill = Bill::query()->lockForUpdate()->findOrFail($payment->bill_id);
            $customer = Customer::query()->lockForUpdate()->findOrFail($payment->customer_id);

            $oldSnapshot = ActivityLog::paymentSnapshot($payment->loadMissing(['customer', 'bill']));
            $oldAmount = round((float) $payment->amount, 2);
            $oldMethod = $payment->payment_method;
            $newAmount = round((float) $data['amount'], 2);
            $newMethod = $data['payment_method'];

            if ($newAmount <= 0) {
                throw new InvalidArgumentException('The payment amount must be greater than zero.');
            }

            $maxAmount = round($oldAmount + (float) $bill->balance_amount, 2);
            if ($newAmount > $maxAmount + 0.001) {
                throw new InvalidArgumentException(
                    'The payment cannot exceed Rs. '.number_format($maxAmount, 2).' for this bill.'
                );
            }

            $delta = round($newAmount - $oldAmount, 2);

            if ($oldMethod === 'wallet') {
                $customer->wallet_balance = round((float) $customer->wallet_balance + $oldAmount, 2);
            }

            if ($newMethod === 'wallet') {
                if ((float) $customer->wallet_balance + 0.001 < $newAmount) {
                    throw new InvalidArgumentException(
                        'Wallet has only Rs. '.number_format((float) $customer->wallet_balance, 2).' available.'
                    );
                }
                $customer->wallet_balance = round((float) $customer->wallet_balance - $newAmount, 2);
            }

            if ($oldMethod === 'wallet' || $newMethod === 'wallet') {
                $customer->save();

                if ($oldMethod === 'wallet' && $newMethod !== 'wallet') {
                    WalletEntry::create([
                        'customer_id' => $customer->id,
                        'bill_id' => $bill->id,
                        'payment_id' => $payment->id,
                        'user_id' => $userId,
                        'amount' => $oldAmount,
                        'reason' => 'payment_reversed',
                        'notes' => 'Wallet restored after editing '.$payment->receipt_number,
                    ]);
                } elseif ($oldMethod !== 'wallet' && $newMethod === 'wallet') {
                    WalletEntry::create([
                        'customer_id' => $customer->id,
                        'bill_id' => $bill->id,
                        'payment_id' => $payment->id,
                        'user_id' => $userId,
                        'amount' => round(-1 * $newAmount, 2),
                        'reason' => 'applied',
                        'notes' => 'Wallet applied after editing '.$payment->receipt_number,
                    ]);
                } elseif ($oldMethod === 'wallet' && $newMethod === 'wallet' && abs($delta) > 0.001) {
                    WalletEntry::create([
                        'customer_id' => $customer->id,
                        'bill_id' => $bill->id,
                        'payment_id' => $payment->id,
                        'user_id' => $userId,
                        'amount' => round(-1 * $delta, 2),
                        'reason' => $delta > 0 ? 'applied' : 'payment_reversed',
                        'notes' => 'Wallet adjusted after editing '.$payment->receipt_number,
                    ]);
                }
            }

            if (abs($delta) > 0.001) {
                $bill->deferPaymentSms = true;
                $bill->paid_amount = round((float) $bill->paid_amount + $delta, 2);
                $bill->save();
            }

            $payment->fill([
                'amount' => $newAmount,
                'payment_method' => $newMethod,
                'paid_on' => Payment::parsePaidOn($data['paid_on'] ?? now()),
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $payment->save();

            $fresh = $payment->fresh(['bill', 'customer', 'receiver']);

            try {
                ActivityLog::logPaymentUpdated($fresh, $oldSnapshot, [
                    'edited_by' => $userId,
                    'amount_delta' => $delta,
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Payment update activity log failed: '.$exception->getMessage());
            }

            return $fresh;
        });
    }

    /**
     * Delete a payment and reverse its effect on the bill (and wallet when needed).
     */
    public function deletePayment(Payment $payment, ?int $userId = null): void
    {
        DB::transaction(function () use ($payment, $userId) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $bill = Bill::query()->lockForUpdate()->findOrFail($payment->bill_id);
            $customer = Customer::query()->lockForUpdate()->findOrFail($payment->customer_id);
            $amount = round((float) $payment->amount, 2);
            $snapshot = ActivityLog::paymentSnapshot($payment->loadMissing(['customer', 'bill']));

            // Capture linked wallet rows before delete — payment_id is nullOnDelete.
            $walletEntries = WalletEntry::query()
                ->where('payment_id', $payment->id)
                ->lockForUpdate()
                ->get();

            $overpayment = round((float) $walletEntries
                ->where('reason', 'overpayment')
                ->where('amount', '>', 0)
                ->sum('amount'), 2);

            if ($overpayment > 0 && (float) $customer->wallet_balance + 0.001 < $overpayment) {
                throw new InvalidArgumentException(
                    'Cannot delete this payment because Rs. '.number_format($overpayment, 2)
                    .' was added to the wallet as overpayment and only Rs. '
                    .number_format((float) $customer->wallet_balance, 2)
                    .' remains. Restore or adjust the wallet first.'
                );
            }

            $bill->deferPaymentSms = true;
            $bill->paid_amount = round(max(0, (float) $bill->paid_amount - $amount), 2);
            $bill->save();

            $walletRestored = 0.0;
            $walletOverpaymentReversed = 0.0;

            if ($payment->payment_method === 'wallet') {
                $customer->wallet_balance = round((float) $customer->wallet_balance + $amount, 2);
                $walletRestored = $amount;

                WalletEntry::create([
                    'customer_id' => $customer->id,
                    'bill_id' => $bill->id,
                    'payment_id' => $payment->id,
                    'user_id' => $userId,
                    'amount' => $amount,
                    'reason' => 'payment_reversed',
                    'notes' => 'Reversed payment '.$payment->receipt_number,
                ]);
            }

            if ($overpayment > 0) {
                $customer->wallet_balance = round((float) $customer->wallet_balance - $overpayment, 2);
                $walletOverpaymentReversed = $overpayment;

                WalletEntry::create([
                    'customer_id' => $customer->id,
                    'bill_id' => $bill->id,
                    'payment_id' => $payment->id,
                    'user_id' => $userId,
                    'amount' => round(-1 * $overpayment, 2),
                    'reason' => 'payment_reversed',
                    'notes' => 'Reversed overpayment from '.$payment->receipt_number,
                ]);

                // Drop the original overpayment rows so the bill no longer shows cash received.
                WalletEntry::query()
                    ->where('payment_id', $payment->id)
                    ->where('reason', 'overpayment')
                    ->where('amount', '>', 0)
                    ->delete();
            }

            if ($walletRestored > 0 || $walletOverpaymentReversed > 0) {
                $customer->save();
            }

            $payment->delete();

            try {
                ActivityLog::logPaymentDeleted($payment, $snapshot, [
                    'deleted_by' => $userId,
                    'wallet_restored' => $walletRestored > 0,
                    'wallet_overpayment_reversed' => $walletOverpaymentReversed,
                    'bill_balance_after' => (float) $bill->fresh()->balance_amount,
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Payment delete activity log failed: '.$exception->getMessage());
            }
        });
    }
}
