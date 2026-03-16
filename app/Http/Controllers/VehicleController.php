<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Vehicle::with(['category', 'driver']);
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('license_plate', 'like', "%{$search}%")
                      ->orWhere('route_id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $vehicles = $query->paginate(10)->withQueryString();

            // Vehicle types with capacity
            $types = VehicleCategory::select('id', 'vehicle_name', 'passenger_capacity')->get();

            // Drivers
            $drivers = User::where('role', 'driver')->get();

            return view('pickdrop.vehicles.index', compact('vehicles', 'types', 'drivers'));
        } catch (\Throwable $e) {
            Log::error('Failed to load vehicles index', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load vehicles: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'license_plate'     => 'required|string|max:255',
            'vehicle_type_id'    => 'nullable|exists:vehicle_categories,id',
            'driver_id'         => 'nullable|exists:users,id',
            'route_id'          => 'nullable|string',
            'status'            => 'nullable|string',
        ]);
        
        try {
            Vehicle::create([
                'name' => $request->string('name'),
                'license_plate' => $request->string('license_plate'),
                // Frontend sends vehicle_type_id, DB column is vehicle_category_id
                'vehicle_category_id' => $request->input('vehicle_type_id'),
                'driver_id' => $request->input('driver_id'),
                'route_id' => $request->input('route_id'),
                'status' => $request->input('status', 'Active'),
            ]);

            return redirect()->route('vehicles.index')->with('success', 'Vehicle added successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to create vehicle', [
                'name'  => $request->name,
                'plate' => $request->license_plate,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create vehicle: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'license_plate'     => 'required|string|max:255',
            'vehicle_type_id'    => 'nullable|exists:vehicle_categories,id',
            'driver_id'         => 'nullable|exists:users,id',
            'route_id'          => 'nullable|string',
            'status'            => 'nullable|string',
        ]);

        try {
            $vehicle->update([
                'name' => $request->string('name'),
                'license_plate' => $request->string('license_plate'),
                // Frontend sends vehicle_type_id, DB column is vehicle_category_id
                'vehicle_category_id' => $request->input('vehicle_type_id'),
                'driver_id' => $request->input('driver_id'),
                'route_id' => $request->input('route_id'),
                'status' => $request->input('status', 'Active'),
            ]);

            return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to update vehicle', [
                'vehicle_id' => $vehicle->id,
                'error'      => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update vehicle: ' . $e->getMessage());
        }
    }

    public function destroy(Vehicle $vehicle)
    {
        try {
            $vehicle->delete();
            return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to delete vehicle', [
                'vehicle_id' => $vehicle->id,
                'error'      => $e->getMessage(),
            ]);

            return redirect()->route('vehicles.index')->with('error', 'Failed to delete vehicle: ' . $e->getMessage());
        }
    }
}
