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

    /**
     * One display name per module. Older rows and some writers used other spellings
     * (for example "customer" and "customers"), which split the module filter in two.
     */
    public const MODULE_ALIASES = [
        'customers' => ['customer'],
        'authentication' => ['login', 'logout', 'register', 'password'],
        'mobile_app' => ['mobile-api', 'mobile_api'],
        'meters' => ['meter', 'water-meters', 'watermeter', 'water_meters'],
        'readings' => ['meter-readings', 'meterreading', 'meter_readings'],
        'billing' => ['bills', 'bill'],
        'payments' => ['payment'],
        'users' => ['user'],
        'rates' => ['rate'],
        'guarantors' => ['guarantor'],
        'administration' => ['activity-logs'],
    ];

    /** Sign-in, sign-out and password events (web and mobile app). */
    public const AUTH_ACTIONS = [
        'login', 'logout', 'login_failed', 'password_reset',
        'mobile_login', 'mobile_login_failed', 'mobile_login_denied', 'mobile_logout', 'token_refresh',
    ];

    /** Actions that change data or settings. Names starting with "bulk_" also count. */
    public const CHANGE_ACTIONS = [
        'create', 'update', 'delete', 'import', 'restore', 'backup', 'verify', 'approve', 'reject', 'sync',
        'payment', 'payment_created', 'payment_updated', 'payment_deleted', 'payment_collection',
        'payment_uploaded', 'payment_recorded',
        'reading_uploaded', 'readings_bulk_uploaded', 'meter_reading_submitted', 'bulk_sync_completed',
        'meter_location_updated', 'mobile_profile_updated', 'mobile_profile_photo_updated',
        'sms_resend', 'sms_bulk_resend', 'sms_send_again', 'sms_delete', 'system_task',
        'wallet_adjusted', 'wallet_adjustment_removed',
    ];

    /** Read-only noise that can be hidden so real changes stand out. */
    public const PAGE_VIEW_ACTIONS = [
        'view', 'read', 'download', 'search', 'route_downloaded', 'route_fetched', 'token_check', 'quote',
    ];

    protected static function booted(): void
    {
        static::creating(function (ActivityLog $log) {
            $log->module = self::canonicalModule($log->module);
        });

        static::created(function (ActivityLog $log) {
            $log->appendToMonthlyFile();
        });
    }

    public static function canonicalModule(?string $module): string
    {
        $module = strtolower(trim((string) $module));
        if ($module === '') {
            return 'system';
        }

        foreach (self::MODULE_ALIASES as $canonical => $aliases) {
            if ($module === $canonical || in_array($module, $aliases, true)) {
                return $canonical;
            }
        }

        return $module;
    }

    /** The module name plus every spelling that older rows may still use. */
    public static function moduleVariants(string $module): array
    {
        $canonical = self::canonicalModule($module);

        return array_values(array_unique(array_merge([$canonical, $module], self::MODULE_ALIASES[$canonical] ?? [])));
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
        return $query->whereIn('module', self::moduleVariants((string) $module));
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
        return $query->whereIn('action', self::AUTH_ACTIONS);
    }

    public function scopeCrudOperations($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('action', self::CHANGE_ACTIONS)->orWhere('action', 'like', 'bulk\\_%');
        });
    }

    public function scopeHidePageViews($query)
    {
        return $query->whereNotIn('action', self::PAGE_VIEW_ACTIONS);
    }

    /** Activity done by the system itself (scheduled jobs, console commands). */
    public static function logSystem(
        string $action,
        string $description,
        string $status = 'success',
        array $properties = [],
        ?string $error = null,
        ?int $durationMs = null
    ): self {
        return self::create([
            'user_name' => 'System',
            'user_role' => 'system',
            'action' => $action,
            'description' => $description,
            'module' => 'system',
            'method' => 'CLI',
            'user_agent' => 'artisan',
            'status' => $status,
            'error_message' => $error,
            'properties' => $properties ?: null,
            'duration_ms' => $durationMs,
        ]);
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

    public static function logPaymentCreated(Payment $payment, array $extra = []): self
    {
        $payment->loadMissing(['customer', 'bill']);
        $actor = auth()->user()?->name ?? 'System';
        $amount = number_format((float) $payment->amount, 2);
        $customer = $payment->customer?->full_name ?? 'customer';
        $account = $payment->customer?->account_number ?? '—';
        $bill = $payment->bill?->bill_number ?? '—';
        $method = str_replace('_', ' ', (string) $payment->payment_method);
        $paidOn = $payment->paidOnDisplay();

        $description = $payment->isAutoWalletCredit()
            ? "Wallet credit of Rs.{$amount} auto-applied to bill {$bill} for {$customer} ({$account}), on {$paidOn} (triggered by {$actor})"
            : "{$actor} recorded payment {$payment->receipt_number} of Rs.{$amount} ({$method}) for {$customer} ({$account}), bill {$bill}, on {$paidOn}";

        return self::logActivity([
            'action' => 'payment_created',
            'module' => 'payments',
            'subject_type' => Payment::class,
            'subject_id' => $payment->id,
            'subject_name' => $payment->receipt_number,
            'description' => $description,
            'new_values' => self::paymentSnapshot($payment),
            'properties' => array_merge([
                'receipt_number' => $payment->receipt_number,
                'payment_id' => $payment->id,
                'bill_id' => $payment->bill_id,
                'bill_number' => $payment->bill?->bill_number,
                'customer_id' => $payment->customer_id,
                'customer_name' => $payment->customer?->full_name,
                'account_number' => $payment->customer?->account_number,
                'amount' => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'paid_on' => optional($payment->paid_on)?->toDateTimeString(),
                'reference' => $payment->reference,
                'notes' => $payment->notes,
                'received_by' => $payment->received_by,
                'bill_balance' => $payment->bill ? (float) $payment->bill->balance_amount : null,
                'bill_status' => $payment->bill?->status,
                'sms_sent' => $extra['sms_sent'] ?? null,
            ], $extra),
        ]);
    }

    public static function logPaymentUpdated(Payment $payment, array $oldValues, array $extra = []): self
    {
        $payment->loadMissing(['customer', 'bill']);
        $actor = auth()->user()?->name ?? 'System';
        $amount = number_format((float) $payment->amount, 2);

        return self::logActivity([
            'action' => 'payment_updated',
            'module' => 'payments',
            'subject_type' => Payment::class,
            'subject_id' => $payment->id,
            'subject_name' => $payment->receipt_number,
            'description' => "{$actor} updated payment {$payment->receipt_number} to Rs.{$amount} ({$payment->payment_method}) for bill ".($payment->bill?->bill_number ?? '—'),
            'old_values' => $oldValues,
            'new_values' => self::paymentSnapshot($payment),
            'properties' => array_merge([
                'receipt_number' => $payment->receipt_number,
                'payment_id' => $payment->id,
                'bill_id' => $payment->bill_id,
                'customer_id' => $payment->customer_id,
            ], $extra),
        ]);
    }

    public static function logPaymentDeleted(Payment $payment, array $snapshot, array $extra = []): self
    {
        $actor = auth()->user()?->name ?? 'System';
        $receipt = $snapshot['receipt_number'] ?? ('ID '.$payment->id);
        $amount = number_format((float) ($snapshot['amount'] ?? 0), 2);
        $bill = $snapshot['bill_number'] ?? '—';

        return self::logActivity([
            'action' => 'payment_deleted',
            'module' => 'payments',
            'subject_type' => Payment::class,
            'subject_id' => $snapshot['id'] ?? $payment->id,
            'subject_name' => $receipt,
            'description' => "{$actor} deleted payment {$receipt} of Rs.{$amount} for bill {$bill}; bill balance restored",
            'old_values' => $snapshot,
            'properties' => array_merge([
                'receipt_number' => $receipt,
                'payment_id' => $snapshot['id'] ?? $payment->id,
                'bill_id' => $snapshot['bill_id'] ?? null,
                'customer_id' => $snapshot['customer_id'] ?? null,
                'amount' => $snapshot['amount'] ?? null,
                'payment_method' => $snapshot['payment_method'] ?? null,
            ], $extra),
        ]);
    }

    public static function logPaymentCollection(Customer $customer, array $result, array $extra = []): self
    {
        $actor = auth()->user()?->name ?? 'System';
        $applied = collect($result['applied'] ?? []);
        $total = number_format((float) $applied->sum('amount'), 2);
        $bills = $applied->pluck('bill_number')->unique()->filter()->implode(', ') ?: 'none';
        $walletUsed = number_format((float) ($result['wallet_used'] ?? 0), 2);
        $walletAdded = number_format((float) ($result['wallet_added'] ?? 0), 2);

        return self::logActivity([
            'action' => 'payment_collection',
            'module' => 'payments',
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'subject_name' => $customer->full_name,
            'description' => "{$actor} collected Rs.{$total} for {$customer->full_name} ({$customer->account_number}) on bills [{$bills}]; wallet used Rs.{$walletUsed}, wallet added Rs.{$walletAdded}",
            'properties' => array_merge([
                'customer_id' => $customer->id,
                'account_number' => $customer->account_number,
                'applied' => $result['applied'] ?? [],
                'wallet_used' => $result['wallet_used'] ?? 0,
                'wallet_added' => $result['wallet_added'] ?? 0,
                'wallet_balance' => $result['wallet_balance'] ?? null,
                'sms_sent' => $result['sms_sent'] ?? 0,
                'sms_skipped' => $result['sms_skipped'] ?? 0,
            ], $extra),
        ]);
    }

    public static function logPaymentReceiptViewed(Payment $payment): self
    {
        $payment->loadMissing(['customer', 'bill']);
        $actor = auth()->user()?->name ?? 'Guest';

        return self::logActivity([
            'action' => 'payment_receipt_viewed',
            'module' => 'payments',
            'subject_type' => Payment::class,
            'subject_id' => $payment->id,
            'subject_name' => $payment->receipt_number,
            'description' => "{$actor} viewed receipt {$payment->receipt_number} for ".($payment->customer?->full_name ?? 'customer'),
            'properties' => [
                'receipt_number' => $payment->receipt_number,
                'payment_id' => $payment->id,
                'bill_number' => $payment->bill?->bill_number,
                'amount' => (float) $payment->amount,
            ],
        ]);
    }

    public static function paymentSnapshot(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'receipt_number' => $payment->receipt_number,
            'bill_id' => $payment->bill_id,
            'bill_number' => $payment->bill?->bill_number,
            'customer_id' => $payment->customer_id,
            'customer_name' => $payment->customer?->full_name,
            'account_number' => $payment->customer?->account_number,
            'amount' => (float) $payment->amount,
            'payment_method' => $payment->payment_method,
            'paid_on' => optional($payment->paid_on)?->toDateTimeString(),
            'reference' => $payment->reference,
            'notes' => $payment->notes,
            'received_by' => $payment->received_by,
        ];
    }

    /**
     * Mobile app activity with a clear human description for the office activity log.
     */
    public static function logMobileApp(
        string $action,
        array $properties = [],
        ?string $description = null,
        string $status = 'success',
        ?User $actor = null
    ): self {
        $user = $actor ?? auth()->user() ?? auth('sanctum')->user();
        $name = $user?->name ?? 'Mobile user';
        $device = $properties['device_name'] ?? null;

        $autoDescription = match ($action) {
            'mobile_login' => "{$name} signed in to the mobile app".($device ? " on device {$device}" : ''),
            'mobile_login_failed' => 'Failed mobile login'.(isset($properties['email']) ? " for {$properties['email']}" : '').($device ? " on device {$device}" : ''),
            'mobile_login_denied' => "{$name} was denied mobile app access".($device ? " on device {$device}" : ''),
            'mobile_logout' => "{$name} signed out of the mobile app".($device ? " on device {$device}" : ''),
            'route_downloaded', 'route_fetched' => sprintf(
                '%s downloaded today\'s route: %d customers, %d meters%s',
                $name,
                (int) ($properties['customer_count'] ?? 0),
                (int) ($properties['meter_count'] ?? 0),
                !empty($properties['route']) ? ' ('.$properties['route'].')' : ''
            ),
            'meter_reading_submitted', 'reading_uploaded' => sprintf(
                '%s uploaded meter reading for %s%s: reading %s%s',
                $name,
                $properties['customer'] ?? 'customer',
                !empty($properties['meter_number']) ? ' (meter '.$properties['meter_number'].')' : '',
                $properties['reading'] ?? '—',
                isset($properties['consumption']) ? ', consumption '.$properties['consumption'] : ''
            ),
            'bulk_sync_completed', 'readings_bulk_uploaded' => sprintf(
                '%s uploaded bulk sync: %d of %d readings succeeded%s',
                $name,
                (int) ($properties['successful'] ?? 0),
                (int) ($properties['total_readings'] ?? 0),
                ((int) ($properties['failed'] ?? 0)) > 0 ? ', '.(int) $properties['failed'].' failed' : ''
            ),
            'payment_recorded', 'payment_uploaded' => sprintf(
                '%s uploaded payment %s of Rs.%s for %s%s',
                $name,
                $properties['receipt_number'] ?? '—',
                number_format((float) ($properties['payment_amount'] ?? $properties['amount'] ?? 0), 2),
                $properties['customer'] ?? 'customer',
                !empty($properties['bill_number']) ? ' (bill '.$properties['bill_number'].')' : ''
            ),
            'meter_location_updated' => "{$name} updated meter GPS from the mobile app",
            'mobile_profile_updated' => "{$name} updated mobile profile",
            'mobile_profile_photo_updated' => "{$name} updated mobile profile photo",
            default => "{$name} ".str_replace('_', ' ', $action).' from the mobile app',
        };

        return self::logActivity([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'action' => $action,
            'description' => $description ?: $autoDescription,
            'module' => 'mobile_app',
            'status' => $status,
            'properties' => array_merge(['source' => 'mobile_api'], $properties),
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
                'module='.$this->archiveText($this->module),
                'ip='.$this->archiveText($this->ip_address),
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
                    fwrite($handle, '# date time | user | email | action | status | description | module=... | ip=... (module and ip were added on 27 Nov 2026; older lines end at description)'.PHP_EOL);
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
        // A "|" inside a value would shift the columns when the file is opened in a spreadsheet.
        $value = str_replace('|', '/', $value);

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

    /**
     * Icon and colour for each action. The same colour is used for the icon and the action text,
     * so for example "Reading uploaded" (green, up arrow) and "Route downloaded" (blue, down arrow)
     * are easy to tell apart.
     *
     * @return array{0: string, 1: string} [icon classes, text colour class]
     */
    private function actionStyle(): array
    {
        return match ($this->action) {
            'login' => ['fas fa-sign-in-alt', 'text-green-600'],
            'logout' => ['fas fa-sign-out-alt', 'text-gray-600'],
            'login_failed', 'mobile_login_failed', 'mobile_login_denied' => ['fas fa-exclamation-triangle', 'text-red-600'],
            'mobile_login' => ['fas fa-mobile-alt', 'text-green-600'],
            'mobile_logout' => ['fas fa-mobile-alt', 'text-gray-600'],
            'create' => ['fas fa-plus', 'text-green-600'],
            'read', 'view' => ['fas fa-eye', 'text-blue-600'],
            'update' => ['fas fa-edit', 'text-yellow-600'],
            'delete' => ['fas fa-trash', 'text-red-600'],
            'export' => ['fas fa-file-export', 'text-purple-600'],
            'import' => ['fas fa-file-import', 'text-purple-600'],
            'backup' => ['fas fa-database', 'text-gray-600'],
            // Mobile app: uploads are green with an up arrow, downloads are blue with a down arrow.
            'reading_uploaded', 'meter_reading_submitted' => ['fas fa-cloud-upload-alt', 'text-green-600'],
            'readings_bulk_uploaded', 'bulk_sync_completed' => ['fas fa-layer-group', 'text-green-600'],
            'payment_uploaded', 'payment_recorded' => ['fas fa-money-bill-wave', 'text-green-600'],
            'route_downloaded', 'route_fetched', 'download' => ['fas fa-cloud-download-alt', 'text-blue-600'],
            'meter_location_updated' => ['fas fa-map-marker-alt', 'text-yellow-600'],
            'payment', 'payment_created', 'payment_collection' => ['fas fa-money-bill-wave', 'text-green-600'],
            'payment_updated' => ['fas fa-edit', 'text-yellow-600'],
            'payment_deleted' => ['fas fa-trash', 'text-red-600'],
            'payment_receipt_viewed' => ['fas fa-receipt', 'text-blue-600'],
            'system_task' => ['fas fa-cogs', 'text-gray-600'],
            'wallet_adjusted', 'wallet_adjustment_removed' => ['fas fa-wallet', 'text-teal-600'],
            default => ['fas fa-info-circle', 'text-gray-600'],
        };
    }

    public function getActionIconAttribute(): string
    {
        [$icon, $color] = $this->actionStyle();

        return $icon.' '.$color;
    }

    public function getActionColorAttribute(): string
    {
        return $this->actionStyle()[1];
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
