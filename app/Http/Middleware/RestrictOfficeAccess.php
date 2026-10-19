<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictOfficeAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role === 'admin' || $user->id === 1) {
            return $next($request);
        }

        $name = $request->route()?->getName() ?? '';

        if ($name === '' || $this->allowed($user->role, $name)) {
            return $next($request);
        }

        abort(403, 'You do not have access to this page.');
    }

    private function allowed(string $role, string $name): bool
    {
        foreach (['profile.', 'about', 'changelog', 'logout', 'login', 'password.', 'verification.', 'system.disabled', 'system.status', 'system.get-status'] as $open) {
            if ($name === $open || str_starts_with($name, $open)) {
                return true;
            }
        }

        $prefixes = match ($role) {
            'manager' => ['dashboard', 'reports.'],
            'billing_officer', 'staff' => ['dashboard', 'customers.', 'guarantors.', 'meters.', 'readings.', 'bills.', 'sms.'],
            'cashier' => ['dashboard', 'bills.index', 'bills.show', 'bills.print', 'bills.payment', 'payments.', 'reports.daily-collection', 'reports.monthly-collection', 'reports.collection-efficiency', 'reports.age-analysis'],
            'meter_reader' => ['readings.', 'meters.index', 'meters.show', 'meters.map'],
            default => [],
        };

        foreach ($prefixes as $prefix) {
            if ($name === $prefix || str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
