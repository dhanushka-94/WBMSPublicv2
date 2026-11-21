<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\WalletEntry;
use Carbon\CarbonInterface;

class CollectionTotals
{
    /**
     * Exact cash received in a period: non-wallet payment amounts + wallet overpayments.
     *
     * @return array{applied: float, overpaid: float, cash: float, receipts: int}
     */
    public static function forPeriod(CarbonInterface $start, CarbonInterface $end): array
    {
        $applied = (float) Payment::query()
            ->whereBetween('paid_on', [$start, $end])
            ->where('payment_method', '!=', 'wallet')
            ->sum('amount');

        $receipts = (int) Payment::query()
            ->whereBetween('paid_on', [$start, $end])
            ->count();

        // Only living payment-linked overpayments. Orphans from deleted receipts must not
        // keep reversed cash visible in daily/monthly collection.
        $overpaid = (float) WalletEntry::query()
            ->livingOverpayments()
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        return [
            'applied' => round($applied, 2),
            'overpaid' => round($overpaid, 2),
            'cash' => round($applied + $overpaid, 2),
            'receipts' => $receipts,
        ];
    }

    /**
     * Exact cash received on a calendar date (app timezone).
     *
     * @return array{applied: float, overpaid: float, cash: float, receipts: int}
     */
    public static function forDate(string $date): array
    {
        $start = \Carbon\Carbon::parse($date, config('app.timezone'))->startOfDay();
        $end = $start->copy()->endOfDay();

        return self::forPeriod($start, $end);
    }
}
