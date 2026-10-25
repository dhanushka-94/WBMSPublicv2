<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Division;
use App\Services\BillPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentCollectionController extends Controller
{
    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        $query = trim((string) $request->input('q', ''));
        $filters = [
            'division_id' => $request->input('division_id'),
            'customer_type_id' => $request->input('customer_type_id'),
            'status' => $request->input('status'),
        ];

        $matches = $this->matchingCustomers($request, $query);

        if ($request->boolean('suggest')) {
            return response()->json([
                'customers' => $matches->limit(8)->get()->map(fn (Customer $row) => [
                    'id' => $row->id,
                    'name' => $row->full_name,
                    'account' => $row->account_number,
                ]),
            ]);
        }

        $customers = $matches->paginate(25)->withQueryString();

        if ($query !== '' && !$request->filled('customer') && $customers->total() === 1) {
            return redirect()->route('payments.collect', array_filter([
                'customer' => $customers->first()->id,
                'q' => $query,
                'division_id' => $filters['division_id'],
                'customer_type_id' => $filters['customer_type_id'],
                'status' => $filters['status'],
            ]));
        }

        $customer = null;
        $bills = collect();
        if ($request->filled('customer')) {
            $customer = Customer::query()->findOrFail($request->integer('customer'));
            $bills = $customer->bills()
                ->where('balance_amount', '>', 0)
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->orderBy('due_date')
                ->orderBy('id')
                ->paginate(25, ['*'], 'bills_page')
                ->withQueryString();
        }

        return view('payments.collect', [
            'query' => $query,
            'filters' => $filters,
            'customers' => $customers,
            'divisions' => Division::query()->orderBy('name')->get(['id', 'name']),
            'customerTypes' => CustomerType::query()->orderBy('name')->get(['id', 'name']),
            'customer' => $customer,
            'bills' => $bills,
        ]);
    }

    public function store(Request $request, BillPaymentService $payments): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bill_ids' => 'nullable|array',
            'bill_ids.*' => 'integer|exists:bills,id',
            'amount' => 'nullable|numeric|min:0',
            'use_wallet' => 'nullable|boolean',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,card',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $customer = Customer::query()->findOrFail($validated['customer_id']);

        try {
            $result = $payments->collect(
                $customer,
                $validated['bill_ids'] ?? [],
                (float) ($validated['amount'] ?? 0),
                $request->boolean('use_wallet'),
                $validated['payment_method'],
                $validated['payment_date'],
                $validated['reference'] ?? null,
                $validated['notes'] ?? null,
                $request->user()?->id
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('payments.collect', array_filter([
                'customer' => $customer->id,
                'q' => $request->input('q'),
                'division_id' => $request->input('division_id'),
                'customer_type_id' => $request->input('customer_type_id'),
                'status' => $request->input('status'),
            ]))
            ->with('success', $this->message($result));
    }

    private function matchingCustomers(Request $request, string $query)
    {
        $like = '%'.$query.'%';

        return Customer::query()
            ->with(['division:id,name', 'customerType:id,name'])
            ->withSum(['bills as open_balance' => function ($bills) {
                $bills->where('balance_amount', '>', 0)
                    ->whereNotIn('status', ['cancelled', 'draft']);
            }], 'balance_amount')
            ->when($request->filled('division_id'), fn ($customers) => $customers->where('division_id', $request->integer('division_id')))
            ->when($request->filled('customer_type_id'), fn ($customers) => $customers->where('customer_type_id', $request->integer('customer_type_id')))
            ->when($request->filled('status'), fn ($customers) => $customers->where('status', $request->input('status')))
            ->when($query !== '', function ($customers) use ($like) {
                $customers->where(function ($rows) use ($like) {
                    $rows->where('account_number', 'like', $like)
                        ->orWhere('reference_number', 'like', $like)
                        ->orWhere('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhereIn('id', Bill::query()->where('bill_number', 'like', $like)->select('customer_id'));
                });
            })
            ->orderByDesc('open_balance')
            ->orderBy('account_number');
    }

    private function message(array $result): string
    {
        $parts = [];
        $applied = collect($result['applied'])->groupBy('bill_number')->map(fn ($rows) => round($rows->sum('amount'), 2));

        foreach ($applied as $number => $amount) {
            $parts[] = 'Rs. '.number_format($amount, 2).' paid on '.$number;
        }

        if ($result['wallet_used'] > 0) {
            $parts[] = 'Rs. '.number_format($result['wallet_used'], 2).' taken from the wallet';
        }

        if ($result['wallet_added'] > 0) {
            $parts[] = 'Rs. '.number_format($result['wallet_added'], 2).' added to the wallet';
        }

        $parts[] = 'Wallet balance is now Rs. '.number_format($result['wallet_balance'], 2);

        return implode('. ', $parts).'.';
    }
}
