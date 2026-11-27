<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ActivityLogController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of activity logs with advanced filtering
     */
    public function index(Request $request): View
    {
        $errors = $this->dropInvalidFilters($request);

        // The list, the statistics cards and the export all use the same filtered query,
        // so the numbers always agree with each other.
        $perPage = in_array((int) $request->input('per_page'), [25, 50, 100], true) ? (int) $request->input('per_page') : 25;
        $activities = $this->filteredQuery($request)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        // Get filter options
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();
        $modules = ActivityLog::distinct()->pluck('module')
            ->map(fn ($module) => ActivityLog::canonicalModule($module))
            ->unique()->sort()->values();

        $stats = $this->getFilteredStats($request);
        $archiveMonths = ActivityLog::archiveMonths();

        // Record the visit once. The page's own 30-second refresh is not user activity.
        if (!$request->boolean('auto')) {
            $this->logActivity('view', 'Viewed activity logs dashboard');
        }

        return view('activity-logs.index', compact(
            'activities',
            'users',
            'actions',
            'modules',
            'stats',
            'archiveMonths'
        ))->withErrors($errors);
    }

    /**
     * Validation rules shared by the list, statistics and export.
     */
    private function filterRules(Request $request): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'regex:/^(\d+|guest)$/'],
            'action' => ['nullable', 'string', 'max:60'],
            'module' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', Rule::in(['success', 'failed', 'warning'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => array_filter(['nullable', 'date', $request->filled('date_from') ? 'after_or_equal:date_from' : null]),
            'period' => ['nullable', Rule::in(['today', 'week', 'month', 'login', 'crud'])],
            'hide_views' => ['nullable', 'boolean'],
        ];
    }

    /**
     * An invalid filter (for example "To" earlier than "From") is ignored and reported on the page,
     * instead of silently returning an empty list.
     */
    private function dropInvalidFilters(Request $request)
    {
        $validator = Validator::make($request->all(), $this->filterRules($request), [
            'date_to.after_or_equal' => 'The "Date To" must be on or after the "Date From". That date range was not applied.',
            'date_from.date' => 'The "Date From" is not a valid date.',
            'date_to.date' => 'The "Date To" is not a valid date.',
        ]);

        if ($validator->fails()) {
            $request->merge(array_fill_keys($validator->errors()->keys(), null));
        }

        return $validator->errors();
    }

    /**
     * The one place that turns the filter form into a query.
     */
    private function filteredQuery(Request $request): Builder
    {
        $query = ActivityLog::query();

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $like = '%'.addcslashes($term, '%_\\').'%';
            $actionLike = '%'.addcslashes(str_replace(' ', '_', $term), '%_\\').'%';
            $query->where(function ($q) use ($like, $actionLike) {
                $q->where('description', 'like', $like)
                    ->orWhere('user_name', 'like', $like)
                    ->orWhere('user_email', 'like', $like)
                    ->orWhere('subject_name', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhere('action', 'like', $actionLike)
                    ->orWhere('module', 'like', $like);
            });
        }

        if ($request->filled('user_id')) {
            $request->input('user_id') === 'guest'
                ? $query->whereNull('user_id')
                : $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('module')) {
            $query->whereIn('module', ActivityLog::moduleVariants((string) $request->input('module')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        match ($request->input('period')) {
            'today' => $query->today(),
            'week' => $query->thisWeek(),
            'month' => $query->thisMonth(),
            'login' => $query->authentication(),
            'crud' => $query->crudOperations(),
            default => null,
        };

        if ($request->boolean('hide_views')) {
            $query->hidePageViews();
        }

        return $query;
    }

    /**
     * Download one month's activity text file.
     */
    public function downloadMonth(string $month)
    {
        $path = ActivityLog::monthlyArchivePath($month);
        if (!$path || !is_file($path)) {
            abort(404);
        }

        return response()->download($path, 'activity-'.$month.'.txt', [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
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
        $query = ActivityLog::where('user_id', $user->id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('module')) {
            $query->whereIn('module', ActivityLog::moduleVariants((string) $request->module));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            $like = '%'.addcslashes($term, '%_\\').'%';
            $actionLike = '%'.addcslashes(str_replace(' ', '_', $term), '%_\\').'%';
            $query->where(function ($inner) use ($like, $actionLike) {
                $inner->where('description', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhere('subject_name', 'like', $like)
                    ->orWhere('action', 'like', $actionLike)
                    ->orWhere('module', 'like', $like);
            });
        }

        match ($request->input('date_range')) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            'week' => $query->where('created_at', '>=', now()->startOfWeek()),
            'month' => $query->where('created_at', '>=', now()->startOfMonth()),
            default => null,
        };

        $activities = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $forUser = ActivityLog::where('user_id', $user->id);
        $stats = [
            'today' => (clone $forUser)->whereDate('created_at', today())->count(),
            'week' => (clone $forUser)->where('created_at', '>=', now()->startOfWeek())->count(),
        ];
        $lastLoginAt = (clone $forUser)->where('action', 'login')->latest('created_at')->value('created_at');
        $lastLogin = $lastLoginAt ? Carbon::parse($lastLoginAt)->diffForHumans() : 'Never';
        $availableActions = (clone $forUser)->distinct()->orderBy('action')->pluck('action');
        $availableModules = (clone $forUser)->distinct()->pluck('module')
            ->map(fn ($module) => ActivityLog::canonicalModule($module))
            ->unique()->sort()->values();

        $this->logActivity('view', "Viewed activity logs for user: {$user->name}");

        return view('activity-logs.user', compact(
            'user',
            'activities',
            'stats',
            'lastLogin',
            'availableActions',
            'availableModules'
        ));
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        if (!in_array($format, ['csv', 'json'], true)) {
            return back()->with('error', 'Invalid export format');
        }

        $errors = $this->dropInvalidFilters($request);
        if ($errors->isNotEmpty()) {
            return back()->withErrors($errors);
        }

        // Exactly the rows the filtered list shows.
        $query = $this->filteredQuery($request);
        $count = (clone $query)->count();

        $this->logExport('activity_logs', $count, $format);

        // CSV is streamed in newest-first batches by id; JSON loads the rows in one go.
        return $format === 'csv'
            ? $this->exportCsv($query)
            : $this->exportJson($query->orderByDesc('created_at')->orderByDesc('id'));
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
        // Same filters as the list, so the cards always match the number of rows listed.
        $row = $this->filteredQuery($request)
            ->selectRaw("COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as today,
                COUNT(DISTINCT user_id) as unique_users", [today()->startOfDay(), today()->addDay()->startOfDay()])
            ->first();

        return [
            'total' => (int) $row->total,
            'successful' => (int) $row->successful,
            'failed' => (int) $row->failed,
            'today' => (int) $row->today,
            'unique_users' => (int) $row->unique_users,
        ];
    }

    /**
     * Export activities as CSV
     */
    private function exportCsv(Builder $query)
    {
        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            // Cells that start with = + - @ can run as formulas in Excel, so mark them as text.
            $safe = fn ($value) => is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true) ? "'".$value : $value;
            
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

            // Write data in batches so a large export does not load every row into memory.
            $query->chunkByIdDesc(1000, function ($activities) use ($file, $safe) {
                foreach ($activities as $activity) {
                    fputcsv($file, [
                        $activity->id,
                        $activity->created_at->format('Y-m-d h:i:s A'),
                        $safe($activity->user_name),
                        $safe($activity->user_email),
                        $activity->user_role,
                        $activity->action,
                        $activity->module,
                        $safe($activity->description),
                        $safe($activity->subject_name),
                        $activity->status,
                        $activity->ip_address,
                        $activity->duration_ms
                    ]);
                }
            });

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export activities as JSON
     */
    private function exportJson(Builder $query)
    {
        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.json';
        
        $data = $query->get()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'datetime' => $activity->created_at->format('Y-m-d h:i:s A'),
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
