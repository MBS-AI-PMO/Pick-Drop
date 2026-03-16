<?php

namespace App\Http\Controllers;

use App\Models\SchoolRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SchoolRouteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = SchoolRoute::with(['vehicle'])
                ->withCount('stops');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('destination', 'like', "%{$search}%");
                });
            }

            if ($request->filled('shift') && $request->shift !== 'all') {
                $query->where('shift', $request->shift);
            }

            $routes = $query->paginate(10)->withQueryString();

            return view('pickdrop.routes.index', compact('routes'));
        } catch (\Throwable $e) {
            Log::error('Failed to load routes index', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load routes: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $vehicles = Vehicle::select('id', 'name', 'license_plate')->get();
            return view('pickdrop.routes.create', compact('vehicles'));
        } catch (\Throwable $e) {
            Log::error('Failed to load route create page', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('routes.index')->with('error', 'Failed to open create route form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:routes,code',
            'shift'       => 'required|string|in:morning,afternoon',
            'vehicle_id'  => 'nullable|exists:vehicles,id',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after_or_equal:start_time',
            'destination' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            // Auto-generate a simple code if not provided
            if (empty($data['code'])) {
                $nextId = (SchoolRoute::max('id') ?? 0) + 1;
                $data['code'] = '#R-' . str_pad((string)$nextId, 3, '0', STR_PAD_LEFT);
            }

            SchoolRoute::create($data);

            return redirect()->route('routes.index')->with('success', 'Route created successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to create route', [
                'data'  => $data,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create route: ' . $e->getMessage());
        }
    }

    public function edit(SchoolRoute $route)
    {
        try {
            $route->load(['vehicle', 'stops']);
            $vehicles = Vehicle::select('id', 'name', 'license_plate')->get();

            return view('pickdrop.routes.edit', compact('route', 'vehicles'));
        } catch (\Throwable $e) {
            Log::error('Failed to load route edit page', [
                'route_id' => $route->id,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('routes.index')->with('error', 'Failed to open edit route form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, SchoolRoute $route)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:routes,code,' . $route->id,
            'shift'       => 'required|string|in:morning,afternoon',
            'vehicle_id'  => 'nullable|exists:vehicles,id',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after_or_equal:start_time',
            'destination' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|string',
        ]);

        try {
            if (empty($data['code'])) {
                $data['code'] = $route->code;
            }

            $route->update($data);

            return redirect()->route('routes.index')->with('success', 'Route updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to update route', [
                'route_id' => $route->id,
                'data'     => $data,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update route: ' . $e->getMessage());
        }
    }

    public function destroy(SchoolRoute $route)
    {
        try {
            $route->delete();

            return redirect()->route('routes.index')->with('success', 'Route deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to delete route', [
                'route_id' => $route->id,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('routes.index')->with('error', 'Failed to delete route: ' . $e->getMessage());
        }
    }
}

