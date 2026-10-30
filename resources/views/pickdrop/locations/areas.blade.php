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
                  <span class="badge rounded-pill px-3 py-1" style="background:#f3effa;color:#5a3fa8;">Active</span>
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
        <form id="areaForm" method="POST" novalidate>
          @csrf
          <input type="hidden" name="_method" id="areaFormMethod" value="POST">

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
              <select name="city_id" id="areaCityId" class="form-select" required>
                <option value="">Select city first</option>
                @foreach($cities as $city)
                  <option value="{{ $city->id }}" data-lat="{{ $city->latitude }}" data-lng="{{ $city->longitude }}" data-name="{{ $city->name }}">
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
              <div class="area-search position-relative">
                <div class="input-group">
                  <input type="text" id="areaMapSearchInput" class="form-control" placeholder="Search location on map..." autocomplete="off" disabled>
                  <button type="button" class="btn btn-outline-secondary" id="areaMapSearchBtn" disabled>
                    <span class="spinner-border spinner-border-sm me-1 d-none" id="areaMapSearchSpinner" role="status" aria-hidden="true"></span>
                    <span id="areaMapSearchLabel">Search</span>
                  </button>
                </div>
                <div id="areaMapSearchResults" class="area-search-results d-none" role="listbox"></div>
              </div>
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
        <div class="row g-3 view-detail-grid">
          <div class="col-12">
            <label class="form-label mb-1">City</label>
            <div class="fw-semibold text-break" id="viewAreaCity">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Area</label>
            <div class="fw-semibold text-break" id="viewAreaName">—</div>
          </div>
          <div class="col-md-6">
            <label class="form-label mb-1">Latitude</label>
            <div class="fw-semibold text-break" id="viewAreaLat">—</div>
          </div>
          <div class="col-md-6">
            <label class="form-label mb-1">Longitude</label>
            <div class="fw-semibold text-break" id="viewAreaLng">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Status</label>
            <div class="fw-semibold text-break" id="viewAreaStatus">—</div>
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
  const mapResultsEl = document.getElementById('areaMapSearchResults');
  const mapSearchUrl = "{{ route('locations.map-search') }}";
  const mapSearchSpinnerEl = document.getElementById('areaMapSearchSpinner');
  const mapSearchLabelEl = document.getElementById('areaMapSearchLabel');
  let areaMap, areaMarker, areaGeocoder;
  let searchRequestId = 0;
  let mapReady = false;
  let googleGeocodingUsable = true;

  // Google calls this for key/referrer rejections. It does NOT fire for BillingNotEnabledMapError,
  // so initAreaMap() also probes the Geocoder once and routes a denial here.
  window.gm_authFailure = function () {
    markGoogleUnavailable();
  };

  function markGoogleUnavailable() {
    googleGeocodingUsable = false;
    document.getElementById('areaMapNotice')?.classList.remove('d-none');
  }
  let activeCityContext = null;

  function initAreaMap() {
    if (areaMap || !areaMapEl || typeof google === 'undefined' || !google.maps) return;
    areaMap = new google.maps.Map(areaMapEl, {
      center: { lat: 24.8607, lng: 67.0011 },
      zoom: 11,
      mapTypeControl: true,
      streetViewControl: false,
      fullscreenControl: false,
      // Map POIs (e.g. "Khan Shinwari Hujra") would otherwise open their own info-window on click.
      clickableIcons: false
    });
    areaGeocoder = new google.maps.Geocoder();
    mapReady = true;
    areaMap.addListener('click', function (e) {
      if (!areaCityEl?.value) return;
      setAreaCoordinates(e.latLng.lat(), e.latLng.lng(), false);
    });

    // One silent probe so a denied key/billing problem is known before the first search.
    try {
      const pending = areaGeocoder.geocode({ address: 'Pakistan' }, function (results, status) {
        if (status === 'REQUEST_DENIED') markGoogleUnavailable();
      });
      if (pending && typeof pending.catch === 'function') pending.catch(function () {});
    } catch (err) {
      markGoogleUnavailable();
    }
  }

  // Single place that keeps the lat/lng inputs, the marker and the map view in sync.
  function setAreaCoordinates(lat, lng, moveMap = false) {
    areaLatEl.value = Number(lat).toFixed(7);
    areaLngEl.value = Number(lng).toFixed(7);
    areaLatEl.classList.remove('is-invalid');
    areaLngEl.classList.remove('is-invalid');
    if (!areaMap || typeof google === 'undefined' || !google.maps) return;
    const pos = { lat: Number(areaLatEl.value), lng: Number(areaLngEl.value) };
    if (!areaMarker) {
      areaMarker = new google.maps.Marker({ map: areaMap, position: pos, draggable: true });
      areaMarker.addListener('dragend', function (event) { setAreaCoordinates(event.latLng.lat(), event.latLng.lng(), false); });
    } else {
      areaMarker.setPosition(pos);
    }
    if (moveMap) { areaMap.panTo(pos); areaMap.setZoom(15); }
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
    activeCityContext = citySelected && lat && lng ? { name: selected.dataset.name || '', latitude: lat, longitude: lng } : null;
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
    document.getElementById('areaForm').action = '{{ route('locations.areas.update', ['area' => '__ID__']) }}'.replace('__ID__', area.id);
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
      form.action = '{{ route('locations.areas.destroy', ['area' => '__ID__']) }}'.replace('__ID__', areaId);
      form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
      document.body.appendChild(form);
      form.submit();
    });
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
  }

  function hideSearchResults() {
    if (!mapResultsEl) return;
    mapResultsEl.classList.add('d-none');
    mapResultsEl.innerHTML = '';
  }

  function setSearchLoading(loading) {
    if (mapSearchBtn) mapSearchBtn.disabled = loading || !areaCityEl?.value;
    if (mapSearchSpinnerEl) mapSearchSpinnerEl.classList.toggle('d-none', !loading);
    if (mapSearchLabelEl) mapSearchLabelEl.textContent = loading ? 'Searching...' : 'Search';
  }

  // Picking a suggestion: fill the input, close the list, and sync marker, view and lat/lng.
  function selectSearchResult(item) {
    if (!item || !Number.isFinite(item.lat) || !Number.isFinite(item.lng)) return;
    if (mapSearchEl) mapSearchEl.value = item.title;
    hideSearchResults();
    setAreaCoordinates(item.lat, item.lng, true);
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

    mapResultsEl.querySelectorAll('.area-search-item').forEach(btn => {
      btn.addEventListener('click', function () {
        selectSearchResult(items[parseInt(this.getAttribute('data-idx'), 10)]);
      });
    });
  }

  function searchOnMap() {
    if (!mapSearchEl || !areaCityEl?.value) return;
    const query = mapSearchEl.value.trim();
    if (query.length < 2) return;

    if (!mapReady && typeof google !== 'undefined' && google.maps) {
      initAreaMap();
    }

    // Ignore answers from an older search if the user searched again meanwhile.
    const requestId = ++searchRequestId;
    setSearchLoading(true);
    if (mapResultsEl) {
      mapResultsEl.innerHTML = '<div class="area-search-status"><span class="spinner-border spinner-border-sm me-1" role="status"></span> Searching location...</div>';
      mapResultsEl.classList.remove('d-none');
    }

    const cityName = activeCityContext?.name ? activeCityContext.name.trim() : '';
    const queryHasCity = cityName && query.toLowerCase().includes(cityName.toLowerCase());
    const searchQuery = queryHasCity || !cityName ? query : `${query}, ${cityName}`;

    function showItems(items) {
      if (requestId !== searchRequestId) return;
      setSearchLoading(false);
      renderSearchResults(items);
      // Preview the best match on the map; the user confirms by picking a suggestion.
      if (items.length > 0) setAreaCoordinates(items[0].lat, items[0].lng, true);
    }

    // Primary search: server-side OpenStreetMap lookup limited to the selected city and to real
    // areas whose name matches what was typed. Works without a Google key/billing.
    function serverSearch(onEmpty) {
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
          if (items.length > 0) showItems(items); else onEmpty();
        })
        .catch(onEmpty);
    }

    // Secondary search, only when the server found nothing. Google results are kept only if
    // they fall near the selected city, so a same-named area in another city is never offered.
    function googleSearch() {
      let finished = false;
      const finish = function (items) {
        if (finished) return;
        finished = true;
        clearTimeout(geocodeTimer);
        showItems(items);
      };
      // A denied/misconfigured Google key can leave the geocoder silent; never hang on it.
      const geocodeTimer = setTimeout(() => finish([]), 4000);

      const cityLat = Number(activeCityContext?.latitude);
      const cityLng = Number(activeCityContext?.longitude);
      const hasCityPoint = Number.isFinite(cityLat) && Number.isFinite(cityLng);

      try {
        const pending = areaGeocoder.geocode({
          address: searchQuery,
          componentRestrictions: { country: 'PK' }
        }, function (results, status) {
          if (status === 'REQUEST_DENIED') googleGeocodingUsable = false;
          if (status !== 'OK' || !results) { finish([]); return; }
          finish(results
            .map(r => {
              const parts = r.formatted_address.split(',');
              return {
                title: parts[0] ? parts[0].trim() : r.formatted_address,
                subtitle: parts.slice(1).join(',').trim() || r.formatted_address,
                lat: r.geometry.location.lat(),
                lng: r.geometry.location.lng()
              };
            })
            .filter(item => !hasCityPoint || Math.hypot(item.lat - cityLat, item.lng - cityLng) <= 0.4));
        });
        if (pending && typeof pending.catch === 'function') pending.catch(function () {});
      } catch (err) {
        googleGeocodingUsable = false;
        finish([]);
      }
    }

    serverSearch(function () {
      if (areaGeocoder && googleGeocodingUsable) googleSearch(); else showItems([]);
    });
  }

  mapSearchBtn?.addEventListener('click', searchOnMap);
  mapSearchEl?.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); searchOnMap(); } });
  mapSearchEl?.addEventListener('keydown', function (e) { if (e.key === 'Escape' && mapResultsEl && !mapResultsEl.classList.contains('d-none')) { e.stopPropagation(); hideSearchResults(); } });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.area-search')) hideSearchResults();
  });
  areaCityEl?.addEventListener('change', function () {
    applyCityContextFromSelect();
    searchRequestId++;
    setSearchLoading(false);
    hideSearchResults();
  });

  document.getElementById('areaModal')?.addEventListener('hidden.bs.modal', function () {
    searchRequestId++;
    setSearchLoading(false);
    if (mapSearchEl) mapSearchEl.value = '';
    hideSearchResults();
    if (areaMarker) { areaMarker.setMap(null); areaMarker = null; }
    [areaNameEl, areaLatEl, areaLngEl].forEach(el => el?.classList.remove('is-invalid'));
  });
  document.getElementById('areaModal')?.addEventListener('shown.bs.modal', function () {
    initAreaMap();
    setTimeout(() => {
      if (areaMap && typeof google !== 'undefined' && google.maps) {
        google.maps.event.trigger(areaMap, 'resize');
        // Editing an area: show its saved point (the map may not have existed when it was loaded).
        if (areaLatEl.value !== '' && areaLngEl.value !== '') {
          setAreaCoordinates(areaLatEl.value, areaLngEl.value, true);
        } else {
          focusMapToCityContext();
        }
      }
    }, 150);
  });

  // Validate before submit: name and a real coordinate pair are required.
  document.getElementById('areaForm')?.addEventListener('submit', function (e) {
    const problems = [];
    const lat = Number(areaLatEl.value);
    const lng = Number(areaLngEl.value);
    [areaNameEl, areaLatEl, areaLngEl].forEach(el => el.classList.remove('is-invalid'));
    areaNameEl.value = areaNameEl.value.trim();

    if (!areaCityEl.value) problems.push('Select a city.');
    if (areaNameEl.value === '') {
      problems.push('Enter the area name.');
      areaNameEl.classList.add('is-invalid');
    }
    if (areaLatEl.value === '' || !Number.isFinite(lat) || lat < -90 || lat > 90) {
      problems.push('Latitude must be a number between -90 and 90. Search or click the map to set it.');
      areaLatEl.classList.add('is-invalid');
    }
    if (areaLngEl.value === '' || !Number.isFinite(lng) || lng < -180 || lng > 180) {
      problems.push('Longitude must be a number between -180 and 180. Search or click the map to set it.');
      areaLngEl.classList.add('is-invalid');
    }

    if (problems.length === 0) return;
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Please complete the form',
      html: '<ul class="text-start mb-0">' + problems.map(p => '<li>' + escapeHtml(p) + '</li>').join('') + '</ul>',
      target: document.getElementById('areaModal')
    });
  });
</script>
@endpush

@push('style')
<style>
  #areaPickerMap { height: 280px; min-height: 220px; position: relative; z-index: 0; }

  /* Search input: clean border, visible placeholder, clear focus state. */
  #areaMapSearchInput { border: 1px solid #D1D5DB; border-radius: .5rem 0 0 .5rem; }
  #areaMapSearchInput::placeholder { color: #9CA3AF; opacity: 1; }
  #areaMapSearchInput:focus { border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); outline: 0; z-index: 3; }
  #areaMapSearchBtn { border-radius: 0 .5rem .5rem 0; min-width: 6.5rem; }

  /* Suggestion list: white card, dark text, light hover. Floats over the map instead of pushing it down. */
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
</style>
@endpush
