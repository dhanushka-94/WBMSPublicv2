<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Listeners\LoginListener;
use App\Listeners\LogoutListener;
use App\Listeners\FailedLoginListener;

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

        // Register authentication event listeners for activity logging
        Event::listen(Login::class, LoginListener::class);
        Event::listen(Logout::class, LogoutListener::class);
        Event::listen(Failed::class, FailedLoginListener::class);

        // Register Sri Lankan Rupees currency formatting Blade directive
        Blade::directive('lkr', function ($expression) {
            return "<?php echo 'Rs. ' . number_format($expression, 2); ?>";
        });

        // Register alternative rupees Blade directive
        Blade::directive('rupees', function ($expression) {
            return "<?php echo 'Rs. ' . number_format($expression, 2); ?>";
        });

        // Helper functions are now available through Blade directives above
        // Global functions removed to prevent redeclaration issues
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
