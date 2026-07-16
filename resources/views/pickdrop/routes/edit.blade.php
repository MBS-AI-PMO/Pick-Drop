@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('build/plugins/select2/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="{{ route('routes.index') }}" class="text-decoration-none text-secondary">Route Management</a></li>
        <li class="breadcrumb-item active">Edit Route</li>
      </ol>
    </nav>
    <h4 class="mb-1">Edit Route <span class="text-secondary fw-normal">{{ $route->code ?? ('#R-'.$route->id) }}</span></h4>
    <p class="text-secondary mb-0">Update route details, destination and assignments</p>
  </div>
  <a href="{{ route('routes.index') }}" class="btn btn-light">
    <i data-lucide="arrow-left" class="icon-sm me-1"></i> Back to Routes
  </a>
</div>

<form method="POST" action="{{ route('routes.update', $route) }}" id="editRouteForm">
  @csrf
  @method('PUT')
  <div class="row g-4">

    {{-- Left Column: Route Info --}}
    <div class="col-lg-8">

      {{-- Basic Info Card --}}
      <div class="card mb-4">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2">
          <div class="w-32px h-32px rounded-2 d-flex align-items-center justify-content-center" style="background:rgba(var(--bs-primary-rgb),0.1);">
            <i data-lucide="map" class="text-primary" style="width:16px;height:16px;"></i>
          </div>
          <h6 class="mb-0 fw-bold">Route Information</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Select City <span class="text-danger">*</span></label>
              <select class="form-select" name="city_id" id="routeCitySelect" required>
                <option value="">Select city</option>
                @foreach($citiesWithAreas as $city)
                  <option value="{{ $city->id }}" {{ (string) old('city_id', $route->city_id) === (string) $city->id ? 'selected' : '' }}>
                    {{ $city->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Select Route Area <span class="text-danger">*</span></label>
              <select class="form-select" name="area_ids[]" id="routeAreaSelect" required>
                <option value="">Select area</option>
              </select>
              <input type="hidden" name="area_id" id="routePrimaryAreaId" value="{{ old('area_id', $route->area_id) }}">
              @php
                $existingAreaIds = old('area_ids', $route->area_ids ?? ($route->area_id ? [$route->area_id] : []));
                $isMultiArea = old('allow_multi_area', is_array($existingAreaIds) && count($existingAreaIds) > 1);
              @endphp
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" value="1" id="allowMultiArea" name="allow_multi_area" {{ $isMultiArea ? 'checked' : '' }}>
                <label class="form-check-label text-secondary" for="allowMultiArea">
                  Allow multi-area route (destination can be outside selected area but inside selected city)
                </label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Route Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="{{ old('name', $route->name) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
              <select class="form-select" name="shift" required>
                @php $shiftVal = old('shift', $route->shift); @endphp
                <option value="morning" {{ $shiftVal === 'morning' ? 'selected' : '' }}>Morning</option>
                <option value="afternoon" {{ $shiftVal === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Assign Vehicle <span class="text-danger">*</span></label>
              <select class="form-select" name="vehicle_id">
                <option value="">Select vehicle</option>
                @foreach($vehicles as $vehicle)
                  <option value="{{ $vehicle->id }}"
                          {{ (int) old('vehicle_id', $route->vehicle_id) === $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->name }} ({{ $vehicle->license_plate }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
              <input type="time" name="start_time" class="form-control"
                     value="{{ old('start_time', $route->start_time ? \Carbon\Carbon::parse($route->start_time)->format('H:i') : '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
              <input type="time" name="end_time" class="form-control"
                     value="{{ old('end_time', $route->end_time ? \Carbon\Carbon::parse($route->end_time)->format('H:i') : '') }}">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Final Destination <span class="text-danger">*</span></label>
              <input type="text" name="destination" id="destinationAddress" class="form-control mb-2"
                     placeholder="Search destination address..." value="{{ old('destination', $route->destination) }}" required autocomplete="off">
              <small class="text-muted d-block mb-2">Type to search from map suggestions, click the map, or drag the marker.</small>
              <div id="destinationMap" class="border rounded" style="height: 260px;"></div>
              <div id="destinationMapStatus" class="text-danger fs-13px mt-2 d-none"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Destination Latitude <span class="text-danger">*</span></label>
              <input type="number" step="0.0000001" name="destination_latitude" id="destinationLat"
                     class="form-control" value="{{ old('destination_latitude', $route->destination_latitude) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Destination Longitude <span class="text-danger">*</span></label>
              <input type="number" step="0.0000001" name="destination_longitude" id="destinationLng"
                     class="form-control" value="{{ old('destination_longitude', $route->destination_longitude) }}" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description <span class="text-secondary fw-normal">(optional)</span></label>
              <textarea class="form-control" name="description" rows="2" placeholder="Add any notes about this route...">{{ old('description', $route->description) }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- Route Stops Card --}}
      <div class="card">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="w-32px h-32px rounded-2 d-flex align-items-center justify-content-center" style="background:rgba(var(--bs-success-rgb),0.1);">
              <i data-lucide="map-pin" class="text-success" style="width:16px;height:16px;"></i>
            </div>
            <h6 class="mb-0 fw-bold">Route Stops</h6>
          </div>
          <span class="badge bg-light text-secondary fs-12px">
            Managed by parents/students via mobile app (read-only here)
          </span>
        </div>
        <div class="card-body">
          <div id="stopsContainer" class="d-flex flex-column gap-3">
            @forelse($route->stops as $index => $stop)
              <div class="stop-row border rounded-3 p-3 bg-light">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <span class="stop-number badge bg-primary rounded-pill px-2 py-1 fs-12px">
                    Stop {{ $index + 1 }}
                  </span>
                </div>
                <div class="row g-2">
                  <div class="col-md-6">
                    <p class="form-label fs-13px text-secondary mb-1">Stop Name / Location</p>
                    <p class="mb-0 fw-semibold">{{ $stop->name }}</p>
                  </div>
                  <div class="col-md-3">
                    <p class="form-label fs-13px text-secondary mb-1">Arrival Time</p>
                    <p class="mb-0">
                      {{ $stop->arrival_time ? \Carbon\Carbon::parse($stop->arrival_time)->format('g:i A') : '—' }}
                    </p>
                  </div>
                  <div class="col-md-3">
                    <p class="form-label fs-13px text-secondary mb-1">Order</p>
                    <p class="mb-0">#{{ $stop->order }}</p>
                  </div>
                </div>
              </div>
            @empty
              <p class="text-secondary fs-13px mb-0">
                No stops have been added yet. Parents and students will add stops from the mobile app.
              </p>
            @endforelse
          </div>
        </div>
      </div>

    </div>

    {{-- Right Column: Summary & Actions --}}
    <div class="col-lg-4">

      {{-- Route Summary Card --}}
      <div class="card mb-4">
        <div class="card-header border-bottom py-3">
          <h6 class="mb-0 fw-bold">Route Summary</h6>
        </div>
        <div class="card-body">
          <div class="d-flex flex-column gap-2">
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Route ID</span>
              <span class="fw-semibold fs-13px">{{ $route->code ?? ('#R-'.$route->id) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Total Stops</span>
              <span class="fw-semibold fs-13px" id="previewStopCount">{{ $route->stops->count() }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Shift</span>
              @php $shift = strtolower($route->shift); @endphp
              @if($shift === 'morning')
                <span class="badge rounded-pill px-3 py-1 fs-12px" style="background:#dbeafe;color:#1d4ed8;">Morning</span>
              @elseif($shift === 'afternoon')
                <span class="badge rounded-pill px-3 py-1 fs-12px" style="background:#fef3c7;color:#92400e;">Afternoon</span>
              @else
                <span class="badge rounded-pill px-3 py-1 fs-12px" style="background:#f3f4f6;color:#6b7280;">{{ $route->shift }}</span>
              @endif
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Status</span>
              <span class="fw-semibold fs-13px">{{ $route->status ?? 'Active' }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2">
              <span class="text-secondary fs-13px">Vehicle</span>
              <span class="fw-semibold fs-13px">
                @if($route->vehicle)
                  {{ $route->vehicle->name }} ({{ $route->vehicle->license_plate }})
                @else
                  —
                @endif
              </span>
            </div>
          </div>
        </div>
      </div>

      {{-- Actions Card --}}
      <div class="card mb-4">
        <div class="card-body d-flex flex-column gap-2">
          <button type="submit" class="btn btn-primary w-100">
            <i data-lucide="save" class="icon-sm me-1"></i> Save Changes
          </button>
          <a href="{{ route('routes.index') }}" class="btn btn-light w-100">
            <i data-lucide="x" class="icon-sm me-1"></i> Cancel
          </a>
        </div>
      </div>

    </div>
  </div>
</form>

{{-- Danger Zone (outside update form to avoid nested forms) --}}
<div class="row g-4 mt-0">
  <div class="col-lg-4 offset-lg-8">
    <div class="card border-danger-subtle">
      <div class="card-header border-bottom py-3">
        <h6 class="mb-0 fw-bold text-danger">Danger Zone</h6>
      </div>
      <div class="card-body">
        <p class="text-secondary fs-13px mb-3">Deleting this route will remove all associated stop and student assignments permanently.</p>
        <form method="POST" action="{{ route('routes.destroy', $route) }}" onsubmit="return confirm('Delete this route permanently?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-outline-danger w-100">
            <i data-lucide="trash-2" class="icon-sm me-1"></i> Delete Route
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@php $googleMapsApiKey = config('services.google.maps_api_key'); @endphp

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
@if($googleMapsApiKey)
<script>
  window.initRouteDestinationMap = function () {
    if (typeof window.onGoogleMapsReady === 'function') {
      window.onGoogleMapsReady();
    }
  };
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&language=en&region=PK&callback=initRouteDestinationMap&loading=async"></script>
@endif
@endpush

@push('custom-scripts')
@php
  $citiesWithAreasJson = $citiesWithAreas->map(function ($city) {
    return [
      'id' => $city->id,
      'name' => $city->name,
      'latitude' => $city->latitude,
      'longitude' => $city->longitude,
      'areas' => $city->areas->map(function ($area) {
        return [
          'id' => $area->id,
          'name' => $area->name,
          'latitude' => $area->latitude,
          'longitude' => $area->longitude,
        ];
      })->values(),
    ];
  })->values();

  $initialAreaIds = old('area_ids', $route->area_ids ?? ($route->area_id ? [$route->area_id] : []));
  $initialAreaId = old('area_id', $route->area_id);
@endphp
<script>
  const citiesWithAreas = @json($citiesWithAreasJson);
  const hasGoogleMapsKey = @json((bool) $googleMapsApiKey);

  const citySelect = document.getElementById('routeCitySelect');
  const areaSelect = document.getElementById('routeAreaSelect');
  const primaryAreaInput = document.getElementById('routePrimaryAreaId');
  const allowMultiArea = document.getElementById('allowMultiArea');
  const destinationLat = document.getElementById('destinationLat');
  const destinationLng = document.getElementById('destinationLng');
  const destinationAddress = document.getElementById('destinationAddress');
  const mapStatusEl = document.getElementById('destinationMapStatus');

  let destinationMap;
  let destinationMarker;
  let destinationGeocoder;
  let destinationAutocomplete;
  let destinationSearchDebounce;
  let select2FallbackLoading = false;

  function showMapStatus(message) {
    if (!mapStatusEl) return;
    mapStatusEl.textContent = message || '';
    mapStatusEl.classList.toggle('d-none', !message);
  }

  function loadSelect2Fallback(callback) {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
      callback?.();
      return;
    }
    if (select2FallbackLoading) return;
    select2FallbackLoading = true;

    function ensureJquery(next) {
      if (window.jQuery) {
        next();
        return;
      }
      const jq = document.createElement('script');
      jq.src = 'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js';
      jq.onload = next;
      document.body.appendChild(jq);
    }

    ensureJquery(function () {
      if (!document.getElementById('routeAreaSelect2FallbackCss')) {
        const link = document.createElement('link');
        link.id = 'routeAreaSelect2FallbackCss';
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
        document.head.appendChild(link);
      }

      if (window.jQuery.fn.select2) {
        select2FallbackLoading = false;
        callback?.();
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
      script.onload = function () {
        select2FallbackLoading = false;
        callback?.();
      };
      script.onerror = function () {
        select2FallbackLoading = false;
      };
      document.body.appendChild(script);
    });
  }

  function initRouteAreaSelect2() {
    if (!areaSelect) return;
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
      loadSelect2Fallback(initRouteAreaSelect2);
      return;
    }
    const $areaSelect = window.jQuery(areaSelect);
    if ($areaSelect.hasClass('select2-hidden-accessible')) {
      $areaSelect.select2('destroy');
    }

    const isMulti = !!allowMultiArea?.checked;
    areaSelect.multiple = isMulti;
    $areaSelect.select2({
      width: '100%',
      placeholder: isMulti ? 'Select one or more areas' : 'Select area',
      closeOnSelect: !isMulti,
      allowClear: true
    });
  }

  function getSelectedCity() {
    const selectedCityId = citySelect.value;
    return citiesWithAreas.find((item) => String(item.id) === String(selectedCityId)) || null;
  }

  function getSelectedCityAreas() {
    const city = getSelectedCity();
    return city ? city.areas : [];
  }

  function getSelectedAreaIds() {
    return Array.from(areaSelect?.selectedOptions || [])
      .map((opt) => opt.value)
      .filter((v) => v);
  }

  function syncPrimaryArea() {
    const selectedIds = getSelectedAreaIds();
    if (primaryAreaInput) primaryAreaInput.value = selectedIds[0] || '';
  }

  function populateAreas(selectedAreaId = '', selectedAreaIds = []) {
    const areas = getSelectedCityAreas();
    areaSelect.innerHTML = '';
    const selectedSet = new Set((selectedAreaIds || []).map((v) => String(v)));
    if (selectedAreaId) selectedSet.add(String(selectedAreaId));
    areas.forEach((area) => {
      const option = document.createElement('option');
      option.value = area.id;
      option.textContent = area.name;
      if (selectedSet.has(String(area.id))) option.selected = true;
      areaSelect.appendChild(option);
    });
    initRouteAreaSelect2();
    syncPrimaryArea();
  }

  function getSelectedArea() {
    const areas = getSelectedCityAreas();
    const selectedIds = getSelectedAreaIds();
    const primaryId = selectedIds[0];
    return areas.find((item) => String(item.id) === String(primaryId));
  }

  function setDestinationCoordinates(lat, lng, moveMap = false) {
    destinationLat.value = Number(lat).toFixed(7);
    destinationLng.value = Number(lng).toFixed(7);
    if (!destinationMap || typeof google === 'undefined' || !google.maps) return;
    const pos = { lat: Number(destinationLat.value), lng: Number(destinationLng.value) };
    if (!destinationMarker) {
      destinationMarker = new google.maps.Marker({ map: destinationMap, position: pos, draggable: true });
      destinationMarker.addListener('dragend', function (event) {
        setDestinationCoordinates(event.latLng.lat(), event.latLng.lng(), false);
      });
    } else {
      destinationMarker.setPosition(pos);
    }
    if (moveMap) {
      destinationMap.setCenter(pos);
      destinationMap.setZoom(14);
    }
  }

  function initDestinationMap() {
    if (destinationMap || typeof google === 'undefined' || !google.maps) return;
    const mapEl = document.getElementById('destinationMap');
    if (!mapEl) return;

    const initialLat = destinationLat.value ? Number(destinationLat.value) : 24.8607;
    const initialLng = destinationLng.value ? Number(destinationLng.value) : 67.0011;

    destinationMap = new google.maps.Map(mapEl, {
      center: { lat: initialLat, lng: initialLng },
      zoom: destinationLat.value && destinationLng.value ? 14 : 11,
      mapTypeControl: true,
      streetViewControl: false,
      fullscreenControl: false
    });
    destinationGeocoder = new google.maps.Geocoder();
    destinationMap.addListener('click', function (event) {
      setDestinationCoordinates(event.latLng.lat(), event.latLng.lng(), true);
      destinationGeocoder.geocode({ location: event.latLng }, function (results, status) {
        if (status === 'OK' && results[0]) destinationAddress.value = results[0].formatted_address;
      });
    });

    if (destinationAddress && google.maps.places) {
      destinationAutocomplete = new google.maps.places.Autocomplete(destinationAddress, {
        fields: ['geometry', 'formatted_address', 'name'],
        componentRestrictions: { country: 'pk' }
      });
      destinationAutocomplete.bindTo('bounds', destinationMap);
      destinationAutocomplete.addListener('place_changed', function () {
        const place = destinationAutocomplete.getPlace();
        if (!place || !place.geometry || !place.geometry.location) return;
        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();
        setDestinationCoordinates(lat, lng, true);
        destinationAddress.value = place.formatted_address || destinationAddress.value;
      });
    }

    showMapStatus('');
    restoreDestinationOnMap();
  }

  function restoreDestinationOnMap() {
    if (!destinationMap) return;
    if (destinationLat.value && destinationLng.value) {
      setDestinationCoordinates(destinationLat.value, destinationLng.value, true);
      return;
    }
    if (destinationAddress.value) {
      searchDestinationFromAddress();
    }
  }

  function ensureDestinationMapReady() {
    if (destinationMap) return;
    if (!hasGoogleMapsKey) {
      showMapStatus('Google Maps API key is missing. Add GOOGLE_MAPS_API_KEY to your .env and clear config cache.');
      return;
    }
    let attempts = 0;
    const timer = setInterval(() => {
      attempts++;
      initDestinationMap();
      if (destinationMap || attempts >= 40) {
        clearInterval(timer);
        if (!destinationMap) {
          showMapStatus('Google Maps failed to load. Check API key, billing, and that Maps JavaScript API + Places API are enabled.');
        }
      }
    }, 250);
  }

  function searchDestinationFromAddress() {
    if (!destinationGeocoder || !destinationAddress) return;
    const query = destinationAddress.value.trim();
    if (query.length < 2) return;
    destinationGeocoder.geocode({ address: query, region: 'PK' }, function (results, status) {
      if (status !== 'OK' || !results[0]) return;
      const result = results[0];
      setDestinationCoordinates(result.geometry.location.lat(), result.geometry.location.lng(), true);
      destinationAddress.value = result.formatted_address || destinationAddress.value;
    });
  }

  function scheduleDestinationSearch() {
    clearTimeout(destinationSearchDebounce);
    destinationSearchDebounce = setTimeout(() => {
      searchDestinationFromAddress();
    }, 500);
  }

  citySelect?.addEventListener('change', function () {
    populateAreas();
    const city = getSelectedCity();
    if (city?.latitude && city?.longitude && destinationMap) {
      destinationMap.setCenter({ lat: Number(city.latitude), lng: Number(city.longitude) });
      destinationMap.setZoom(11);
    }
  });

  allowMultiArea?.addEventListener('change', function () {
    areaSelect.multiple = !!allowMultiArea.checked;
    if (!allowMultiArea.checked) {
      const selectedIds = getSelectedAreaIds();
      const keepId = selectedIds[0] || '';
      Array.from(areaSelect.options).forEach((opt) => {
        opt.selected = keepId ? String(opt.value) === String(keepId) : false;
      });
    }
    initRouteAreaSelect2();
    syncPrimaryArea();
  });

  areaSelect?.addEventListener('change', function () {
    if (!allowMultiArea?.checked) {
      const selectedIds = getSelectedAreaIds();
      const keepId = selectedIds[selectedIds.length - 1] || '';
      Array.from(areaSelect.options).forEach((opt) => {
        opt.selected = keepId ? String(opt.value) === String(keepId) : false;
      });
    }
    syncPrimaryArea();
    const area = getSelectedArea();
    if (!area || !area.latitude || !area.longitude || !destinationMap) return;
    destinationMap.setCenter({ lat: Number(area.latitude), lng: Number(area.longitude) });
    destinationMap.setZoom(13);
  });

  destinationAddress?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      searchDestinationFromAddress();
    }
  });
  destinationAddress?.addEventListener('input', scheduleDestinationSearch);
  destinationAddress?.addEventListener('focus', ensureDestinationMapReady);

  destinationLat?.addEventListener('change', function () {
    if (!destinationLat.value || !destinationLng.value) return;
    setDestinationCoordinates(destinationLat.value, destinationLng.value, false);
  });
  destinationLng?.addEventListener('change', function () {
    if (!destinationLat.value || !destinationLng.value) return;
    setDestinationCoordinates(destinationLat.value, destinationLng.value, false);
  });

  window.onGoogleMapsReady = function () {
    initDestinationMap();
  };

  document.addEventListener('DOMContentLoaded', function () {
    areaSelect.multiple = !!allowMultiArea?.checked;
    populateAreas(@json($initialAreaId), @json($initialAreaIds));
    ensureDestinationMapReady();

    const previewStopCount = document.getElementById('previewStopCount');
    if (previewStopCount) {
      previewStopCount.textContent = document.querySelectorAll('#stopsContainer .stop-row').length;
    }
  });
</script>
@endpush

@push('style')
<style>
  .w-32px { width: 32px; }
  .h-32px { height: 32px; }
  .fs-12px { font-size: 12px; }
  .fs-13px { font-size: 13px; }
  .stop-row { transition: box-shadow 0.15s; }
  .stop-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
  .pac-container { z-index: 2000 !important; }
</style>
@endpush
