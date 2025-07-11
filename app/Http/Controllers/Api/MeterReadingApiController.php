<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Bill;
use App\Models\ActivityLog;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MeterReadingApiController extends Controller
{
    use LogsActivity;

    /**
     * Get customers assigned to meter reader for today's route
     */
    public function getTodaysRoute(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get customers for meter reader's route
            $customers = Customer::with(['waterMeter', 'latestReading'])
                ->where('status', 'active')
                ->when($request->has('area'), function ($query) use ($request) {
                    return $query->where('area', $request->area);
                })
                ->when($request->has('route'), function ($query) use ($request) {
                    return $query->where('route', $request->route);
                })
                ->orderBy('route')
                ->orderBy('connection_number')
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'connection_number' => $customer->connection_number,
                        'name' => $customer->full_name,
                        'address' => $customer->full_address,
                        'phone' => $customer->phone,
                        'area' => $customer->area,
                        'route' => $customer->route,
                        'meter' => $customer->waterMeter ? [
                            'id' => $customer->waterMeter->id,
                            'meter_number' => $customer->waterMeter->meter_number,
                            'type' => $customer->waterMeter->type,
                            'current_reading' => $customer->waterMeter->current_reading,
                            'status' => $customer->waterMeter->status,
                            'location_description' => $customer->waterMeter->location_description,
                            'gps_latitude' => $customer->waterMeter->gps_latitude,
                            'gps_longitude' => $customer->waterMeter->gps_longitude,
                        ] : null,
                        'last_reading' => $customer->latestReading ? [
                            'reading' => $customer->latestReading->current_reading,
                            'date' => $customer->latestReading->reading_date,
                            'reader' => $customer->latestReading->reader_name,
                        ] : null,
                        'status' => $customer->status,
                        'billing_status' => $customer->billing_status,
                        'last_sync' => now()->toISOString(),
                    ];
                });

            $this->logMobileActivity('route_fetched', [
                'customer_count' => $customers->count(),
                'area' => $request->area,
                'route' => $request->route,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'customers' => $customers,
                    'total_count' => $customers->count(),
                    'route_info' => [
                        'area' => $request->area,
                        'route' => $request->route,
                        'date' => now()->toDateString(),
                        'reader' => $user->name,
                    ]
                ],
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch route data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit meter reading from mobile app
     */
    public function submitReading(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'meter_id' => 'required|exists:water_meters,id',
                'current_reading' => 'required|numeric|min:0',
                'reading_date' => 'required|date',
                'gps_latitude' => 'nullable|numeric|between:-90,90',
                'gps_longitude' => 'nullable|numeric|between:-180,180',
                'meter_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
                'notes' => 'nullable|string|max:500',
                'meter_condition' => 'nullable|in:good,damaged,broken,needs_repair',
                'reading_accuracy' => 'nullable|in:exact,estimated,calculated',
                'offline_timestamp' => 'nullable|date', // For offline readings
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $customer = Customer::find($request->customer_id);
            $meter = WaterMeter::find($request->meter_id);

            // Verify the meter belongs to the customer
            if ($meter->customer_id !== $customer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meter does not belong to this customer'
                ], 400);
            }

            // Check if reading is logical (not less than previous reading for cumulative meters)
            if ($meter->type === 'cumulative' && $request->current_reading < $meter->current_reading) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reading cannot be less than previous reading for cumulative meters',
                    'previous_reading' => $meter->current_reading,
                    'submitted_reading' => $request->current_reading
                ], 400);
            }

            DB::beginTransaction();

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('meter_photo')) {
                $photo = $request->file('meter_photo');
                $filename = 'meter_' . $meter->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('meter-photos', $filename, 'public');
            }

            // Calculate consumption
            $previousReading = $meter->current_reading;
            $consumption = max(0, $request->current_reading - $previousReading);

            // Create meter reading record
            $meterReading = MeterReading::create([
                'customer_id' => $customer->id,
                'water_meter_id' => $meter->id,
                'previous_reading' => $previousReading,
                'current_reading' => $request->current_reading,
                'consumption' => $consumption,
                'reading_date' => $request->reading_date,
                'reader_id' => $user->id,
                'reader_name' => $user->name,
                'notes' => $request->notes,
                'reading_type' => $request->reading_accuracy ?? 'exact',
                'meter_condition' => $request->meter_condition ?? 'good',
                'photo_path' => $photoPath,
                'gps_latitude' => $request->gps_latitude,
                'gps_longitude' => $request->gps_longitude,
                'submitted_via' => 'mobile_app',
                'offline_timestamp' => $request->offline_timestamp,
                'created_at' => $request->offline_timestamp ?? now(),
            ]);

            // Update meter current reading
            $meter->update([
                'current_reading' => $request->current_reading,
                'last_reading_date' => $request->reading_date,
                'gps_latitude' => $request->gps_latitude ?? $meter->gps_latitude,
                'gps_longitude' => $request->gps_longitude ?? $meter->gps_longitude,
            ]);

            // Log activity
            $this->logMobileActivity('meter_reading_submitted', [
                'customer' => $customer->full_name,
                'meter_number' => $meter->meter_number,
                'reading' => $request->current_reading,
                'consumption' => $consumption,
                'location' => $request->gps_latitude && $request->gps_longitude ? 
                    [$request->gps_latitude, $request->gps_longitude] : null,
            ]);

            DB::commit();

            // Prepare response data for mobile app
            $responseData = [
                'reading_id' => $meterReading->id,
                'customer' => [
                    'name' => $customer->full_name,
                    'connection_number' => $customer->connection_number,
                    'address' => $customer->full_address,
                ],
                'meter' => [
                    'meter_number' => $meter->meter_number,
                    'previous_reading' => $previousReading,
                    'current_reading' => $request->current_reading,
                    'consumption' => $consumption,
                ],
                'reading_details' => [
                    'date' => $request->reading_date,
                    'reader' => $user->name,
                    'condition' => $request->meter_condition ?? 'good',
                    'accuracy' => $request->reading_accuracy ?? 'exact',
                    'notes' => $request->notes,
                ],
                'receipt_data' => $this->generateReceiptData($customer, $meter, $meterReading),
                'sync_status' => 'completed',
                'timestamp' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Meter reading submitted successfully',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit meter reading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk sync multiple readings (for offline mode)
     */
    public function bulkSyncReadings(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'readings' => 'required|array|min:1',
                'readings.*.customer_id' => 'required|exists:customers,id',
                'readings.*.meter_id' => 'required|exists:water_meters,id',
                'readings.*.current_reading' => 'required|numeric|min:0',
                'readings.*.reading_date' => 'required|date',
                'readings.*.offline_timestamp' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $results = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($request->readings as $index => $readingData) {
                try {
                    // Create a new request for each reading
                    $subRequest = new Request($readingData);
                    $response = $this->submitReading($subRequest);
                    $responseData = json_decode($response->getContent(), true);

                    if ($responseData['success']) {
                        $successCount++;
                        $results[] = [
                            'index' => $index,
                            'status' => 'success',
                            'data' => $responseData['data']
                        ];
                    } else {
                        $failureCount++;
                        $results[] = [
                            'index' => $index,
                            'status' => 'failed',
                            'error' => $responseData['message']
                        ];
                    }
                } catch (\Exception $e) {
                    $failureCount++;
                    $results[] = [
                        'index' => $index,
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                }
            }

            $this->logMobileActivity('bulk_sync_completed', [
                'total_readings' => count($request->readings),
                'successful' => $successCount,
                'failed' => $failureCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bulk sync completed: {$successCount} successful, {$failureCount} failed",
                'data' => [
                    'results' => $results,
                    'summary' => [
                        'total' => count($request->readings),
                        'successful' => $successCount,
                        'failed' => $failureCount,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer details for mobile app
     */
    public function getCustomerDetails($customerId): JsonResponse
    {
        try {
            $customer = Customer::with(['waterMeter', 'meterReadings' => function($query) {
                $query->orderBy('reading_date', 'desc')->limit(5);
            }])->find($customerId);

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $data = [
                'customer' => [
                    'id' => $customer->id,
                    'connection_number' => $customer->connection_number,
                    'name' => $customer->full_name,
                    'address' => $customer->full_address,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'area' => $customer->area,
                    'route' => $customer->route,
                    'status' => $customer->status,
                    'billing_status' => $customer->billing_status,
                ],
                'meter' => $customer->waterMeter ? [
                    'id' => $customer->waterMeter->id,
                    'meter_number' => $customer->waterMeter->meter_number,
                    'type' => $customer->waterMeter->type,
                    'current_reading' => $customer->waterMeter->current_reading,
                    'status' => $customer->waterMeter->status,
                    'installation_date' => $customer->waterMeter->installation_date,
                    'location_description' => $customer->waterMeter->location_description,
                    'gps_latitude' => $customer->waterMeter->gps_latitude,
                    'gps_longitude' => $customer->waterMeter->gps_longitude,
                ] : null,
                'recent_readings' => $customer->meterReadings->map(function($reading) {
                    return [
                        'id' => $reading->id,
                        'reading' => $reading->current_reading,
                        'consumption' => $reading->consumption,
                        'date' => $reading->reading_date,
                        'reader' => $reading->reader_name,
                        'condition' => $reading->meter_condition,
                        'photo_available' => !empty($reading->photo_path),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers (for mobile app search functionality)
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $limit = min($request->get('limit', 20), 50); // Max 50 results

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters'
                ], 400);
            }

            $customers = Customer::with('waterMeter')
                ->where(function($q) use ($query) {
                    $q->where('first_name', 'LIKE', "%{$query}%")
                      ->orWhere('last_name', 'LIKE', "%{$query}%")
                      ->orWhere('connection_number', 'LIKE', "%{$query}%")
                      ->orWhere('phone', 'LIKE', "%{$query}%")
                      ->orWhere('address', 'LIKE', "%{$query}%");
                })
                ->where('status', 'active')
                ->limit($limit)
                ->get()
                ->map(function($customer) {
                    return [
                        'id' => $customer->id,
                        'connection_number' => $customer->connection_number,
                        'name' => $customer->full_name,
                        'address' => $customer->full_address,
                        'phone' => $customer->phone,
                        'area' => $customer->area,
                        'meter_number' => $customer->waterMeter?->meter_number,
                        'current_reading' => $customer->waterMeter?->current_reading,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'customers' => $customers,
                    'count' => $customers->count(),
                    'query' => $query,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get meter reading history for a customer
     */
    public function getMeterHistory($customerId): JsonResponse
    {
        try {
            $customer = Customer::find($customerId);
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $readings = MeterReading::where('customer_id', $customerId)
                ->orderBy('reading_date', 'desc')
                ->limit(12) // Last 12 readings
                ->get()
                ->map(function($reading) {
                    return [
                        'id' => $reading->id,
                        'reading_date' => $reading->reading_date,
                        'previous_reading' => $reading->previous_reading,
                        'current_reading' => $reading->current_reading,
                        'consumption' => $reading->consumption,
                        'reader_name' => $reading->reader_name,
                        'reading_type' => $reading->reading_type,
                        'meter_condition' => $reading->meter_condition,
                        'photo_available' => !empty($reading->photo_path),
                        'notes' => $reading->notes,
                        'submitted_via' => $reading->submitted_via,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'customer_name' => $customer->full_name,
                    'connection_number' => $customer->connection_number,
                    'readings' => $readings,
                    'total_readings' => $readings->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch meter history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate receipt data for mobile printing
     */
    private function generateReceiptData($customer, $meter, $reading): array
    {
        return [
            'receipt_number' => 'MR-' . str_pad($reading->id, 6, '0', STR_PAD_LEFT),
            'date' => $reading->reading_date,
            'time' => $reading->created_at->format('H:i:s'),
            'customer' => [
                'name' => $customer->full_name,
                'connection_number' => $customer->connection_number,
                'address' => $customer->full_address,
                'phone' => $customer->phone,
            ],
            'meter' => [
                'meter_number' => $meter->meter_number,
                'type' => $meter->type,
                'location' => $meter->location_description,
            ],
            'reading' => [
                'previous' => $reading->previous_reading,
                'current' => $reading->current_reading,
                'consumption' => $reading->consumption,
                'units' => 'cubic meters',
            ],
            'reader' => [
                'name' => $reading->reader_name,
                'signature_line' => '________________________',
            ],
            'footer' => [
                'company' => 'Water Billing Management System',
                'note' => 'Thank you for your cooperation',
                'website' => 'www.waterbilling.com',
            ]
        ];
    }

    /**
     * Log mobile app activities
     */
    private function logMobileActivity(string $action, array $data = []): void
    {
        try {
            ActivityLog::logActivity([
                'action' => $action,
                'description' => "Mobile app: " . ucfirst(str_replace('_', ' ', $action)),
                'module' => 'mobile_app',
                'properties' => $data,
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't break app functionality for logging issues
        }
    }
}
