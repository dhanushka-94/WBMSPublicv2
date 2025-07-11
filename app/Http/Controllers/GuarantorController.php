<?php

namespace App\Http\Controllers;

use App\Models\Guarantor;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class GuarantorController extends Controller
{
    /**
     * Display a listing of guarantors.
     */
    public function index(Request $request): View
    {
        $query = Guarantor::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('guarantor_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('nic', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $guarantors = $query->with(['customers'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('guarantors.index', compact('guarantors'));
    }

    /**
     * Show the form for creating a new guarantor.
     */
    public function create(): View
    {
        return view('guarantors.create');
    }

    /**
     * Store a newly created guarantor in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nic' => 'required|string|max:12|unique:guarantors,nic',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'relationship' => 'required|string|max:100',
        ]);

        $guarantor = Guarantor::create($validated);

        return redirect()->route('guarantors.show', $guarantor)
            ->with('success', 'Guarantor created successfully.');
    }

    /**
     * Display the specified guarantor.
     */
    public function show(Guarantor $guarantor): View
    {
        $guarantor->load(['customers']);
        return view('guarantors.show', compact('guarantor'));
    }

    /**
     * Show the form for editing the specified guarantor.
     */
    public function edit(Guarantor $guarantor): View
    {
        return view('guarantors.edit', compact('guarantor'));
    }

    /**
     * Update the specified guarantor in storage.
     */
    public function update(Request $request, Guarantor $guarantor): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nic' => [
                'required',
                'string',
                'max:12',
                Rule::unique('guarantors')->ignore($guarantor->id)
            ],
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'relationship' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $guarantor->update($validated);

        return redirect()->route('guarantors.show', $guarantor)
            ->with('success', 'Guarantor updated successfully.');
    }

    /**
     * Remove the specified guarantor from storage.
     */
    public function destroy(Guarantor $guarantor): RedirectResponse
    {
        // Check if guarantor has active customers
        if ($guarantor->customers()->exists()) {
            return back()->with('error', 'Cannot delete guarantor with associated customers.');
        }

        $guarantor->delete();

        return redirect()->route('guarantors.index')
            ->with('success', 'Guarantor deleted successfully.');
    }
}
