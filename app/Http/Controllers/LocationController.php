<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = City::with('areas');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('areas', function ($sub) use ($search) {
                          $sub->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $cities = $query->paginate(10)->withQueryString();

            return view('pickdrop.locations.index', compact('cities'));
        } catch (\Throwable $e) {
            Log::error('Failed to load locations index', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load locations: ' . $e->getMessage());
        }
    }

    public function storeCity(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status'    => 'nullable|string',
        ]);

        try {
            City::create([
                'name'      => $data['name'],
                'latitude'  => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status'    => $data['status'] ?? 'Active',
            ]);

            return redirect()->route('locations.index')->with('success', 'City added successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to create city', [
                'data'  => $data,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create city: ' . $e->getMessage());
        }
    }

    public function importCities(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), 'r');

            if ($handle === false) {
                throw new \RuntimeException('Unable to open uploaded file.');
            }

            $header = fgetcsv($handle);
            $imported = 0;

            while (($row = fgetcsv($handle)) !== false) {
                // Expecting: name, latitude, longitude, status
                $name      = $row[0] ?? null;
                $latitude  = $row[1] ?? null;
                $longitude = $row[2] ?? null;
                $status    = $row[3] ?? 'Active';

                if (!$name) {
                    continue;
                }

                City::updateOrCreate(
                    ['name' => $name],
                    [
                        'latitude'  => $latitude !== '' ? $latitude : null,
                        'longitude' => $longitude !== '' ? $longitude : null,
                        'status'    => $status ?: 'Active',
                    ]
                );

                $imported++;
            }

            fclose($handle);

            return redirect()
                ->route('locations.index')
                ->with('success', "Imported {$imported} cities successfully.");
        } catch (\Throwable $e) {
            Log::error('Failed to import cities', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to import cities: ' . $e->getMessage());
        }
    }

    public function updateCity(Request $request, City $city)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status'    => 'nullable|string',
        ]);

        try {
            $city->update([
                'name'      => $data['name'],
                'latitude'  => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status'    => $data['status'] ?? 'Active',
            ]);

            return redirect()->route('locations.index')->with('success', 'City updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to update city', [
                'city_id' => $city->id,
                'data'    => $data,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update city: ' . $e->getMessage());
        }
    }

    public function destroyCity(City $city)
    {
        try {
            $city->delete();

            return redirect()->route('locations.index')->with('success', 'City deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to delete city', [
                'city_id' => $city->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->route('locations.index')->with('error', 'Failed to delete city: ' . $e->getMessage());
        }
    }

    public function storeArea(Request $request)
    {
        $data = $request->validate([
            'city_id'   => 'required|exists:cities,id',
            'name'      => 'required|string|max:255',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status'    => 'nullable|string',
        ]);

        try {
            Area::create([
                'city_id'   => $data['city_id'],
                'name'      => $data['name'],
                'latitude'  => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status'    => $data['status'] ?? 'Active',
            ]);

            return redirect()->route('locations.index')->with('success', 'Area added successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to create area', [
                'data'  => $data,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create area: ' . $e->getMessage());
        }
    }

    public function updateArea(Request $request, Area $area)
    {
        $data = $request->validate([
            'city_id'   => 'required|exists:cities,id',
            'name'      => 'required|string|max:255',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status'    => 'nullable|string',
        ]);

        try {
            $area->update([
                'city_id'   => $data['city_id'],
                'name'      => $data['name'],
                'latitude'  => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status'    => $data['status'] ?? 'Active',
            ]);

            return redirect()->route('locations.index')->with('success', 'Area updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to update area', [
                'area_id' => $area->id,
                'data'    => $data,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update area: ' . $e->getMessage());
        }
    }

    public function destroyArea(Area $area)
    {
        try {
            $area->delete();

            return redirect()->route('locations.index')->with('success', 'Area deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to delete area', [
                'area_id' => $area->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->route('locations.index')->with('error', 'Failed to delete area: ' . $e->getMessage());
        }
    }
}

