<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use App\Listeners\LoginListener;
use App\Listeners\LogoutListener;
use App\Listeners\FailedLoginListener;
use App\Models\ActivityLog;
use App\Models\SmsNotification;
use App\Models\SmsRateChange;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->runPendingMigrations();
        SmsRateChange::applyToConfig();
        ActivityLog::maintainArchive();
        SmsNotification::maintainArchive();

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);
        }

        // Register authentication event listeners for activity logging
        // Login, Logout and Failed are handled by LoginListener, LogoutListener and FailedLoginListener.
        // Laravel discovers classes in app/Listeners by itself; registering them here as well made
        // every sign-in, sign-out and failed login appear twice in the activity log.
        Event::listen(PasswordReset::class, function (PasswordReset $event) {
            try {
                ActivityLog::logActivity([
                    'user_id' => $event->user->id,
                    'user_name' => $event->user->name,
                    'user_email' => $event->user->email,
                    'user_role' => $event->user->role ?? 'user',
                    'action' => 'password_reset',
                    'description' => "Password was reset for '{$event->user->email}'",
                    'module' => 'authentication',
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        $this->logSystemTasks();

        // Register Sri Lankan Rupees currency formatting Blade directive
        Blade::directive('lkr', function ($expression) {
            return "<?php echo 'Rs. ' . number_format($expression, 2); ?>";
        });

        // Register alternative rupees Blade directive
        Blade::directive('rupees', function ($expression) {
            return "<?php echo 'Rs. ' . number_format($expression, 2); ?>";
        });

        // Meter readings / units: whole numbers without trailing .00
        Blade::directive('units', function ($expression) {
            return "<?php \$__units = (float) ($expression); echo fmod(abs(\$__units), 1.0) < 0.00001 ? number_format(\$__units, 0) : rtrim(rtrim(number_format(\$__units, 2, '.', ','), '0'), '.'); ?>";
        });

        // Helper functions are now available through Blade directives above
        // Global functions removed to prevent redeclaration issues
    }

    /**
     * Scheduled and manual console tasks (bill generation, late fees, SMS processing, backups...)
     * are not web requests, so record them here. They show up under the user "System".
     */
    private function logSystemTasks(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $startedAt = [];

        Event::listen(CommandStarting::class, function (CommandStarting $event) use (&$startedAt) {
            if ($event->command) {
                $startedAt[$event->command] = microtime(true);
            }
        });

        Event::listen(CommandFinished::class, function (CommandFinished $event) use (&$startedAt) {
            try {
                $name = $event->command;
                if (!$name) {
                    return;
                }

                $command = Artisan::all()[$name] ?? null;
                $isAppCommand = $command && str_starts_with(get_class($command), 'App\\Console\\Commands\\');
                if (!$isAppCommand && $name !== 'backup:run') {
                    return;
                }

                $ms = isset($startedAt[$name]) ? (int) round((microtime(true) - $startedAt[$name]) * 1000) : null;
                $ok = $event->exitCode === 0;
                $arguments = trim((string) $event->input);

                ActivityLog::logSystem(
                    'system_task',
                    $ok ? "Task '{$name}' finished successfully" : "Task '{$name}' failed (exit code {$event->exitCode})",
                    $ok ? 'success' : 'failed',
                    ['command' => $name, 'arguments' => $arguments, 'exit_code' => $event->exitCode],
                    $ok ? null : "Exit code {$event->exitCode}",
                    $ms
                );
            } catch (\Throwable $e) {
                // The table may not exist yet (for example during a fresh migrate); never break the command.
            }
        });
    }

    /**
     * Apply new migrations on the first web request after a git pull.
     * The live server has no terminal, so artisan cannot be run there.
     */
    private function runPendingMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $files = glob(database_path('migrations/*.php')) ?: [];
        sort($files);
        $latest = $files ? basename(end($files), '.php') : null;
        $stamp = storage_path('framework/migration-stamp.txt');

        if (!$latest || (is_file($stamp) && trim((string) file_get_contents($stamp)) === $latest)) {
            return;
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            file_put_contents($stamp, $latest);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
