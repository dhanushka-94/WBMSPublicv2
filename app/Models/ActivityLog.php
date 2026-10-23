<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

    public const DATABASE_RETENTION_DAYS = 90;

    protected static function booted(): void
    {
        static::created(function (ActivityLog $log) {
            $log->appendToMonthlyFile();
        });
    }

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'action',
        'subject_type',
        'subject_id',
        'subject_name',
        'description',
        'module',
        'method',
        'url',
        'route_name',
        'old_values',
        'new_values',
        'properties',
        'ip_address',
        'user_agent',
        'session_id',
        'status',
        'error_message',
        'duration_ms'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeBySubjectType($query, $type)
    {
        return $query->where('subject_type', $type);
    }

    public function scopeInDateRange($query, $from, $to = null)
    {
        $to = $to ?? now();
        return $query->whereBetween('created_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay()
        ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeAuthentication($query)
    {
        return $query->whereIn('action', ['login', 'logout', 'login_failed', 'password_reset']);
    }

    public function scopeCrudOperations($query)
    {
        return $query->whereIn('action', ['create', 'read', 'update', 'delete']);
    }

    // Helper methods
    public static function logActivity(array $data): self
    {
        $user = auth()->user();
        $request = request();

        $sessionId = null;
        if ($request && $request->hasSession()) {
            $sessionId = $request->session()->getId();
        }

        $defaultData = [
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->role ?? 'guest',
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'session_id' => $sessionId,
            'method' => $request?->method(),
            'url' => $request ? substr($request->fullUrl(), 0, 255) : null,
            'route_name' => $request?->route()?->getName(),
            'status' => 'success',
            'module' => 'system' // Default module
        ];

        $mergedData = array_merge($defaultData, $data);
        
        // Ensure module is never null or empty
        if (empty($mergedData['module'])) {
            $mergedData['module'] = 'system';
        }

        if ($request) {
            $request->attributes->set('activity_logged', true);
        }

        return self::create($mergedData);
    }

    public static function logLogin(User $user, bool $successful = true): self
    {
        return self::logActivity([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'action' => $successful ? 'login' : 'login_failed',
            'description' => $successful 
                ? "User '{$user->name}' logged in successfully" 
                : "Failed login attempt for user '{$user->email}'",
            'module' => 'authentication',
            'status' => $successful ? 'success' : 'failed'
        ]);
    }

    public static function logLogout(User $user): self
    {
        return self::logActivity([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'action' => 'logout',
            'description' => "User '{$user->name}' logged out",
            'module' => 'authentication'
        ]);
    }

    public static function logModelActivity(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): self
    {
        $modelName = class_basename($model);
        $subjectName = method_exists($model, 'getLogName') ? $model->getLogName() : ($model->name ?? $model->id);

        return self::logActivity([
            'action' => $action,
            'subject_type' => get_class($model),
            'subject_id' => $model->id,
            'subject_name' => $subjectName,
            'description' => $description ?? ucfirst($action) . " {$modelName}: {$subjectName}",
            'module' => strtolower(str_replace('_', '', Str::snake($modelName))),
            'old_values' => $oldValues,
            'new_values' => $newValues
        ]);
    }

    /**
     * Copy rows that existed before monthly files started, then drop database rows older than 90 days.
     * Runs at most once a day. The text files are kept.
     */
    public static function maintainArchive(): void
    {
        $dir = storage_path('logs/activity');
        $marker = $dir.DIRECTORY_SEPARATOR.'.backfilled';
        $stamp = storage_path('framework/activity-prune-stamp.txt');
        $today = now()->toDateString();
        $needsBackfill = !is_file($marker);
        $needsPrune = !is_file($stamp) || trim((string) file_get_contents($stamp)) !== $today;

        if (!$needsBackfill && !$needsPrune) {
            return;
        }

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return;
        }

        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        $lock = fopen($dir.DIRECTORY_SEPARATOR.'.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            return;
        }

        try {
            if (!is_file($marker)) {
                $progress = $dir.DIRECTORY_SEPARATOR.'.backfill-id';
                $lastId = is_file($progress) ? (int) trim((string) file_get_contents($progress)) : 0;

                self::where('id', '>', $lastId)->orderBy('id')->chunkById(200, function ($logs) use ($progress) {
                    foreach ($logs as $log) {
                        $log->appendToMonthlyFile();
                        file_put_contents($progress, (string) $log->id);
                    }
                });

                file_put_contents($marker, now()->toIso8601String());
            }

            $today = now()->toDateString();
            if (!is_file($stamp) || trim((string) file_get_contents($stamp)) !== $today) {
                self::where('created_at', '<', now()->subDays(self::DATABASE_RETENTION_DAYS))->delete();
                file_put_contents($stamp, $today);
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function appendToMonthlyFile(): void
    {
        try {
            $at = $this->created_at ?? now();
            $dir = storage_path('logs/activity');
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                return;
            }

            $path = $dir.DIRECTORY_SEPARATOR.$at->format('Y-m').'.txt';
            $line = implode(' | ', [
                $at->format('Y-m-d H:i:s'),
                $this->archiveText($this->user_name),
                $this->archiveText($this->user_email),
                $this->archiveText($this->action),
                $this->archiveText($this->status),
                $this->archiveText($this->description),
            ]).PHP_EOL;

            $handle = fopen($path, 'ab');
            if ($handle === false) {
                return;
            }

            try {
                if (!flock($handle, LOCK_EX)) {
                    return;
                }
                clearstatcache(true, $path);
                if (!is_file($path) || filesize($path) === 0) {
                    fwrite($handle, '# AquaBill activity log '.$at->format('Y-m').PHP_EOL);
                    fwrite($handle, '# date time | user | email | action | status | description'.PHP_EOL);
                }
                fwrite($handle, $line);
                fflush($handle);
                flock($handle, LOCK_UN);
            } finally {
                fclose($handle);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function archiveMonths(): array
    {
        $months = [];
        foreach (glob(storage_path('logs/activity/*.txt')) ?: [] as $file) {
            $name = basename($file, '.txt');
            if (preg_match('/^\d{4}-\d{2}$/', $name)) {
                $months[] = $name;
            }
        }
        rsort($months);

        return $months;
    }

    public static function monthlyArchivePath(string $month): ?string
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return null;
        }

        return storage_path('logs/activity/'.$month.'.txt');
    }

    private function archiveText(?string $value): string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value)) ?? '';

        return $value === '' ? '-' : $value;
    }

    // Attribute accessors
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M d, Y h:i:s A');
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'login' => 'fas fa-sign-in-alt text-green-600',
            'logout' => 'fas fa-sign-out-alt text-gray-600',
            'login_failed' => 'fas fa-exclamation-triangle text-red-600',
            'create' => 'fas fa-plus text-green-600',
            'read', 'view' => 'fas fa-eye text-blue-600',
            'update' => 'fas fa-edit text-yellow-600',
            'delete' => 'fas fa-trash text-red-600',
            'export' => 'fas fa-download text-purple-600',
            'import' => 'fas fa-upload text-purple-600',
            'backup' => 'fas fa-database text-gray-600',
            default => 'fas fa-info-circle text-gray-600'
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'success' => '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Success</span>',
            'failed' => '<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Failed</span>',
            'warning' => '<span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Warning</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">Unknown</span>'
        };
    }

    // Static helper methods for common queries
    public static function getUserActivity(int $userId, int $days = 30)
    {
        return self::where('user_id', $userId)
                  ->where('created_at', '>=', now()->subDays($days))
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    public static function getRecentActivity(int $limit = 50)
    {
        return self::with('user')
                  ->orderBy('created_at', 'desc')
                  ->limit($limit)
                  ->get();
    }

    public static function getActivityStats(int $days = 30): array
    {
        $from = now()->subDays($days);
        
        return [
            'total_activities' => self::where('created_at', '>=', $from)->count(),
            'successful_activities' => self::where('created_at', '>=', $from)->successful()->count(),
            'failed_activities' => self::where('created_at', '>=', $from)->failed()->count(),
            'unique_users' => self::where('created_at', '>=', $from)->distinct('user_id')->count('user_id'),
            'login_count' => self::where('created_at', '>=', $from)->where('action', 'login')->count(),
            'activities_by_module' => self::where('created_at', '>=', $from)
                ->selectRaw('module, COUNT(*) as count')
                ->groupBy('module')
                ->pluck('count', 'module')
                ->toArray()
        ];
    }
}
