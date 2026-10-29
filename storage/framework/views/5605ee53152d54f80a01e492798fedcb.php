<?php $__env->startSection('content'); ?>


<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Cities Management</h4>
    <p class="text-secondary mb-0">Manage cities and their core coordinates</p>
  </div>
</div>


<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('locations.cities.index')); ?>" id="locationFilterForm">
      <div class="row g-2 align-items-center">

        
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="locationSearch" placeholder="Search cities or areas..."
                   value="<?php echo e(request('search')); ?>">
          </div>
        </div>

        <div class="col-12 col-md-3">
    <select class="form-select" name="status" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="Active" <?php echo e(request('status') === 'Active' ? 'selected' : ''); ?>>Active</option>
        <option value="Inactive" <?php echo e(request('status') === 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
    </select>
</div>

<div class="col-auto">
    <button type="submit" class="btn btn-outline-secondary">
        Filter
    </button>
</div>
        
        <div class="col"></div>

        
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
          <?php $__empty_1 = true; $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php
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
          ?>
          <tr>
            <td class="ps-4 py-3 text-muted"><?php echo e($cities->firstItem() + $loop->index); ?></td>
            <td class="py-3 fw-semibold"><?php echo e($city->name); ?></td>
            <td class="py-3 text-secondary">
              <?php if(!is_null($city->latitude) && !is_null($city->longitude)): ?>
                <?php echo e($city->latitude); ?>, <?php echo e($city->longitude); ?>

              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td class="py-3">
              <?php if(strtolower($city->status) === 'active'): ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Active</span>
              <?php else: ?>
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;"><?php echo e($city->status); ?></span>
              <?php endif; ?>
            </td>
            <td class="py-3">
              <?php if($city->areas->isEmpty()): ?>
                <span class="text-secondary fs-13px">No areas</span>
              <?php else: ?>
                <div class="d-flex flex-wrap gap-1">
                  <?php $__currentLoopData = $city->areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                      $areaPayload = [
                          'id' => $area->id,
                          'city_id' => $area->city_id,
                          'name' => $area->name,
                          'latitude' => $area->latitude,
                          'longitude' => $area->longitude,
                          'status' => $area->status,
                      ];
                    ?>
                    <span class="badge bg-light text-secondary border"
                          onclick="openEditAreaModal(event, <?php echo e(json_encode($areaPayload, $jsonFlags)); ?>, <?php echo e(json_encode($cityAreaContext, $jsonFlags)); ?>)"
                          style="cursor:pointer;">
                      <?php echo e($area->name); ?>

                    </span>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
              <?php endif; ?>
              <div class="mt-1">
                <a href="<?php echo e(route('locations.areas.index', ['city_id' => $city->id])); ?>" class="small text-decoration-none">
                  View areas
                </a>
              </div>
            </td>
            <td class="py-3 text-center">
              <div class="action-btns">
                <button type="button" class="action-btn action-btn-view" title="View City"
                        onclick="openViewCityModal(event, <?php echo e(json_encode($cityPayload, $jsonFlags)); ?>)">
                  <i data-lucide="eye"></i>
                </button>
                <button type="button" class="action-btn action-btn-edit" title="Edit City"
                        onclick="openEditCityModal(event, <?php echo e(json_encode($cityEditPayload, $jsonFlags)); ?>)">
                  <i data-lucide="edit-2"></i>
                </button>
                <button type="button" class="action-btn action-btn-extra" title="Add Area"
                        onclick="openAddAreaModal(event, <?php echo e(json_encode($cityAreaContext, $jsonFlags)); ?>)">
                  <i data-lucide="map-pin"></i>
                </button>
                <form action="<?php echo e(route('locations.cities.destroy', $city)); ?>" method="POST"
                      onsubmit="confirmCityDelete(event, this)">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="action-btn action-btn-delete" title="Delete">
                    <i data-lucide="trash-2"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="6" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="map-pin" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Locations Found</h6>
              <p class="text-muted small mb-0">Try adding a city to get started.</p>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $cities,'label' => 'cities']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cities),'label' => 'cities']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38def3cf8acaa014b33af3e7e7449e67)): ?>
<?php $attributes = $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67; ?>
<?php unset($__attributesOriginal38def3cf8acaa014b33af3e7e7449e67); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38def3cf8acaa014b33af3e7e7449e67)): ?>
<?php $component = $__componentOriginal38def3cf8acaa014b33af3e7e7449e67; ?>
<?php unset($__componentOriginal38def3cf8acaa014b33af3e7e7449e67); ?>
<?php endif; ?>
</div>



<div class="modal fade" id="addCityModal" tabindex="-1" aria-labelledby="addCityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="addCityModalLabel">Add City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form action="<?php echo e(route('locations.cities.store')); ?>" method="POST">
          <?php echo csrf_field(); ?>
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


<div class="modal fade" id="importCitiesModal" tabindex="-1" aria-labelledby="importCitiesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="importCitiesModalLabel">Import Cities</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form action="<?php echo e(route('locations.cities.import')); ?>" method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>
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


<div class="modal fade" id="editCityModal" tabindex="-1" aria-labelledby="editCityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="editCityModalLabel">Edit City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="editCityForm" method="POST">
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>
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


<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="areaModalLabel">Add Area</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="areaForm" method="POST">
          <?php echo csrf_field(); ?>
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
              <div class="input-group">
                <input type="text" id="areaMapSearchInput" class="form-control" placeholder="Search location on map...">
                <button type="button" class="btn btn-outline-secondary" id="areaMapSearchBtn">
                  <i data-lucide="search" class="icon-sm me-1"></i> Search
                </button>
              </div>
              <div id="areaMapSearchResults" class="list-group mt-2 d-none"></div>
              <small class="text-muted d-block mt-1">Search a place or click directly on map to set area coordinates.</small>
            </div>
            <div class="col-12">
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('plugin-scripts'); ?>
<?php
  $googleMapsApiKey = config('services.google.maps_api_key');
?>
<?php if($googleMapsApiKey): ?>
<script>
  window.initPickDropAreaMap = function () {
    if (typeof window.onGoogleMapsReady === 'function') {
      window.onGoogleMapsReady();
    }
  };
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($googleMapsApiKey); ?>&libraries=places&language=en&region=PK&callback=initPickDropAreaMap&loading=async"></script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
  <?php if(session('success')): ?>
  Swal.fire({
      icon: 'success', title: 'Success', text: "<?php echo e(session('success')); ?>",
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 3000, timerProgressBar: true
  });
  <?php endif; ?>

  <?php if(session('error')): ?>
  Swal.fire({
      icon: 'error', title: 'Error', text: "<?php echo e(session('error')); ?>",
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 4000, timerProgressBar: true
  });
  <?php endif; ?>

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
  const mapSearchUrl = "<?php echo e(route('locations.map-search')); ?>";

  // Google calls this when the key is rejected (e.g. billing not enabled).
  window.gm_authFailure = function () {
    googleGeocodingUsable = false;
  };

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

      if (mapSearchEl && google.maps.places) {
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
          const lat = place.geometry.location.lat();
          const lng = place.geometry.location.lng();
          setAreaCoordinates(lat, lng, true);
          if (areaMap) areaMap.setZoom(15);
        });
      }
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
      mapResultsEl.innerHTML = '<div class="list-group-item text-muted small py-2 px-3">No location found. Try a different search term.</div>';
      mapResultsEl.classList.remove('d-none');
      return;
    }

    mapResultsEl.innerHTML = items.map((item, idx) => `
      <button type="button" class="list-group-item list-group-item-action py-2 px-3 small ${idx === 0 ? 'active' : ''}" data-idx="${idx}">
        <div class="fw-semibold text-truncate">${escapeHtml(item.title)}</div>
        ${item.subtitle ? `<div class="text-muted text-truncate" style="font-size:11px;">${escapeHtml(item.subtitle)}</div>` : ''}
      </button>
    `).join('');
    mapResultsEl.classList.remove('d-none');

    const buttons = mapResultsEl.querySelectorAll('.list-group-item-action');
    buttons.forEach(btn => {
      btn.addEventListener('click', function () {
        buttons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const idx = parseInt(this.getAttribute('data-idx'), 10);
        const chosen = items[idx];
        if (chosen) {
          setAreaCoordinates(chosen.lat, chosen.lng, true);
          if (areaMap) areaMap.setZoom(15);
        }
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
      mapResultsEl.innerHTML = '<div class="list-group-item text-muted small py-2 px-3"><span class="spinner-border spinner-border-sm me-1" role="status"></span> Searching location...</div>';
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

  const updateCityUrlTemplate = "<?php echo e(route('locations.cities.update', ':id')); ?>";
  const updateAreaUrlTemplate = "<?php echo e(route('locations.areas.update', ':id')); ?>";
  const deleteAreaUrlTemplate = "<?php echo e(route('locations.areas.destroy', ':id')); ?>";

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
    document.getElementById('areaForm').action            = '<?php echo e(route('locations.areas.store')); ?>';
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
          <?php echo csrf_field(); ?>
          <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    });
  }
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('style'); ?>
<style>
  .fs-13px { font-size: 13px; }
  #areaPickerMap { height: 280px; min-height: 220px; }
  #areaMapSearchResults { max-height: 180px; overflow-y: auto; }
  .pac-container { z-index: 2000 !important; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/locations/index.blade.php ENDPATH**/ ?>