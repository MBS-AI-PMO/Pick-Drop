@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Vehicle Management</h4>
    <p class="text-secondary mb-0">Monitor and manage all vehicles</p>
  </div>
</div>

{{-- Filters + Add Button --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('vehicles.index') }}" id="filterForm">
      <div class="row g-2 align-items-center">

        {{-- Search --}}
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="vehicleSearch" placeholder="Search vehicles..."
                   value="{{ request('search') }}">
          </div>
        </div>

        {{-- Status Filter --}}
        <div class="col-12 col-md-3">
          <select class="form-select" name="status" id="statusFilter" onchange="this.form.submit()">
            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
            <option value="Active"       {{ request('status') === 'Active'       ? 'selected' : '' }}>Active</option>
            <option value="Inactive"     {{ request('status') === 'Inactive'     ? 'selected' : '' }}>Inactive</option>
            <option value="Maintenance"  {{ request('status') === 'Maintenance'  ? 'selected' : '' }}>Maintenance</option>
          </select>
        </div>

        {{-- Search / Clear Buttons --}}
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          @if(request('search') || (request('status') && request('status') !== 'all'))
            <a href="{{ route('vehicles.index') }}" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          @endif
        </div>

        {{-- Spacer --}}
        <div class="col"></div>

        {{-- Add Vehicle Button --}}
        <div class="col-auto">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add Vehicle
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Vehicles Table --}}
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">#</th>
            <th class="py-3">Vehicle Name</th>
            <th class="py-3">Plate No.</th>
            <th class="py-3">Type</th>
            <th class="py-3">Capacity</th>
            <th class="py-3">Driver</th>
            <th class="py-3">Route</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="vehiclesTableBody">
          @forelse($vehicles as $vehicle)
          <tr>
            <td class="ps-4 py-3 text-muted">{{ $vehicles->firstItem() + $loop->index }}</td>
            <td class="py-3 fw-semibold">{{ $vehicle->name }}</td>
            <td class="py-3 text-secondary">{{ $vehicle->license_plate }}</td>
            <td class="py-3 text-secondary">{{ optional($vehicle->category)->vehicle_name ?? '—' }}</td>
            <td class="py-3 text-secondary">
              {{ optional($vehicle->category)->passenger_capacity ? optional($vehicle->category)->passenger_capacity . ' Seats' : '—' }}
            </td>
            <td class="py-3 text-secondary">{{ optional($vehicle->driver)->name ?? '—' }}</td>
            <td class="py-3 text-secondary">{{ $vehicle->route_id ?: '—' }}</td>
            <td class="py-3">
              @php $s = strtolower($vehicle->status); @endphp
              @if($s === 'active')
                <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
              @elseif($s === 'maintenance')
                <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7;color:#92400e;">Maintenance</span>
              @else
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">{{ $vehicle->status }}</span>
              @endif
            </td>
            <td class="py-3 text-center">
              <div class="d-flex justify-content-center align-items-center gap-2">
                <button class="btn btn-sm btn-light btn-icon" title="View / Edit"
                  onclick='openDetailsModal(@json(collect($vehicle->toArray())->merge([
                    "type_name"   => optional($vehicle->category)->vehicle_name,
                    "capacity_val"=> optional($vehicle->category)->passenger_capacity,
                    "driver_name" => optional($vehicle->driver)->name
                  ])))'>
                  <i data-lucide="eye" class="icon-sm"></i>
                </button>
                <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST"
                      class="d-inline" onsubmit="confirmDelete(event, this)">
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
            <td colspan="9" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="truck" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Vehicles Found</h6>
              <p class="text-muted small mb-0">Try adjusting your search or filters.</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination Footer --}}
  @if($vehicles->hasPages())
  <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-3">
    <small class="text-muted">
      Showing {{ $vehicles->firstItem() }}–{{ $vehicles->lastItem() }} of {{ $vehicles->total() }} vehicles
    </small>
    <div>
      {{ $vehicles->links('pagination::bootstrap-5') }}
    </div>
  </div>
  @endif
</div>


{{-- ========== ADD VEHICLE MODAL ========== --}}
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="addVehicleModalLabel">Add New Vehicle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form action="{{ route('vehicles.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Bus #46" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Plate Number <span class="text-danger">*</span></label>
              <input type="text" name="license_plate" class="form-control" placeholder="e.g. ABC-1234" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Type</label>
              <select name="vehicle_type_id" id="add_vehicle_type" class="form-select">
                <option value="">Select type</option>
                @foreach($types as $type)
                  <option value="{{ $type->id }}" data-capacity="{{ $type->passenger_capacity }}">
                    {{ $type->vehicle_name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Capacity</label>
              <input type="text" id="add_vehicle_capacity" class="form-control" placeholder="Auto-filled" disabled>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Assign Driver</label>
              <select name="driver_id" class="form-select">
                <option value="">Select driver</option>
                @foreach($drivers as $driver)
                  <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Assign Route</label>
              <input type="text" name="route_id" class="form-control" placeholder="e.g. Route #R-123">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Maintenance">Maintenance</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Add Vehicle</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ========== VIEW / EDIT VEHICLE MODAL ========== --}}
<div class="modal fade" id="viewVehicleModal" tabindex="-1" aria-labelledby="viewVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="viewVehicleModalLabel">Vehicle Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        {{-- View Mode --}}
        <div id="vehicleViewMode">
          <div class="row g-3">
            <div class="col-6">
              <p class="text-secondary small mb-1">Vehicle Name</p>
              <p class="fw-semibold mb-0" id="vDetailName">—</p>
            </div>
            <div class="col-6">
              <p class="text-secondary small mb-1">Plate Number</p>
              <p class="fw-semibold mb-0" id="vDetailPlate">—</p>
            </div>
            <div class="col-6">
              <p class="text-secondary small mb-1">Type</p>
              <p class="fw-semibold mb-0" id="vDetailType">—</p>
            </div>
            <div class="col-6">
              <p class="text-secondary small mb-1">Capacity</p>
              <p class="fw-semibold mb-0" id="vDetailCapacity">—</p>
            </div>
            <div class="col-6">
              <p class="text-secondary small mb-1">Driver</p>
              <p class="fw-semibold mb-0" id="vDetailDriver">—</p>
            </div>
            <div class="col-6">
              <p class="text-secondary small mb-1">Route</p>
              <p class="fw-semibold mb-0" id="vDetailRoute">—</p>
            </div>
            <div class="col-6">
              <p class="text-secondary small mb-1">Status</p>
              <span class="badge rounded-pill px-3 py-1" id="vDetailStatusBadge">—</span>
            </div>
          </div>
          <div class="mt-4 d-flex gap-2">
            <button type="button" class="btn btn-primary px-4 flex-grow-1" onclick="toggleEditMode(true)">
              <i data-lucide="edit-2" class="icon-sm me-1"></i> Edit Vehicle
            </button>
            <form id="deleteVehicleForm" method="POST" class="d-inline" onsubmit="confirmDelete(event, this)">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger px-3">
                <i data-lucide="trash-2" class="icon-sm"></i>
              </button>
            </form>
          </div>
        </div>

        {{-- Edit Mode --}}
        <div id="vehicleEditMode" style="display:none;">
          <form id="editVehicleForm" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="vEditName" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Plate Number <span class="text-danger">*</span></label>
                <input type="text" name="license_plate" id="vEditPlate" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Type</label>
                <select name="vehicle_type_id" id="vEditType" class="form-select">
                  <option value="">Select type</option>
                  @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->vehicle_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" id="vEditStatus" class="form-select">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="Maintenance">Maintenance</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Assign Driver</label>
                <select name="driver_id" id="vEditDriver" class="form-select">
                  <option value="">Select driver</option>
                  @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Assign Route</label>
                <input type="text" name="route_id" id="vEditRoute" class="form-control">
              </div>
            </div>
            <div class="mt-4 d-flex gap-2">
              <button type="button" class="btn btn-light px-4" onclick="toggleEditMode(false)">Cancel</button>
              <button type="submit" class="btn btn-primary px-4 flex-grow-1">Save Changes</button>
            </div>
          </form>
        </div>

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

  @if($errors->any())
  Swal.fire({
      icon: 'error', title: 'Validation Error',
      html: `{!! implode('<br>', $errors->all()) !!}`,
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 5000, timerProgressBar: true
  });
  @endif

  // Submit search on Enter
  document.getElementById('vehicleSearch').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') document.getElementById('filterForm').submit();
  });

  // Auto-fill capacity when type selected (Add modal)
  document.getElementById('add_vehicle_type').addEventListener('change', function () {
    const cap = this.options[this.selectedIndex].getAttribute('data-capacity');
    document.getElementById('add_vehicle_capacity').value = cap ? cap + ' Seats' : '';
  });

  // View/Edit modal
  const viewModal = new bootstrap.Modal(document.getElementById('viewVehicleModal'));

  function openDetailsModal(vehicle) {
    toggleEditMode(false);

    document.getElementById('viewVehicleModalLabel').textContent = 'Vehicle — ' + vehicle.name;

    document.getElementById('vDetailName').textContent     = vehicle.name        || '—';
    document.getElementById('vDetailPlate').textContent    = vehicle.license_plate || '—';
    document.getElementById('vDetailType').textContent     = vehicle.category?.vehicle_name   || '—';
    document.getElementById('vDetailCapacity').textContent = vehicle.category?.passenger_capacity ? vehicle.category.passenger_capacity + ' Seats' : '—';
    document.getElementById('vDetailDriver').textContent   = vehicle.driver_name || '—';
    document.getElementById('vDetailRoute').textContent    = vehicle.route_id    || '—';

    const badge = document.getElementById('vDetailStatusBadge');
    badge.textContent = vehicle.status || '—';
    const s = (vehicle.status || '').toLowerCase();
    if (s === 'active') {
      badge.style.background = '#d1fae5'; badge.style.color = '#065f46';
    } else if (s === 'maintenance') {
      badge.style.background = '#fef3c7'; badge.style.color = '#92400e';
    } else {
      badge.style.background = '#f3f4f6'; badge.style.color = '#6b7280';
    }

    document.getElementById('deleteVehicleForm').action = `/vehicles/${vehicle.id}`;
    document.getElementById('editVehicleForm').action   = `/vehicles/${vehicle.id}`;

    document.getElementById('vEditName').value    = vehicle.name          || '';
    document.getElementById('vEditPlate').value   = vehicle.license_plate || '';
    // DB column is vehicle_category_id; keep fallback for older payloads
    document.getElementById('vEditType').value    = vehicle.vehicle_category_id || vehicle.vehicle_type_id || '';
    document.getElementById('vEditDriver').value  = vehicle.driver_id     || '';
    document.getElementById('vEditRoute').value   = vehicle.route_id      || '';
    document.getElementById('vEditStatus').value  = vehicle.status        || 'Active';

    if (typeof lucide !== 'undefined') lucide.createIcons();
    viewModal.show();
  }

  function toggleEditMode(isEdit) {
    document.getElementById('vehicleViewMode').style.display = isEdit ? 'none'  : 'block';
    document.getElementById('vehicleEditMode').style.display = isEdit ? 'block' : 'none';
  }

  function confirmDelete(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Are you sure?', text: "You won't be able to revert this!",
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then(result => { if (result.isConfirmed) form.submit(); });
  }
</script>
@endpush

@push('style')
<style>
  .w-38px { width: 38px; }
  .h-38px { height: 38px; }
</style>
@endpush
