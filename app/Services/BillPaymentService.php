<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\WalletEntry;
use Illuminate\Support\Facades\DB;
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
            ];

            foreach ($bills as $bill) {
                $walletLeft = $this->apply($bill, $walletLeft, 'wallet', $details, $applied);
                $bill->refresh();
                $cashLeft = $this->apply($bill, $cashLeft, $method, $details, $applied);
            }

            $walletAdded = round($cashLeft, 2);
            $customer->wallet_balance = round($walletBalance - $walletUse + $walletAdded, 2);
            $customer->save();

            if ($walletUse > 0) {
                WalletEntry::create([
                    'customer_id' => $customer->id,
                    'user_id' => $userId,
                    'amount' => round(-1 * $walletUse, 2),
                    'reason' => 'applied',
                    'notes' => 'Used on '.collect($applied)->pluck('bill_number')->unique()->implode(', '),
                ]);
            }

            if ($walletAdded > 0) {
                WalletEntry::create([
                    'customer_id' => $customer->id,
                    'user_id' => $userId,
                    'amount' => $walletAdded,
                    'reason' => 'overpayment',
                    'notes' => $notes ?: 'Amount left after the selected bills were paid',
                ]);
            }

            return [
                'applied' => $applied,
                'wallet_used' => $walletUse,
                'wallet_added' => $walletAdded,
                'wallet_balance' => (float) $customer->wallet_balance,
            ];
        });
    }

    private function apply(Bill $bill, float $pool, string $method, array $details, array &$applied): float
    {
        $take = round(min((float) $bill->balance_amount, $pool), 2);
        if ($take <= 0) {
            return round($pool, 2);
        }

        $saved = $bill->recordPayment($take, $method, $details);
        if (!$saved) {
            throw new InvalidArgumentException('The payment could not be saved on '.$bill->bill_number.'.');
        }

        $applied[] = [
            'bill_number' => $bill->bill_number,
            'amount' => $take,
            'method' => $method,
        ];

        return round($pool - $take, 2);
    }
}
