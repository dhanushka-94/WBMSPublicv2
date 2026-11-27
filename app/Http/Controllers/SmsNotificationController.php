<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SmsNotification;
use App\Models\Customer;
use App\Models\Bill;
use App\Services\SmsNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmsNotificationController extends Controller
{
    private const PER_PAGE = [25, 50, 100];
    private const BULK_RESEND_LIMIT = 50;

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
        $notifications = $this->logQuery($request)
            ->with(['customer', 'bill'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        // Status tab counts follow every filter except the status itself.
        $counts = $this->logQuery($request, false)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = $this->getStatistics();
        $rate = (float) config('sms.cost_per_segment', 0);
        $types = $this->typeOptions();
        $archiveMonths = SmsNotification::archiveMonths();

        return view('sms.index', compact('notifications', 'counts', 'stats', 'rate', 'types', 'archiveMonths'));
    }

    /**
     * Download one month's SMS text file.
     */
    public function downloadMonth(string $month)
    {
        $path = SmsNotification::monthlyArchivePath($month);
        if (!$path || !is_file($path)) {
            abort(404);
        }

        return response()->download($path, 'sms-'.$month.'.txt', [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /**
     * Failed SMS with resend
     */
    public function failed(Request $request)
    {
        $base = $this->logQuery($request, false)->where('status', 'failed');

        $notifications = (clone $base)
            ->with(['customer', 'bill'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($this->perPage($request, [25, 50]), ['*'], 'page')
            ->withQueryString();

        $rate = (float) config('sms.cost_per_segment', 0);

        $summary = (clone $base)->selectRaw(
            'COUNT(*) as total, COUNT(DISTINCT customer_id) as customers, COALESCE(SUM(GREATEST(COALESCE(segments, 1), 1)), 0) as segments'
        )->first();

        $last24h = (clone $base)->where('updated_at', '>=', now()->subDay())->count();

        // Most common reasons across the filtered failures (the reason filter itself is ignored so the chips stay clickable).
        $reasons = $this->logQuery($request, false, false)
            ->where('status', 'failed')
            ->limit(5000)->get(['id', 'status', 'error_message', 'response_data'])
            ->groupBy(fn ($row) => $row->failure_reason)
            ->map->count()
            ->sortDesc()
            ->take(6);

        $types = $this->typeOptions();

        return view('sms.failed', compact('notifications', 'summary', 'last24h', 'reasons', 'rate', 'types'));
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

        $result = $this->smsService->resend($sms_notification);
        $phone = $sms_notification->fresh()->phone_number;

        if ($result['success']) {
            $this->audit('sms_resend', "Resent failed SMS #{$sms_notification->id} to {$phone}", ['sms_id' => $sms_notification->id, 'phone' => $phone], $sms_notification);
            return redirect()->back()->with('success', "SMS resent to {$phone}.");
        }

        $this->audit('sms_resend', "Resend of SMS #{$sms_notification->id} to {$phone} failed", ['sms_id' => $sms_notification->id, 'phone' => $phone], $sms_notification, 'failed', $result['message'] ?? 'Unknown error');

        return redirect()->back()->with('error', 'Could not resend: ' . ($result['message'] ?? 'Unknown error'));
    }

    /**
     * Write one descriptive row to the system activity log for an SMS action.
     */
    private function audit(string $action, string $description, array $properties = [], ?SmsNotification $subject = null, string $status = 'success', ?string $error = null): void
    {
        try {
            ActivityLog::logActivity([
                'action' => $action,
                'module' => 'sms',
                'description' => (auth()->user()?->name ?? 'A user') . ': ' . $description,
                'subject_type' => $subject ? SmsNotification::class : null,
                'subject_id' => $subject?->id,
                'subject_name' => $subject ? "SMS #{$subject->id}" : null,
                'properties' => $properties ?: null,
                'status' => $status,
                'error_message' => $error,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Send a delivered SMS again. Logged as a new message because it is charged again.
     */
    public function sendAgain(SmsNotification $sms_notification)
    {
        $result = $this->smsService->sendAgain($sms_notification);
        $copy = $result['notification'] ?? null;

        $this->audit(
            'sms_send_again',
            $result['success']
                ? "Sent SMS #{$sms_notification->id} again to {$sms_notification->phone_number} (new message #{$copy->id})"
                : "Send again of SMS #{$sms_notification->id} to {$sms_notification->phone_number} failed",
            ['sms_id' => $sms_notification->id, 'new_sms_id' => $copy?->id, 'phone' => $sms_notification->phone_number],
            $sms_notification,
            $result['success'] ? 'success' : 'failed',
            $result['success'] ? null : ($result['message'] ?? 'Unknown error')
        );

        if ($result['success']) {
            return redirect()->back()->with('success', "SMS sent again to {$copy->phone_number}. It is logged as a new message (#{$copy->id}).");
        }

        $message = 'Could not send again: ' . ($result['message'] ?? 'Unknown error');
        if ($copy) {
            $message .= ' A failed entry was added to Failed SMS where you can retry.';
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * Resend many failed SMS (max 50 per request because every message is a live API call)
     */
    public function bulkResend(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array|min:1|max:' . self::BULK_RESEND_LIMIT,
            'selected_ids.*' => 'integer|exists:sms_notifications,id',
        ], [
            'selected_ids.required' => 'Select at least one SMS to resend.',
            'selected_ids.max' => 'Select up to ' . self::BULK_RESEND_LIMIT . ' messages at a time.',
        ]);

        @set_time_limit(300);

        $ids = array_unique($request->selected_ids);
        $rows = SmsNotification::with('customer')
            ->whereIn('id', $ids)
            ->where('status', 'failed')
            ->orderBy('id')
            ->get();

        $sent = 0;
        $failed = 0;
        $skipped = count($ids) - $rows->count();
        $notTried = 0;
        $stopReason = null;
        $reasons = [];

        foreach ($rows as $index => $row) {
            $result = $this->smsService->resend($row);

            if ($result['skipped'] ?? false) {
                $skipped++;
                continue;
            }

            if ($result['success']) {
                $sent++;
                continue;
            }

            $failed++;
            $reason = $result['message'] ?? 'Unknown error';
            $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;

            // No point calling the API for the rest when sending is off or the account is empty.
            if (($result['disabled'] ?? false) || str_contains(strtolower($reason), 'balance')) {
                $notTried = $rows->count() - $index - 1;
                $stopReason = $reason;
                break;
            }
        }

        $this->audit(
            'sms_bulk_resend',
            "Bulk resend of failed SMS: {$sent} sent, {$failed} failed again, {$skipped} skipped" . ($notTried > 0 ? ", {$notTried} not attempted" : ''),
            ['selected' => count($ids), 'sent' => $sent, 'failed' => $failed, 'skipped' => $skipped, 'not_attempted' => $notTried, 'stopped_because' => $stopReason],
            null,
            $failed > 0 || $notTried > 0 ? ($sent > 0 ? 'warning' : 'failed') : 'success',
            $stopReason
        );

        $redirect = redirect()->back();

        if ($sent > 0) {
            $redirect->with('success', "Resent {$sent} SMS successfully.");
        }

        if ($failed > 0 || $notTried > 0 || $skipped > 0) {
            $parts = [];
            if ($failed > 0) {
                $parts[] = "{$failed} failed again (" . collect($reasons)->map(fn ($n, $r) => "{$r} x{$n}")->implode('; ') . ')';
            }
            if ($notTried > 0) {
                $parts[] = "stopped early, {$notTried} not attempted";
            }
            if ($skipped > 0) {
                $parts[] = "{$skipped} skipped (no longer failed)";
            }
            $redirect->with($sent > 0 ? 'warning' : 'error', ucfirst(implode(', ', $parts)) . '.');
        }

        return $redirect;
    }

    /**
     * Delete SMS notification record
     */
    public function destroy(SmsNotification $sms_notification)
    {
        $this->audit('sms_delete', "Deleted SMS record #{$sms_notification->id} to {$sms_notification->phone_number}", ['sms_id' => $sms_notification->id, 'phone' => $sms_notification->phone_number, 'status' => $sms_notification->status], $sms_notification);
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
     * Export SMS logs to CSV (uses the same filters as the log and failed pages)
     */
    public function export(Request $request)
    {
        $notifications = $this->logQuery($request)
            ->with(['customer', 'bill', 'payment'])
            ->orderByDesc('created_at')
            ->get();

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
                'Error Message',
                'Bill',
                'Receipt',
                'Segments',
                'Source'
            ]);

            foreach ($notifications as $notification) {
                fputcsv($file, [
                    $notification->id,
                    $notification->created_at->format('Y-m-d h:i:s A'),
                    $notification->customer ? $notification->customer->full_name : 'N/A',
                    $notification->customer ? $notification->customer->account_number : 'N/A',
                    $notification->phone_number,
                    $notification->getTypeLabelAttribute(),
                    ucfirst($notification->status),
                    $notification->message,
                    $notification->http_status,
                    $notification->response_data ? json_encode($notification->response_data) : '',
                    $notification->retry_count,
                    $notification->failure_reason ?? $notification->error_message,
                    $notification->bill->bill_number ?? '',
                    $notification->receipt_number,
                    $notification->segments,
                    $notification->source
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
        $this->audit('sms_delete', "Deleted {$count} SMS records", ['count' => $count, 'ids' => array_values($request->selected_ids)]);
        
        return redirect()->route('sms.index')->with('success', "Deleted {$count} SMS records successfully.");
    }

    /**
     * Shared filters for the log, failed and export screens.
     */
    private function logQuery(Request $request, bool $withStatus = true, bool $withReason = true): Builder
    {
        $query = SmsNotification::query();

        if ($withStatus && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // "search" covers name, account, phone and message. "customer" and "phone" are kept for old links.
        foreach (['search', 'customer', 'phone'] as $key) {
            $term = trim((string) $request->query($key, $request->input($key)));
            if ($term === '') {
                continue;
            }
            $like = '%' . $term . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('phone_number', 'like', $like)
                    ->orWhere('message', 'like', $like)
                    ->orWhereHas('customer', function ($c) use ($like) {
                        $c->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('account_number', 'like', $like)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$like]);
                    });
            });
        }

        if ($withReason && $request->filled('reason')) {
            $like = '%' . trim((string) $request->reason) . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('error_message', 'like', $like)->orWhere('response_data', 'like', $like);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    private function perPage(Request $request, array $allowed = self::PER_PAGE): int
    {
        $value = (int) $request->query('per_page');

        return in_array($value, $allowed, true) ? $value : $allowed[0];
    }

    private function typeOptions(): array
    {
        return [
            'new_bill' => 'New Bill',
            'due_reminder' => 'Due Reminder',
            'overdue_alert' => 'Overdue Alert',
            'payment_confirmation' => 'Payment Confirmation',
            'late_fee_notice' => 'Late Fee Notice',
            'service_disconnection' => 'Service Disconnection',
            'meter_reading_reminder' => 'Meter Reading',
            'maintenance_notice' => 'Maintenance Notice',
            'custom' => 'Custom',
            'reconnection' => 'Reconnection Notice',
        ];
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
        $recentActivity = SmsNotification::with(['customer', 'bill', 'payment'])
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
