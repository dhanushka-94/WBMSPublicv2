<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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

        // Register global currency helper
        if (!function_exists('lkr')) {
            function lkr($amount, $decimals = 2) {
                return 'Rs. ' . number_format($amount, $decimals);
            }
        }

        // Register Sri Lankan Rupees helper function
        if (!function_exists('rupees')) {
            function rupees($amount, $decimals = 2) {
                return 'Rs. ' . number_format($amount, $decimals);
            }
        }
    }
}
