<?php

namespace App\Http\Controllers;

use App\Models\SchoolRoute;
use App\Models\Area;
use App\Models\City;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SchoolRouteController extends Controller
{
    private const ROUTE_AREA_MAX_DISTANCE_KM = 2;
    private const ROUTE_CITY_MAX_DISTANCE_KM = 30;

    public function index(Request $request)
    {
        try {
            $query = SchoolRoute::with(['vehicle'])
                ->withCount('stops');
            $citiesWithAreas = City::with(['areas' => function ($q) {
                $q->orderBy('name');
            }])->orderBy('name')->get();

            $availableCities = SchoolRoute::query()
                ->whereNotNull('destination')
                ->pluck('destination')
                ->map(function ($destination) {
                    $parts = array_filter(array_map('trim', explode(',', (string) $destination)));
                    return count($parts) > 1 ? $parts[count($parts) - 2] : null;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('destination', 'like', "%{$search}%");
                });
            }

            if ($request->filled('city') && $request->city !== 'all') {
                $query->where('destination', 'like', '%' . $request->city . '%');
            }

            $routes = $query->paginate(10)->withQueryString();

            return view('pickdrop.routes.index', compact('routes', 'citiesWithAreas', 'availableCities'));
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
            $citiesWithAreas = City::with(['areas' => function ($q) {
                $q->orderBy('name');
            }])->orderBy('name')->get();
            return view('pickdrop.routes.create', compact('vehicles', 'citiesWithAreas'));
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
            'city_id'                 => 'required|exists:cities,id',
            'area_id'                 => 'nullable|exists:areas,id',
            'area_ids'                => 'nullable|array|min:1',
            'area_ids.*'              => 'nullable|integer|exists:areas,id',
            'allow_multi_area'        => 'nullable|boolean',
            'name'                    => 'required|string|max:255',
            'code'                    => 'nullable|string|max:50|unique:routes,code',
            'shift'                   => 'required|string|in:morning,afternoon',
            'vehicle_id'              => 'nullable|exists:vehicles,id',
            'start_time'              => 'nullable|date_format:H:i',
            'end_time'                => 'nullable|date_format:H:i|after_or_equal:start_time',
            'destination'             => 'required|string|max:255',
            'destination_latitude'    => 'required|numeric|between:-90,90',
            'destination_longitude'   => 'required|numeric|between:-180,180',
            'description'             => 'nullable|string',
        ]);

        try {
            $allowMultiArea = (bool) ($data['allow_multi_area'] ?? false);
            $selectedAreaIds = collect($data['area_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($allowMultiArea && $selectedAreaIds->isEmpty()) {
                return redirect()->back()->withInput()->with('error', 'Please select at least one route area.');
            }
            if (!$allowMultiArea && empty($data['area_id'])) {
                return redirect()->back()->withInput()->with('error', 'Please select route area.');
            }
            if (!$allowMultiArea && !empty($data['area_id'])) {
                $selectedAreaIds = collect([(int) $data['area_id']]);
            }
            if ($allowMultiArea && empty($data['area_id']) && $selectedAreaIds->isNotEmpty()) {
                $data['area_id'] = $selectedAreaIds->first();
            }

            $areas = Area::whereIn('id', $selectedAreaIds)->get();
            if ($areas->isEmpty()) {
                return redirect()->back()->withInput()->with('error', 'Please select valid route area.');
            }
            if ($areas->contains(fn ($a) => (int) $a->city_id !== (int) $data['city_id'])) {
                return redirect()->back()->withInput()->with('error', 'One or more selected areas do not belong to selected city.');
            }

            $area = $areas->firstWhere('id', (int) $data['area_id']) ?? $areas->first();
            $city = City::findOrFail($data['city_id']);
            if ((int) $area->city_id !== (int) $data['city_id']) {
                return redirect()->back()->withInput()->with('error', 'Selected area does not belong to selected city.');
            }

            $validationError = $this->validateDestinationScope($city, $area, $data, $allowMultiArea);
            if ($validationError) {
                return redirect()->back()->withInput()->with('error', $validationError);
            }

            $data['area_ids'] = $selectedAreaIds->all();

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
            $citiesWithAreas = City::with(['areas' => function ($q) {
                $q->orderBy('name');
            }])->orderBy('name')->get();

            return view('pickdrop.routes.edit', compact('route', 'vehicles', 'citiesWithAreas'));
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
            'city_id'                 => 'required|exists:cities,id',
            'area_id'                 => 'nullable|exists:areas,id',
            'area_ids'                => 'nullable|array|min:1',
            'area_ids.*'              => 'nullable|integer|exists:areas,id',
            'allow_multi_area'        => 'nullable|boolean',
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:routes,code,' . $route->id,
            'shift'       => 'required|string|in:morning,afternoon',
            'vehicle_id'  => 'nullable|exists:vehicles,id',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after_or_equal:start_time',
            'destination' => 'required|string|max:255',
            'destination_latitude'    => 'required|numeric|between:-90,90',
            'destination_longitude'   => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'status'      => 'nullable|string',
        ]);

        try {
            $allowMultiArea = (bool) ($data['allow_multi_area'] ?? false);
            $selectedAreaIds = collect($data['area_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($allowMultiArea && $selectedAreaIds->isEmpty()) {
                return redirect()->back()->withInput()->with('error', 'Please select at least one route area.');
            }
            if (!$allowMultiArea && empty($data['area_id'])) {
                return redirect()->back()->withInput()->with('error', 'Please select route area.');
            }
            if (!$allowMultiArea && !empty($data['area_id'])) {
                $selectedAreaIds = collect([(int) $data['area_id']]);
            }
            if ($allowMultiArea && empty($data['area_id']) && $selectedAreaIds->isNotEmpty()) {
                $data['area_id'] = $selectedAreaIds->first();
            }

            $areas = Area::whereIn('id', $selectedAreaIds)->get();
            if ($areas->isEmpty()) {
                return redirect()->back()->withInput()->with('error', 'Please select valid route area.');
            }
            if ($areas->contains(fn ($a) => (int) $a->city_id !== (int) $data['city_id'])) {
                return redirect()->back()->withInput()->with('error', 'One or more selected areas do not belong to selected city.');
            }

            $area = $areas->firstWhere('id', (int) $data['area_id']) ?? $areas->first();
            $city = City::findOrFail($data['city_id']);
            if ((int) $area->city_id !== (int) $data['city_id']) {
                return redirect()->back()->withInput()->with('error', 'Selected area does not belong to selected city.');
            }

            $validationError = $this->validateDestinationScope($city, $area, $data, $allowMultiArea);
            if ($validationError) {
                return redirect()->back()->withInput()->with('error', $validationError);
            }

            $data['area_ids'] = $selectedAreaIds->all();

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

    private function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function validateDestinationScope(City $city, Area $area, array $data, bool $allowMultiArea): ?string
    {
        if ($allowMultiArea) {
            if (is_null($city->latitude) || is_null($city->longitude)) {
                return 'Selected city does not have coordinates, so multi-area destination cannot be verified.';
            }

            $cityDistanceKm = $this->haversineDistanceKm(
                (float) $city->latitude,
                (float) $city->longitude,
                (float) $data['destination_latitude'],
                (float) $data['destination_longitude']
            );
            if ($cityDistanceKm > self::ROUTE_CITY_MAX_DISTANCE_KM) {
                return 'For multi-area routes, final destination must remain within selected city range.';
            }

            return null;
        }

        if (is_null($area->latitude) || is_null($area->longitude)) {
            return 'Selected area does not have coordinates, so destination cannot be verified.';
        }

        $areaDistanceKm = $this->haversineDistanceKm(
            (float) $area->latitude,
            (float) $area->longitude,
            (float) $data['destination_latitude'],
            (float) $data['destination_longitude']
        );
        if ($areaDistanceKm > self::ROUTE_AREA_MAX_DISTANCE_KM) {
            return 'Final destination must lie within selected route area.';
        }

        return null;
    }
}

