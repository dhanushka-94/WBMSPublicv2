<?php

namespace App\Services;

use App\Exceptions\ReadingChainLockedException;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Rate;
use App\Models\WalletEntry;
use App\Models\WaterMeter;
use InvalidArgumentException;

class BillReadingChainService
{
    public function __construct(
        private readonly BillPaymentService $payments
    ) {
    }

    /**
     * After a billed meter reading is edited from Readings, push dials into the linked
     * unpaid bill, cascade later readings/bills, refresh meter dial, and reconcile money.
     *
     * @return array{bill_synced: bool, later_readings: int, later_bills: int, wallet_credited: float, wallet_applied: float, meter_dial: ?float}
     */
    public function syncAfterReadingUpdate(MeterReading $reading, ?int $userId = null): array
    {
        $reading = MeterReading::query()->lockForUpdate()->findOrFail($reading->id);

        $previous = round((float) $reading->previous_reading, 2);
        $current = round((float) $reading->current_reading, 2);
        $consumption = round(max(0, $current - $previous), 2);

        MeterReading::withoutEvents(function () use ($reading, $consumption) {
            $reading->forceFill(['consumption' => $consumption])->save();
        });

        $bill = Bill::query()
            ->where(function ($query) use ($reading) {
                $query->where('meter_reading_id', $reading->id)
                    ->orWhere(function ($sameMeter) use ($reading) {
                        $sameMeter->where('water_meter_id', $reading->water_meter_id)
                            ->whereDate('billing_period_to', $reading->reading_date);
                    });
            })
            ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $billSynced = false;
        if ($bill) {
            $this->repriceBillFromReading($bill, $previous, $current, $consumption);
            if ((int) $bill->meter_reading_id !== (int) $reading->id) {
                $bill->meter_reading_id = $reading->id;
                $bill->save();
            }
            $billSynced = true;
        }

        $cascade = $this->cascadeLaterReadings($reading->fresh());

        $meterDial = null;
        if ($reading->water_meter_id) {
            $meter = WaterMeter::query()->lockForUpdate()->find($reading->water_meter_id);
            if ($meter) {
                $meterDial = $meter->refreshDialFromLatestReading();
            }
        }

        $money = ['wallet_credited' => 0.0, 'wallet_applied' => 0.0];
        if ($bill) {
            $money = $this->reconcileMoney($bill->fresh(), $userId);
        }

        return [
            'bill_synced' => $billSynced,
            'later_readings' => $cascade['later_readings'],
            'later_bills' => $cascade['later_bills'],
            'wallet_credited' => $money['wallet_credited'],
            'wallet_applied' => $money['wallet_applied'],
            'meter_dial' => $meterDial,
        ];
    }

    /**
     * After a bill's dials/charges are saved, sync the meter reading ledger,
     * later readings (+ unpaid later bills), meter dial, and wallet vs paid amount.
     *
     * @return array{reading_synced: bool, later_readings: int, later_bills: int, wallet_credited: float, wallet_applied: float, meter_dial: ?float}
     */
    public function syncAfterBillUpdate(Bill $bill, ?int $userId = null): array
    {
        $bill = Bill::query()->lockForUpdate()->findOrFail($bill->id);

        if ((float) $bill->current_reading + 0.0001 < (float) $bill->previous_reading) {
            throw new InvalidArgumentException(
                'Current reading cannot be lower than previous reading.'
            );
        }

        $reading = $this->syncLinkedReading($bill);
        $cascade = $reading
            ? $this->cascadeLaterReadings($reading)
            : ['later_readings' => 0, 'later_bills' => 0];

        $meterDial = null;
        if ($bill->water_meter_id) {
            $meter = WaterMeter::query()->lockForUpdate()->find($bill->water_meter_id);
            if ($meter) {
                $meterDial = $meter->refreshDialFromLatestReading();
            }
        }

        $money = $this->reconcileMoney($bill->fresh(), $userId);

        return [
            'reading_synced' => (bool) $reading,
            'later_readings' => $cascade['later_readings'],
            'later_bills' => $cascade['later_bills'],
            'wallet_credited' => $money['wallet_credited'],
            'wallet_applied' => $money['wallet_applied'],
            'meter_dial' => $meterDial,
        ];
    }

    private function syncLinkedReading(Bill $bill): ?MeterReading
    {
        if (!$bill->water_meter_id) {
            return null;
        }

        $reading = null;
        if ($bill->meter_reading_id) {
            $reading = MeterReading::query()->lockForUpdate()->find($bill->meter_reading_id);
        }

        if (!$reading) {
            $from = $bill->billing_period_from?->toDateString();
            $to = $bill->billing_period_to?->toDateString();
            $reading = MeterReading::query()
                ->where('water_meter_id', $bill->water_meter_id)
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('reading_date', [$from, $to]);
                })
                ->orderByDesc('reading_date')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();
        }

        $previous = round((float) $bill->previous_reading, 2);
        $current = round((float) $bill->current_reading, 2);
        $consumption = round(max(0, $current - $previous), 2);

        if ($reading) {
            MeterReading::withoutEvents(function () use ($reading, $bill, $previous, $current, $consumption) {
                $reading->forceFill([
                    'water_meter_id' => $bill->water_meter_id,
                    'previous_reading' => $previous,
                    'current_reading' => $current,
                    'consumption' => $consumption,
                    'status' => 'billed',
                    'is_billable' => true,
                ])->save();
            });
        } else {
            $reading = MeterReading::withoutEvents(function () use ($bill, $previous, $current, $consumption) {
                return MeterReading::create([
                    'water_meter_id' => $bill->water_meter_id,
                    'reading_date' => $bill->billing_period_to?->toDateString()
                        ?? $bill->bill_date?->toDateString()
                        ?? now()->toDateString(),
                    'previous_reading' => $previous,
                    'current_reading' => $current,
                    'consumption' => $consumption,
                    'reading_type' => 'actual',
                    'status' => 'billed',
                    'is_billable' => true,
                    'notes' => 'Created when bill '.$bill->bill_number.' was edited',
                    'submitted_via' => 'office_bill_edit',
                ]);
            });
        }

        if ((int) $bill->meter_reading_id !== (int) $reading->id) {
            $bill->meter_reading_id = $reading->id;
            $bill->save();
        }

        return $reading->fresh();
    }

    /**
     * @return array{later_readings: int, later_bills: int}
     */
    private function cascadeLaterReadings(MeterReading $anchor): array
    {
        $later = MeterReading::query()
            ->where('water_meter_id', $anchor->water_meter_id)
            ->where(function ($query) use ($anchor) {
                $query->where('reading_date', '>', $anchor->reading_date)
                    ->orWhere(function ($sameDay) use ($anchor) {
                        $sameDay->whereDate('reading_date', $anchor->reading_date)
                            ->where('id', '>', $anchor->id);
                    });
            })
            ->orderBy('reading_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $prev = round((float) $anchor->current_reading, 2);
        $readingsUpdated = 0;
        $billsUpdated = 0;

        foreach ($later as $next) {
            $current = round((float) $next->current_reading, 2);
            $consumption = round(max(0, $current - $prev), 2);

            // A paid or cancelled bill is never repriced, so the chain can't move past it.
            if (abs(round((float) $next->previous_reading, 2) - $prev) > 0.001) {
                $this->assertLaterBillNotLocked($next);
            }

            MeterReading::withoutEvents(function () use ($next, $prev, $consumption) {
                $next->forceFill([
                    'previous_reading' => $prev,
                    'consumption' => $consumption,
                ])->save();
            });
            $readingsUpdated++;

            $linkedBill = Bill::query()
                ->where(function ($query) use ($next) {
                    $query->where('meter_reading_id', $next->id)
                        ->orWhere(function ($sameMeter) use ($next) {
                            $sameMeter->where('water_meter_id', $next->water_meter_id)
                                ->whereDate('billing_period_to', $next->reading_date);
                        });
                })
                ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($linkedBill) {
                $this->repriceBillFromReading($linkedBill, $prev, $current, $consumption);
                $billsUpdated++;
            }

            $prev = $current;
        }

        return [
            'later_readings' => $readingsUpdated,
            'later_bills' => $billsUpdated,
        ];
    }

    /**
     * Cascade for a reading that is not billed yet (pending or verified): its later readings and
     * unpaid bills follow the new current reading. Billed readings use syncAfterReadingUpdate instead.
     *
     * @return array{later_readings: int, later_bills: int}
     */
    public function syncLaterReadings(MeterReading $reading): array
    {
        return $this->cascadeLaterReadings($reading);
    }

    /**
     * Stop the edit if the reading after this one is already tied to a paid or cancelled bill.
     * That bill's numbers can't be changed, so the chain would be left inconsistent.
     */
    private function assertLaterBillNotLocked(MeterReading $reading): void
    {
        $bill = Bill::query()
            ->where(function ($query) use ($reading) {
                $query->where('meter_reading_id', $reading->id)
                    ->orWhere(function ($sameMeter) use ($reading) {
                        $sameMeter->where('water_meter_id', $reading->water_meter_id)
                            ->whereDate('billing_period_to', $reading->reading_date);
                    });
            })
            ->orderByDesc('id')
            ->first();

        if ($bill && in_array($bill->status, ['paid', 'cancelled'], true)) {
            throw new ReadingChainLockedException(
                'This change would move the next reading ('.$reading->reading_date->format('d M Y').
                ') so its bill '.$bill->bill_number.' would no longer match its reading. That bill is already '.
                $bill->status.', and paid or cancelled bills cannot be changed. The edit was cancelled.'
            );
        }
    }

    private function repriceBillFromReading(
        Bill $bill,
        float $previous,
        float $current,
        float $consumption
    ): void {
        // Opening-balance bills also get their water and fixed charges from the tariff when their reading
        // changes. The carried-forward amount in adjustments is never touched, so the old dues stay as typed.
        $customer = Customer::query()->find($bill->customer_id);
        if (!$customer) {
            return;
        }

        $charges = Rate::calculateCharges(
            $customer->billingCategory(),
            $consumption,
            $bill->bill_date
        );

        $bill->deferPaymentSms = true;
        $bill->forceFill([
            'previous_reading' => $previous,
            'current_reading' => $current,
            'consumption' => $consumption,
            'water_charges' => $charges['water_charges'],
            'fixed_charges' => $charges['fixed_charges'],
            'rate_breakdown' => $charges['breakdown'],
        ])->save();
    }

    /**
     * @return array{wallet_credited: float, wallet_applied: float}
     */
    private function reconcileMoney(Bill $bill, ?int $userId): array
    {
        $bill = Bill::query()->lockForUpdate()->findOrFail($bill->id);
        $customer = Customer::query()->lockForUpdate()->findOrFail($bill->customer_id);

        $walletCredited = 0.0;
        $paid = round((float) $bill->paid_amount, 2);
        $total = round((float) $bill->total_amount, 2);

        if ($paid > $total + 0.001) {
            $excess = round($paid - $total, 2);
            $bill->deferPaymentSms = true;
            $bill->paid_amount = $total;
            $bill->save();

            $customer->wallet_balance = round((float) $customer->wallet_balance + $excess, 2);
            $customer->save();

            WalletEntry::create([
                'customer_id' => $customer->id,
                'bill_id' => $bill->id,
                'payment_id' => null,
                'user_id' => $userId,
                'amount' => $excess,
                'reason' => 'overpayment',
                'notes' => 'Credit from bill '.$bill->bill_number.' after reading edit reduced the total',
            ]);

            $walletCredited = $excess;
        }

        $walletApplied = 0.0;
        $bill->refresh();
        if ((float) $bill->balance_amount > 0.001 && (float) $customer->wallet_balance > 0.001) {
            $applied = $this->payments->applyWalletCredit($bill, $userId, [
                'notes' => 'Auto-applied wallet after bill '.$bill->bill_number.' reading edit',
                'source' => 'bill_edit_wallet_apply',
                'skip_sms' => true,
            ]);
            $walletApplied = (float) ($applied['wallet_used'] ?? 0);
        }

        return [
            'wallet_credited' => $walletCredited,
            'wallet_applied' => $walletApplied,
        ];
    }
}
