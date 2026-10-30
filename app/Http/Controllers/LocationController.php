<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use Illuminate\Http\Request;
use App\Support\AppPagination;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    private const AREA_CITY_MAX_DISTANCE_KM = 60;
    private const MAP_SEARCH_CITY_RADIUS_DEGREES = 0.4;

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

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $cities = $query->paginate(AppPagination::PER_PAGE)->withQueryString();

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

            $areas = $query->orderByDesc('id')->paginate(AppPagination::PER_PAGE)->withQueryString();
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

        $latitude  = (isset($data['latitude']) && $data['latitude'] !== '') ? $data['latitude'] : null;
        $longitude = (isset($data['longitude']) && $data['longitude'] !== '') ? $data['longitude'] : null;

        try {
            City::create([
                'name'      => $data['name'],
                'latitude'  => $latitude,
                'longitude' => $longitude,
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
                ->route('locations.cities.index')
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

        $latitude  = (isset($data['latitude']) && $data['latitude'] !== '') ? $data['latitude'] : null;
        $longitude = (isset($data['longitude']) && $data['longitude'] !== '') ? $data['longitude'] : null;

        try {
            $city->update([
                'name'      => $data['name'],
                'latitude'  => $latitude,
                'longitude' => $longitude,
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

            return redirect()->route('locations.cities.index')->with('success', 'City deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to delete city', [
                'city_id' => $city->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->route('locations.cities.index')->with('error', 'Failed to delete city: ' . $e->getMessage());
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
            $hasCityCoords = !is_null($cityLat) && !is_null($cityLng);

            Log::info('Map search requested', [
                'q' => $rawQuery,
                'city_name' => $cityName,
                'country_code' => $countryCode,
                'city_lat' => $cityLat,
                'city_lng' => $cityLng,
            ]);

            $cacheKey = 'map_search:v2:' . md5(json_encode([
                'q' => mb_strtolower($rawQuery),
                'city_name' => $hasCityCoords ? '' : mb_strtolower($cityName),
                'country_code' => $countryCode,
                'lat' => $hasCityCoords ? round($cityLat, 3) : null,
                'lng' => $hasCityCoords ? round($cityLng, 3) : null,
            ]));
            $cachedResults = Cache::get($cacheKey);
            if (!empty($cachedResults)) {
                return response()->json(['results' => $cachedResults], 200);
            }

            // The selected city's coordinates are the reliable signal: city names are free text
            // (typos, aliases) and only add noise to the search. Use the name only without coordinates.
            $variants = $this->mapSearchQueryVariants($rawQuery);
            $queries = $variants;
            if (!$hasCityCoords && $cityName !== '') {
                $queries = array_map(fn ($variant) => "{$variant}, {$cityName}", $queries);
            }

            $candidates = collect();
            $requestsMade = 0;

            foreach ($queries as $query) {
                $params = [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'namedetails' => 1,
                    'accept-language' => 'en',
                    'limit' => 20,
                    'dedupe' => 1,
                ];

                if ($countryCode !== '') {
                    $params['countrycodes'] = $countryCode;
                }

                // Restrict to the selected city, otherwise same-named areas in other cities
                // (e.g. "Gulshan", "Satellite Town", "DHA Phase 5") crowd out the right one.
                if ($hasCityCoords) {
                    $delta = self::MAP_SEARCH_CITY_RADIUS_DEGREES;
                    $params['viewbox'] = implode(',', [
                        $cityLng - $delta,
                        $cityLat + $delta,
                        $cityLng + $delta,
                        $cityLat - $delta,
                    ]);
                    $params['bounded'] = 1;
                }

                // Nominatim's usage policy allows at most one request per second.
                if ($requestsMade > 0) {
                    usleep(1100000);
                }
                $requestsMade++;

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

                $candidates = $candidates->concat(
                    collect($response->json())->filter(fn ($item) => is_array($item) && isset($item['lat'], $item['lon']))
                );
            }

            $results = $this->rankMapSearchResults($candidates, $variants, $cityLat, $cityLng);

            Log::info('Map search completed', [
                'q' => $rawQuery,
                'candidates' => $candidates->count(),
                'results_count' => $results->count(),
            ]);

            // Never cache an empty answer: it may be a transient provider failure.
            if ($results->isNotEmpty()) {
                Cache::put($cacheKey, $results->toArray(), now()->addHours(12));
            }

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

    /**
     * The typed query, plus a hyphenated form for sector-style names ("F7" -> "F-7", "G10/4" -> "G-10/4"),
     * which OpenStreetMap stores with the hyphen, and "Cantt" -> "Cantonment".
     *
     * @return list<string>
     */
    private function mapSearchQueryVariants(string $query): array
    {
        $variants = [$query];

        $expanded = preg_replace('/\bcantt\b\.?/i', 'Cantonment', $query);
        if (is_string($expanded) && $expanded !== $query) {
            $variants[] = $expanded;
        }

        $hyphenated = preg_replace('/\b([A-Za-z])(\d+)/', '$1-$2', $query);
        if (is_string($hyphenated) && $hyphenated !== $query) {
            $variants[] = $hyphenated;
        }

        return $variants;
    }

    /**
     * Keep only results that are real areas matching what was typed, best match first.
     *
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $candidates
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function rankMapSearchResults($candidates, array $variants, ?float $cityLat, ?float $cityLng)
    {
        // Compare against every spelling we searched for ("F7" and "F-7", "Cantt" and "Cantonment").
        $needles = collect($variants)
            ->map(fn (string $variant) => [
                'needle' => $this->normalizeSearchText($variant),
                'tokens' => $this->searchTokens($variant),
            ])
            ->filter(fn (array $variant) => $variant['needle'] !== '')
            ->values();
        if ($needles->isEmpty()) {
            return collect();
        }

        // The search box is square, so its corners reach into neighbouring cities; keep a circle.
        if (!is_null($cityLat) && !is_null($cityLng)) {
            $candidates = $candidates->filter(
                fn (array $item) => $this->mapDistance($item, $cityLat, $cityLng) <= self::MAP_SEARCH_CITY_RADIUS_DEGREES
            );
        }

        $scored = $candidates
            ->map(function (array $item) use ($needles) {
                $score = 0;
                $nameLength = 0;

                foreach ($this->searchNameCandidates($item) as $name) {
                    $normalized = $this->normalizeSearchText($name);
                    if ($normalized === '') {
                        continue;
                    }

                    foreach ($needles as $variant) {
                        $needle = $variant['needle'];
                        $current = match (true) {
                            $normalized === $needle => 4,
                            str_starts_with($normalized, $needle) => 3,
                            str_contains($normalized, $needle) => 2,
                            $this->containsAllTokens($normalized, $variant['tokens']) => 1,
                            default => 0,
                        };

                        if ($current > $score) {
                            $score = $current;
                            $nameLength = mb_strlen($normalized);
                        }
                    }
                }

                return [
                    'item' => $item,
                    'score' => $score,
                    'area_like' => $this->isAreaLikeResult($item),
                    'name_length' => $nameLength,
                ];
            })
            ->filter(fn (array $row) => $row['score'] > 0);

        // Prefer genuine areas (suburb, neighbourhood, town, administrative/residential zone).
        // Fall back to other places (landmarks, markets) only when no area matches, and then
        // only on an exact name: "Clifton Hotel" is not the area "Clifton".
        $areaRows = $scored->where('area_like', true);
        $pool = $areaRows->isNotEmpty() ? $areaRows : $scored->where('score', 4);

        // Show only the best match tier: an exact name hides partial matches such as
        // "Latifabad Taluka" when "Latifabad" was typed, unless nothing matches exactly.
        $pool = $pool->where('score', (int) $pool->max('score'));

        return $pool
            ->sort(function (array $a, array $b) use ($cityLat, $cityLng) {
                if ($a['score'] !== $b['score']) {
                    return $b['score'] <=> $a['score'];
                }
                if ($a['name_length'] !== $b['name_length']) {
                    return $a['name_length'] <=> $b['name_length'];
                }
                if (!is_null($cityLat) && !is_null($cityLng)) {
                    return $this->mapDistance($a['item'], $cityLat, $cityLng)
                        <=> $this->mapDistance($b['item'], $cityLat, $cityLng);
                }

                return ((float) ($b['item']['importance'] ?? 0)) <=> ((float) ($a['item']['importance'] ?? 0));
            })
            ->map(fn (array $row) => [
                'name' => $row['item']['display_name'] ?? 'Unknown place',
                'lat' => (float) $row['item']['lat'],
                'lng' => (float) $row['item']['lon'],
                'type' => $row['item']['type'] ?? null,
            ])
            // Same place returned as both a boundary and a landuse polygon, or by both query variants.
            ->unique(fn (array $row) => round($row['lat'], 3) . '|' . round($row['lng'], 3))
            ->take(6)
            ->values();
    }

    /**
     * @return list<string>
     */
    private function searchNameCandidates(array $item): array
    {
        $names = [];

        foreach ((array) ($item['namedetails'] ?? []) as $key => $value) {
            $isNameKey = $key === 'name'
                || str_starts_with($key, 'name:')
                || str_contains($key, 'alt_name')
                || in_array($key, ['official_name', 'short_name', 'old_name'], true);

            if (is_string($value) && $isNameKey) {
                foreach (explode(';', $value) as $part) {
                    $names[] = trim($part);
                }
            }
        }

        if (!empty($item['name']) && is_string($item['name'])) {
            $names[] = $item['name'];
        }

        return array_values(array_unique(array_filter($names)));
    }

    private function isAreaLikeResult(array $item): bool
    {
        $category = (string) ($item['category'] ?? '');
        $type = (string) ($item['type'] ?? '');

        if ($category === 'place') {
            return !in_array($type, ['house', 'building', 'postcode', 'country', 'state', 'region'], true);
        }

        if ($category === 'boundary') {
            return $type !== 'marker';
        }

        return $category === 'landuse' && in_array($type, ['residential', 'commercial', 'retail', 'industrial'], true);
    }

    private function normalizeSearchText(string $text): string
    {
        return preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($text)) ?? '';
    }

    /**
     * @return list<string>
     */
    private function searchTokens(string $text): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($parts, fn ($part) => mb_strlen($part) >= 2 || ctype_digit($part)));
    }

    /**
     * @param  list<string>  $tokens
     */
    private function containsAllTokens(string $normalized, array $tokens): bool
    {
        if (count($tokens) < 2) {
            return false;
        }

        foreach ($tokens as $token) {
            if (!str_contains($normalized, $token)) {
                return false;
            }
        }

        return true;
    }

    private function mapDistance(array $item, float $lat, float $lng): float
    {
        return hypot((float) $item['lat'] - $lat, (float) $item['lon'] - $lng);
    }

    public function pointsIndex(Request $request)
    {
        try {
            $query = \App\Models\LocationPoint::with(['city', 'area']);

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }

            $points = $query->orderByDesc('id')->paginate(AppPagination::PER_PAGE)->withQueryString();
            $cities = City::orderBy('name')->get(['id', 'name']);
            $areas = Area::orderBy('name')->get(['id', 'city_id', 'name']);
            $types = \App\Models\LocationPoint::TYPES;

            return view('pickdrop.locations.points', compact('points', 'cities', 'areas', 'types'));
        } catch (\Throwable $e) {
            Log::error('Failed to load location points', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to load pickup/drop points: ' . $e->getMessage());
        }
    }

    public function storePoint(Request $request)
    {
        $data = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:pickup,drop,both',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|max:20',
        ]);

        if (! empty($data['area_id'])) {
            $area = Area::find($data['area_id']);
            if ($area && (int) $area->city_id !== (int) $data['city_id']) {
                return redirect()->back()->withInput()->with('error', 'Selected area does not belong to this city.');
            }
        }

        \App\Models\LocationPoint::create([
            'city_id' => $data['city_id'],
            'area_id' => $data['area_id'] ?? null,
            'name' => $data['name'],
            'type' => $data['type'],
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status' => $data['status'] ?? 'Active',
        ]);

        return redirect()->route('locations.points.index')->with('success', 'Pickup/drop point added.');
    }

    public function updatePoint(Request $request, \App\Models\LocationPoint $point)
    {
        $data = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:pickup,drop,both',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|max:20',
        ]);

        if (! empty($data['area_id'])) {
            $area = Area::find($data['area_id']);
            if ($area && (int) $area->city_id !== (int) $data['city_id']) {
                return redirect()->back()->withInput()->with('error', 'Selected area does not belong to this city.');
            }
        }

        $point->update([
            'city_id' => $data['city_id'],
            'area_id' => $data['area_id'] ?? null,
            'name' => $data['name'],
            'type' => $data['type'],
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status' => $data['status'] ?? 'Active',
        ]);

        return redirect()->route('locations.points.index')->with('success', 'Pickup/drop point updated.');
    }

    public function destroyPoint(\App\Models\LocationPoint $point)
    {
        $point->delete();

        return redirect()->route('locations.points.index')->with('success', 'Pickup/drop point removed.');
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

