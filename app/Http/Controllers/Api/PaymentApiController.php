<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Bill;
use App\Models\ActivityLog;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentApiController extends Controller
{
    use LogsActivity;

    /**
     * Get customer bills for payment
     */
    public function getCustomerBills(Request $request, $customer_id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:generated,sent,overdue,paid',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = Customer::find($customer_id);
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            $query = Bill::where('customer_id', $customer_id)
                ->with(['waterMeter', 'meterReading']);

            if ($request->status) {
                $query->where('status', $request->status);
            } else {
                // Default to unpaid bills
                $query->whereIn('status', ['generated', 'sent', 'overdue']);
            }

            $bills = $query->orderBy('bill_date', 'desc')
                ->get()
                ->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'bill_number' => $bill->bill_number,
                        'bill_date' => $bill->bill_date->format('Y-m-d'),
                        'due_date' => $bill->due_date->format('Y-m-d'),
                        'billing_period' => [
                            'from' => $bill->billing_period_from->format('Y-m-d'),
                            'to' => $bill->billing_period_to->format('Y-m-d'),
                        ],
                        'consumption' => $bill->consumption,
                        'charges' => [
                            'water_charges' => $bill->water_charges,
                            'fixed_charges' => $bill->fixed_charges,
                            'service_charges' => $bill->service_charges,
                            'late_fees' => $bill->late_fees,
                            'taxes' => $bill->taxes,
                            'adjustments' => $bill->adjustments,
                        ],
                        'total_amount' => $bill->total_amount,
                        'paid_amount' => $bill->paid_amount,
                        'balance_amount' => $bill->balance_amount,
                        'status' => $bill->status,
                        'is_overdue' => $bill->isOverdue(),
                        'days_overdue' => $bill->getDaysOverdue(),
                        'meter_number' => $bill->waterMeter->meter_number ?? null,
                    ];
                });

            $totalOutstanding = $bills->sum('balance_amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->full_name,
                        'account_number' => $customer->account_number,
                        'address' => $customer->full_address,
                        'phone' => $customer->phone,
                    ],
                    'bills' => $bills,
                    'summary' => [
                        'total_bills' => $bills->count(),
                        'total_outstanding' => $totalOutstanding,
                        'overdue_bills' => $bills->where('is_overdue', true)->count(),
                        'overdue_amount' => $bills->where('is_overdue', true)->sum('balance_amount'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer bills',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record payment for bills
     */
    public function recordPayment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'bill_id' => 'required|exists:bills,id',
                'payment_amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,card,bank_transfer,mobile_payment,cheque',
                'payment_date' => 'required|date',
                'transaction_reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500',
                'receipt_photo' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
                'gps_latitude' => 'nullable|numeric|between:-90,90',
                'gps_longitude' => 'nullable|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $customer = Customer::find($request->customer_id);
            $bill = Bill::find($request->bill_id);

            // Verify the bill belongs to the customer
            if ($bill->customer_id !== $customer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bill does not belong to this customer'
                ], 400);
            }

            // Check if payment amount is valid
            if ($request->payment_amount > $bill->balance_amount && $bill->balance_amount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed outstanding balance',
                    'outstanding_balance' => $bill->balance_amount,
                    'payment_amount' => $request->payment_amount
                ], 400);
            }
            
            // Allow overpayment for testing purposes, but warn about it
            if ($bill->balance_amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This bill has already been paid in full',
                    'bill_status' => $bill->status,
                    'paid_amount' => $bill->paid_amount,
                    'total_amount' => $bill->total_amount,
                    'suggestion' => 'Use the customer bills API to find unpaid bills'
                ], 400);
            }

            DB::beginTransaction();

            // Handle receipt photo upload
            $receiptPath = null;
            if ($request->hasFile('receipt_photo')) {
                $photo = $request->file('receipt_photo');
                $filename = 'receipt_' . $bill->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $receiptPath = $photo->storeAs('payment-receipts', $filename, 'public');
            }

            // Record payment in the bill
            $previousPaidAmount = $bill->paid_amount;
            $bill->paid_amount += $request->payment_amount;
            $bill->balance_amount = $bill->total_amount - $bill->paid_amount;
            
            if ($bill->balance_amount <= 0) {
                $bill->status = 'paid';
                $bill->paid_at = $request->payment_date;
            }

            $bill->save();

            // Create payment record (you might want to create a separate payments table)
            $paymentRecord = [
                'bill_id' => $bill->id,
                'customer_id' => $customer->id,
                'payment_amount' => $request->payment_amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'transaction_reference' => $request->transaction_reference,
                'notes' => $request->notes,
                'receipt_path' => $receiptPath,
                'collector_id' => $user->id,
                'collector_name' => $user->name,
                'gps_latitude' => $request->gps_latitude,
                'gps_longitude' => $request->gps_longitude,
                'created_at' => now(),
            ];

            // Log activity
            $this->logMobileActivity('payment_recorded', [
                'customer' => $customer->full_name,
                'bill_number' => $bill->bill_number,
                'payment_amount' => $request->payment_amount,
                'payment_method' => $request->payment_method,
                'transaction_reference' => $request->transaction_reference,
                'previous_balance' => $bill->balance_amount + $request->payment_amount,
                'new_balance' => $bill->balance_amount,
                'bill_status' => $bill->status,
                'location' => $request->gps_latitude && $request->gps_longitude ? 
                    [$request->gps_latitude, $request->gps_longitude] : null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'payment_id' => $bill->id . '_' . time(), // Temporary ID
                    'bill_number' => $bill->bill_number,
                    'customer_name' => $customer->full_name,
                    'payment_amount' => $request->payment_amount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => $request->payment_date,
                    'transaction_reference' => $request->transaction_reference,
                    'previous_balance' => $previousPaidAmount,
                    'new_balance' => $bill->balance_amount,
                    'bill_status' => $bill->status,
                    'receipt_number' => $this->generateReceiptNumber($bill),
                    'receipt_data' => $this->generatePaymentReceiptData($customer, $bill, $paymentRecord),
                    'timestamp' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Payment recording failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history for a customer
     */
    public function getPaymentHistory(Request $request, $customer_id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'nullable|integer|min:1|max:100',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = Customer::find($customer_id);
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            
            $query = Bill::where('customer_id', $customer_id)
                ->where('paid_amount', '>', 0);

            if ($request->from_date) {
                $query->where('paid_at', '>=', $request->from_date);
            }

            if ($request->to_date) {
                $query->where('paid_at', '<=', $request->to_date);
            }

            $payments = $query->orderBy('paid_at', 'desc')
                ->limit($request->limit ?? 50)
                ->get()
                ->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'bill_number' => $bill->bill_number,
                        'payment_amount' => $bill->paid_amount,
                        'payment_date' => $bill->paid_at ? $bill->paid_at->format('Y-m-d H:i:s') : null,
                        'bill_amount' => $bill->total_amount,
                        'status' => $bill->status,
                        'billing_period' => [
                            'from' => $bill->billing_period_from->format('Y-m-d'),
                            'to' => $bill->billing_period_to->format('Y-m-d'),
                        ],
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->full_name,
                        'account_number' => $customer->account_number,
                    ],
                    'payments' => $payments,
                    'summary' => [
                        'total_payments' => $payments->count(),
                        'total_amount_paid' => $payments->sum('payment_amount'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers for payment collection
     */
    public function searchCustomersForPayment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'search' => 'required|string|min:1',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $search = $request->search;
            $customers = Customer::where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('account_number', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('nic', 'LIKE', "%{$search}%");
            })
            ->where('status', 'active')
            ->with(['bills' => function ($query) {
                $query->whereIn('status', ['generated', 'sent', 'overdue'])
                    ->where('balance_amount', '>', 0);
            }])
            ->limit($request->limit ?? 20)
            ->get()
            ->map(function ($customer) {
                $outstandingBills = $customer->bills->where('balance_amount', '>', 0);
                $totalOutstanding = $outstandingBills->sum('balance_amount');
                $overdueAmount = $outstandingBills->where('status', 'overdue')->sum('balance_amount');

                return [
                    'id' => $customer->id,
                    'name' => $customer->full_name,
                    'account_number' => $customer->account_number,
                    'phone' => $customer->phone,
                    'address' => $customer->full_address,
                    'outstanding_bills' => $outstandingBills->count(),
                    'total_outstanding' => $totalOutstanding,
                    'overdue_amount' => $overdueAmount,
                    'has_overdue' => $overdueAmount > 0,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'customers' => $customers,
                    'total_found' => $customers->count(),
                    'search_term' => $search,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate receipt number
     */
    private function generateReceiptNumber($bill): string
    {
        return "RCP" . date('Ymd') . str_pad($bill->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate payment receipt data
     */
    private function generatePaymentReceiptData($customer, $bill, $paymentRecord): array
    {
        return [
            'receipt_number' => $this->generateReceiptNumber($bill),
            'date' => now()->format('Y-m-d H:i:s'),
            'customer' => [
                'name' => $customer->full_name,
                'account_number' => $customer->account_number,
                'address' => $customer->full_address,
            ],
            'bill' => [
                'number' => $bill->bill_number,
                'date' => $bill->bill_date->format('Y-m-d'),
                'due_date' => $bill->due_date->format('Y-m-d'),
                'total_amount' => $bill->total_amount,
                'previous_balance' => $bill->total_amount - $paymentRecord['payment_amount'],
            ],
            'payment' => [
                'amount' => $paymentRecord['payment_amount'],
                'method' => $paymentRecord['payment_method'],
                'date' => $paymentRecord['payment_date'],
                'reference' => $paymentRecord['transaction_reference'],
            ],
            'balance' => [
                'remaining' => $bill->balance_amount,
                'status' => $bill->status,
            ],
            'collector' => [
                'name' => $paymentRecord['collector_name'],
                'id' => $paymentRecord['collector_id'],
            ],
            'company' => [
                'name' => 'Water Board Management System',
                'address' => 'Your Company Address',
                'phone' => 'Your Company Phone',
            ],
        ];
    }

    /**
     * Log mobile activity
     */
    private function logMobileActivity(string $action, array $data = []): void
    {
        $user = Auth::user();
        
        ActivityLog::logActivity([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'action' => $action,
            'description' => ucfirst(str_replace('_', ' ', $action)) . ' via mobile app',
            'module' => 'mobile_app',
            'properties' => $data,
        ]);
    }
} 