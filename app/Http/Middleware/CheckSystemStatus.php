<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\SystemConfig;

class CheckSystemStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for super admin (user with ID 1) or admin role
        if (auth()->check() && (auth()->id() === 1 || auth()->user()->role === 'admin')) {
            return $next($request);
        }

        // Check if system is disabled
        if (!SystemConfig::isSystemEnabled()) {
            // Define allowed routes when system is disabled
            $allowedRoutes = [
                'dashboard',
                'customers.index',
                'customers.show',
                'bills.index',
                'bills.show',
                'bills.payments',
                'payments.*', // All payment routes
                'profile.*',
                'logout',
                'about',
                'system.status', // Allow viewing system status
            ];

            $currentRoute = $request->route()->getName();
            
            // Check if current route is allowed
            $isAllowed = false;
            foreach ($allowedRoutes as $allowedRoute) {
                if (str_contains($allowedRoute, '*')) {
                    // Wildcard matching
                    $pattern = str_replace('*', '', $allowedRoute);
                    if (str_starts_with($currentRoute, $pattern)) {
                        $isAllowed = true;
                        break;
                    }
                } else {
                    // Exact matching
                    if ($currentRoute === $allowedRoute) {
                        $isAllowed = true;
                        break;
                    }
                }
            }

            // If route is not allowed, redirect to system disabled page
            if (!$isAllowed) {
                return redirect()->route('system.disabled')
                    ->with('error', 'This feature is currently disabled. Please contact system administrator.');
            }
        }

        return $next($request);
    }
}