<?php

namespace App\Http\Controllers;

use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleCategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = VehicleCategory::query();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('vehicle_name', 'like', "%{$search}%");
            }

            if ($request->filled('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            $categories = $query->paginate(10)->withQueryString();

            return view('pickdrop.vehicle-categories.index', compact('categories'));
        } catch (\Throwable $e) {
            Log::error('Failed to load vehicle categories index', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load vehicle categories: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_name'       => 'required|string|max:255',
            'passenger_capacity' => 'required|integer|min:1',
            'status'             => 'nullable|in:1,0',
        ]);

        $status = $request->has('status') ? $request->status : 1;

        try {
            VehicleCategory::create([
                'vehicle_name'       => $request->vehicle_name,
                'passenger_capacity' => $request->passenger_capacity,
                'status'             => $status,
            ]);

            return redirect()->route('vehicle-categories.index')->with('success', 'Vehicle category added successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to create vehicle category', [
                'vehicle_name' => $request->vehicle_name,
                'error'        => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create vehicle category: ' . $e->getMessage());
        }
    }

    public function update(Request $request, VehicleCategory $vehicle_category)
    {
        $request->validate([
            'vehicle_name'       => 'required|string|max:255',
            'passenger_capacity' => 'required|integer|min:1',
            'status'             => 'nullable|in:1,0',
        ]);

        $status = $request->has('status') ? $request->status : 1;

        try {
            $vehicle_category->update([
                'vehicle_name'       => $request->vehicle_name,
                'passenger_capacity' => $request->passenger_capacity,
                'status'             => $status,
            ]);

            return redirect()->route('vehicle-categories.index')->with('success', 'Vehicle category updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to update vehicle category', [
                'vehicle_category_id' => $vehicle_category->id,
                'error'               => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update vehicle category: ' . $e->getMessage());
        }
    }

    public function destroy(VehicleCategory $vehicle_category)
    {
        try {
            $vehicle_category->delete();
            return redirect()->route('vehicle-categories.index')->with('success', 'Vehicle category deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to delete vehicle category', [
                'vehicle_category_id' => $vehicle_category->id,
                'error'               => $e->getMessage(),
            ]);

            return redirect()->route('vehicle-categories.index')->with('error', 'Failed to delete vehicle category: ' . $e->getMessage());
        }
    }
}
