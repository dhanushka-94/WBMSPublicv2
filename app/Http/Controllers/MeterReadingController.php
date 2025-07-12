<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\WaterMeter;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of meter readings with filtering and search.
     */
    public function index(Request $request): View
    {
        $query = MeterReading::with(['waterMeter.customer']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('waterMeter', function ($meterQuery) use ($search) {
                    $meterQuery->where('meter_number', 'like', "%{$search}%")
                              ->orWhereHas('customer', function ($customerQuery) use ($search) {
                                  $customerQuery->where('first_name', 'like', "%{$search}%")
                                              ->orWhere('last_name', 'like', "%{$search}%")
                                              ->orWhere('account_number', 'like', "%{$search}%");
                              });
                });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Reading type filter
        if ($request->filled('reading_type')) {
            $query->where('reading_type', $request->reading_type);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('reading_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('reading_date', '<=', $request->date_to);
        }

        // Meter filter
        if ($request->filled('meter_id')) {
            $query->where('water_meter_id', $request->meter_id);
        }

        // Reader filter
        if ($request->filled('reader_name')) {
            $query->where('reader_name', 'like', "%{$request->reader_name}%");
        }

        $readings = $query->latest('reading_date')->paginate(15);

        // Statistics
        $totalReadings = MeterReading::count();
        $pendingReadings = MeterReading::pending()->count();
        $verifiedReadings = MeterReading::verified()->count();
        $billedReadings = MeterReading::billed()->count();
        $todayReadings = MeterReading::whereDate('reading_date', Carbon::today())->count();
        $thisMonthReadings = MeterReading::whereMonth('reading_date', Carbon::now()->month)
            ->whereYear('reading_date', Carbon::now()->year)
            ->count();

        $waterMeters = WaterMeter::active()->with('customer')->orderBy('meter_number')->get();

        return view('readings.index', compact(
            'readings',
            'totalReadings',
            'pendingReadings',
            'verifiedReadings',
            'billedReadings',
            'todayReadings',
            'thisMonthReadings',
            'waterMeters'
        ));
    }

    /**
     * Show the form for creating a new meter reading.
     */
    public function create(): View
    {
        $waterMeters = WaterMeter::active()->with('customer')->orderBy('meter_number')->get();
        
        return view('readings.create', compact('waterMeters'));
    }

    /**
     * Store a newly created meter reading in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading_date' => 'required|date|before_or_equal:today',
            'current_reading' => 'required|numeric|min:0',
            'reading_type' => 'required|in:actual,estimated,customer_read',
            'reader_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Get the water meter
            $waterMeter = WaterMeter::findOrFail($request->water_meter_id);

            // Validate reading is not less than previous reading
            $lastReading = $waterMeter->meterReadings()
                ->latest('reading_date')
                ->first();

            if ($lastReading && $request->current_reading < $lastReading->current_reading) {
                return back()->withErrors([
                    'current_reading' => 'Current reading cannot be less than the previous reading (' . $lastReading->current_reading . ')'
                ])->withInput();
            }

            // Check for duplicate reading on same date
            $existingReading = MeterReading::where('water_meter_id', $request->water_meter_id)
                ->whereDate('reading_date', $request->reading_date)
                ->first();

            if ($existingReading) {
                return back()->withErrors([
                    'reading_date' => 'A reading already exists for this meter on this date.'
                ])->withInput();
            }

            // Create the reading
            $reading = MeterReading::create([
                'water_meter_id' => $request->water_meter_id,
                'reading_date' => $request->reading_date,
                'current_reading' => $request->current_reading,
                'reading_type' => $request->reading_type,
                'reader_name' => $request->reader_name,
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            DB::commit();

            return redirect()->route('readings.show', $reading)
                ->with('success', 'Meter reading recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating meter reading: ' . $e->getMessage());
            return back()->with('error', 'Failed to record meter reading. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified meter reading.
     */
    public function show(MeterReading $reading): View
    {
        $reading->load(['waterMeter.customer', 'bill']);
        
        return view('readings.show', compact('reading'));
    }

    /**
     * Show the form for editing the specified meter reading.
     */
    public function edit(MeterReading $reading): View
    {
        if ($reading->status === 'billed') {
            return redirect()->route('readings.show', $reading)
                ->with('error', 'Cannot edit billed readings.');
        }

        $waterMeters = WaterMeter::active()->with('customer')->orderBy('meter_number')->get();
        
        return view('readings.edit', compact('reading', 'waterMeters'));
    }

    /**
     * Update the specified meter reading in storage.
     */
    public function update(Request $request, MeterReading $reading): RedirectResponse
    {
        if ($reading->status === 'billed') {
            return redirect()->route('readings.show', $reading)
                ->with('error', 'Cannot update billed readings.');
        }

        $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading_date' => 'required|date|before_or_equal:today',
            'current_reading' => 'required|numeric|min:0',
            'reading_type' => 'required|in:actual,estimated,customer_read',
            'reader_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Get the water meter
            $waterMeter = WaterMeter::findOrFail($request->water_meter_id);

            // Validate reading is not less than previous reading (excluding current reading)
            $lastReading = $waterMeter->meterReadings()
                ->where('id', '!=', $reading->id)
                ->where('reading_date', '<', $request->reading_date)
                ->latest('reading_date')
                ->first();

            if ($lastReading && $request->current_reading < $lastReading->current_reading) {
                return back()->withErrors([
                    'current_reading' => 'Current reading cannot be less than the previous reading (' . $lastReading->current_reading . ')'
                ])->withInput();
            }

            // Check for duplicate reading on same date (excluding current reading)
            $existingReading = MeterReading::where('water_meter_id', $request->water_meter_id)
                ->where('id', '!=', $reading->id)
                ->whereDate('reading_date', $request->reading_date)
                ->first();

            if ($existingReading) {
                return back()->withErrors([
                    'reading_date' => 'A reading already exists for this meter on this date.'
                ])->withInput();
            }

            // Update the reading
            $reading->update([
                'water_meter_id' => $request->water_meter_id,
                'reading_date' => $request->reading_date,
                'current_reading' => $request->current_reading,
                'reading_type' => $request->reading_type,
                'reader_name' => $request->reader_name,
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('readings.show', $reading)
                ->with('success', 'Meter reading updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating meter reading: ' . $e->getMessage());
            return back()->with('error', 'Failed to update meter reading. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified meter reading from storage.
     */
    public function destroy(MeterReading $reading): RedirectResponse
    {
        if ($reading->status === 'billed') {
            return redirect()->route('readings.index')
                ->with('error', 'Cannot delete billed readings.');
        }

        try {
            $reading->delete();
            return redirect()->route('readings.index')
                ->with('success', 'Meter reading deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting meter reading: ' . $e->getMessage());
            return redirect()->route('readings.index')
                ->with('error', 'Failed to delete meter reading. Please try again.');
        }
    }

    /**
     * Verify a meter reading.
     */
    public function verify(MeterReading $reading): RedirectResponse
    {
        try {
            $reading->verify();
            return redirect()->route('readings.show', $reading)
                ->with('success', 'Meter reading verified successfully.');
        } catch (\Exception $e) {
            Log::error('Error verifying meter reading: ' . $e->getMessage());
            return redirect()->route('readings.show', $reading)
                ->with('error', 'Failed to verify meter reading. Please try again.');
        }
    }

    /**
     * Show bulk entry form for multiple meter readings.
     */
    public function bulkEntry(): View
    {
        $waterMeters = WaterMeter::active()
            ->with(['customer', 'meterReadings' => function ($query) {
                $query->latest('reading_date')->limit(1);
            }])
            ->orderBy('meter_number')
            ->get();

        return view('readings.bulk', compact('waterMeters'));
    }

    /**
     * Store multiple meter readings from bulk entry.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'readings' => 'required|array|min:1',
            'readings.*.water_meter_id' => 'required|exists:water_meters,id',
            'readings.*.reading_date' => 'required|date|before_or_equal:today',
            'readings.*.current_reading' => 'required|numeric|min:0',
            'readings.*.reading_type' => 'required|in:actual,estimated,customer_read',
            'readings.*.reader_name' => 'required|string|max:255',
            'readings.*.notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errors = [];

            foreach ($request->readings as $index => $readingData) {
                try {
                    // Get the water meter
                    $waterMeter = WaterMeter::findOrFail($readingData['water_meter_id']);

                    // Validate reading is not less than previous reading
                    $lastReading = $waterMeter->meterReadings()
                        ->latest('reading_date')
                        ->first();

                    if ($lastReading && $readingData['current_reading'] < $lastReading->current_reading) {
                        $errors[] = "Row " . ($index + 1) . ": Current reading cannot be less than previous reading for meter {$waterMeter->meter_number}";
                        continue;
                    }

                    // Check for duplicate reading on same date
                    $existingReading = MeterReading::where('water_meter_id', $readingData['water_meter_id'])
                        ->whereDate('reading_date', $readingData['reading_date'])
                        ->first();

                    if ($existingReading) {
                        $errors[] = "Row " . ($index + 1) . ": Reading already exists for meter {$waterMeter->meter_number} on this date";
                        continue;
                    }

                    // Create the reading
                    MeterReading::create([
                        'water_meter_id' => $readingData['water_meter_id'],
                        'reading_date' => $readingData['reading_date'],
                        'current_reading' => $readingData['current_reading'],
                        'reading_type' => $readingData['reading_type'],
                        'reader_name' => $readingData['reader_name'],
                        'notes' => $readingData['notes'] ?? null,
                        'status' => 'pending'
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully recorded {$successCount} meter readings.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', $errors);
            }

            return redirect()->route('readings.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk meter reading entry: ' . $e->getMessage());
            return back()->with('error', 'Failed to record meter readings. Please try again.')
                ->withInput();
        }
    }

    /**
     * Get meter readings data for AJAX requests.
     */
    public function getReadingsData(Request $request)
    {
        $query = MeterReading::with(['waterMeter.customer']);

        if ($request->filled('meter_id')) {
            $query->where('water_meter_id', $request->meter_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('reading_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('reading_date', '<=', $request->date_to);
        }

        $readings = $query->latest('reading_date')->take(10)->get();

        return response()->json($readings);
    }

    /**
     * Get meter details for AJAX requests.
     */
    public function getMeterDetails(Request $request)
    {
        $request->validate([
            'meter_id' => 'required|exists:water_meters,id'
        ]);

        $meter = WaterMeter::with(['customer', 'meterReadings' => function ($query) {
            $query->latest('reading_date')->limit(1);
        }])->findOrFail($request->meter_id);

        $lastReading = $meter->meterReadings->first();

        return response()->json([
            'success' => true,
            'meter' => [
                'id' => $meter->id,
                'meter_number' => $meter->meter_number,
                'meter_type' => $meter->meter_type,
                'location_notes' => $meter->location_notes,
                'customer_name' => $meter->customer ? $meter->customer->full_name : 'Unassigned Customer',
                'account_number' => $meter->customer ? $meter->customer->account_number : 'N/A',
                'previous_reading' => $lastReading ? $lastReading->current_reading : $meter->initial_reading,
                'last_reading_date' => $lastReading ? $lastReading->reading_date->format('Y-m-d') : null
            ]
        ]);
    }

    /**
     * Search meters for AJAX requests.
     */
    public function searchMeters(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 50);

        $query = WaterMeter::active()->with(['customer']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('meter_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('account_number', 'like', "%{$search}%");
                  });
            });
        }

        $meters = $query->orderBy('meter_number')
                       ->limit($limit)
                       ->get()
                       ->map(function ($meter) {
                           $lastReading = $meter->meterReadings()
                               ->latest('reading_date')
                               ->first();
                           
                           $customerName = $meter->customer ? $meter->customer->full_name : 'Unassigned Customer';
                           $accountNumber = $meter->customer ? $meter->customer->account_number : 'N/A';
                           
                           return [
                               'id' => $meter->id,
                               'meter_number' => $meter->meter_number,
                               'customer_name' => $customerName,
                               'account_number' => $accountNumber,
                               'display_text' => "📊 {$meter->meter_number} | 👤 {$customerName} | 🏠 {$accountNumber}",
                               'previous_reading' => $lastReading ? $lastReading->current_reading : $meter->initial_reading,
                               'last_reading_date' => $lastReading ? $lastReading->reading_date->format('Y-m-d') : null
                           ];
                       });

        return response()->json([
            'success' => true,
            'meters' => $meters,
            'total' => $meters->count()
        ]);
    }

    /**
     * Generate monthly reading schedule for agents.
     */
    public function monthlySchedule(): View
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get all active meters with their customers and last readings
        $meters = WaterMeter::active()
            ->with([
                'customer',
                'meterReadings' => function ($query) use ($currentMonth, $currentYear) {
                    $query->whereMonth('reading_date', $currentMonth)
                          ->whereYear('reading_date', $currentYear)
                          ->latest('reading_date');
                }
            ])
            ->orderBy('meter_number')
            ->get();

        // Separate meters into read and unread for current month
        $readMeters = $meters->filter(function ($meter) {
            return $meter->meterReadings->count() > 0;
        });

        $unreadMeters = $meters->filter(function ($meter) {
            return $meter->meterReadings->count() === 0;
        });

        return view('readings.schedule', compact('readMeters', 'unreadMeters', 'currentMonth', 'currentYear'));
    }

    /**
     * Handle edit route without parameter (fallback)
     */
    public function editFallback(): RedirectResponse
    {
        Log::warning('Attempted to access readings.edit route without parameter', [
            'url' => request()->fullUrl(),
            'referer' => request()->header('referer'),
            'user_agent' => request()->header('user-agent'),
            'ip' => request()->ip()
        ]);

        return redirect()->route('readings.index')
            ->with('error', 'Please select a specific meter reading to edit.');
    }
}
