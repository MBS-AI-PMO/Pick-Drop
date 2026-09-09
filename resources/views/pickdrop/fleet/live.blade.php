@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin gap-3">
  <div>
    <h4 class="mb-1">Live fleet</h4>
    <p class="text-secondary mb-0">Active paid trips and driver GPS — Baig/UTS style ops board</p>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <span class="badge bg-light text-dark" id="fleetSummary">Loading…</span>
    <button type="button" class="btn btn-outline-primary btn-sm" id="fleetRefresh">Refresh</button>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-body p-0">
        <div id="fleetMap" style="height:560px;border-radius:12px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header"><h6 class="mb-0">Active trips</h6></div>
      <div class="card-body p-0" style="max-height:560px;overflow:auto;">
        <div id="fleetTripList" class="list-group list-group-flush">
          <div class="p-4 text-secondary text-center">Loading trips…</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('custom-scripts')
<script>
(function () {
  const mapEl = document.getElementById('fleetMap');
  const listEl = document.getElementById('fleetTripList');
  const summaryEl = document.getElementById('fleetSummary');
  let map, markers = [];

  function ensureMap() {
    if (map || !window.L) return;
    map = L.map(mapEl).setView([33.6844, 73.0479], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);
  }

  function clearMarkers() {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
  }

  async function loadFleet() {
    ensureMap();
    const res = await fetch(@json(route('fleet.live')), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    summaryEl.textContent = (data.summary?.active_trips || 0) + ' trips · ' + (data.summary?.drivers_on_map || 0) + ' on map · ' + (data.summary?.drivers_on_duty || 0) + ' on duty';

    clearMarkers();
    const bounds = [];
    listEl.innerHTML = '';

    if (!data.trips || !data.trips.length) {
      listEl.innerHTML = '<div class="p-4 text-secondary text-center">No active paid trips right now.</div>';
      return;
    }

    data.trips.forEach(trip => {
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'list-group-item list-group-item-action text-start';
      item.innerHTML = '<div class="fw-semibold">#' + trip.id + ' · ' + (trip.passenger || 'Passenger') + '</div>'
        + '<div class="small text-secondary">' + (trip.driver?.name || 'No driver') + ' · ' + (trip.vehicle?.plate || 'No plate') + '</div>'
        + '<div class="small">' + (trip.pickup || '') + ' → ' + (trip.drop || '') + '</div>'
        + '<span class="badge bg-light text-dark mt-1">' + (trip.status || '') + '</span>';

      if (trip.driver?.lat && trip.driver?.lng && map) {
        const marker = L.marker([trip.driver.lat, trip.driver.lng]).addTo(map)
          .bindPopup('<strong>' + (trip.driver.name || 'Driver') + '</strong><br>' + (trip.vehicle?.plate || '') + '<br>Trip #' + trip.id);
        markers.push(marker);
        bounds.push([trip.driver.lat, trip.driver.lng]);
        item.addEventListener('click', () => {
          map.setView([trip.driver.lat, trip.driver.lng], 14);
          marker.openPopup();
        });
      }
      listEl.appendChild(item);
    });

    if (bounds.length && map) {
      map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
    }
  }

  document.getElementById('fleetRefresh')?.addEventListener('click', loadFleet);

  function boot() {
    if (!window.L) {
      const css = document.createElement('link');
      css.rel = 'stylesheet';
      css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      document.head.appendChild(css);
      const js = document.createElement('script');
      js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      js.onload = () => { loadFleet(); setInterval(loadFleet, 20000); };
      document.body.appendChild(js);
    } else {
      loadFleet();
      setInterval(loadFleet, 20000);
    }
  }
  boot();
})();
</script>
@endpush
