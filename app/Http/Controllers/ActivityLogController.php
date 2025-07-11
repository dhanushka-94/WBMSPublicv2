<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ActivityLogController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of activity logs with advanced filtering
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('subject_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Module filter
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Quick date filters
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->today();
                    break;
                case 'week':
                    $query->thisWeek();
                    break;
                case 'month':
                    $query->thisMonth();
                    break;
                case 'login':
                    $query->authentication();
                    break;
                case 'crud':
                    $query->crudOperations();
                    break;
            }
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(25);

        // Get filter options
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();
        $modules = ActivityLog::distinct()->pluck('module')->sort()->values();

        // Get statistics for the current filter
        $stats = $this->getFilteredStats($request);

        // Log this view activity
        $this->logActivity('view', 'Viewed activity logs dashboard');

        return view('activity-logs.index', compact(
            'activities',
            'users',
            'actions',
            'modules',
            'stats'
        ));
    }

    /**
     * Display the specified activity log details
     */
    public function show(ActivityLog $activity): View
    {
        $activity->load('user');

        // Log this view activity
        $this->logActivity('view', "Viewed activity log details: {$activity->id}");

        return view('activity-logs.show', compact('activity'));
    }

    /**
     * Get activity statistics for dashboard
     */
    public function stats(Request $request): JsonResponse
    {
        $period = $request->input('period', 30); // Days
        $stats = ActivityLog::getActivityStats($period);

        return response()->json($stats);
    }

    /**
     * Get recent activities for AJAX requests
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $activities = ActivityLog::getRecentActivity($limit);

        return response()->json([
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user_name' => $activity->user_name,
                    'description' => $activity->description,
                    'action' => $activity->action,
                    'time_ago' => $activity->time_ago,
                    'status' => $activity->status,
                    'action_icon' => $activity->action_icon
                ];
            })
        ]);
    }

    /**
     * Get user activity for profile/user management
     */
    public function userActivity(Request $request, User $user): View
    {
        $days = $request->input('days', 30);
        $activities = ActivityLog::getUserActivity($user->id, $days);

        // Log this view activity
        $this->logActivity('view', "Viewed activity logs for user: {$user->name}");

        return view('activity-logs.user', compact('user', 'activities', 'days'));
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        
        $query = ActivityLog::with('user');
        
        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        // Log export activity
        $this->logExport('activity_logs', $activities->count(), $format);

        if ($format === 'csv') {
            return $this->exportCsv($activities);
        } elseif ($format === 'json') {
            return $this->exportJson($activities);
        }

        return back()->with('error', 'Invalid export format');
    }

    /**
     * Delete old activity logs (cleanup)
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        $days = $request->input('days');
        $cutoffDate = now()->subDays($days);
        
        $count = ActivityLog::where('created_at', '<', $cutoffDate)->count();
        ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        // Log cleanup activity
        $this->logActivity('cleanup', "Cleaned up {$count} activity logs older than {$days} days", null, [
            'deleted_count' => $count,
            'cutoff_days' => $days
        ]);

        return back()->with('success', "Cleaned up {$count} old activity logs successfully.");
    }

    /**
     * Get filtered statistics
     */
    private function getFilteredStats(Request $request): array
    {
        $query = ActivityLog::query();

        // Apply same filters as main query
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return [
            'total' => $query->count(),
            'successful' => $query->clone()->where('status', 'success')->count(),
            'failed' => $query->clone()->where('status', 'failed')->count(),
            'today' => $query->clone()->whereDate('created_at', today())->count(),
            'unique_users' => $query->clone()->distinct('user_id')->count('user_id')
        ];
    }

    /**
     * Export activities as CSV
     */
    private function exportCsv($activities)
    {
        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'Email',
                'Role',
                'Action',
                'Module',
                'Description',
                'Subject',
                'Status',
                'IP Address',
                'Duration (ms)'
            ]);

            // Write data
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->user_name,
                    $activity->user_email,
                    $activity->user_role,
                    $activity->action,
                    $activity->module,
                    $activity->description,
                    $activity->subject_name,
                    $activity->status,
                    $activity->ip_address,
                    $activity->duration_ms
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export activities as JSON
     */
    private function exportJson($activities)
    {
        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.json';
        
        $data = $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'datetime' => $activity->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $activity->user_id,
                    'name' => $activity->user_name,
                    'email' => $activity->user_email,
                    'role' => $activity->user_role
                ],
                'action' => $activity->action,
                'module' => $activity->module,
                'description' => $activity->description,
                'subject' => [
                    'type' => $activity->subject_type,
                    'id' => $activity->subject_id,
                    'name' => $activity->subject_name
                ],
                'request' => [
                    'method' => $activity->method,
                    'url' => $activity->url,
                    'ip_address' => $activity->ip_address,
                    'user_agent' => $activity->user_agent
                ],
                'status' => $activity->status,
                'duration_ms' => $activity->duration_ms,
                'properties' => $activity->properties
            ];
        });

        return Response::json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
