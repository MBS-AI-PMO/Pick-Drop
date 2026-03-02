@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Vehicle Management</h4>
    <p class="text-secondary mb-0">Monitor and manage all vehicles</p>
  </div>
</div>

{{-- Tabs + Add Button --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <ul class="nav nav-pills gap-1" id="vehicleTabs">
    <li class="nav-item">
      <a class="nav-link active px-4" href="#" data-filter="all">All Vehicles</a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-4" href="#" data-filter="active">Active</a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-4" href="#" data-filter="inactive">Inactive</a>
    </li>
  </ul>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
    <i data-lucide="plus" class="icon-sm me-1"></i> Add Vehicle
  </button>
</div>

{{-- Vehicle Cards Grid --}}
<div class="row g-3" id="vehiclesGrid">

  {{-- Bus #45 --}}
  <div class="col-md-6 col-xl-4 vehicle-card" data-status="active">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="w-38px h-38px rounded-2 d-flex align-items-center justify-content-center" style="background:rgba(var(--bs-primary-rgb),0.1);">
              <i data-lucide="bus" class="text-primary" style="width:20px;height:20px;"></i>
            </div>
            <h6 class="mb-0 fw-bold">Bus #45</h6>
          </div>
          <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
        </div>
        <div class="d-flex flex-column gap-2 mb-3">
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="user" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Driver:</span>
            <span class="fs-13px">John Smith</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="map" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Route:</span>
            <span class="fs-13px">Route #R-123</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="map-pin" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Last Location:</span>
            <span class="fs-13px">Central Elementary School</span>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <div class="d-flex align-items-center gap-1">
              <i data-lucide="battery" class="text-secondary" style="width:15px;height:15px;"></i>
              <span class="text-secondary fs-12px">GPS Battery</span>
            </div>
            <span class="fs-12px fw-semibold">85%</span>
          </div>
          <div class="progress" style="height:6px;">
            <div class="progress-bar bg-success" style="width:85%;"></div>
          </div>
        </div>
        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#viewVehicleModal">
          View Details
        </button>
      </div>
    </div>
  </div>

  {{-- Bus #32 --}}
  <div class="col-md-6 col-xl-4 vehicle-card" data-status="active">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="w-38px h-38px rounded-2 d-flex align-items-center justify-content-center" style="background:rgba(var(--bs-primary-rgb),0.1);">
              <i data-lucide="bus" class="text-primary" style="width:20px;height:20px;"></i>
            </div>
            <h6 class="mb-0 fw-bold">Bus #32</h6>
          </div>
          <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
        </div>
        <div class="d-flex flex-column gap-2 mb-3">
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="user" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Driver:</span>
            <span class="fs-13px">Robert Wilson</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="map" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Route:</span>
            <span class="fs-13px">Route #R-124</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="map-pin" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Last Location:</span>
            <span class="fs-13px">456 Oak Avenue</span>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <div class="d-flex align-items-center gap-1">
              <i data-lucide="battery" class="text-secondary" style="width:15px;height:15px;"></i>
              <span class="text-secondary fs-12px">GPS Battery</span>
            </div>
            <span class="fs-12px fw-semibold">72%</span>
          </div>
          <div class="progress" style="height:6px;">
            <div class="progress-bar bg-success" style="width:72%;"></div>
          </div>
        </div>
        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#viewVehicleModal">
          View Details
        </button>
      </div>
    </div>
  </div>

  {{-- Bus #18 --}}
  <div class="col-md-6 col-xl-4 vehicle-card" data-status="inactive">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="w-38px h-38px rounded-2 d-flex align-items-center justify-content-center" style="background:rgba(var(--bs-secondary-rgb),0.1);">
              <i data-lucide="bus" class="text-secondary" style="width:20px;height:20px;"></i>
            </div>
            <h6 class="mb-0 fw-bold">Bus #18</h6>
          </div>
          <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">Inactive</span>
        </div>
        <div class="d-flex flex-column gap-2 mb-3">
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="user" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Driver:</span>
            <span class="fs-13px">Mary Johnson</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="map" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Route:</span>
            <span class="fs-13px">Route #R-125</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="map-pin" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Last Location:</span>
            <span class="fs-13px">Bus Depot</span>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <div class="d-flex align-items-center gap-1">
              <i data-lucide="battery-low" class="text-danger" style="width:15px;height:15px;"></i>
              <span class="text-secondary fs-12px">GPS Battery</span>
            </div>
            <span class="fs-12px fw-semibold text-danger">30%</span>
          </div>
          <div class="progress" style="height:6px;">
            <div class="progress-bar bg-danger" style="width:30%;"></div>
          </div>
        </div>
        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#viewVehicleModal">
          View Details
        </button>
      </div>
    </div>
  </div>

  {{-- Van #12 --}}
  <div class="col-md-6 col-xl-4 vehicle-card" data-status="active">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="w-38px h-38px rounded-2 d-flex align-items-center justify-content-center" style="background:rgba(var(--bs-primary-rgb),0.1);">
              <i data-lucide="truck" class="text-primary" style="width:20px;height:20px;"></i>
            </div>
            <h6 class="mb-0 fw-bold">Van #12</h6>
          </div>
          <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
        </div>
        <div class="d-flex flex-column gap-2 mb-3">
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="user" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Driver:</span>
            <span class="fs-13px">David Thompson</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="map" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Route:</span>
            <span class="fs-13px">Route #R-126</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="map-pin" class="text-secondary flex-shrink-0" style="width:15px;height:15px;"></i>
            <span class="text-secondary fs-13px">Last Location:</span>
            <span class="fs-13px">789 Pine Boulevard</span>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <div class="d-flex align-items-center gap-1">
              <i data-lucide="battery" class="text-secondary" style="width:15px;height:15px;"></i>
              <span class="text-secondary fs-12px">GPS Battery</span>
            </div>
            <span class="fs-12px fw-semibold">65%</span>
          </div>
          <div class="progress" style="height:6px;">
            <div class="progress-bar bg-success" style="width:65%;"></div>
          </div>
        </div>
        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#viewVehicleModal">
          View Details
        </button>
      </div>
    </div>
  </div>

</div>{{-- /vehiclesGrid --}}


{{-- ========== ADD VEHICLE MODAL ========== --}}
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="addVehicleModalLabel">Add New Vehicle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="e.g. Bus #46">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Plate Number <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="e.g. ABC-1234">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Type</label>
              <select class="form-select">
                <option>Bus</option>
                <option>Van</option>
                <option>Mini Bus</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Capacity</label>
              <input type="number" class="form-control" placeholder="e.g. 28">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Assign Driver</label>
              <select class="form-select">
                <option value="">Select driver</option>
                <option>John Smith</option>
                <option>Robert Wilson</option>
                <option>David Thompson</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Assign Route</label>
              <select class="form-select">
                <option value="">Select route</option>
                <option>Route #R-123</option>
                <option>Route #R-124</option>
                <option>Route #R-125</option>
                <option>Route #R-126</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select">
                <option>Active</option>
                <option>Inactive</option>
                <option>Maintenance</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary px-4">Add Vehicle</button>
      </div>
    </div>
  </div>
</div>

{{-- ========== VIEW VEHICLE MODAL ========== --}}
<div class="modal fade" id="viewVehicleModal" tabindex="-1" aria-labelledby="viewVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="viewVehicleModalLabel">Vehicle Details – Bus #45</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Vehicle</p>
            <p class="fw-semibold mb-0">Bus #45</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Plate</p>
            <p class="fw-semibold mb-0">ABC-1234</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Driver</p>
            <p class="fw-semibold mb-0">John Smith</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Capacity</p>
            <p class="fw-semibold mb-0">28 Seats</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Route</p>
            <p class="fw-semibold mb-0">Route #R-123</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Status</p>
            <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
          </div>
          <div class="col-12">
            <p class="text-secondary fs-12px mb-1">Last Location</p>
            <p class="fw-semibold mb-0">Central Elementary School</p>
          </div>
          <div class="col-12">
            <p class="text-secondary fs-12px mb-1">GPS Battery</p>
            <div class="d-flex align-items-center gap-2">
              <div class="progress flex-grow-1" style="height:8px;">
                <div class="progress-bar bg-success" style="width:85%;"></div>
              </div>
              <span class="fw-semibold fs-13px">85%</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary px-4">Edit Vehicle</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('custom-scripts')
<script>
  // Tab filter
  document.querySelectorAll('#vehicleTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelectorAll('#vehicleTabs .nav-link').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      const filter = this.dataset.filter;
      document.querySelectorAll('.vehicle-card').forEach(card => {
        card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
      });
    });
  });
</script>
@endpush

@push('style')
<style>
  .w-38px { width: 38px; }
  .h-38px { height: 38px; }
</style>
@endpush
