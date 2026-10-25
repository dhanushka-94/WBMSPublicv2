<?php

namespace App\Http\Controllers;

use App\Models\WaterMeter;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WaterMeterController extends Controller
{
    /**
     * Display a listing of the water meters.
     */
    public function index(Request $request): View
    {
        $query = WaterMeter::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('meter_number', 'like', "%{$search}%")
                  ->orWhere('meter_brand', 'like', "%{$search}%")
                  ->orWhere('meter_model', 'like', "%{$search}%")
                  ->orWhere('location_notes', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('account_number', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('meter_type')) {
            $query->where('meter_type', $request->input('meter_type'));
        }

        if ($request->filled('maintenance_due')) {
            $query->dueForMaintenance();
        }

        if ($request->filled('division_id') || $request->filled('customer_type_id')) {
            $query->whereHas('customer', function ($customer) use ($request) {
                if ($request->filled('division_id')) {
                    $customer->where('division_id', $request->input('division_id'));
                }
                if ($request->filled('customer_type_id')) {
                    $customer->where('customer_type_id', $request->input('customer_type_id'));
                }
            });
        }

        if ($request->input('assignment') === 'assigned') {
            $query->whereNotNull('customer_id');
        } elseif ($request->input('assignment') === 'unassigned') {
            $query->whereNull('customer_id');
        }

        if ($request->input('location') === 'mapped') {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        } elseif ($request->input('location') === 'unmapped') {
            $query->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            });
        }

        if ($from = $this->filterDate($request->input('installed_from'))) {
            $query->whereDate('installation_date', '>=', $from);
        }

        if ($to = $this->filterDate($request->input('installed_to'))) {
            $query->whereDate('installation_date', '<=', $to);
        }

        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->endOfMonth()->toDateString();

        if ($request->input('reading') === 'this_month') {
            $query->whereHas('meterReadings', function ($reading) use ($monthStart, $monthEnd) {
                $reading->whereBetween('reading_date', [$monthStart, $monthEnd]);
            });
        } elseif ($request->input('reading') === 'missed') {
            $query->whereDoesntHave('meterReadings', function ($reading) use ($monthStart, $monthEnd) {
                $reading->whereBetween('reading_date', [$monthStart, $monthEnd]);
            });
        } elseif ($request->input('reading') === 'zero') {
            $query->where('current_reading', '<=', 0);
        }

        $meters = $query->with(['customer', 'meterReadings' => function ($q) {
            $q->latest('reading_date')->limit(1);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate(25)
        ->withQueryString();

        // Statistics
        $totalMeters = WaterMeter::count();
        $activeMeters = WaterMeter::active()->count();
        $inactiveMeters = WaterMeter::where('status', 'inactive')->count();
        $faultyMeters = WaterMeter::where('status', 'faulty')->count();
        $maintenanceDue = WaterMeter::dueForMaintenance()->count();

        $divisions = Division::active()->orderBy('name')->get();
        $customerTypes = CustomerType::active()->orderBy('name')->get();

        return view('meters.index', compact(
            'meters',
            'totalMeters',
            'activeMeters',
            'inactiveMeters',
            'faultyMeters',
            'maintenanceDue',
            'divisions',
            'customerTypes'
        ));
    }

    private function filterDate(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    /**
     * Show the form for creating a new water meter.
     */
    public function create(): View
    {
        $customers = Customer::active()
            ->orderBy('first_name')
            ->get();

        return view('meters.create', compact('customers'));
    }

    /**
     * Store a newly created water meter in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Debug: Log the incoming request data
        \Log::info('Water Meter Creation Request:', [
            'all_data' => $request->all(),
            'customer_id' => $request->input('customer_id'),
            'customer_id_type' => gettype($request->input('customer_id')),
            'has_customer_id' => $request->has('customer_id'),
            'filled_customer_id' => $request->filled('customer_id')
        ]);

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'meter_number' => [
                'required',
                'string',
                'regex:/^[0-9]{1,20}$/',
                'unique:water_meters,meter_number'
            ],
            'meter_brand' => 'nullable|string|max:100',
            'meter_model' => 'nullable|string|max:100',
            'meter_size' => 'nullable|integer|min:1',
            'meter_type' => 'required|in:mechanical,digital,smart',
            'installation_date' => 'required|date',
            'initial_reading' => 'required|numeric|min:0|max:9999',
            'current_reading' => 'required|numeric|min:0|max:9999',
            'multiplier' => 'nullable|numeric|min:0.0001|max:10000',
            'location_notes' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'google_place_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,faulty,replaced'
        ], [
            'meter_number.required' => 'Meter number is required.',
            'meter_number.regex' => 'Meter number must be 1-20 digits only (no letters or special characters).',
            'meter_number.unique' => 'This meter number already exists. Please enter a different number.',
            'initial_reading.max' => 'Initial reading cannot exceed 9999.',
            'current_reading.max' => 'Current reading cannot exceed 9999.'
        ]);

        try {
            DB::beginTransaction();
            
            // Double-check meter number uniqueness within transaction
            if (WaterMeter::where('meter_number', $validated['meter_number'])->exists()) {
                DB::rollBack();
                return back()->withErrors([
                    'meter_number' => 'This meter number already exists. Please enter a different number.'
                ])->withInput();
            }
            
            // Set default values for optional fields
            $validated['multiplier'] = $validated['multiplier'] ?? 1.0000;
            
            // Set next maintenance date (6 months from installation) if not provided
            if (!isset($validated['next_maintenance_date'])) {
                $validated['next_maintenance_date'] = Carbon::parse($validated['installation_date'])->addMonths(6);
            }

            $meter = WaterMeter::create($validated);
            
            // Generate QR code for the meter using existing model method (SVG format)
            try {
                $meter->getQrCodePath(300, 'svg'); // Generate SVG QR code
            } catch (\Exception $e) {
                \Log::warning('QR code generation failed for meter ' . $meter->id . ': ' . $e->getMessage());
                // Continue without QR code if generation fails
            }
            
            DB::commit();
            
            return redirect()->route('meters.show', $meter)
                ->with('success', 'Water meter created successfully with QR code.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Check if it's a unique constraint violation
            if (str_contains($e->getMessage(), 'meter_number')) {
                return back()->withErrors([
                    'meter_number' => 'This meter number already exists. Please enter a different number.'
                ])->withInput();
            }
            
            // Log the error for debugging
            \Log::error('Water meter creation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'general' => 'Failed to create water meter. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Display the specified water meter.
     */
    public function show(WaterMeter $water_meter): View
    {
        $water_meter->load([
            'customer',
            'meterReadings' => function ($query) {
                $query->latest('reading_date')->limit(10);
            },
            'bills' => function ($query) {
                $query->latest('bill_date')->limit(5);
            }
        ]);

        $latestReading = $water_meter->getLatestReading();
        $monthlyConsumption = $water_meter->getMonthlyConsumption();
        $averageConsumption = $water_meter->getAverageMonthlyConsumption();
        $totalConsumption = $water_meter->getTotalConsumption();
        $readingHistory = $water_meter->meterReadings()
            ->latest('reading_date')
            ->paginate(25, ['*'], 'readings_page')
            ->withQueryString();
        $meterBills = $water_meter->bills()
            ->latest('bill_date')
            ->paginate(25, ['*'], 'bills_page')
            ->withQueryString();
        $consumptionByMonth = $water_meter->meterReadings()
            ->where('reading_date', '>=', now()->subMonths(11)->startOfMonth())
            ->orderBy('reading_date')
            ->get(['reading_date', 'consumption'])
            ->groupBy(fn ($reading) => $reading->reading_date->format('Y-m'));

        $chartLabels = [];
        $chartValues = [];
        $month = now()->subMonths(11)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $key = $month->format('Y-m');
            $chartLabels[] = $month->format('M Y');
            $chartValues[] = round((float) ($consumptionByMonth->get($key)?->sum('consumption') ?? 0), 2);
            $month->addMonth();
        }

        return view('meters.show', compact(
            'water_meter',
            'latestReading',
            'monthlyConsumption',
            'averageConsumption',
            'totalConsumption',
            'readingHistory',
            'chartLabels',
            'chartValues',
            'meterBills'
        ));
    }

    /**
     * Show the form for editing the specified water meter.
     */
    public function edit(WaterMeter $water_meter): View
    {
        $customers = Customer::active()
            ->orderBy('first_name')
            ->get();

        return view('meters.edit', compact('water_meter', 'customers'));
    }

    /**
     * Update the specified water meter in storage.
     */
    public function update(Request $request, WaterMeter $water_meter): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'meter_number' => [
                'required',
                'string',
                'regex:/^[0-9]{1,20}$/',
                Rule::unique('water_meters', 'meter_number')->ignore($water_meter->id)
            ],
            'meter_brand' => 'nullable|string|max:100',
            'meter_model' => 'nullable|string|max:100',
            'meter_size' => 'nullable|integer|min:1',
            'meter_type' => 'required|in:mechanical,digital,smart',
            'installation_date' => 'required|date',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'initial_reading' => 'required|numeric|min:0|max:9999',
            'current_reading' => 'required|numeric|min:0|max:9999',
            'multiplier' => 'nullable|numeric|min:0.0001|max:10000',
            'location_notes' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'google_place_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,faulty,replaced'
        ], [
            'meter_number.required' => 'Meter number is required.',
            'meter_number.regex' => 'Meter number must be 1-20 digits only (no letters or special characters).',
            'meter_number.unique' => 'This meter number already exists. Please enter a different number.',
            'initial_reading.max' => 'Initial reading cannot exceed 9999.',
            'current_reading.max' => 'Current reading cannot exceed 9999.'
        ]);

        try {
            DB::beginTransaction();
            
            // Double-check meter number uniqueness within transaction (excluding current meter)
            if (WaterMeter::where('meter_number', $validated['meter_number'])
                          ->where('id', '!=', $water_meter->id)
                          ->exists()) {
                DB::rollBack();
                return back()->withErrors([
                    'meter_number' => 'This meter number already exists. Please enter a different number.'
                ])->withInput();
            }
            
            // Set default values for optional fields
            $validated['multiplier'] = $validated['multiplier'] ?? 1.0000;

            $water_meter->update($validated);
            
            DB::commit();

            return redirect()->route('meters.show', $water_meter)
                ->with('success', 'Water meter updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Check if it's a unique constraint violation
            if (str_contains($e->getMessage(), 'meter_number')) {
                return back()->withErrors([
                    'meter_number' => 'This meter number already exists. Please enter a different number.'
                ])->withInput();
            }
            
            // Log the error for debugging
            \Log::error('Water meter update failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'general' => 'Failed to update water meter. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Remove the specified water meter from storage.
     */
    public function destroy(WaterMeter $water_meter): RedirectResponse
    {
        // Check if meter has readings or bills
        if (!auth()->user()?->isSuperAdmin()) {
            if ($water_meter->meterReadings()->exists()) {
                return back()->with('error', 'Cannot delete meter with existing readings.');
            }

            if ($water_meter->bills()->exists()) {
                return back()->with('error', 'Cannot delete meter with existing bills.');
            }
        }

        $water_meter->delete();

        return redirect()->route('meters.index')
            ->with('success', 'Water meter deleted successfully.');
    }

    /**
     * Record maintenance for the specified meter.
     */
    public function recordMaintenance(Request $request, WaterMeter $water_meter): RedirectResponse
    {
        $validated = $request->validate([
            'maintenance_date' => 'required|date',
            'maintenance_notes' => 'nullable|string',
            'next_maintenance_months' => 'required|integer|min:1|max:24'
        ]);

        $water_meter->update([
            'last_maintenance_date' => $validated['maintenance_date'],
            'next_maintenance_date' => Carbon::parse($validated['maintenance_date'])
                ->addMonths($validated['next_maintenance_months']),
            'notes' => $water_meter->notes ? 
                $water_meter->notes . "\n\nMaintenance on " . $validated['maintenance_date'] . ": " . ($validated['maintenance_notes'] ?? 'Routine maintenance') :
                "Maintenance on " . $validated['maintenance_date'] . ": " . ($validated['maintenance_notes'] ?? 'Routine maintenance')
        ]);

        return back()->with('success', 'Maintenance recorded successfully.');
    }

    /**
     * Check if meter number already exists (AJAX)
     */
    public function checkMeterNumber(Request $request)
    {
        $meterNumber = $request->input('meter_number');
        $excludeId = $request->input('exclude_id'); // For edit forms
        
        if (empty($meterNumber)) {
            return response()->json(['available' => true]);
        }
        
        // Check if meter number format is valid
        if (!preg_match('/^[0-9]{1,20}$/', $meterNumber)) {
            return response()->json([
                'available' => false,
                'message' => 'Meter number must be 1-20 digits only (no letters or special characters)'
            ]);
        }
        
        $query = WaterMeter::where('meter_number', $meterNumber);
        
        // Exclude current meter when editing
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This meter number already exists' : 'Meter number is available'
        ]);
    }



    /**
     * Display all meters on a map view
     */
    public function mapView(): View
    {
        $meters = WaterMeter::with('customer')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('meters.map', compact('meters'));
    }

    /**
     * Download QR code for a specific meter
     */
    public function downloadQRCode(WaterMeter $water_meter)
    {
        // Generate QR code if it doesn't exist
        $qrCodePath = $water_meter->getQrCodePath(300, 'svg');
        $filePath = storage_path('app/public/' . $qrCodePath);
        $filename = 'QR_Meter_' . $water_meter->meter_number . '.svg';

        return response()->download($filePath, $filename);
    }

    /**
     * Display QR code for a specific meter
     */
    public function showQRCode(WaterMeter $water_meter)
    {
        $qrCodeUrl = $water_meter->getQrCodeUrl(300, 'svg');
        $qrCodeData = $water_meter->getQrCodeData();
        
        return view('meters.qr-code', compact('water_meter', 'qrCodeUrl', 'qrCodeData'));
    }

    public function regenerateQrCode(WaterMeter $water_meter): RedirectResponse
    {
        $water_meter->regenerateQrCode();

        return back()->with('success', 'QR code regenerated for meter ' . $water_meter->meter_number . '.');
    }
}
