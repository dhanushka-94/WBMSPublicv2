<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

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

        $defaultData = [
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->role ?? 'guest',
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'session_id' => session()->getId(),
            'method' => $request?->method(),
            'url' => $request?->fullUrl(),
            'route_name' => $request?->route()?->getName(),
            'status' => 'success',
            'module' => 'system' // Default module
        ];

        $mergedData = array_merge($defaultData, $data);
        
        // Ensure module is never null or empty
        if (empty($mergedData['module'])) {
            $mergedData['module'] = 'system';
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

    // Attribute accessors
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M d, Y H:i:s');
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
