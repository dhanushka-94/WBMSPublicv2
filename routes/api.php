<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\MeterReadingApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/login', [AuthApiController::class, 'login']);
    
    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
            'server' => 'Water Billing Management System API'
        ]);
    });
    
    // App info
    Route::get('/app-info', function () {
        return response()->json([
            'app_name' => 'WBMS Mobile',
            'version' => '1.0.0',
            'api_version' => 'v1',
            'features' => [
                'offline_mode' => true,
                'photo_capture' => true,
                'gps_tracking' => true,
                'receipt_printing' => true,
                'auto_sync' => true,
            ],
            'contact' => [
                'support_email' => 'support@waterbilling.com',
                'website' => 'https://waterbilling.com',
            ]
        ]);
    });
});

// Protected routes requiring authentication
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Authentication management
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/refresh', [AuthApiController::class, 'refresh']);
    Route::get('/check-token', [AuthApiController::class, 'checkToken']);
    
    // User profile
    Route::get('/profile', [AuthApiController::class, 'profile']);
    Route::put('/profile', [AuthApiController::class, 'updateProfile']);
    
    // Meter reading routes
    Route::prefix('meter-reading')->group(function () {
        // Daily route and customer management
        Route::get('/route/today', [MeterReadingApiController::class, 'getTodaysRoute']);
        Route::get('/customers/search', [MeterReadingApiController::class, 'searchCustomers']);
        Route::get('/customers/{customerId}', [MeterReadingApiController::class, 'getCustomerDetails']);
        Route::get('/customers/{customerId}/history', [MeterReadingApiController::class, 'getMeterHistory']);
        
        // Reading submission
        Route::post('/submit', [MeterReadingApiController::class, 'submitReading']);
        Route::post('/bulk-sync', [MeterReadingApiController::class, 'bulkSyncReadings']);
        
        // Reading management
        Route::get('/readings/recent', function () {
            $user = auth()->user();
            $readings = \App\Models\MeterReading::where('reader_id', $user->id)
                ->with(['customer', 'waterMeter'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function($reading) {
                    return [
                        'id' => $reading->id,
                        'customer_name' => $reading->customer->full_name,
                        'connection_number' => $reading->customer->connection_number,
                        'meter_number' => $reading->waterMeter->meter_number,
                        'reading' => $reading->current_reading,
                        'consumption' => $reading->consumption,
                        'date' => $reading->reading_date,
                        'status' => 'completed',
                        'submitted_via' => $reading->submitted_via,
                    ];
                });
                
            return response()->json([
                'success' => true,
                'data' => $readings
            ]);
        });
        
        // Statistics for mobile dashboard
        Route::get('/stats', function () {
            $user = auth()->user();
            $today = now()->toDateString();
            $thisMonth = now()->format('Y-m');
            
            $stats = [
                'today' => [
                    'readings_completed' => \App\Models\MeterReading::where('reader_id', $user->id)
                        ->whereDate('created_at', $today)->count(),
                    'customers_visited' => \App\Models\MeterReading::where('reader_id', $user->id)
                        ->whereDate('created_at', $today)->distinct('customer_id')->count(),
                ],
                'this_month' => [
                    'total_readings' => \App\Models\MeterReading::where('reader_id', $user->id)
                        ->where('created_at', 'like', $thisMonth . '%')->count(),
                    'total_consumption' => \App\Models\MeterReading::where('reader_id', $user->id)
                        ->where('created_at', 'like', $thisMonth . '%')->sum('consumption'),
                ],
                'performance' => [
                    'average_readings_per_day' => \App\Models\MeterReading::where('reader_id', $user->id)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count() / 30,
                    'accuracy_rate' => 98.5, // Placeholder - calculate based on readings without issues
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        });
    });
    
    // Sync management
    Route::prefix('sync')->group(function () {
        // Get pending sync data
        Route::get('/pending', function () {
            $user = auth()->user();
            
            // Get unsync readings (if any tracking is needed)
            $pendingCount = 0; // Placeholder - implement if offline tracking needed
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pending_uploads' => $pendingCount,
                    'last_sync' => now()->toISOString(),
                    'sync_status' => 'up_to_date'
                ]
            ]);
        });
        
        // Force sync
        Route::post('/force', function () {
            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'data' => [
                    'synced_at' => now()->toISOString(),
                    'items_synced' => 0
                ]
            ]);
        });
    });
    
    // Utility routes
    Route::prefix('utils')->group(function () {
        // Get areas and routes for filtering
        Route::get('/areas', function () {
            $areas = \App\Models\Customer::select('area')
                ->distinct()
                ->whereNotNull('area')
                ->where('area', '!=', '')
                ->orderBy('area')
                ->pluck('area');
                
            return response()->json([
                'success' => true,
                'data' => $areas
            ]);
        });
        
        Route::get('/routes', function (Request $request) {
            $query = \App\Models\Customer::select('route')
                ->distinct()
                ->whereNotNull('route')
                ->where('route', '!=', '');
                
            if ($request->has('area')) {
                $query->where('area', $request->area);
            }
            
            $routes = $query->orderBy('route')->pluck('route');
                
            return response()->json([
                'success' => true,
                'data' => $routes
            ]);
        });
        
        // System information
        Route::get('/system-info', function () {
            return response()->json([
                'success' => true,
                'data' => [
                    'server_time' => now()->toISOString(),
                    'timezone' => config('app.timezone'),
                    'app_version' => '1.0.0',
                    'api_version' => 'v1',
                    'maintenance_mode' => app()->isDownForMaintenance(),
                ]
            ]);
        });
    });
});

// Fallback for unsupported routes
Route::fallback(function(){
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'authentication' => '/api/v1/login',
            'health_check' => '/api/v1/health',
            'app_info' => '/api/v1/app-info',
            'documentation' => '/api/v1/docs', // Future
        ]
    ], 404);
}); 