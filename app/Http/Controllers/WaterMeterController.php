<?php

namespace App\Http\Controllers;

use App\Models\WaterMeter;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class WaterMeterController extends Controller
{
    /**
     * Display a listing of the water meters.
     */
    public function index(Request $request): View
    {
        $query = WaterMeter::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('meter_number', 'like', "%{$search}%")
                  ->orWhere('meter_brand', 'like', "%{$search}%")
                  ->orWhere('meter_model', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('account_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by meter type
        if ($request->filled('meter_type')) {
            $query->where('meter_type', $request->input('meter_type'));
        }

        // Filter by maintenance due
        if ($request->filled('maintenance_due')) {
            $query->dueForMaintenance();
        }

        $meters = $query->with(['customer', 'meterReadings' => function ($q) {
            $q->latest('reading_date')->limit(1);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        // Statistics
        $totalMeters = WaterMeter::count();
        $activeMeters = WaterMeter::active()->count();
        $inactiveMeters = WaterMeter::where('status', 'inactive')->count();
        $faultyMeters = WaterMeter::where('status', 'faulty')->count();
        $maintenanceDue = WaterMeter::dueForMaintenance()->count();

        return view('meters.index', compact(
            'meters', 
            'totalMeters', 
            'activeMeters', 
            'inactiveMeters', 
            'faultyMeters', 
            'maintenanceDue'
        ));
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
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'meter_number' => 'required|string|unique:water_meters,meter_number',
            'meter_brand' => 'nullable|string|max:100',
            'meter_model' => 'nullable|string|max:100',
            'meter_size' => 'nullable|integer|min:1',
            'meter_type' => 'required|in:mechanical,digital,smart',
            'installation_date' => 'required|date',
            'initial_reading' => 'required|numeric|min:0',
            'current_reading' => 'required|numeric|min:0',
            'multiplier' => 'nullable|numeric|min:0.0001|max:10000',
            'location_notes' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'google_place_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,faulty,replaced'
        ]);

        // Set default values for optional fields
        $validated['multiplier'] = $validated['multiplier'] ?? 1.0000;
        
        // Set next maintenance date (6 months from installation) if not provided
        if (!isset($validated['next_maintenance_date'])) {
            $validated['next_maintenance_date'] = Carbon::parse($validated['installation_date'])->addMonths(6);
        }

        $meter = WaterMeter::create($validated);

        return redirect()->route('meters.show', $meter)
            ->with('success', 'Water meter created successfully.');
    }

    /**
     * Display the specified water meter.
     */
    public function show(WaterMeter $meter): View
    {
        $meter->load([
            'customer',
            'meterReadings' => function ($query) {
                $query->latest('reading_date')->limit(10);
            },
            'bills' => function ($query) {
                $query->latest('bill_date')->limit(5);
            }
        ]);

        $latestReading = $meter->getLatestReading();
        $monthlyConsumption = $meter->getMonthlyConsumption();
        $averageConsumption = $meter->getAverageMonthlyConsumption();
        $totalConsumption = $meter->getTotalConsumption();
        $readingHistory = $meter->getReadingHistory(12);

        return view('meters.show', compact(
            'meter',
            'latestReading',
            'monthlyConsumption',
            'averageConsumption',
            'totalConsumption',
            'readingHistory'
        ));
    }

    /**
     * Show the form for editing the specified water meter.
     */
    public function edit(WaterMeter $meter): View
    {
        $customers = Customer::active()
            ->orderBy('first_name')
            ->get();

        return view('meters.edit', compact('meter', 'customers'));
    }

    /**
     * Update the specified water meter in storage.
     */
    public function update(Request $request, WaterMeter $meter): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'meter_number' => [
                'required',
                'string',
                Rule::unique('water_meters', 'meter_number')->ignore($meter->id)
            ],
            'meter_brand' => 'nullable|string|max:100',
            'meter_model' => 'nullable|string|max:100',
            'meter_size' => 'nullable|integer|min:1',
            'meter_type' => 'required|in:mechanical,digital,smart',
            'installation_date' => 'required|date',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'initial_reading' => 'required|numeric|min:0',
            'current_reading' => 'required|numeric|min:0',
            'multiplier' => 'nullable|numeric|min:0.0001|max:10000',
            'location_notes' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'google_place_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,faulty,replaced'
        ]);

        // Set default values for optional fields
        $validated['multiplier'] = $validated['multiplier'] ?? 1.0000;

        $meter->update($validated);

        return redirect()->route('meters.show', $meter)
            ->with('success', 'Water meter updated successfully.');
    }

    /**
     * Remove the specified water meter from storage.
     */
    public function destroy(WaterMeter $meter): RedirectResponse
    {
        // Check if meter has readings or bills
        if ($meter->meterReadings()->exists()) {
            return back()->with('error', 'Cannot delete meter with existing readings.');
        }

        if ($meter->bills()->exists()) {
            return back()->with('error', 'Cannot delete meter with existing bills.');
        }

        $meter->delete();

        return redirect()->route('meters.index')
            ->with('success', 'Water meter deleted successfully.');
    }

    /**
     * Record maintenance for the specified meter.
     */
    public function recordMaintenance(Request $request, WaterMeter $meter): RedirectResponse
    {
        $validated = $request->validate([
            'maintenance_date' => 'required|date',
            'maintenance_notes' => 'nullable|string',
            'next_maintenance_months' => 'required|integer|min:1|max:24'
        ]);

        $meter->update([
            'last_maintenance_date' => $validated['maintenance_date'],
            'next_maintenance_date' => Carbon::parse($validated['maintenance_date'])
                ->addMonths($validated['next_maintenance_months']),
            'notes' => $meter->notes ? 
                $meter->notes . "\n\nMaintenance on " . $validated['maintenance_date'] . ": " . ($validated['maintenance_notes'] ?? 'Routine maintenance') :
                "Maintenance on " . $validated['maintenance_date'] . ": " . ($validated['maintenance_notes'] ?? 'Routine maintenance')
        ]);

        return back()->with('success', 'Maintenance recorded successfully.');
    }

    /**
     * Generate meter number
     */
    public function generateMeterNumber(): string
    {
        $year = date('y');
        $lastMeter = WaterMeter::where('meter_number', 'like', "WM{$year}%")
            ->latest('meter_number')
            ->first();

        if ($lastMeter) {
            $lastNumber = intval(substr($lastMeter->meter_number, -6));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "WM{$year}{$newNumber}";
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
}
