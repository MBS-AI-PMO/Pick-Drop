@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Areas Management</h4>
    <p class="text-secondary mb-0">Select a city first, then manage only that city's areas</p>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('locations.areas.index') }}" id="areaFilterForm">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="areaSearch" placeholder="Search city or area..." value="{{ request('search') }}">
          </div>
        </div>
        <div class="col-12 col-md-3">
          <select name="city_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Cities</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}" {{ (string) request('city_id') === (string) $city->id ? 'selected' : '' }}>
                {{ $city->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col"></div>
        @if(request('search') || request('city_id'))
          <div class="col-auto">
            <a href="{{ route('locations.areas.index') }}" class="btn btn-outline-danger">
              <i data-lucide="x" class="icon-sm"></i>
            </a>
          </div>
        @endif
        <div class="col-auto">
          <button type="button" class="btn btn-primary" onclick="openAddAreaModal()">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add Area
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">#</th>
            <th class="py-3">City</th>
            <th class="py-3">Area</th>
            <th class="py-3">Coordinates</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($areas as $area)
            <tr>
              <td class="ps-4 py-3 text-muted">{{ $areas->firstItem() + $loop->index }}</td>
              <td class="py-3 fw-semibold">{{ $area->city->name ?? '—' }}</td>
              <td class="py-3">{{ $area->name }}</td>
              <td class="py-3 text-secondary">
                @if(!is_null($area->latitude) && !is_null($area->longitude))
                  {{ $area->latitude }}, {{ $area->longitude }}
                @else
                  —
                @endif
              </td>
              <td class="py-3">
                @if(strtolower($area->status) === 'active')
                  <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Active</span>
                @else
                  <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">{{ $area->status }}</span>
                @endif
              </td>
              <td class="py-3 text-center">
                <div class="action-btns">
                  <button type="button" class="action-btn action-btn-view" title="View"
                          onclick='openViewAreaModal(@json(array_merge($area->toArray(), ["city_name" => $area->city->name ?? "—"])))'>
                    <i data-lucide="eye"></i>
                  </button>
                  <button type="button" class="action-btn action-btn-edit" title="Edit"
                          onclick='openEditAreaModal(@json($area->toArray()))'>
                    <i data-lucide="edit-2"></i>
                  </button>
                  <button type="button" class="action-btn action-btn-delete" title="Delete"
                          onclick="confirmAreaDelete({{ $area->id }})">
                    <i data-lucide="trash-2"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5">
                <h6 class="text-secondary mb-1">No Areas Found</h6>
                <p class="text-muted small mb-0">Select a city first, then add an area for that city.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$areas" label="areas" />
</div>

<div class="modal fade" id="areaModal" tabindex="-1" aria-hidden="true">
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

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
              <select name="city_id" id="areaCityId" class="form-select" required>
                <option value="">Select city first</option>
                @foreach($cities as $city)
                  <option value="{{ $city->id }}" data-lat="{{ $city->latitude }}" data-lng="{{ $city->longitude }}">
                    {{ $city->name }}
                  </option>
                @endforeach
              </select>
              <small class="text-muted">Area list and map unlock after a city is selected.</small>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Area Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="areaName" class="form-control" required disabled>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Search from Map</label>
              <div class="input-group">
                <input type="text" id="areaMapSearchInput" class="form-control" placeholder="Search location on map..." disabled>
                <button type="button" class="btn btn-outline-secondary" id="areaMapSearchBtn" disabled>Search</button>
              </div>
              <div id="areaMapSearchResults" class="list-group mt-2 d-none"></div>
            </div>
            <div class="col-12"><div id="areaPickerMap" class="border rounded"></div></div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Latitude</label>
              <input type="number" step="0.0000001" name="latitude" id="areaLat" class="form-control" required disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Longitude</label>
              <input type="number" step="0.0000001" name="longitude" id="areaLng" class="form-control" required disabled>
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
            <button type="submit" class="btn btn-primary px-4" id="areaSubmitBtn">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewAreaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Area Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="row g-3">
          <div class="col-12">
            <p class="text-secondary small mb-1">City</p>
            <p class="fw-semibold mb-0" id="viewAreaCity">—</p>
          </div>
          <div class="col-12">
            <p class="text-secondary small mb-1">Area</p>
            <p class="fw-semibold mb-0" id="viewAreaName">—</p>
          </div>
          <div class="col-md-6">
            <p class="text-secondary small mb-1">Latitude</p>
            <p class="fw-semibold mb-0" id="viewAreaLat">—</p>
          </div>
          <div class="col-md-6">
            <p class="text-secondary small mb-1">Longitude</p>
            <p class="fw-semibold mb-0" id="viewAreaLng">—</p>
          </div>
          <div class="col-12">
            <p class="text-secondary small mb-1">Status</p>
            <p class="fw-semibold mb-0" id="viewAreaStatus">—</p>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
@php $googleMapsApiKey = config('services.google.maps_api_key'); @endphp
@if($googleMapsApiKey)
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&language=en&region=PK" async defer></script>
@endif
@endpush

@push('custom-scripts')
<script>
  const areaModal = new bootstrap.Modal(document.getElementById('areaModal'));
  const areaCityEl = document.getElementById('areaCityId');
  const areaLatEl = document.getElementById('areaLat');
  const areaLngEl = document.getElementById('areaLng');
  const mapSearchEl = document.getElementById('areaMapSearchInput');
  const mapSearchBtn = document.getElementById('areaMapSearchBtn');
  const areaMapEl = document.getElementById('areaPickerMap');
  let areaMap, areaMarker, areaGeocoder, areaSearchAutocomplete;
  let mapReady = false;
  let activeCityContext = null;
  let mapSearchDebounce;

  function initAreaMap() {
    if (areaMap || !areaMapEl || typeof google === 'undefined' || !google.maps) return;
    areaMap = new google.maps.Map(areaMapEl, {
      center: { lat: 24.8607, lng: 67.0011 },
      zoom: 11,
      mapTypeControl: true,
      streetViewControl: false,
      fullscreenControl: false
    });
    areaGeocoder = new google.maps.Geocoder();
    mapReady = true;
    areaMap.addListener('click', function (e) {
      if (!areaCityEl?.value) return;
      setAreaCoordinates(e.latLng.lat(), e.latLng.lng(), true);
    });

    if (mapSearchEl && google.maps.places) {
      areaSearchAutocomplete = new google.maps.places.Autocomplete(mapSearchEl, {
        fields: ['geometry', 'formatted_address', 'name'],
        componentRestrictions: { country: 'pk' }
      });
      areaSearchAutocomplete.bindTo('bounds', areaMap);
      areaSearchAutocomplete.addListener('place_changed', function () {
        const place = areaSearchAutocomplete.getPlace();
        if (!place || !place.geometry || !place.geometry.location) return;
        setAreaCoordinates(place.geometry.location.lat(), place.geometry.location.lng(), true);
      });
    }
  }

  function setAreaCoordinates(lat, lng, moveMap = false) {
    areaLatEl.value = Number(lat).toFixed(7);
    areaLngEl.value = Number(lng).toFixed(7);
    if (!areaMap || typeof google === 'undefined' || !google.maps) return;
    const pos = { lat: Number(areaLatEl.value), lng: Number(areaLngEl.value) };
    if (!areaMarker) {
      areaMarker = new google.maps.Marker({ map: areaMap, position: pos, draggable: true });
      areaMarker.addListener('dragend', function (event) { setAreaCoordinates(event.latLng.lat(), event.latLng.lng(), false); });
    } else {
      areaMarker.setPosition(pos);
    }
    if (moveMap) { areaMap.setCenter(pos); areaMap.setZoom(14); }
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

  const areaNameEl = document.getElementById('areaName');
  const areaSubmitBtn = document.getElementById('areaSubmitBtn');
  const preselectedCityId = @json(request('city_id'));

  function setAreaFieldsEnabled(enabled) {
    [areaNameEl, mapSearchEl, mapSearchBtn, areaLatEl, areaLngEl].forEach((el) => {
      if (el) el.disabled = !enabled;
    });
    if (areaSubmitBtn) areaSubmitBtn.disabled = !enabled;
  }

  function applyCityContextFromSelect() {
    const selected = areaCityEl?.options[areaCityEl.selectedIndex];
    const citySelected = !!(areaCityEl && areaCityEl.value);
    const lat = selected?.dataset?.lat;
    const lng = selected?.dataset?.lng;
    activeCityContext = citySelected && lat && lng ? { latitude: lat, longitude: lng } : null;
    setAreaFieldsEnabled(citySelected);
    if (citySelected) focusMapToCityContext();
  }

  function openViewAreaModal(area) {
    document.getElementById('viewAreaCity').textContent = area.city_name || '—';
    document.getElementById('viewAreaName').textContent = area.name || '—';
    document.getElementById('viewAreaLat').textContent = area.latitude ?? '—';
    document.getElementById('viewAreaLng').textContent = area.longitude ?? '—';
    document.getElementById('viewAreaStatus').textContent = area.status || '—';
    new bootstrap.Modal(document.getElementById('viewAreaModal')).show();
  }

  function openAddAreaModal() {
    document.getElementById('areaModalLabel').textContent = 'Add Area';
    document.getElementById('areaForm').action = '{{ route('locations.areas.store') }}';
    document.getElementById('areaFormMethod').value = 'POST';
    areaCityEl.value = preselectedCityId || '';
    areaNameEl.value = '';
    areaLatEl.value = '';
    areaLngEl.value = '';
    document.getElementById('areaStatus').value = 'Active';
    areaSubmitBtn.textContent = 'Add Area';
    applyCityContextFromSelect();
    areaModal.show();
  }

  function openEditAreaModal(area) {
    document.getElementById('areaModalLabel').textContent = 'Edit Area';
    document.getElementById('areaForm').action = `/locations/areas/${area.id}`;
    document.getElementById('areaFormMethod').value = 'PUT';
    areaCityEl.value = area.city_id;
    document.getElementById('areaName').value = area.name || '';
    areaLatEl.value = area.latitude ?? '';
    areaLngEl.value = area.longitude ?? '';
    document.getElementById('areaStatus').value = area.status || 'Active';
    document.getElementById('areaSubmitBtn').textContent = 'Save Changes';
    applyCityContextFromSelect();
    areaModal.show();
    if (area.latitude && area.longitude) setAreaCoordinates(area.latitude, area.longitude, true);
  }

  function confirmAreaDelete(areaId) {
    Swal.fire({
      title: 'Delete area?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it'
    }).then((result) => {
      if (!result.isConfirmed) return;
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/locations/areas/${areaId}`;
      form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
      document.body.appendChild(form);
      form.submit();
    });
  }

  function searchOnMap() {
    if (!mapReady || !areaGeocoder || !mapSearchEl) return;
    if (!areaCityEl?.value) return;
    const query = mapSearchEl.value.trim();
    if (query.length < 2) return;
    mapSearchBtn.disabled = true;
    areaGeocoder.geocode({ address: query, region: 'PK' }, function (results, status) {
      mapSearchBtn.disabled = false;
      if (status === 'OK' && results[0]) {
        setAreaCoordinates(results[0].geometry.location.lat(), results[0].geometry.location.lng(), true);
        return;
      }
      Swal.fire({
        icon: 'info',
        title: 'No location found',
        text: 'Try a more specific search term.'
      });
    });
  }

  function scheduleMapSearch() {
    clearTimeout(mapSearchDebounce);
    mapSearchDebounce = setTimeout(() => {
      searchOnMap();
    }, 450);
  }

  mapSearchBtn?.addEventListener('click', searchOnMap);
  mapSearchEl?.addEventListener('input', scheduleMapSearch);
  mapSearchEl?.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); searchOnMap(); } });
  areaCityEl?.addEventListener('change', applyCityContextFromSelect);
  document.getElementById('areaModal')?.addEventListener('shown.bs.modal', function () {
    initAreaMap();
    setTimeout(() => {
      if (areaMap && typeof google !== 'undefined' && google.maps) {
        google.maps.event.trigger(areaMap, 'resize');
        focusMapToCityContext();
      }
    }, 150);
  });
</script>
@endpush

@push('style')
<style>
  #areaPickerMap { height: 280px; min-height: 220px; }
  .pac-container { z-index: 2000 !important; }
</style>
@endpush
