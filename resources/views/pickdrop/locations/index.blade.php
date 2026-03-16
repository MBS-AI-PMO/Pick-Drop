@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Location Management</h4>
    <p class="text-secondary mb-0">Manage cities and areas with coordinates</p>
  </div>
</div>

{{-- Filters + Add City Button --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('locations.index') }}" id="locationFilterForm">
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

{{-- Cities & Areas Table --}}
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
                <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
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
                    <span class="badge bg-light text-secondary border"
                          onclick='openEditAreaModal(@json($area->toArray()), @json($city->only(["id","name"])))'
                          style="cursor:pointer;">
                      {{ $area->name }}
                    </span>
                  @endforeach
                </div>
              @endif
            </td>
            <td class="py-3 text-center">
              <div class="d-flex justify-content-center align-items-center gap-2">
                <button class="btn btn-sm btn-light btn-icon" title="Edit City"
                        onclick='openEditCityModal(@json($city->toArray()))'>
                  <i data-lucide="edit-2" class="icon-sm"></i>
                </button>
                <button class="btn btn-sm btn-light btn-icon" title="Add Area"
                        onclick='openAddAreaModal({{ $city->id }}, @json($city->name))'>
                  <i data-lucide="map-pin" class="icon-sm"></i>
                </button>
                <form action="{{ route('locations.cities.destroy', $city) }}" method="POST"
                      class="d-inline m-0 p-0" onsubmit="confirmCityDelete(event, this)">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger btn-icon">
                    <i data-lucide="trash-2" class="icon-sm"></i>
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

  {{-- Pagination Footer --}}
  @if($cities->hasPages())
  <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-3">
    <small class="text-muted">
      Showing {{ $cities->firstItem() }}–{{ $cities->lastItem() }} of {{ $cities->total() }} cities
    </small>
    <div>
      {{ $cities->links('pagination::bootstrap-5') }}
    </div>
  </div>
  @endif
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
            <div class="col-md-6">
              <label class="form-label fw-semibold">Latitude</label>
              <input type="number" step="0.0000001" name="latitude" id="areaLat" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Longitude</label>
              <input type="number" step="0.0000001" name="longitude" id="areaLng" class="form-control">
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

  @if($errors->any())
  Swal.fire({
      icon: 'error', title: 'Validation Error',
      html: `{!! implode('<br>', $errors->all()) !!}`,
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 5000, timerProgressBar: true
  });
  @endif

  // Submit search on Enter
  const locationSearch = document.getElementById('locationSearch');
  if (locationSearch) {
    locationSearch.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') document.getElementById('locationFilterForm').submit();
    });
  }

  const editCityModal = new bootstrap.Modal(document.getElementById('editCityModal'));
  const areaModal     = new bootstrap.Modal(document.getElementById('areaModal'));

  function openEditCityModal(city) {
    document.getElementById('editCityName').value   = city.name || '';
    document.getElementById('editCityLat').value    = city.latitude ?? '';
    document.getElementById('editCityLng').value    = city.longitude ?? '';
    document.getElementById('editCityStatus').value = city.status || 'Active';
    document.getElementById('editCityForm').action  = `/locations/cities/${city.id}`;
    editCityModal.show();
  }

  function openAddAreaModal(cityId, cityName) {
    document.getElementById('areaModalLabel').textContent = 'Add Area';
    document.getElementById('areaForm').action            = '{{ route('locations.areas.store') }}';
    document.getElementById('areaFormMethod').value       = 'POST';
    document.getElementById('areaCityId').value           = cityId;
    document.getElementById('areaCityName').value         = cityName;
    document.getElementById('areaName').value             = '';
    document.getElementById('areaLat').value              = '';
    document.getElementById('areaLng').value              = '';
    document.getElementById('areaStatus').value           = 'Active';
    document.getElementById('deleteAreaBtn').classList.add('d-none');
    document.getElementById('areaSubmitBtn').textContent  = 'Add Area';
    areaModal.show();
  }

  function openEditAreaModal(area, city) {
    document.getElementById('areaModalLabel').textContent = 'Edit Area';
    document.getElementById('areaForm').action            = `/locations/areas/${area.id}`;
    document.getElementById('areaFormMethod').value       = 'PUT';
    document.getElementById('areaCityId').value           = area.city_id;
    document.getElementById('areaCityName').value         = city.name;
    document.getElementById('areaName').value             = area.name || '';
    document.getElementById('areaLat').value              = area.latitude ?? '';
    document.getElementById('areaLng').value              = area.longitude ?? '';
    document.getElementById('areaStatus').value           = area.status || 'Active';

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
        form.action = `/locations/areas/${areaId}`;
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
</style>
@endpush

