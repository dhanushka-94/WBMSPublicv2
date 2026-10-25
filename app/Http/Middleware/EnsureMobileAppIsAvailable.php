<?php

namespace App\Http\Middleware;

use App\Models\SystemConfiguration;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileAppIsAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (SystemConfiguration::mobileAppMaintenance()) {
            return response()->json([
                'success' => false,
                'message' => 'The meter reader app is in maintenance. Try again later.',
                'mobile_app_maintenance' => true,
            ], 503);
        }

        return $next($request);
    }
}
