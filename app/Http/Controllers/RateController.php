<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rate;
use App\Models\CustomerType;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class RateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rates = Rate::orderBy('customer_type')
            ->orderBy('tier_from')
            ->paginate(20);

        $customerTypes = CustomerType::pluck('name', 'name');

        return view('settings.rates.index', compact('rates', 'customerTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customerTypes = CustomerType::all();
        return view('settings.rates.create', compact('customerTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_type' => 'required|string|max:100',
            'tier_from' => 'required|numeric|min:0',
            'tier_to' => 'nullable|numeric|gte:tier_from',
            'rate_per_unit' => 'required|numeric|min:0|max:999.9999',
            'fixed_charge' => 'nullable|numeric|min:0|max:99999.99',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        // Set default values
        $validated['is_active'] = $request->has('is_active');
        $validated['fixed_charge'] = $validated['fixed_charge'] ?? 0;

        // Validate tier ranges don't overlap for the same customer type and effective period
        $this->validateTierRanges($validated);

        Rate::create($validated);

        return redirect()->route('settings.rates.index')
            ->with('success', 'Rate created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rate $rate)
    {
        return view('settings.rates.show', compact('rate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rate $rate)
    {
        $customerTypes = CustomerType::all();
        return view('settings.rates.edit', compact('rate', 'customerTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rate $rate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_type' => 'required|string|max:100',
            'tier_from' => 'required|numeric|min:0',
            'tier_to' => 'nullable|numeric|gte:tier_from',
            'rate_per_unit' => 'required|numeric|min:0|max:999.9999',
            'fixed_charge' => 'nullable|numeric|min:0|max:99999.99',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        // Set default values
        $validated['is_active'] = $request->has('is_active');
        $validated['fixed_charge'] = $validated['fixed_charge'] ?? 0;

        // Validate tier ranges don't overlap (excluding current rate)
        $this->validateTierRanges($validated, $rate->id);

        $rate->update($validated);

        return redirect()->route('settings.rates.index')
            ->with('success', 'Rate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rate $rate)
    {
        // Check if rate is being used in any bills
        if ($rate->bills()->exists()) {
            return redirect()->route('settings.rates.index')
                ->with('error', 'Cannot delete rate that is being used in bills.');
        }

        $rate->delete();

        return redirect()->route('settings.rates.index')
            ->with('success', 'Rate deleted successfully.');
    }

    /**
     * Toggle the active status of a rate
     */
    public function toggleStatus(Rate $rate)
    {
        $rate->update(['is_active' => !$rate->is_active]);

        $status = $rate->is_active ? 'activated' : 'deactivated';
        return redirect()->route('settings.rates.index')
            ->with('success', "Rate {$status} successfully.");
    }

    /**
     * Duplicate a rate for easy creation of similar rates
     */
    public function duplicate(Rate $rate)
    {
        $customerTypes = CustomerType::all();
        $duplicatedRate = $rate->replicate();
        $duplicatedRate->name = $rate->name . ' (Copy)';
        $duplicatedRate->is_active = false;
        $duplicatedRate->effective_from = Carbon::now();
        $duplicatedRate->effective_to = null;

        return view('settings.rates.create', compact('customerTypes'))
            ->with('rate', $duplicatedRate);
    }

    /**
     * Validate that tier ranges don't overlap for the same customer type
     */
    private function validateTierRanges(array $data, $excludeId = null)
    {
        $query = Rate::where('customer_type', $data['customer_type'])
            ->where('is_active', true);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Check for effective date overlaps
        $query->where(function ($q) use ($data) {
            $q->where('effective_from', '<=', $data['effective_to'] ?? '9999-12-31')
              ->where(function ($q2) use ($data) {
                  $q2->whereNull('effective_to')
                     ->orWhere('effective_to', '>=', $data['effective_from']);
              });
        });

        $existingRates = $query->get();

        foreach ($existingRates as $existingRate) {
            $existingFrom = $existingRate->tier_from;
            $existingTo = $existingRate->tier_to;
            $newFrom = $data['tier_from'];
            $newTo = $data['tier_to'];

            // Check for tier range overlaps
            $overlaps = false;

            if ($existingTo === null && $newTo === null) {
                // Both are unlimited tiers
                $overlaps = true;
            } elseif ($existingTo === null) {
                // Existing is unlimited, check if new tier starts before existing
                $overlaps = $newTo >= $existingFrom;
            } elseif ($newTo === null) {
                // New is unlimited, check if it starts before existing tier ends
                $overlaps = $newFrom <= $existingTo;
            } else {
                // Both have defined ranges
                $overlaps = !($newTo < $existingFrom || $newFrom > $existingTo);
            }

            if ($overlaps) {
                $tierRange = $existingTo ? "{$existingFrom}-{$existingTo}" : "{$existingFrom}+";
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []), 
                    ['tier_from' => ["Tier range overlaps with existing rate: {$tierRange}"]]
                );
            }
        }
    }
}
