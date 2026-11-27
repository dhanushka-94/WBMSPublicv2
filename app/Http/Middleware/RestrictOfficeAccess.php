<?php

namespace App\Http\Middleware;

use App\Models\SystemConfiguration;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictOfficeAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role === 'super_admin') {
            return $next($request);
        }

        $name = $request->route()?->getName() ?? '';

        // Super Admin switch in Settings: Initial Readings (page and save) for everyone else, Admin included.
        if (($name === 'readings.initial' || str_starts_with($name, 'readings.initial.')) && !SystemConfiguration::initialReadingsOpen()) {
            abort(403, 'Initial Readings is turned off. Ask a Super Admin to turn it on.');
        }

        if ($this->superAdminOnly($name)) {
            abort(403, 'Only a Super Admin can open this page.');
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($name === '' || $this->allowed($user->role, $name)) {
            return $next($request);
        }

        abort(403, 'You do not have access to this page.');
    }

    private function allowed(string $role, string $name): bool
    {
        foreach (['profile.', 'about', 'changelog', 'logout', 'login', 'password.', 'verification.', 'system.disabled'] as $open) {
            if ($name === $open || str_starts_with($name, $open)) {
                return true;
            }
        }

        $prefixes = match ($role) {
            'manager' => ['dashboard', 'reports.'],
            'billing_officer', 'staff' => ['dashboard', 'customers.', 'guarantors.', 'meters.', 'readings.', 'bills.', 'payments.', 'sms.'],
            'cashier' => ['dashboard', 'bills.index', 'bills.show', 'bills.print', 'bills.pdf', 'bills.payment', 'payments.', 'reports.daily-collection', 'reports.office', 'reports.monthly-collection', 'reports.collection-efficiency', 'reports.age-analysis'],
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

    private function superAdminOnly(string $name): bool
    {
        if ($name === 'settings.rates' || str_starts_with($name, 'settings.rates.')) {
            return false;
        }

        // Admins may open the reader APK page; only Super Admin may upload.
        if ($name === 'settings.mobile-app' || str_starts_with($name, 'settings.mobile-app.')) {
            return false;
        }

        // Admins also manage Customer Divisions and Customer Types. They open them from the Settings page,
        // which only shows them those sections. Every other Settings screen stays Super Admin only.
        if ($name === 'settings.index'
            || str_starts_with($name, 'settings.divisions.')
            || str_starts_with($name, 'settings.customer-types.')) {
            return false;
        }

        foreach (['settings.', 'sms.settings', 'system.enable', 'system.disable', 'system.status', 'system.get-status', 'mobile-api.'] as $only) {
            if ($name === $only || str_starts_with($name, $only)) {
                return true;
            }
        }

        return false;
    }
}
