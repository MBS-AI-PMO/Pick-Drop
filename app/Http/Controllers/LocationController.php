<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    private const AREA_CITY_MAX_DISTANCE_KM = 60;

    public function citiesIndex(Request $request)
    {
        return $this->index($request);
    }

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

    public function areasIndex(Request $request)
    {
        try {
            $query = Area::with('city');

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('city', function ($cityQuery) use ($search) {
                            $cityQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $areas = $query->orderByDesc('id')->paginate(15)->withQueryString();
            $cities = City::orderBy('name')->get(['id', 'name', 'latitude', 'longitude']);

            return view('pickdrop.locations.areas', compact('areas', 'cities'));
        } catch (\Throwable $e) {
            Log::error('Failed to load areas index', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to load areas: ' . $e->getMessage());
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

            return redirect()->route('locations.cities.index')->with('success', 'City added successfully!');
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

            return redirect()->route('locations.cities.index')->with('success', 'City updated successfully!');
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
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status'    => 'nullable|string',
        ]);

        try {
            $city = City::findOrFail($data['city_id']);
            $insideCheck = $this->validateAreaInsideCity($city, (float) $data['latitude'], (float) $data['longitude']);
            if (!$insideCheck['valid']) {
                return redirect()->back()->withInput()->with('error', $insideCheck['message']);
            }

            Area::create([
                'city_id'   => $data['city_id'],
                'name'      => $data['name'],
                'latitude'  => $data['latitude'],
                'longitude' => $data['longitude'],
                'status'    => $data['status'] ?? 'Active',
            ]);

            return redirect()->back()->with('success', 'Area added successfully!');
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
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status'    => 'nullable|string',
        ]);

        try {
            $city = City::findOrFail($data['city_id']);
            $insideCheck = $this->validateAreaInsideCity($city, (float) $data['latitude'], (float) $data['longitude']);
            if (!$insideCheck['valid']) {
                return redirect()->back()->withInput()->with('error', $insideCheck['message']);
            }

            $area->update([
                'city_id'   => $data['city_id'],
                'name'      => $data['name'],
                'latitude'  => $data['latitude'],
                'longitude' => $data['longitude'],
                'status'    => $data['status'] ?? 'Active',
            ]);

            return redirect()->back()->with('success', 'Area updated successfully!');
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

            return redirect()->back()->with('success', 'Area deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to delete area', [
                'area_id' => $area->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete area: ' . $e->getMessage());
        }
    }

    public function searchMapLocations(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'city_name' => 'nullable|string|max:100',
            'city_lat' => 'nullable|numeric|between:-90,90',
            'city_lng' => 'nullable|numeric|between:-180,180',
            'country_code' => 'nullable|string|size:2',
        ]);

        try {
            $rawQuery = trim($request->string('q')->toString());
            $cityName = trim($request->input('city_name', ''));
            $countryCode = strtolower((string) $request->input('country_code', ''));
            $cityLat = $request->filled('city_lat') ? (float) $request->input('city_lat') : null;
            $cityLng = $request->filled('city_lng') ? (float) $request->input('city_lng') : null;

            Log::info('Map search requested', [
                'q' => $rawQuery,
                'city_name' => $cityName,
                'country_code' => $countryCode,
                'city_lat' => $cityLat,
                'city_lng' => $cityLng,
            ]);

            $cacheKey = 'map_search:' . md5(json_encode([
                'q' => $rawQuery,
                'city_name' => $cityName,
                'country_code' => $countryCode,
            ]));
            $cachedResults = Cache::get($cacheKey);
            if (!is_null($cachedResults)) {
                return response()->json(['results' => $cachedResults], 200);
            }

            $queries = [$rawQuery];
            if ($cityName !== '') {
                $queries[] = "{$rawQuery}, {$cityName}";
            }

            $queries = array_values(array_unique(array_filter($queries)));
            $results = collect();

            foreach ($queries as $index => $query) {
                $params = [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'limit' => 10,
                    'dedupe' => 1,
                ];

                if ($countryCode !== '' && $index === 0) {
                    $params['countrycodes'] = $countryCode;
                }

                // Keep a soft geo-bias only for the first pass; strict bounds can hide valid local sectors.
                if (!is_null($cityLat) && !is_null($cityLng) && $index === 0) {
                    $delta = 0.35;
                    $left = $cityLng - $delta;
                    $right = $cityLng + $delta;
                    $top = $cityLat + $delta;
                    $bottom = $cityLat - $delta;
                    $params['viewbox'] = "{$left},{$top},{$right},{$bottom}";
                }

                $response = $this->mapHttpClient()->get('https://nominatim.openstreetmap.org/search', $params);

                if (!$response->successful()) {
                    Log::warning('Map search provider returned non-success status', [
                        'query' => $query,
                        'status' => $response->status(),
                        'params' => $params,
                        'body' => mb_substr((string) $response->body(), 0, 500),
                    ]);
                    // Nominatim rate-limit reached, avoid further retries in same request.
                    if ($response->status() === 429) {
                        break;
                    }
                    continue;
                }

                $batch = collect($response->json())
                    ->map(function ($item) {
                        return [
                            'name' => $item['display_name'] ?? 'Unknown place',
                            'lat' => isset($item['lat']) ? (float) $item['lat'] : null,
                            'lng' => isset($item['lon']) ? (float) $item['lon'] : null,
                        ];
                    })
                    ->filter(fn ($item) => !is_null($item['lat']) && !is_null($item['lng']));

                $results = $results->concat($batch);
                if ($results->count() >= 6) {
                    break;
                }
            }

            $results = $results
                ->unique(fn ($item) => $item['lat'] . '|' . $item['lng'])
                ->take(6)
                ->values();

            Log::info('Map search completed', [
                'q' => $rawQuery,
                'results_count' => $results->count(),
            ]);

            Cache::put($cacheKey, $results->toArray(), now()->addHours(12));

            return response()->json(['results' => $results], 200);
        } catch (\Throwable $e) {
            Log::error('Map search failed with exception', [
                'error' => $e->getMessage(),
                'trace' => mb_substr($e->getTraceAsString(), 0, 1500),
                'input' => $request->only(['q', 'city_name', 'city_lat', 'city_lng', 'country_code']),
            ]);
            return response()->json(['results' => []], 200);
        }
    }

    private function validateAreaInsideCity(City $city, float $areaLat, float $areaLng): array
    {
        // Fallback to distance-only validation if reverse geocoding is unavailable.
        $resolvedCityName = $this->resolveCityNameFromCoordinates($areaLat, $areaLng);
        if ($resolvedCityName !== null) {
            $normalizedResolved = $this->normalizeLocationName($resolvedCityName);
            $normalizedCity = $this->normalizeLocationName((string) $city->name);
            if ($normalizedResolved !== '' && $normalizedCity !== '' && $normalizedResolved !== $normalizedCity) {
                return [
                    'valid' => false,
                    'message' => 'Area coordinates are outside the selected city (city name mismatch).',
                ];
            }
        }

        if (!is_null($city->latitude) && !is_null($city->longitude)) {
            $distanceKm = $this->haversineDistanceKm(
                (float) $city->latitude,
                (float) $city->longitude,
                $areaLat,
                $areaLng
            );

            if ($distanceKm > self::AREA_CITY_MAX_DISTANCE_KM) {
                return [
                    'valid' => false,
                    'message' => 'Area coordinates are too far from the selected city center.',
                ];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    private function resolveCityNameFromCoordinates(float $latitude, float $longitude): ?string
    {
        try {
            $response = $this->mapHttpClient()->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $address = $response->json('address', []);
            $candidates = [
                $address['city'] ?? null,
                $address['town'] ?? null,
                $address['municipality'] ?? null,
                $address['village'] ?? null,
                $address['county'] ?? null,
            ];

            foreach ($candidates as $candidate) {
                if (is_string($candidate) && trim($candidate) !== '') {
                    return $candidate;
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('Reverse geocode failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function normalizeLocationName(string $name): string
    {
        $normalized = mb_strtolower(trim($name));
        return preg_replace('/[^a-z0-9]/', '', $normalized) ?? '';
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

    private function mapHttpClient()
    {
        $client = Http::timeout(8)->withHeaders([
            'User-Agent' => 'PickDropAdmin/1.0',
            'Accept' => 'application/json',
        ]);

        // Laragon local env can have broken CA path (cURL error 77). Keep TLS verification enabled outside local.
        if (app()->environment('local')) {
            $client = $client->withOptions(['verify' => false]);
        }

        return $client;
    }
}

