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
            $customers = Customer::with(['waterMeters'])
                ->where('status', 'active')
                ->orderBy('account_number')
                ->get()
                ->map(function ($customer) {
                    $waterMeter = $customer->waterMeter;
                    $latestReading = $customer->latestReading;
                    
                    return [
                        'id' => $customer->id,
                        'connection_number' => $customer->account_number,
                        'name' => $customer->full_name,
                        'address' => $customer->full_address,
                        'phone' => $customer->phone,
                        'area' => null, // Area not implemented yet
                        'route' => null, // Route not implemented yet
                        'meter' => $waterMeter ? [
                            'id' => $waterMeter->id,
                            'meter_number' => $waterMeter->meter_number,
                            'type' => $waterMeter->meter_type,
                            'current_reading' => $waterMeter->current_reading,
                            'status' => $waterMeter->status,
                            'location_description' => $waterMeter->location_notes,
                            'gps_latitude' => $waterMeter->latitude,
                            'gps_longitude' => $waterMeter->longitude,
                        ] : null,
                        'last_reading' => $latestReading ? [
                            'reading' => $latestReading->current_reading,
                            'date' => $latestReading->reading_date,
                            'reader' => $latestReading->reader_name,
                        ] : null,
                        'status' => $customer->status,
                        'billing_status' => 'active', // Default status since column doesn't exist
                        'last_sync' => now()->toISOString(),
                    ];
                });

            $this->logMobileActivity('route_fetched', [
                'customer_count' => $customers->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'customers' => $customers,
                    'total_count' => $customers->count(),
                    'route_info' => [
                        'area' => null,
                        'route' => null,
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
                'meter_id' => 'nullable|exists:water_meters,id',
                'current_reading' => 'required|numeric|min:0',
                'reading_date' => 'required|date',
                'gps_latitude' => 'nullable|numeric|between:-90,90',
                'gps_longitude' => 'nullable|numeric|between:-180,180',
                'meter_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
                'notes' => 'nullable|string|max:500',
                'meter_condition' => 'nullable|in:good,damaged,broken,needs_repair',
                'reading_accuracy' => 'nullable|in:exact,estimated,calculated,actual',
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
            
            // Find meter - either from request or customer's active meter
            if ($request->meter_id) {
            $meter = WaterMeter::find($request->meter_id);

            // Verify the meter belongs to the customer
            if ($meter->customer_id !== $customer->id) {
                return response()->json([
                    'success' => false,
                        'message' => 'Meter does not belong to this customer',
                        'debug' => [
                            'customer_id' => $customer->id,
                            'meter_customer_id' => $meter->customer_id,
                            'meter_id' => $meter->id
                        ]
                    ], 400);
                }
            } else {
                // Find customer's active meter automatically
                $meter = $customer->waterMeter; // This uses the accessor method
                
                if (!$meter) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active water meter found for this customer'
                ], 400);
                }
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
                'water_meter_id' => $meter->id,
                'previous_reading' => $previousReading,
                'current_reading' => $request->current_reading,
                'consumption' => $consumption,
                'reading_date' => $request->reading_date,
                'reader_id' => $user->id,
                'reader_name' => $user->name,
                'notes' => $request->notes,
                'reading_type' => $this->mapReadingAccuracyToType($request->reading_accuracy ?? 'actual'),
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
                    'connection_number' => $customer->account_number,
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
            $customer = Customer::with([
                'waterMeters' => function($query) {
                    $query->where('status', 'active')->first();
                }, 
                'meterReadings' => function($query) {
                $query->orderBy('reading_date', 'desc')->limit(5);
                }
            ])->find($customerId);

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            // Get the active water meter (use the accessor method)
            $waterMeter = $customer->waterMeter;
            
            $data = [
                'customer' => [
                    'id' => $customer->id,
                    'connection_number' => $customer->account_number,
                    'name' => $customer->full_name,
                    'address' => $customer->full_address,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'area' => null, // Area not implemented yet
                    'route' => null, // Route not implemented yet
                    'status' => $customer->status,
                    'billing_status' => 'active', // Default status since column doesn't exist
                ],
                'meter' => $waterMeter ? [
                    'id' => $waterMeter->id,
                    'meter_number' => $waterMeter->meter_number,
                    'type' => $waterMeter->meter_type,
                    'current_reading' => $waterMeter->current_reading,
                    'status' => $waterMeter->status,
                    'installation_date' => $waterMeter->installation_date,
                    'location_description' => $waterMeter->location_notes,
                    'gps_latitude' => $waterMeter->latitude,
                    'gps_longitude' => $waterMeter->longitude,
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
            // Accept both 'q' and 'search' parameters for flexibility
            $query = $request->get('search', $request->get('q', ''));
            $limit = min($request->get('limit', 20), 50); // Max 50 results

            if (strlen($query) < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $customers = Customer::with('waterMeters')
                ->where(function($q) use ($query) {
                    $q->where('first_name', 'LIKE', "%{$query}%")
                      ->orWhere('last_name', 'LIKE', "%{$query}%")
                      ->orWhere('account_number', 'LIKE', "%{$query}%")
                      ->orWhere('phone', 'LIKE', "%{$query}%")
                      ->orWhere('address', 'LIKE', "%{$query}%");
                })
                ->where('status', 'active')
                ->limit($limit)
                ->get()
                ->map(function($customer) {
                    $waterMeter = $customer->waterMeter;
                    return [
                        'id' => $customer->id,
                        'connection_number' => $customer->account_number,
                        'name' => $customer->full_name,
                        'address' => $customer->full_address,
                        'phone' => $customer->phone,
                        'area' => null, // Area not implemented yet
                        'meter_number' => $waterMeter?->meter_number,
                        'current_reading' => $waterMeter?->current_reading,
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

            // Get readings through the water meter relationship
            $waterMeter = $customer->waterMeter;
            if (!$waterMeter) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active water meter found for this customer'
                ], 404);
            }

            $readings = MeterReading::where('water_meter_id', $waterMeter->id)
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
                    'connection_number' => $customer->account_number,
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
                'connection_number' => $customer->account_number,
                'address' => $customer->full_address,
                'phone' => $customer->phone,
            ],
            'meter' => [
                'meter_number' => $meter->meter_number,
                'type' => $meter->meter_type,
                'location' => $meter->location_notes,
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

    /**
     * Generate QR code for a specific meter
     */
    public function generateQrCode(Request $request)
    {
        try {
            $meterId = $request->input('meter_id');
            $size = $request->input('size', 200);
            $format = $request->input('format', 'png');
            
            if (!$meterId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meter ID is required'
                ], 400);
            }

            $meter = WaterMeter::find($meterId);
            if (!$meter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meter not found'
                ], 404);
            }

            // Generate QR code
            $qrCodeUrl = $meter->getQrCodeUrl($size, $format);
            $qrCodeBase64 = $meter->getQrCodeBase64($size, $format);
            
            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully',
                'data' => [
                    'meter_id' => $meter->id,
                    'meter_number' => $meter->meter_number,
                    'customer_name' => $meter->customer ? $meter->customer->full_name : 'Unassigned Customer',
                    'qr_code_url' => $qrCodeUrl,
                    'qr_code_base64' => $qrCodeBase64,
                    'qr_code_data' => $meter->getQrCodeData(),
                    'download_url' => route('api.meter.qr-code.download', ['meter_id' => $meter->id])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Scan QR code and get meter details
     */
    public function scanQrCode(Request $request)
    {
        try {
            $request->validate([
                'qr_data' => 'required|string'
            ]);

            $qrData = $request->input('qr_data');
            
            // Find meter by QR code data
            $meter = WaterMeter::findByQrCode($qrData);
            
            if (!$meter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code or meter not found'
                ], 404);
            }

            // Load meter with related data
            $meter->load(['customer', 'meterReadings' => function($query) {
                $query->latest('reading_date')->limit(1);
            }]);

            $latestReading = $meter->meterReadings->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Meter found successfully',
                'data' => [
                    'meter' => [
                        'id' => $meter->id,
                        'meter_number' => $meter->meter_number,
                        'meter_type' => $meter->meter_type,
                        'meter_brand' => $meter->meter_brand,
                        'meter_model' => $meter->meter_model,
                        'current_reading' => $meter->current_reading,
                        'status' => $meter->status,
                        'location_notes' => $meter->location_notes,
                        'latitude' => $meter->latitude,
                        'longitude' => $meter->longitude,
                        'installation_date' => $meter->installation_date,
                        'last_maintenance_date' => $meter->last_maintenance_date,
                        'next_maintenance_date' => $meter->next_maintenance_date,
                    ],
                    'customer' => $meter->customer ? [
                        'id' => $meter->customer->id,
                        'account_number' => $meter->customer->account_number,
                        'name' => $meter->customer->full_name,
                        'address' => $meter->customer->full_address,
                        'phone' => $meter->customer->phone,
                        'email' => $meter->customer->email,
                        'status' => $meter->customer->status,
                    ] : null,
                    'last_reading' => $latestReading ? [
                        'id' => $latestReading->id,
                        'reading' => $latestReading->current_reading,
                        'previous_reading' => $latestReading->previous_reading,
                        'consumption' => $latestReading->consumption,
                        'reading_date' => $latestReading->reading_date,
                        'reader_name' => $latestReading->reader_name,
                        'reader_id' => $latestReading->reader_id,
                        'reading_type' => $latestReading->reading_type,
                        'status' => $latestReading->status,
                    ] : null,
                    'scan_timestamp' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error scanning QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download QR code for printing
     */
    public function downloadQrCode(Request $request)
    {
        try {
            $meterId = $request->input('meter_id') ?? $request->route('meter_id');
            $size = $request->input('size', 300);
            $format = $request->input('format', 'png');
            
            if (!$meterId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meter ID is required'
                ], 400);
            }

            $meter = WaterMeter::with('customer')->find($meterId);
            if (!$meter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meter not found'
                ], 404);
            }

            // Generate QR code
            $qrCode = $meter->generateQrCode($size, $format);
            
            // Set appropriate headers
            $headers = [
                'Content-Type' => $format === 'svg' ? 'image/svg+xml' : 'image/' . $format,
                'Content-Disposition' => 'attachment; filename="meter_' . $meter->meter_number . '_qr_code.' . $format . '"'
            ];

            return response($qrCode, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get QR codes for multiple meters (batch generation)
     */
    public function batchGenerateQrCodes(Request $request)
    {
        try {
            $request->validate([
                'meter_ids' => 'required|array',
                'meter_ids.*' => 'exists:water_meters,id',
                'size' => 'nullable|integer|min:50|max:1000',
                'format' => 'nullable|in:png,jpg,svg'
            ]);

            $meterIds = $request->input('meter_ids');
            $size = $request->input('size', 200);
            $format = $request->input('format', 'png');

            $meters = WaterMeter::with('customer')->whereIn('id', $meterIds)->get();
            
            $qrCodes = [];
            foreach ($meters as $meter) {
                $qrCodes[] = [
                    'meter_id' => $meter->id,
                    'meter_number' => $meter->meter_number,
                    'customer_name' => $meter->customer ? $meter->customer->full_name : 'Unassigned Customer',
                    'qr_code_url' => $meter->getQrCodeUrl($size, $format),
                    'qr_code_base64' => $meter->getQrCodeBase64($size, $format),
                    'download_url' => route('api.meter.qr-code.download', ['meter_id' => $meter->id])
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'QR codes generated successfully',
                'data' => [
                    'total_meters' => count($qrCodes),
                    'qr_codes' => $qrCodes
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR codes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map reading accuracy to valid reading type
     */
    private function mapReadingAccuracyToType($accuracy)
    {
        $mapping = [
            'exact' => 'actual',
            'estimated' => 'estimated',
            'calculated' => 'estimated',
            'actual' => 'actual'
        ];
        
        return $mapping[$accuracy] ?? 'actual';
    }
}
