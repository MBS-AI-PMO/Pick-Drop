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
        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-secondary">Route Management</a></li>
        <li class="breadcrumb-item active">Create Route</li>
      </ol>
    </nav>
    <h4 class="mb-1">Create New Route</h4>
    <p class="text-secondary mb-0">Define a new transportation route with stops and assignments</p>
  </div>
  <a href="#" class="btn btn-light">
    <i data-lucide="arrow-left" class="icon-sm me-1"></i> Back to Routes
  </a>
</div>

<form method="POST" action="{{ route('routes.store') }}" id="createRouteForm">
  @csrf
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
                  <option value="{{ $city->id }}" {{ (string) old('city_id') === (string) $city->id ? 'selected' : '' }}>
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
              <input type="hidden" name="area_id" id="routePrimaryAreaId" value="{{ old('area_id') }}">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" value="1" id="allowMultiArea" name="allow_multi_area" {{ old('allow_multi_area') ? 'checked' : '' }}>
                <label class="form-check-label text-secondary" for="allowMultiArea">
                  Allow multi-area route (destination can be outside selected area but inside selected city)
                </label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Custom Route Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="e.g. G-10 Morning Pickup 1" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
              <select class="form-select" name="shift" required>
                <option value="">Select shift</option>
                <option value="morning" {{ old('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                <option value="afternoon" {{ old('shift') === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Assign Vehicle <span class="text-danger">*</span></label>
              <select class="form-select" name="vehicle_id">
                <option value="">Select vehicle</option>
                @foreach($vehicles as $vehicle)
                  <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->name }} ({{ $vehicle->license_plate }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
              <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
              <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Final Destination <span class="text-danger">*</span></label>
              <input type="text" name="destination" id="destinationAddress" class="form-control mb-2"
                     placeholder="Search destination address..." value="{{ old('destination') }}" required>
              <small class="text-muted d-block mb-2">Type to search from map suggestions, or manually enter destination text.</small>
              <div id="destinationMap" class="border rounded" style="height: 260px;"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Destination Latitude <span class="text-danger">*</span></label>
              <input type="number" step="0.0000001" name="destination_latitude" id="destinationLat"
                     class="form-control" value="{{ old('destination_latitude') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Destination Longitude <span class="text-danger">*</span></label>
              <input type="number" step="0.0000001" name="destination_longitude" id="destinationLng"
                     class="form-control" value="{{ old('destination_longitude') }}" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description <span class="text-secondary fw-normal">(optional)</span></label>
              <textarea class="form-control" name="description" rows="2" placeholder="Add any notes about this route...">{{ old('description') }}</textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Right Column: Summary --}}
    <div class="col-lg-4">

      {{-- Route Preview Card --}}
      <div class="card mb-4">
        <div class="card-header border-bottom py-3">
          <h6 class="mb-0 fw-bold">Route Preview</h6>
        </div>
        <div class="card-body">
          {{-- Mini Map Placeholder --}}
          <div class="rounded-3 d-flex flex-column align-items-center justify-content-center text-center mb-3"
            style="height:180px; background:linear-gradient(135deg,#e8f0fe 0%,#f0f4ff 100%); position:relative;">
            <svg width="100%" height="100%" style="position:absolute;top:0;left:0;opacity:0.2;">
              <defs>
                <pattern id="previewGrid" width="30" height="30" patternUnits="userSpaceOnUse">
                  <path d="M 30 0 L 0 0 0 30" fill="none" stroke="#94a3b8" stroke-width="0.5"/>
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill="url(#previewGrid)"/>
            </svg>
            <svg width="100%" height="100%" style="position:absolute;top:0;left:0;">
              <polyline points="40,140 130,110 240,70 330,45"
                fill="none" stroke="#3b5bdb" stroke-width="2" stroke-dasharray="5,3"/>
              <circle cx="40" cy="140" r="5" fill="#3b5bdb"/>
              <circle cx="130" cy="110" r="5" fill="#3b5bdb"/>
              <circle cx="240" cy="70" r="5" fill="#3b5bdb"/>
              <circle cx="330" cy="45" r="7" fill="#22c55e" stroke="white" stroke-width="2"/>
            </svg>
            <i data-lucide="map" style="width:28px;height:28px;color:#3b5bdb;position:relative;z-index:1;opacity:0.4;"></i>
            <p class="text-secondary fs-12px mt-2 mb-0" style="position:relative;z-index:1;">Route Map Preview</p>
          </div>

          {{-- Summary Info --}}
          <div class="d-flex flex-column gap-2">
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Total Stops</span>
              <span class="fw-semibold fs-13px" id="previewStopCount">1</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Shift</span>
              <span class="fw-semibold fs-13px">—</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Route Time</span>
              <span class="fw-semibold fs-13px">—</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2">
              <span class="text-secondary fs-13px">Vehicle</span>
              <span class="fw-semibold fs-13px">—</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Actions Card --}}
      <div class="card">
        <div class="card-body d-flex flex-column gap-2">
          <button type="submit" class="btn btn-primary w-100">
            <i data-lucide="check" class="icon-sm me-1"></i> Create Route
          </button>
          <button type="reset" class="btn btn-light w-100">
            <i data-lucide="rotate-ccw" class="icon-sm me-1"></i> Reset Form
          </button>
          <a href="#" class="btn btn-light w-100 text-danger">
            <i data-lucide="x" class="icon-sm me-1"></i> Cancel
          </a>
        </div>
      </div>

    </div>
  </div>
</form>

@endsection

@push('plugin-scripts')
@php $googleMapsApiKey = env('GOOGLE_MAPS_API_KEY', ''); @endphp
<script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
@if($googleMapsApiKey)
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&language=en&region=PK" async defer></script>
@endif
@endpush

@push('custom-scripts')
@php
  $citiesWithAreasJson = $citiesWithAreas->map(function ($city) {
    return [
      'id' => $city->id,
      'name' => $city->name,
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
@endphp
<script>
  @if(session('success'))
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: "{{ session('success') }}",
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });
  @endif

  @if(session('error'))
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: "{{ session('error') }}",
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4500,
    timerProgressBar: true
  });
  @endif

  @if($errors->any())
  Swal.fire({
    icon: 'error',
    title: 'Validation Error',
    html: `{!! implode('<br>', $errors->all()) !!}`,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5500,
    timerProgressBar: true
  });
  @endif

  const citiesWithAreas = @json($citiesWithAreasJson);

  const citySelect = document.getElementById('routeCitySelect');
  const areaSelect = document.getElementById('routeAreaSelect');
  const primaryAreaInput = document.getElementById('routePrimaryAreaId');
  const allowMultiArea = document.getElementById('allowMultiArea');
  const destinationLat = document.getElementById('destinationLat');
  const destinationLng = document.getElementById('destinationLng');
  const destinationAddress = document.getElementById('destinationAddress');

  let destinationMap;
  let destinationMarker;
  let destinationGeocoder;
  let destinationAutocomplete;
  let destinationSearchDebounce;
  let select2FallbackLoading = false;

  function loadSelect2Fallback(callback) {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
      callback?.();
      return;
    }
    if (select2FallbackLoading) return;
    select2FallbackLoading = true;

    if (!document.getElementById('routeAreaSelect2FallbackCss')) {
      const link = document.createElement('link');
      link.id = 'routeAreaSelect2FallbackCss';
      link.rel = 'stylesheet';
      link.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
      document.head.appendChild(link);
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

  function getSelectedCityAreas() {
    const selectedCityId = citySelect.value;
    const city = citiesWithAreas.find((item) => String(item.id) === String(selectedCityId));
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
    destinationMap = new google.maps.Map(document.getElementById('destinationMap'), {
      center: { lat: 24.8607, lng: 67.0011 },
      zoom: 11
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
  }

  function ensureDestinationMapReady() {
    if (destinationMap) return;
    let attempts = 0;
    const timer = setInterval(() => {
      attempts++;
      initDestinationMap();
      if (destinationMap || attempts >= 20) {
        clearInterval(timer);
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

  document.addEventListener('DOMContentLoaded', function () {
    areaSelect.multiple = !!allowMultiArea?.checked;
    populateAreas(@json(old('area_id')), @json(old('area_ids', [])));
    ensureDestinationMapReady();
    setTimeout(() => {
      if (destinationLat.value && destinationLng.value) {
        setDestinationCoordinates(destinationLat.value, destinationLng.value, true);
      } else if (destinationAddress.value) {
        searchDestinationFromAddress();
      }
    }, 700);
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
