<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReaderRouteController extends Controller
{
    public function index(): View
    {
        $readers = User::query()
            ->where('role', 'meter_reader')
            ->with(['routeDivisions:id,name'])
            ->withCount(['routeDivisions', 'routeCustomers'])
            ->orderBy('name')
            ->paginate(25);

        return view('reader-routes.index', compact('readers'));
    }

    public function edit(User $user): View
    {
        $this->ensureReader($user);

        $user->load(['routeDivisions:id,name', 'routeCustomers.division:id,name']);

        $divisions = Division::query()
            ->where('is_active', true)
            ->withCount(['customers as active_customers_count' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('name')
            ->get();

        $selectedDivisionIds = $user->routeDivisions->pluck('id')->all();
        $selectedCustomers = $user->routeCustomers;
        $routeCount = $user->assignedRouteCustomers()->count();

        return view('reader-routes.edit', compact(
            'user',
            'divisions',
            'selectedDivisionIds',
            'selectedCustomers',
            'routeCount'
        ));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureReader($user);

        $validated = $request->validate([
            'division_ids' => 'nullable|array',
            'division_ids.*' => 'integer|exists:divisions,id',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'integer|exists:customers,id',
        ]);

        $user->routeDivisions()->sync($validated['division_ids'] ?? []);
        $user->routeCustomers()->sync($validated['customer_ids'] ?? []);

        $count = $user->assignedRouteCustomers()->count();

        return redirect()
            ->route('reader-routes.edit', $user)
            ->with('success', "Route saved. {$user->name} will receive {$count} active customers today.");
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));

        if (strlen($term) < 2) {
            return response()->json(['customers' => []]);
        }

        $customers = Customer::query()
            ->with('division:id,name')
            ->where('status', 'active')
            ->where(function ($query) use ($term) {
                $query->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('account_number', 'like', "%{$term}%")
                    ->orWhere('meter_number', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            })
            ->orderBy('account_number')
            ->limit(15)
            ->get()
            ->map(function (Customer $customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->full_name,
                    'account_number' => $customer->account_number,
                    'meter_number' => $customer->meter_number,
                    'division' => $customer->division?->name,
                ];
            });

        return response()->json(['customers' => $customers]);
    }

    private function ensureReader(User $user): void
    {
        abort_unless($user->role === 'meter_reader', 404);
    }
}
