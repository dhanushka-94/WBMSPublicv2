<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\NoIndex::class);

        // ActivityLogger goes first (outermost) so requests that fail early, such as a missing record (404)
        // or an expired form (419), are still recorded for signed-in users.
        $middleware->web(
            prepend: [\App\Http\Middleware\ActivityLogger::class],
            append: [\App\Http\Middleware\RestrictOfficeAccess::class],
        );

        $middleware->api(append: [
            \App\Http\Middleware\ActivityLogger::class,
        ]);
        
        // Register custom middleware aliases
        $middleware->alias([
            'system.check' => \App\Http\Middleware\CheckSystemStatus::class,
            'mobile.maintenance' => \App\Http\Middleware\EnsureMobileAppIsAvailable::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'signed.flexible' => \App\Http\Middleware\ValidateFlexibleSignedUrl::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
