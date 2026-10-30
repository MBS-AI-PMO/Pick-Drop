@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Cities Management</h4>
    <p class="text-secondary mb-0">Manage cities and their core coordinates</p>
  </div>
</div>

{{-- Filters + Add City Button --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('locations.cities.index') }}" id="locationFilterForm">
      <div class="row g-2 align-items-center">

        {{-- Search --}}
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="locationSearch" placeholder="Search cities or areas..."
                   value="{{ request('search') }}">
          </div>
        </div>

        <div class="col-12 col-md-3">
    <select class="form-select" name="status" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
        <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
    </select>
</div>

<div class="col-auto">
    <button type="submit" class="btn btn-outline-secondary">
        Filter
    </button>
</div>
        {{-- Spacer --}}
        <div class="col"></div>

        {{-- Import / Add City Buttons --}}
        <div class="col-auto d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importCitiesModal">
            <i data-lucide="upload" class="icon-sm me-1"></i> Import Cities
          </button>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCityModal">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add City
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Cities Table --}}
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">#</th>
            <th class="py-3">City</th>
            <th class="py-3">Coordinates</th>
            <th class="py-3">Status</th>
            <th class="py-3">Areas</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="locationsTableBody">
          @forelse($cities as $city)
          @php
            $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
            $cityAreaContext = [
                'id' => $city->id,
                'name' => $city->name,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
            ];
            $cityEditPayload = [
                'id' => $city->id,
                'name' => $city->name,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
                'status' => $city->status,
            ];
            $cityPayload = array_merge($cityEditPayload, [
                'areas_count' => $city->areas->count(),
                'area_names' => $city->areas->pluck('name')->values()->all(),
                'created_at' => optional($city->created_at)->format('Y-m-d H:i'),
                'updated_at' => optional($city->updated_at)->format('Y-m-d H:i'),
            ]);
          @endphp
          <tr>
            <td class="ps-4 py-3 text-muted">{{ $cities->firstItem() + $loop->index }}</td>
            <td class="py-3 fw-semibold">{{ $city->name }}</td>
            <td class="py-3 text-secondary">
              @if(!is_null($city->latitude) && !is_null($city->longitude))
                {{ $city->latitude }}, {{ $city->longitude }}
              @else
                —
              @endif
            </td>
            <td class="py-3">
              @if(strtolower($city->status) === 'active')
                <span class="badge rounded-pill px-3 py-1" style="background:#f3effa;color:#5a3fa8;">Active</span>
              @else
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">{{ $city->status }}</span>
              @endif
            </td>
            <td class="py-3">
              @if($city->areas->isEmpty())
                <span class="text-secondary fs-13px">No areas</span>
              @else
                <div class="d-flex flex-wrap gap-1">
                  @foreach($city->areas as $area)
                    @php
                      $areaPayload = [
                          'id' => $area->id,
                          'city_id' => $area->city_id,
                          'name' => $area->name,
                          'latitude' => $area->latitude,
                          'longitude' => $area->longitude,
                          'status' => $area->status,
                      ];
                    @endphp
                    <span class="badge bg-light text-secondary border"
                          onclick="openEditAreaModal(event, {{ json_encode($areaPayload, $jsonFlags) }}, {{ json_encode($cityAreaContext, $jsonFlags) }})"
                          style="cursor:pointer;">
                      {{ $area->name }}
                    </span>
                  @endforeach
                </div>
              @endif
              <div class="mt-1">
                <a href="{{ route('locations.areas.index', ['city_id' => $city->id]) }}" class="small text-decoration-none">
                  View areas
                </a>
              </div>
            </td>
            <td class="py-3 text-center">
              <div class="action-btns">
                <button type="button" class="action-btn action-btn-view" title="View City"
                        onclick="openViewCityModal(event, {{ json_encode($cityPayload, $jsonFlags) }})">
                  <i data-lucide="eye"></i>
                </button>
                <button type="button" class="action-btn action-btn-edit" title="Edit City"
                        onclick="openEditCityModal(event, {{ json_encode($cityEditPayload, $jsonFlags) }})">
                  <i data-lucide="edit-2"></i>
                </button>
                <button type="button" class="action-btn action-btn-extra" title="Add Area"
                        onclick="openAddAreaModal(event, {{ json_encode($cityAreaContext, $jsonFlags) }})">
                  <i data-lucide="map-pin"></i>
                </button>
                <form action="{{ route('locations.cities.destroy', $city) }}" method="POST"
                      onsubmit="confirmCityDelete(event, this)">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="action-btn action-btn-delete" title="Delete">
                    <i data-lucide="trash-2"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="map-pin" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Locations Found</h6>
              <p class="text-muted small mb-0">Try adding a city to get started.</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <x-app-pagination :paginator="$cities" label="cities" />
</div>


{{-- ========== ADD CITY MODAL ========== --}}
<div class="modal fade" id="addCityModal" tabindex="-1" aria-labelledby="addCityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="addCityModalLabel">Add City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form action="{{ route('locations.cities.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">City Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Karachi" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Latitude</label>
              <input type="number" step="0.0000001" name="latitude" class="form-control" placeholder="e.g. 24.8607">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Longitude</label>
              <input type="number" step="0.0000001" name="longitude" class="form-control" placeholder="e.g. 67.0011">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Add City</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ========== IMPORT CITIES MODAL ========== --}}
<div class="modal fade" id="importCitiesModal" tabindex="-1" aria-labelledby="importCitiesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="importCitiesModalLabel">Import Cities</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form action="{{ route('locations.cities.import') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label fw-semibold">CSV File <span class="text-danger">*</span></label>
            <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
            <small class="text-muted d-block mt-1">
              Format: <code>name, latitude, longitude, status</code> (first row as header is allowed).
            </small>
          </div>
          <div class="modal-footer border-0 pt-2 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">
              <i data-lucide="upload" class="icon-sm me-1"></i> Import
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ========== VIEW CITY MODAL ========== --}}
<div class="modal fade" id="viewCityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">City Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3 view-city-grid">
          <div class="col-12">
            <label class="form-label mb-1">City Name</label>
            <div class="fw-semibold text-break" id="viewCityName">—</div>
          </div>
          <div class="col-sm-6">
            <label class="form-label mb-1">Latitude</label>
            <div class="fw-semibold text-break" id="viewCityLat">—</div>
          </div>
          <div class="col-sm-6">
            <label class="form-label mb-1">Longitude</label>
            <div class="fw-semibold text-break" id="viewCityLng">—</div>
          </div>
          <div class="col-sm-6">
            <label class="form-label mb-1">Status</label>
            <div class="fw-semibold text-break" id="viewCityStatus">—</div>
          </div>
          <div class="col-sm-6">
            <label class="form-label mb-1">Areas Count</label>
            <div class="fw-semibold text-break" id="viewCityAreasCount">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Areas</label>
            <div class="fw-semibold text-break" id="viewCityAreas">—</div>
          </div>
          <div class="col-sm-6">
            <label class="form-label mb-1">Created At</label>
            <div class="fw-semibold text-break" id="viewCityCreatedAt">—</div>
          </div>
          <div class="col-sm-6">
            <label class="form-label mb-1">Updated At</label>
            <div class="fw-semibold text-break" id="viewCityUpdatedAt">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- ========== EDIT CITY MODAL ========== --}}
<div class="modal fade" id="editCityModal" tabindex="-1" aria-labelledby="editCityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="editCityModalLabel">Edit City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="editCityForm" method="POST">
          @csrf
          @method('PUT')
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">City Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editCityName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Latitude</label>
              <input type="number" step="0.0000001" name="latitude" id="editCityLat" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Longitude</label>
              <input type="number" step="0.0000001" name="longitude" id="editCityLng" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="editCityStatus" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ========== ADD / EDIT AREA MODAL ========== --}}
<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="areaModalLabel">Add Area</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="areaForm" method="POST">
          @csrf
          <input type="hidden" name="_method" id="areaFormMethod" value="POST">
          <input type="hidden" name="city_id" id="areaCityId">

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">City</label>
              <input type="text" id="areaCityName" class="form-control" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Area Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="areaName" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Search from Map</label>
              <div class="area-search position-relative">
                <div class="input-group">
                  <input type="text" id="areaMapSearchInput" class="form-control" placeholder="Search location on map..." autocomplete="off">
                  <button type="button" class="btn btn-outline-secondary" id="areaMapSearchBtn">
                    <i data-lucide="search" class="icon-sm me-1"></i> Search
                  </button>
                </div>
                <div id="areaMapSearchResults" class="area-search-results d-none" role="listbox"></div>
              </div>
              <small class="text-muted d-block mt-1">Search a place or click directly on map to set area coordinates.</small>
            </div>
            <div class="col-12">
              <div id="areaMapNotice" class="alert alert-warning py-2 px-3 small mb-2 d-none" role="alert">
                Google Maps is unavailable for this site right now (billing / API key). Location search still works;
                you can also click the map or type the latitude and longitude.
              </div>
              <div id="areaPickerMap" class="border rounded"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Latitude</label>
              <input type="number" step="0.0000001" name="latitude" id="areaLat" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Longitude</label>
              <input type="number" step="0.0000001" name="longitude" id="areaLng" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="areaStatus" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0 d-flex justify-content-between">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-danger d-none" id="deleteAreaBtn">
                <i data-lucide="trash-2" class="icon-sm me-1"></i> Delete
              </button>
              <button type="submit" class="btn btn-primary px-4" id="areaSubmitBtn">Save</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('plugin-scripts')
@php
  $googleMapsApiKey = config('services.google.maps_api_key');
@endphp
@if($googleMapsApiKey)
<script>
  window.initPickDropAreaMap = function () {
    if (typeof window.onGoogleMapsReady === 'function') {
      window.onGoogleMapsReady();
    }
  };
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&language=en&region=PK&callback=initPickDropAreaMap&loading=async"></script>
@endif
@endpush

@push('custom-scripts')
<script>
  @if(session('success'))
  Swal.fire({
      icon: 'success', title: 'Success', text: "{{ session('success') }}",
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 3000, timerProgressBar: true
  });
  @endif

  @if(session('error'))
  Swal.fire({
      icon: 'error', title: 'Error', text: "{{ session('error') }}",
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 4000, timerProgressBar: true
  });
  @endif

  // Submit search on Enter
  const locationSearch = document.getElementById('locationSearch');
  if (locationSearch) {
    locationSearch.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') document.getElementById('locationFilterForm').submit();
    });
  }

  const viewCityModal = new bootstrap.Modal(document.getElementById('viewCityModal'));
  const editCityModal = new bootstrap.Modal(document.getElementById('editCityModal'));
  const areaModal     = new bootstrap.Modal(document.getElementById('areaModal'));
  const areaMapEl     = document.getElementById('areaPickerMap');
  const areaLatEl     = document.getElementById('areaLat');
  const areaLngEl     = document.getElementById('areaLng');
  const mapSearchEl   = document.getElementById('areaMapSearchInput');
  const mapResultsEl  = document.getElementById('areaMapSearchResults');
  const mapSearchBtn  = document.getElementById('areaMapSearchBtn');

  let areaMap;
  let areaMarker;
  let activeCityContext = null;
  let areaGeocoder;
  let areaSearchAutocomplete;
  let mapReady = false;
  let googleGeocodingUsable = true;
  const mapSearchUrl = "{{ route('locations.map-search') }}";

  // Google calls this for key/referrer rejections. It does NOT fire for BillingNotEnabledMapError,
  // so initAreaMap() also probes the Geocoder once and routes a denial here.
  window.gm_authFailure = function () {
    markGoogleUnavailable();
  };

  function markGoogleUnavailable() {
    googleGeocodingUsable = false;
    if (typeof google !== 'undefined' && google.maps && mapSearchEl) {
      google.maps.event.clearInstanceListeners(mapSearchEl);
    }
    document.querySelectorAll('.pac-container').forEach(el => el.remove());
    document.getElementById('areaMapNotice')?.classList.remove('d-none');
  }

  // Places Autocomplete is only attached once Google has proven usable; otherwise every
  // keystroke would hit a Places API that rejects the key.
  function attachPlacesAutocomplete() {
    if (areaSearchAutocomplete || !mapSearchEl || !areaMap || !google.maps.places) return;
    areaSearchAutocomplete = new google.maps.places.Autocomplete(mapSearchEl, {
      fields: ['geometry', 'formatted_address', 'name'],
      componentRestrictions: { country: 'pk' }
    });
    areaSearchAutocomplete.bindTo('bounds', areaMap);
    areaSearchAutocomplete.addListener('place_changed', function () {
      const place = areaSearchAutocomplete.getPlace();
      if (!place || !place.geometry || !place.geometry.location) {
        searchOnMap();
        return;
      }
      hideMapSearchResults();
      setAreaCoordinates(place.geometry.location.lat(), place.geometry.location.lng(), true);
      if (areaMap) areaMap.setZoom(15);
    });
  }

  function probeGoogleServices() {
    try {
      const pending = areaGeocoder.geocode({ address: 'Pakistan' }, function (results, status) {
        if (status === 'REQUEST_DENIED') markGoogleUnavailable(); else attachPlacesAutocomplete();
      });
      if (pending && typeof pending.catch === 'function') pending.catch(function () {});
    } catch (err) {
      markGoogleUnavailable();
    }
  }

  window.onGoogleMapsReady = function () {
    const modalEl = document.getElementById('areaModal');
    if (modalEl && modalEl.classList.contains('show')) {
      initAreaMap();
      focusMapToCityContext();
    }
  };

  function initAreaMap() {
    if (!areaMapEl || typeof google === 'undefined' || !google.maps) return;

    if (!areaMap) {
      areaMap = new google.maps.Map(areaMapEl, {
        center: { lat: 24.8607, lng: 67.0011 },
        zoom: 11,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: false
      });
      areaGeocoder = new google.maps.Geocoder();
      mapReady = true;

      areaMap.addListener('click', function (event) {
        setAreaCoordinates(event.latLng.lat(), event.latLng.lng(), true);
      });

      probeGoogleServices();
    } else {
      mapReady = true;
    }
  }

  function setAreaCoordinates(lat, lng, moveMap = false) {
    const fixedLat = Number(lat).toFixed(7);
    const fixedLng = Number(lng).toFixed(7);
    if (areaLatEl) areaLatEl.value = fixedLat;
    if (areaLngEl) areaLngEl.value = fixedLng;

    if (!areaMap || typeof google === 'undefined' || !google.maps) return;
    const markerPosition = { lat: Number(fixedLat), lng: Number(fixedLng) };
    if (!areaMarker) {
      areaMarker = new google.maps.Marker({
        map: areaMap,
        position: markerPosition,
        draggable: true
      });
      areaMarker.addListener('dragend', function (event) {
        setAreaCoordinates(event.latLng.lat(), event.latLng.lng(), false);
      });
    } else {
      areaMarker.setPosition(markerPosition);
    }
    if (moveMap) {
      areaMap.setCenter(markerPosition);
      areaMap.setZoom(15);
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
  }

  function renderSearchResults(items) {
    if (!mapResultsEl) return;
    if (!items || items.length === 0) {
      mapResultsEl.innerHTML = '<div class="area-search-status">No results found</div>';
      mapResultsEl.classList.remove('d-none');
      return;
    }

    mapResultsEl.innerHTML = items.map((item, idx) => `
      <button type="button" class="area-search-item${idx === 0 ? ' is-selected' : ''}" role="option" data-idx="${idx}">
        <span class="area-search-title">${escapeHtml(item.title)}</span>
        ${item.subtitle ? `<span class="area-search-sub">${escapeHtml(item.subtitle)}</span>` : ''}
      </button>
    `).join('');
    mapResultsEl.classList.remove('d-none');

    const buttons = mapResultsEl.querySelectorAll('.area-search-item');
    buttons.forEach(btn => {
      btn.addEventListener('click', function () {
        const chosen = items[parseInt(this.getAttribute('data-idx'), 10)];
        if (!chosen) return;
        // Fill the input with the pick, close the list and move marker + map to it.
        mapSearchEl.value = chosen.title;
        hideMapSearchResults();
        setAreaCoordinates(chosen.lat, chosen.lng, true);
        if (areaMap) areaMap.setZoom(15);
      });
    });
  }

  function searchOnMap() {
    if (!mapSearchEl) return;
    const query = mapSearchEl.value.trim();
    if (query.length < 2) return;

    if (!mapReady && typeof google !== 'undefined' && google.maps) {
      initAreaMap();
    }

    if (mapSearchBtn) mapSearchBtn.disabled = true;
    if (mapResultsEl) {
      mapResultsEl.innerHTML = '<div class="area-search-status"><span class="spinner-border spinner-border-sm me-1" role="status"></span> Searching location...</div>';
      mapResultsEl.classList.remove('d-none');
    }

    const cityName = activeCityContext?.name ? activeCityContext.name.trim() : '';
    const queryHasCity = cityName && query.toLowerCase().includes(cityName.toLowerCase());
    const searchQuery = queryHasCity || !cityName ? query : `${query}, ${cityName}`;

    function showItems(items) {
      if (mapSearchBtn) mapSearchBtn.disabled = false;
      renderSearchResults(items);
      if (items.length > 0) {
        setAreaCoordinates(items[0].lat, items[0].lng, true);
        if (areaMap) areaMap.setZoom(15);
      }
    }

    // Server-side OpenStreetMap search; works without a Google key/billing.
    function fallbackServerSearch() {
      const params = new URLSearchParams({ q: query, country_code: 'pk' });
      if (cityName) params.set('city_name', cityName);
      if (activeCityContext?.latitude && activeCityContext?.longitude) {
        params.set('city_lat', activeCityContext.latitude);
        params.set('city_lng', activeCityContext.longitude);
      }

      fetch(mapSearchUrl + '?' + params.toString(), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      })
        .then(res => res.ok ? res.json() : { results: [] })
        .then(data => {
          const items = (data.results || []).map(r => {
            const parts = String(r.name || '').split(',');
            return {
              title: (parts[0] || r.name || 'Unknown place').trim(),
              subtitle: parts.slice(1).join(',').trim() || r.name,
              lat: Number(r.lat),
              lng: Number(r.lng)
            };
          });
          showItems(items);
        })
        .catch(() => showItems([]));
    }

    if (areaGeocoder && googleGeocodingUsable) {
      let finished = false;
      const finish = function (fn) {
        if (finished) return;
        finished = true;
        clearTimeout(geocodeTimer);
        fn();
      };
      // A denied/misconfigured Google key can leave the geocoder silent; never hang on it.
      const geocodeTimer = setTimeout(() => finish(fallbackServerSearch), 4000);

      const geocodeReq = { address: searchQuery, region: 'PK' };
      if (areaMap && areaMap.getBounds()) {
        geocodeReq.bounds = areaMap.getBounds();
      }

      try {
        const pending = areaGeocoder.geocode(geocodeReq, function (results, status) {
          finish(function () {
            if (status === 'OK' && results && results.length > 0) {
              showItems(results.map(r => {
                const parts = r.formatted_address.split(',');
                const title = parts[0] ? parts[0].trim() : r.formatted_address;
                const subtitle = parts.slice(1).join(',').trim();
                return {
                  title: title,
                  subtitle: subtitle || r.formatted_address,
                  lat: r.geometry.location.lat(),
                  lng: r.geometry.location.lng()
                };
              }));
              return;
            }
            if (status === 'REQUEST_DENIED') googleGeocodingUsable = false;
            fallbackServerSearch();
          });
        });
        // Newer Maps JS returns a promise that rejects on denial; the callback above already handles it.
        if (pending && typeof pending.catch === 'function') pending.catch(function () {});
      } catch (err) {
        googleGeocodingUsable = false;
        finish(fallbackServerSearch);
      }
    } else {
      fallbackServerSearch();
    }
  }

  function hideMapSearchResults() {
    if (!mapResultsEl) return;
    mapResultsEl.classList.add('d-none');
    mapResultsEl.innerHTML = '';
  }

  function focusMapToCityContext() {
    if (!areaMap || !activeCityContext) return;
    if (activeCityContext.latitude && activeCityContext.longitude) {
      areaMap.setCenter({
        lat: Number(activeCityContext.latitude),
        lng: Number(activeCityContext.longitude)
      });
      areaMap.setZoom(12);
    }
  }

  function resetMapSearchState() {
    if (mapSearchEl) mapSearchEl.value = '';
    hideMapSearchResults();
    if (areaMarker && areaMap) {
      areaMarker.setMap(null);
      areaMarker = null;
    }
  }

  mapSearchBtn?.addEventListener('click', searchOnMap);
  mapSearchEl?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      searchOnMap();
    }
  });

  // Prevent Bootstrap modal from stealing focus or cancelling Google Places click
  document.addEventListener('mousedown', function (e) {
    if (e.target && (e.target.classList.contains('pac-item') || e.target.closest('.pac-item') || e.target.closest('.pac-container'))) {
      e.stopPropagation();
    }
  }, true);

  areaLatEl?.addEventListener('change', function () {
    if (!areaLatEl.value || !areaLngEl.value) return;
    setAreaCoordinates(areaLatEl.value, areaLngEl.value, false);
  });
  areaLngEl?.addEventListener('change', function () {
    if (!areaLatEl.value || !areaLngEl.value) return;
    setAreaCoordinates(areaLatEl.value, areaLngEl.value, false);
  });

  document.getElementById('areaModal')?.addEventListener('shown.bs.modal', function () {
    initAreaMap();
    setTimeout(() => {
      if (areaMap && typeof google !== 'undefined' && google.maps) {
        google.maps.event.trigger(areaMap, 'resize');
        focusMapToCityContext();
      }
    }, 150);
  });

  function openViewCityModal(event, city) {
    if (event) event.preventDefault();
    const areaNames = Array.isArray(city.area_names) ? city.area_names.filter(Boolean) : [];
    document.getElementById('viewCityName').textContent = city.name || '—';
    document.getElementById('viewCityLat').textContent = (city.latitude !== null && city.latitude !== undefined && city.latitude !== '')
      ? city.latitude
      : '—';
    document.getElementById('viewCityLng').textContent = (city.longitude !== null && city.longitude !== undefined && city.longitude !== '')
      ? city.longitude
      : '—';
    document.getElementById('viewCityStatus').textContent = city.status || '—';
    document.getElementById('viewCityAreasCount').textContent = String(city.areas_count ?? areaNames.length);
    document.getElementById('viewCityAreas').textContent = areaNames.length ? areaNames.join(', ') : 'No areas';
    document.getElementById('viewCityCreatedAt').textContent = city.created_at || '—';
    document.getElementById('viewCityUpdatedAt').textContent = city.updated_at || '—';
    viewCityModal.show();
  }

  const updateCityUrlTemplate = "{{ route('locations.cities.update', ':id') }}";
  const updateAreaUrlTemplate = "{{ route('locations.areas.update', ':id') }}";
  const deleteAreaUrlTemplate = "{{ route('locations.areas.destroy', ':id') }}";

  function openEditCityModal(event, city) {
    if (event) event.preventDefault();
    document.getElementById('editCityName').value   = city.name || '';
    document.getElementById('editCityLat').value    = (city.latitude !== null && city.latitude !== undefined) ? city.latitude : '';
    document.getElementById('editCityLng').value    = (city.longitude !== null && city.longitude !== undefined) ? city.longitude : '';
    document.getElementById('editCityStatus').value = city.status || 'Active';
    document.getElementById('editCityForm').action  = updateCityUrlTemplate.replace(':id', city.id);
    editCityModal.show();
  }

  function openAddAreaModal(event, city) {
    if (event) event.preventDefault();
    activeCityContext = city;
    document.getElementById('areaModalLabel').textContent = 'Add Area';
    document.getElementById('areaForm').action            = '{{ route('locations.areas.store') }}';
    document.getElementById('areaFormMethod').value       = 'POST';
    document.getElementById('areaCityId').value           = city.id;
    document.getElementById('areaCityName').value         = city.name;
    document.getElementById('areaName').value             = '';
    document.getElementById('areaLat').value              = '';
    document.getElementById('areaLng').value              = '';
    document.getElementById('areaStatus').value           = 'Active';
    resetMapSearchState();
    focusMapToCityContext();
    document.getElementById('deleteAreaBtn').classList.add('d-none');
    document.getElementById('areaSubmitBtn').textContent  = 'Add Area';
    areaModal.show();
  }

  function openEditAreaModal(event, area, city) {
    if (event) event.preventDefault();
    activeCityContext = city;
    document.getElementById('areaModalLabel').textContent = 'Edit Area';
    document.getElementById('areaForm').action            = updateAreaUrlTemplate.replace(':id', area.id);
    document.getElementById('areaFormMethod').value       = 'PUT';
    document.getElementById('areaCityId').value           = area.city_id;
    document.getElementById('areaCityName').value         = city.name;
    document.getElementById('areaName').value             = area.name || '';
    document.getElementById('areaLat').value              = (area.latitude !== null && area.latitude !== undefined) ? area.latitude : '';
    document.getElementById('areaLng').value              = (area.longitude !== null && area.longitude !== undefined) ? area.longitude : '';
    document.getElementById('areaStatus').value           = area.status || 'Active';
    resetMapSearchState();
    focusMapToCityContext();
    if (area.latitude && area.longitude) {
      setAreaCoordinates(area.latitude, area.longitude, true);
    }

    const deleteBtn = document.getElementById('deleteAreaBtn');
    deleteBtn.classList.remove('d-none');
    deleteBtn.onclick = function () {
      confirmAreaDelete(area.id);
    };

    document.getElementById('areaSubmitBtn').textContent  = 'Save Changes';
    areaModal.show();
  }

  function confirmCityDelete(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Delete city?',
      text: "This will also delete all its areas.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it'
    }).then(result => { if (result.isConfirmed) form.submit(); });
  }

  function confirmAreaDelete(areaId) {
    Swal.fire({
      title: 'Delete area?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it'
    }).then(result => {
      if (result.isConfirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = deleteAreaUrlTemplate.replace(':id', areaId);
        form.innerHTML = `
          @csrf
          <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    });
  }
</script>
@endpush

@push('style')
<style>
  .fs-13px { font-size: 13px; }
  #areaPickerMap { height: 280px; min-height: 220px; }
  #areaPickerMap { position: relative; z-index: 0; }

  /* Search suggestions: dark card with white text, floats over the map. */
  .area-search-results {
    position: absolute; left: 0; right: 0; top: calc(100% + 4px); z-index: 30;
    max-height: 260px; overflow-y: auto; padding: .25rem;
    background: #1F2937; border: 1px solid #374151; border-radius: .5rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .25);
  }
  .area-search-item {
    display: block; width: 100%; text-align: left; cursor: pointer;
    padding: .5rem .75rem; margin: 0; background: transparent; border: 0; border-radius: .375rem;
  }
  .area-search-item:hover, .area-search-item:focus, .area-search-item.is-selected { background: #374151; outline: 0; }
  .area-search-title, .area-search-sub { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .area-search-title { color: #fff; font-size: .875rem; font-weight: 600; }
  .area-search-sub { color: rgba(255, 255, 255, .75); font-size: .75rem; }
  .area-search-status { padding: .5rem .75rem; color: #fff; font-size: .875rem; }

  #areaMapSearchInput::placeholder { color: #9CA3AF; opacity: 1; }
  #areaMapSearchInput:focus { border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); outline: 0; }
  .pac-container { z-index: 2000 !important; }
</style>
@endpush
