<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FailedLoginListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        // Try to find user by email for logging
        $user = null;
        if (!empty($event->credentials['email'])) {
            $user = User::where('email', $event->credentials['email'])->first();
        }

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $event->credentials['email'] ?? 'Unknown',
            'user_role' => $user?->role ?? 'unknown',
            'action' => 'login_failed',
            'description' => "Failed login attempt for email: " . ($event->credentials['email'] ?? 'Unknown'),
            'module' => 'authentication',
            'method' => request()?->method(),
            'url' => request()?->fullUrl(),
            'route_name' => request()?->route()?->getName(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'session_id' => session()->getId(),
            'status' => 'failed',
            'error_message' => 'Invalid credentials',
            'properties' => [
                'attempted_email' => $event->credentials['email'] ?? null
            ]
        ]);
    }
}
