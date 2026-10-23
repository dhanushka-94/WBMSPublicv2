<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Continue with the request
        $response = $next($request);
        
        // Log the activity after the response
        $this->logActivity($request, $response, $startTime);
        
        return $response;
    }

    /**
     * Log the activity
     */
    protected function logActivity(Request $request, Response $response, float $startTime): void
    {
        try {
            $user = Auth::user() ?? Auth::guard('sanctum')->user();

            // Public routes, including the health check, have no signed-in user.
            // Login and failed login are recorded by the API controller itself.
            if (!$user) {
                return;
            }

            if ($request->is('api/*') && $request->attributes->get('activity_logged')) {
                return;
            }

            // Skip certain routes to avoid noise
            if ($this->shouldSkipLogging($request)) {
                return;
            }

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000); // Convert to milliseconds

            if ($request->is('api/*')) {
                $apiActivity = $this->describeApiRequest($request, $user);
                $action = $apiActivity['action'];
                $module = $apiActivity['module'];
                $description = $apiActivity['description'];
            } else {
                $action = $this->determineAction($request, $response);
                $module = $this->determineModule($request);
                $description = $this->generateDescription($request, $action, $module);
            }

            // Get subject information if available
            $subjectInfo = $this->extractSubjectInfo($request);

            ActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role ?? 'user',
                'action' => $action,
                'subject_type' => $subjectInfo['type'],
                'subject_id' => $subjectInfo['id'],
                'subject_name' => $subjectInfo['name'],
                'description' => $description,
                'module' => $module ?: 'system', // Ensure module is never null
                'method' => $request->method(),
                'url' => substr($request->fullUrl(), 0, 255),
                'route_name' => $request->route()?->getName(),
                'properties' => $this->getRequestProperties($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'status' => $response->getStatusCode() >= 400 ? 'failed' : 'success',
                'error_message' => $response->getStatusCode() >= 400 ? 'HTTP ' . $response->getStatusCode() : null,
                'duration_ms' => $duration
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Determine if logging should be skipped for this request
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        $skipRoutes = [
            'debugbar.*',
            'livewire.*',
            'telescope.*',
            'horizon.*',
            '_ignition.*'
        ];

        $skipPaths = [
            '/css/',
            '/js/',
            '/images/',
            '/fonts/',
            '/favicon.ico',
            '/robots.txt',
            '/_debugbar',
            '/livewire/',
            '/broadcasting/auth'
        ];

        $routeName = $request->route()?->getName();
        $path = $request->path();

        // Skip if route matches skip patterns
        if ($routeName) {
            foreach ($skipRoutes as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return true;
                }
            }
        }

        // Skip if path matches skip patterns
        foreach ($skipPaths as $skipPath) {
            if (str_contains($path, $skipPath)) {
                return true;
            }
        }

        // Skip AJAX requests that are just data fetching
        if ($request->ajax() && $request->method() === 'GET') {
            $ajaxRoutes = [
                '*.data',
                '*.ajax',
                '*.search',
                '*.autocomplete'
            ];
            
            foreach ($ajaxRoutes as $pattern) {
                if ($routeName && fnmatch($pattern, $routeName)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Name an API call so the office log shows who did what, and when.
     */
    protected function describeApiRequest(Request $request, $user): array
    {
        $path = preg_replace('#^api/v1/#', '', $request->path());
        $method = $request->method();
        $patterns = [
            ['POST', '#^logout$#', 'logout', 'signed out of the mobile app', 'mobile_app'],
            ['POST', '#^refresh$#', 'token_refresh', 'refreshed the mobile login token', 'mobile_app'],
            ['GET', '#^check-token$#', 'token_check', 'checked the mobile login token', 'mobile_app'],
            ['GET', '#^profile$#', 'view', 'viewed their profile from the mobile app', 'mobile_app'],
            ['PUT', '#^profile$#', 'update', 'updated their profile from the mobile app', 'mobile_app'],
            ['PATCH', '#^profile$#', 'update', 'updated their profile from the mobile app', 'mobile_app'],
            ['GET', '#^meter-reading/route/today$#', 'view', "viewed today's reading route", 'readings'],
            ['GET', '#^meter-reading/customers/search$#', 'search', 'searched customers from the mobile app', 'customers'],
            ['GET', '#^meter-reading/customers/\d+$#', 'view', 'viewed a customer from the mobile app', 'customers'],
            ['GET', '#^meter-reading/customers/\d+/history$#', 'view', 'viewed meter history from the mobile app', 'readings'],
            ['GET', '#^meter-reading/rates$#', 'view', 'viewed water rates from the mobile app', 'billing'],
            ['POST', '#^meter-reading/quote$#', 'quote', 'calculated a bill quote from the mobile app', 'billing'],
            ['POST', '#^meter-reading/submit$#', 'create', 'submitted a meter reading from the mobile app', 'readings'],
            ['POST', '#^meter-reading/bills$#', 'create', 'submitted a bill from the mobile app', 'billing'],
            ['POST', '#^meter-reading/bulk-sync$#', 'sync', 'synced meter readings from the mobile app', 'readings'],
            ['POST', '#^meter-reading/qr-code/generate$#', 'create', 'generated a meter QR code from the mobile app', 'meters'],
            ['POST', '#^meter-reading/qr-code/scan$#', 'scan', 'scanned a meter QR code', 'meters'],
            ['GET', '#^meter-reading/qr-code/download/\d+$#', 'download', 'downloaded a meter QR code', 'meters'],
            ['POST', '#^meter-reading/qr-code/batch-generate$#', 'create', 'generated meter QR codes from the mobile app', 'meters'],
            ['GET', '#^meter-reading/readings/recent$#', 'view', 'viewed recent meter readings', 'readings'],
            ['GET', '#^meter-reading/stats$#', 'view', 'viewed mobile reading statistics', 'readings'],
            ['GET', '#^payments/customer/\d+/bills$#', 'view', "viewed a customer's bills for collection", 'payments'],
            ['POST', '#^payments/record$#', 'payment', 'recorded a payment from the mobile app', 'payments'],
            ['GET', '#^payments/customer/\d+/history$#', 'view', 'viewed payment history from the mobile app', 'payments'],
            ['GET', '#^payments/customers/search$#', 'search', 'searched customers for payment collection', 'payments'],
            ['GET', '#^sync/pending$#', 'view', 'checked pending mobile sync items', 'mobile_app'],
            ['POST', '#^sync/force$#', 'sync', 'forced a mobile sync', 'mobile_app'],
            ['GET', '#^utils/areas$#', 'view', 'viewed areas from the mobile app', 'mobile_app'],
            ['GET', '#^utils/routes$#', 'view', 'viewed routes from the mobile app', 'mobile_app'],
            ['GET', '#^utils/system-info$#', 'view', 'viewed system information from the mobile app', 'mobile_app'],
        ];

        foreach ($patterns as [$verb, $regex, $action, $text, $module]) {
            if ($method === $verb && preg_match($regex, $path)) {
                return [
                    'action' => $action,
                    'module' => $module,
                    'description' => "{$user->name} {$text}",
                ];
            }
        }

        return [
            'action' => match ($method) {
                'POST' => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default => 'view',
            },
            'module' => 'mobile_app',
            'description' => "{$user->name} used {$method} /api/v1/{$path}",
        ];
    }

    /**
     * Determine the action based on request method and route
     */
    protected function determineAction(Request $request, Response $response): string
    {
        $method = $request->method();
        $routeName = $request->route()?->getName();
        $path = $request->path();

        // Special cases
        if (str_contains($path, 'export')) {
            return 'export';
        }
        if (str_contains($path, 'import')) {
            return 'import';
        }
        if (str_contains($path, 'backup')) {
            return 'backup';
        }
        if (str_contains($path, 'restore')) {
            return 'restore';
        }

        // Route-based actions
        if ($routeName) {
            if (str_contains($routeName, '.create') || str_contains($routeName, '.store')) {
                return 'create';
            }
            if (str_contains($routeName, '.show') || str_contains($routeName, '.index')) {
                return 'view';
            }
            if (str_contains($routeName, '.edit') || str_contains($routeName, '.update')) {
                return 'update';
            }
            if (str_contains($routeName, '.destroy') || str_contains($routeName, '.delete')) {
                return 'delete';
            }
            if (str_contains($routeName, 'verify')) {
                return 'verify';
            }
            if (str_contains($routeName, 'approve')) {
                return 'approve';
            }
            if (str_contains($routeName, 'reject')) {
                return 'reject';
            }
        }

        // HTTP method-based actions
        return match($method) {
            'GET' => 'view',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'action'
        };
    }

    /**
     * Determine the module/section based on the request
     */
    protected function determineModule(Request $request): string
    {
        $path = $request->path();
        $routeName = $request->route()?->getName();

        // Extract module from route name
        if ($routeName) {
            $parts = explode('.', $routeName);
            if (count($parts) > 0) {
                $firstPart = $parts[0];
                
                // Map route prefixes to modules
                $routeModuleMap = [
                    'activity-logs' => 'administration',
                    'customers' => 'customers',
                    'meters' => 'meters',
                    'readings' => 'readings',
                    'bills' => 'billing',
                    'users' => 'users',
                    'dashboard' => 'dashboard',
                    'reports' => 'reports',
                    'settings' => 'settings'
                ];
                
                if (isset($routeModuleMap[$firstPart])) {
                    return $routeModuleMap[$firstPart];
                }
                
                return $firstPart;
            }
        }

        // Extract module from path
        $pathParts = explode('/', trim($path, '/'));
        if (count($pathParts) > 0) {
            $module = $pathParts[0];
            
            // Map common paths to modules
            $moduleMap = [
                'customers' => 'customers',
                'meters' => 'meters',
                'water-meters' => 'meters',
                'meter-readings' => 'readings',
                'readings' => 'readings',
                'bills' => 'bills',
                'billing' => 'billing',
                'rates' => 'rates',
                'settings' => 'settings',
                'dashboard' => 'dashboard',
                'reports' => 'reports',
                'users' => 'users',
                'profile' => 'profile',
                'login' => 'authentication',
                'logout' => 'authentication',
                'register' => 'authentication',
                'password' => 'authentication',
                'activity-logs' => 'administration'
            ];

            return $moduleMap[$module] ?? $module;
        }

        return 'system';
    }

    /**
     * Generate a human-readable description
     */
    protected function generateDescription(Request $request, string $action, string $module): string
    {
        $user = Auth::user();
        $userName = $user->name;
        
        $subjectInfo = $this->extractSubjectInfo($request);
        $subject = $subjectInfo['name'] ? " '{$subjectInfo['name']}'" : '';

        $actionText = match($action) {
            'create' => 'created',
            'view' => 'viewed',
            'update' => 'updated',
            'delete' => 'deleted',
            'export' => 'exported',
            'import' => 'imported',
            'verify' => 'verified',
            'approve' => 'approved',
            'reject' => 'rejected',
            default => $action
        };

        $moduleText = match($module) {
            'customers' => 'customer',
            'meters' => 'water meter',
            'readings' => 'meter reading',
            'bills' => 'bill',
            'rates' => 'rate',
            'settings' => 'settings',
            'users' => 'user',
            'reports' => 'report',
            default => $module
        };

        if ($subject) {
            return "{$userName} {$actionText} {$moduleText}{$subject}";
        } else {
            return "{$userName} {$actionText} {$moduleText} data";
        }
    }

    /**
     * Extract subject information from request
     */
    protected function extractSubjectInfo(Request $request): array
    {
        $route = $request->route();
        if (!$route) {
            return ['type' => null, 'id' => null, 'name' => null];
        }

        $parameters = $route->parameters();
        
        // Common model parameter names
        $modelParams = [
            'customer' => 'App\Models\Customer',
            'meter' => 'App\Models\WaterMeter',
            'water_meter' => 'App\Models\WaterMeter',
            'reading' => 'App\Models\MeterReading',
            'meter_reading' => 'App\Models\MeterReading',
            'bill' => 'App\Models\Bill',
            'rate' => 'App\Models\Rate',
            'user' => 'App\Models\User'
        ];

        foreach ($modelParams as $param => $modelClass) {
            if (isset($parameters[$param])) {
                $model = $parameters[$param];
                if (is_object($model)) {
                    return [
                        'type' => get_class($model),
                        'id' => $model->id,
                        'name' => method_exists($model, 'getLogName') 
                            ? $model->getLogName() 
                            : ($model->name ?? $model->full_name ?? "ID: {$model->id}")
                    ];
                }
            }
        }

        return ['type' => null, 'id' => null, 'name' => null];
    }

    /**
     * Get additional request properties for logging
     */
    protected function getRequestProperties(Request $request): array
    {
        $properties = [];

        if ($request->is('api/*')) {
            $properties['source'] = 'api';
        }

        // Add relevant request data (excluding sensitive information)
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $data = $request->except([
                'password',
                'password_confirmation',
                'current_password',
                'new_password',
                'new_password_confirmation',
                'token',
                '_token',
                '_method'
            ]);

            if (!empty($data)) {
                $properties['request_data'] = $data;
            }
        }

        // Add query parameters
        if (!empty($request->query())) {
            $properties['query_params'] = $request->query();
        }

        // Add file uploads info
        $allFiles = $request->allFiles();
        if (!empty($allFiles)) {
            $files = [];
            foreach ($allFiles as $key => $file) {
                if (is_array($file)) {
                    foreach ($file as $f) {
                        $files[$key][] = [
                            'name' => $f->getClientOriginalName(),
                            'size' => $f->getSize(),
                            'type' => $f->getClientMimeType()
                        ];
                    }
                } else {
                    $files[$key] = [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType()
                    ];
                }
            }
            $properties['uploaded_files'] = $files;
        }

        return $properties;
    }
}
