<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\WalletEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsController extends Controller
{
    /**
     * One place to see and manage readings, bills, and payments.
     */
    public function index(Request $request): View
    {
        $tab = in_array($request->input('tab'), ['readings', 'bills', 'payments'], true)
            ? $request->input('tab')
            : 'readings';

        $q = trim((string) $request->input('q', ''));
        $from = (string) $request->input('from', '');
        $to = (string) $request->input('to', '');
        $status = (string) $request->input('status', '');

        $summary = [
            'readings' => MeterReading::count(),
            'readings_today' => MeterReading::whereDate('reading_date', Carbon::today())->count(),
            'bills' => Bill::where('status', '!=', 'cancelled')->count(),
            'bills_open' => Bill::where('balance_amount', '>', 0)->whereNotIn('status', ['cancelled', 'draft'])->count(),
            'payments' => Payment::count(),
            'paid_exact' => round(
                (float) Payment::sum('amount')
                + (float) WalletEntry::query()->livingOverpayments()->sum('amount'),
                2
            ),
        ];

        $readings = null;
        $bills = null;
        $payments = null;

        if ($tab === 'readings') {
            $readings = MeterReading::query()
                ->with(['waterMeter.customer:id,account_number,first_name,last_name,title', 'bill:id,meter_reading_id,bill_number,status'])
                ->when($q !== '', function ($query) use ($q) {
                    $like = '%'.$q.'%';
                    $query->where(function ($rows) use ($like) {
                        $rows->where('reader_name', 'like', $like)
                            ->orWhereHas('waterMeter', function ($meter) use ($like) {
                                $meter->where('meter_number', 'like', $like)
                                    ->orWhereHas('customer', function ($customer) use ($like) {
                                        $customer->where('account_number', 'like', $like)
                                            ->orWhere('first_name', 'like', $like)
                                            ->orWhere('last_name', 'like', $like);
                                    });
                            });
                    });
                })
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($from !== '', fn ($query) => $query->whereDate('reading_date', '>=', $from))
                ->when($to !== '', fn ($query) => $query->whereDate('reading_date', '<=', $to))
                ->orderByDesc('reading_date')
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString();
        }

        if ($tab === 'bills') {
            $bills = Bill::query()
                ->with(['customer:id,account_number,first_name,last_name,title', 'waterMeter:id,meter_number'])
                ->when($q !== '', function ($query) use ($q) {
                    $like = '%'.$q.'%';
                    $query->where(function ($rows) use ($like) {
                        $rows->where('bill_number', 'like', $like)
                            ->orWhereHas('customer', function ($customer) use ($like) {
                                $customer->where('account_number', 'like', $like)
                                    ->orWhere('first_name', 'like', $like)
                                    ->orWhere('last_name', 'like', $like);
                            });
                    });
                })
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($from !== '', fn ($query) => $query->whereDate('bill_date', '>=', $from))
                ->when($to !== '', fn ($query) => $query->whereDate('bill_date', '<=', $to))
                ->orderByDesc('bill_date')
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString();
        }

        if ($tab === 'payments') {
            $payments = Payment::query()
                ->with([
                    'customer:id,account_number,first_name,last_name,title',
                    'bill:id,bill_number',
                    'receiver:id,name',
                    'overpaymentEntries',
                ])
                ->when($q !== '', function ($query) use ($q) {
                    $like = '%'.$q.'%';
                    $query->where(function ($rows) use ($like) {
                        $rows->where('receipt_number', 'like', $like)
                            ->orWhere('reference', 'like', $like)
                            ->orWhereHas('customer', function ($customer) use ($like) {
                                $customer->where('account_number', 'like', $like)
                                    ->orWhere('first_name', 'like', $like)
                                    ->orWhere('last_name', 'like', $like);
                            })
                            ->orWhereHas('bill', fn ($bill) => $bill->where('bill_number', 'like', $like));
                    });
                })
                ->when($status !== '', fn ($query) => $query->where('payment_method', $status))
                ->when($from !== '', fn ($query) => $query->whereDate('paid_on', '>=', $from))
                ->when($to !== '', fn ($query) => $query->whereDate('paid_on', '<=', $to))
                ->orderByDesc('paid_on')
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString();
        }

        return view('operations.index', compact(
            'tab',
            'q',
            'from',
            'to',
            'status',
            'summary',
            'readings',
            'bills',
            'payments'
        ));
    }
}
