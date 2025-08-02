<?php

namespace App\Http\Controllers;

use App\Models\SmsNotification;
use App\Models\Customer;
use App\Models\Bill;
use App\Services\SmsNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmsNotificationController extends Controller
{
    protected $smsService;

    public function __construct(SmsNotificationService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Display SMS notifications log
     */
    public function index(Request $request)
    {
        $query = SmsNotification::with(['customer', 'bill'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('customer')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->customer . '%')
                  ->orWhere('last_name', 'like', '%' . $request->customer . '%')
                  ->orWhere('account_number', 'like', '%' . $request->customer . '%');
            });
        }

        if ($request->filled('phone')) {
            $query->where('phone_number', 'like', '%' . $request->phone . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->paginate(20)->appends($request->query());

        // Get statistics
        $stats = $this->getStatistics();

        return view('sms.index', compact('notifications', 'stats'));
    }

    /**
     * Show SMS notification details
     */
    public function show(SmsNotification $sms_notification)
    {
        $sms_notification->load(['customer', 'bill']);
        return view('sms.show', compact('sms_notification'));
    }

    /**
     * Resend a failed SMS
     */
    public function resend(SmsNotification $sms_notification)
    {
        if ($sms_notification->status !== 'failed') {
            return redirect()->back()->with('error', 'Only failed SMS messages can be resent.');
        }

        try {
            $result = $this->smsService->sendSmsNotification(
                $sms_notification->phone_number,
                $sms_notification->message,
                $sms_notification->type,
                $sms_notification->customer_id,
                $sms_notification->bill_id
            );

            if ($result['success']) {
                return redirect()->back()->with('success', 'SMS resent successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to resend SMS: ' . $result['error']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to resend SMS: ' . $e->getMessage());
        }
    }

    /**
     * Delete SMS notification record
     */
    public function destroy(SmsNotification $sms_notification)
    {
        $sms_notification->delete();
        return redirect()->route('sms.index')->with('success', 'SMS record deleted successfully.');
    }

    /**
     * Show SMS statistics dashboard
     */
    public function statistics()
    {
        $stats = $this->getDetailedStatistics();
        return view('sms.statistics', compact('stats'));
    }

    /**
     * Export SMS logs to CSV
     */
    public function export(Request $request)
    {
        $query = SmsNotification::with(['customer', 'bill'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->get();

        $filename = 'sms_log_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($notifications) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Date/Time',
                'Customer',
                'Account Number',
                'Phone Number',
                'Type',
                'Status',
                'Message',
                'HTTP Status',
                'Response',
                'Retry Count',
                'Error Message'
            ]);

            foreach ($notifications as $notification) {
                fputcsv($file, [
                    $notification->id,
                    $notification->created_at->format('Y-m-d H:i:s'),
                    $notification->customer ? $notification->customer->full_name : 'N/A',
                    $notification->customer ? $notification->customer->account_number : 'N/A',
                    $notification->phone_number,
                    $notification->getTypeLabelAttribute(),
                    ucfirst($notification->status),
                    $notification->message,
                    $notification->http_status,
                    $notification->response_data ? json_encode($notification->response_data) : '',
                    $notification->retry_count,
                    $notification->error_message
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk delete SMS records
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:sms_notifications,id'
        ]);

        $count = SmsNotification::whereIn('id', $request->selected_ids)->delete();
        
        return redirect()->route('sms.index')->with('success', "Deleted {$count} SMS records successfully.");
    }

    /**
     * Get basic statistics
     */
    private function getStatistics()
    {
        return [
            'total' => SmsNotification::count(),
            'sent' => SmsNotification::sent()->count(),
            'failed' => SmsNotification::failed()->count(),
            'pending' => SmsNotification::pending()->count(),
            'today' => SmsNotification::whereDate('created_at', today())->count(),
            'this_week' => SmsNotification::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month' => SmsNotification::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get detailed statistics for dashboard
     */
    private function getDetailedStatistics()
    {
        $basic = $this->getStatistics();

        // SMS by type
        $byType = SmsNotification::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Daily stats for last 30 days
        $dailyStats = SmsNotification::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Success rate by type
        $successRateByType = SmsNotification::select(
                'type',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent')
            )
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $rate = $item->total > 0 ? round(($item->sent / $item->total) * 100, 2) : 0;
                return [$item->type => $rate];
            });

        // Top customers by SMS count
        $topCustomers = SmsNotification::with('customer')
            ->select('customer_id', DB::raw('count(*) as sms_count'))
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderBy('sms_count', 'desc')
            ->limit(10)
            ->get();

        // Recent activity
        $recentActivity = SmsNotification::with(['customer', 'bill'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return array_merge($basic, [
            'by_type' => $byType,
            'daily_stats' => $dailyStats,
            'success_rate_by_type' => $successRateByType,
            'top_customers' => $topCustomers,
            'recent_activity' => $recentActivity,
            'success_rate' => $basic['total'] > 0 ? round(($basic['sent'] / $basic['total']) * 100, 2) : 0,
        ]);
    }
}