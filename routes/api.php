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
        
        // QR Code routes
        Route::post('/qr-code/generate', [MeterReadingApiController::class, 'generateQrCode'])->name('api.meter.qr-code.generate');
        Route::post('/qr-code/scan', [MeterReadingApiController::class, 'scanQrCode'])->name('api.meter.qr-code.scan');
        Route::get('/qr-code/download/{meter_id}', [MeterReadingApiController::class, 'downloadQrCode'])->name('api.meter.qr-code.download');
        Route::post('/qr-code/batch-generate', [MeterReadingApiController::class, 'batchGenerateQrCodes'])->name('api.meter.qr-code.batch-generate');
        
        // Reading management
        Route::get('/readings/recent', function () {
            try {
                $user = auth()->user();
                $readings = \App\Models\MeterReading::with(['waterMeter.customer'])
                    ->where('reader_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get()
                    ->map(function($reading) {
                        $customer = $reading->waterMeter->customer ?? null;
                        return [
                            'id' => $reading->id,
                            'customer_name' => $customer ? $customer->first_name . ' ' . $customer->last_name : 'N/A',
                            'connection_number' => $customer ? $customer->account_number : 'N/A',
                            'meter_number' => $reading->waterMeter->meter_number ?? 'N/A',
                            'reading' => $reading->current_reading,
                            'consumption' => $reading->consumption,
                            'date' => $reading->reading_date,
                            'status' => 'completed',
                            'submitted_via' => $reading->submitted_via ?? 'manual',
                        ];
                    });
                    
                return response()->json([
                    'success' => true,
                    'data' => $readings
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load recent readings',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
        
        // Statistics for mobile dashboard
        Route::get('/stats', function () {
            try {
                $user = auth()->user();
                $today = now()->toDateString();
                $thisMonth = now()->format('Y-m');
                
                // Get readings for today
                $todayReadings = \App\Models\MeterReading::where('reader_id', $user->id)
                    ->whereDate('created_at', $today)->count();
                
                // Get unique water meter IDs for today to count customers visited
                $todayCustomers = \App\Models\MeterReading::where('reader_id', $user->id)
                    ->whereDate('created_at', $today)
                    ->distinct('water_meter_id')
                    ->count();
                
                // Get monthly stats
                $monthlyReadings = \App\Models\MeterReading::where('reader_id', $user->id)
                    ->where('created_at', 'like', $thisMonth . '%')->count();
                
                $monthlyConsumption = \App\Models\MeterReading::where('reader_id', $user->id)
                    ->where('created_at', 'like', $thisMonth . '%')
                    ->sum('consumption') ?? 0;
                
                // Get 30-day average
                $thirtyDayCount = \App\Models\MeterReading::where('reader_id', $user->id)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();
                
                $stats = [
                    'today' => [
                        'readings_completed' => $todayReadings,
                        'customers_visited' => $todayCustomers,
                    ],
                    'this_month' => [
                        'total_readings' => $monthlyReadings,
                        'total_consumption' => number_format($monthlyConsumption, 2),
                    ],
                    'performance' => [
                        'average_readings_per_day' => number_format($thirtyDayCount / 30, 1),
                        'accuracy_rate' => 98.5, // Placeholder - calculate based on readings without issues
                    ]
                ];
                
                return response()->json([
                    'success' => true,
                    'data' => $stats
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load statistics',
                    'error' => $e->getMessage()
                ], 500);
            }
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
            // Area functionality not implemented yet
            // TODO: Add area column to customers table or implement area management
            $areas = collect(); // Empty collection for now
                
            return response()->json([
                'success' => true,
                'data' => $areas,
                'message' => 'Area functionality not implemented yet'
            ]);
        });
        
        Route::get('/routes', function (Request $request) {
            // Route functionality not implemented yet
            // TODO: Add route column to customers table or implement route management
            $routes = collect(); // Empty collection for now
                
            return response()->json([
                'success' => true,
                'data' => $routes,
                'message' => 'Route functionality not implemented yet'
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