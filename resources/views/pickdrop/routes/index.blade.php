@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Route Management</h4>
    <p class="text-secondary mb-0">Create and manage transportation routes</p>
  </div>
</div>

{{-- Tabs + Create Button --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <ul class="nav nav-pills gap-1" id="routeTabs">
    <li class="nav-item">
      <a class="nav-link active px-4" href="#" data-filter="all">All Routes</a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-4" href="#" data-filter="morning">Morning</a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-4" href="#" data-filter="afternoon">Afternoon</a>
    </li>
  </ul>
  <a href="{{ route('routes.create') }}" class="btn btn-primary">
    <i data-lucide="plus" class="icon-sm me-1"></i> Create Route
  </a>
</div>

{{-- Routes Table --}}
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Route</th>
            <th class="py-3">Destination</th>
            <th class="py-3">Vehicle</th>
            <th class="py-3">Students</th>
            <th class="py-3">Timing</th>
            <th class="py-3">Stops</th>
            <th class="py-3">Shift</th>
            <th class="py-3 text-end pe-4">Actions</th>
          </tr>
        </thead>
        <tbody id="routesTableBody">

          {{-- Row 1 --}}
          <tr class="route-item" data-shift="morning">
            <td class="ps-4">
              <div class="d-flex align-items-center gap-2">
                <div class="w-36px h-36px rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="background:rgba(var(--bs-primary-rgb),0.1);">
                  <i data-lucide="map" class="text-primary" style="width:16px;height:16px;"></i>
                </div>
                <div>
                  <p class="mb-0 fw-semibold fs-14px">#R-123</p>
                  <small class="text-secondary">Central Elementary Morning</small>
                </div>
              </div>
            </td>
            <td>
              <span class="fs-13px">Central Elementary School</span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i data-lucide="bus" class="text-info" style="width:14px;height:14px;"></i>
                <span class="fs-13px">Bus #45</span>
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i data-lucide="users" class="text-success" style="width:14px;height:14px;"></i>
                <span class="fs-13px">12 Students</span>
              </div>
            </td>
            <td>
              <span class="fs-13px">7:45 AM – 8:45 AM</span>
            </td>
            <td>
              <button class="btn btn-sm btn-light px-2 py-1 fs-12px" data-bs-toggle="modal" data-bs-target="#viewStopsModal">
                <i data-lucide="map-pin" style="width:12px;height:12px;" class="me-1"></i>6 Stops
              </button>
            </td>
            <td>
              <span class="badge rounded-pill px-3 py-1" style="background:#dbeafe;color:#1d4ed8;">Morning</span>
            </td>
            <td class="text-end pe-4">
              <div class="d-flex align-items-center justify-content-end gap-2">
                <button class="btn btn-sm btn-light" title="View Stops" data-bs-toggle="modal" data-bs-target="#viewStopsModal">
                  <i data-lucide="eye" style="width:14px;height:14px;"></i>
                </button>
                <a href="{{ url('routes/123/edit') }}" class="btn btn-sm btn-success px-3">
                  <i data-lucide="edit-2" class="icon-xs me-1"></i> Edit
                </a>
                <button class="btn btn-sm btn-light text-danger" title="Delete">
                  <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
              </div>
            </td>
          </tr>

          {{-- Row 2 --}}
          <tr class="route-item" data-shift="afternoon">
            <td class="ps-4">
              <div class="d-flex align-items-center gap-2">
                <div class="w-36px h-36px rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="background:rgba(var(--bs-warning-rgb),0.1);">
                  <i data-lucide="map" class="text-warning" style="width:16px;height:16px;"></i>
                </div>
                <div>
                  <p class="mb-0 fw-semibold fs-14px">#R-124</p>
                  <small class="text-secondary">Gulshan Afternoon Drop</small>
                </div>
              </div>
            </td>
            <td>
              <span class="fs-13px">Gulshan Roundabout</span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i data-lucide="bus" class="text-info" style="width:14px;height:14px;"></i>
                <span class="fs-13px">Bus #32</span>
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i data-lucide="users" class="text-success" style="width:14px;height:14px;"></i>
                <span class="fs-13px">9 Students</span>
              </div>
            </td>
            <td>
              <span class="fs-13px">3:30 PM – 4:30 PM</span>
            </td>
            <td>
              <button class="btn btn-sm btn-light px-2 py-1 fs-12px" data-bs-toggle="modal" data-bs-target="#viewStopsModal">
                <i data-lucide="map-pin" style="width:12px;height:12px;" class="me-1"></i>4 Stops
              </button>
            </td>
            <td>
              <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7;color:#92400e;">Afternoon</span>
            </td>
            <td class="text-end pe-4">
              <div class="d-flex align-items-center justify-content-end gap-2">
                <button class="btn btn-sm btn-light" title="View Stops" data-bs-toggle="modal" data-bs-target="#viewStopsModal">
                  <i data-lucide="eye" style="width:14px;height:14px;"></i>
                </button>
                <a href="{{ url('routes/124/edit') }}" class="btn btn-sm btn-success px-3">
                  <i data-lucide="edit-2" class="icon-xs me-1"></i> Edit
                </a>
                <button class="btn btn-sm btn-light text-danger" title="Delete">
                  <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
              </div>
            </td>
          </tr>

          {{-- Row 3 --}}
          <tr class="route-item" data-shift="morning">
            <td class="ps-4">
              <div class="d-flex align-items-center gap-2">
                <div class="w-36px h-36px rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="background:rgba(var(--bs-success-rgb),0.1);">
                  <i data-lucide="map" class="text-success" style="width:16px;height:16px;"></i>
                </div>
                <div>
                  <p class="mb-0 fw-semibold fs-14px">#R-125</p>
                  <small class="text-secondary">North Side Morning Pickup</small>
                </div>
              </div>
            </td>
            <td>
              <span class="fs-13px">North Academy</span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i data-lucide="truck" class="text-info" style="width:14px;height:14px;"></i>
                <span class="fs-13px">Van #12</span>
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i data-lucide="users" class="text-success" style="width:14px;height:14px;"></i>
                <span class="fs-13px">7 Students</span>
              </div>
            </td>
            <td>
              <span class="fs-13px">8:00 AM – 9:00 AM</span>
            </td>
            <td>
              <button class="btn btn-sm btn-light px-2 py-1 fs-12px" data-bs-toggle="modal" data-bs-target="#viewStopsModal">
                <i data-lucide="map-pin" style="width:12px;height:12px;" class="me-1"></i>5 Stops
              </button>
            </td>
            <td>
              <span class="badge rounded-pill px-3 py-1" style="background:#dbeafe;color:#1d4ed8;">Morning</span>
            </td>
            <td class="text-end pe-4">
              <div class="d-flex align-items-center justify-content-end gap-2">
                <button class="btn btn-sm btn-light" title="View Stops" data-bs-toggle="modal" data-bs-target="#viewStopsModal">
                  <i data-lucide="eye" style="width:14px;height:14px;"></i>
                </button>
                <a href="{{ url('routes/125/edit') }}" class="btn btn-sm btn-success px-3">
                  <i data-lucide="edit-2" class="icon-xs me-1"></i> Edit
                </a>
                <button class="btn btn-sm btn-light text-danger" title="Delete">
                  <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
              </div>
            </td>
          </tr>

          {{-- Row 4 --}}
          <tr class="route-item" data-shift="afternoon">
            <td class="ps-4">
              <div class="d-flex align-items-center gap-2">
                <div class="w-36px h-36px rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="background:rgba(var(--bs-danger-rgb),0.1);">
                  <i data-lucide="map" class="text-danger" style="width:16px;height:16px;"></i>
                </div>
                <div>
                  <p class="mb-0 fw-semibold fs-14px">#R-126</p>
                  <small class="text-secondary">Defence Afternoon Drop</small>
                </div>
              </div>
            </td>
            <td>
              <span class="fs-13px">DHA Phase 5</span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i data-lucide="bus" class="text-info" style="width:14px;height:14px;"></i>
                <span class="fs-13px">Bus #18</span>
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i data-lucide="users" class="text-success" style="width:14px;height:14px;"></i>
                <span class="fs-13px">11 Students</span>
              </div>
            </td>
            <td>
              <span class="fs-13px">2:30 PM – 3:30 PM</span>
            </td>
            <td>
              <button class="btn btn-sm btn-light px-2 py-1 fs-12px" data-bs-toggle="modal" data-bs-target="#viewStopsModal">
                <i data-lucide="map-pin" style="width:12px;height:12px;" class="me-1"></i>7 Stops
              </button>
            </td>
            <td>
              <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7;color:#92400e;">Afternoon</span>
            </td>
            <td class="text-end pe-4">
              <div class="d-flex align-items-center justify-content-end gap-2">
                <button class="btn btn-sm btn-light" title="View Stops" data-bs-toggle="modal" data-bs-target="#viewStopsModal">
                  <i data-lucide="eye" style="width:14px;height:14px;"></i>
                </button>
                <a href="{{ url('routes/126/edit') }}" class="btn btn-sm btn-success px-3">
                  <i data-lucide="edit-2" class="icon-xs me-1"></i> Edit
                </a>
                <button class="btn btn-sm btn-light text-danger" title="Delete">
                  <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
              </div>
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>
</div>


{{-- ========== VIEW STOPS MODAL ========== --}}
<div class="modal fade" id="viewStopsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Route Stops – #R-123</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-secondary fs-13px mb-3">Central Elementary Morning · 7:45 AM – 8:45 AM</p>
        <div class="d-flex flex-column gap-3">
          <div class="d-flex align-items-start gap-3">
            <div class="d-flex flex-column align-items-center">
              <div class="w-8px h-8px rounded-circle bg-primary mt-1 flex-shrink-0"></div>
              <div class="route-line flex-grow-1"></div>
            </div>
            <div class="pb-2 w-100 border-bottom">
              <p class="mb-0 fw-semibold fs-13px">123 Main St</p>
              <div class="d-flex gap-3 mt-1">
                <small class="text-secondary"><i data-lucide="clock" style="width:11px;height:11px;" class="me-1"></i>8:05 AM</small>
                <small class="text-secondary"><i data-lucide="users" style="width:11px;height:11px;" class="me-1"></i>2 students</small>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3">
            <div class="d-flex flex-column align-items-center">
              <div class="w-8px h-8px rounded-circle bg-primary mt-1 flex-shrink-0"></div>
              <div class="route-line flex-grow-1"></div>
            </div>
            <div class="pb-2 w-100 border-bottom">
              <p class="mb-0 fw-semibold fs-13px">456 Oak Ave</p>
              <div class="d-flex gap-3 mt-1">
                <small class="text-secondary"><i data-lucide="clock" style="width:11px;height:11px;" class="me-1"></i>8:18 AM</small>
                <small class="text-secondary"><i data-lucide="users" style="width:11px;height:11px;" class="me-1"></i>3 students</small>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3">
            <div class="d-flex flex-column align-items-center">
              <div class="w-8px h-8px rounded-circle bg-primary mt-1 flex-shrink-0"></div>
              <div class="route-line flex-grow-1"></div>
            </div>
            <div class="pb-2 w-100 border-bottom">
              <p class="mb-0 fw-semibold fs-13px">789 Pine Blvd</p>
              <div class="d-flex gap-3 mt-1">
                <small class="text-secondary"><i data-lucide="clock" style="width:11px;height:11px;" class="me-1"></i>8:26 AM</small>
                <small class="text-secondary"><i data-lucide="users" style="width:11px;height:11px;" class="me-1"></i>2 students</small>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3">
            <div class="d-flex flex-column align-items-center">
              <div class="w-8px h-8px rounded-circle bg-primary mt-1 flex-shrink-0"></div>
              <div class="route-line flex-grow-1"></div>
            </div>
            <div class="pb-2 w-100 border-bottom">
              <p class="mb-0 fw-semibold fs-13px">22 Elm Street</p>
              <div class="d-flex gap-3 mt-1">
                <small class="text-secondary"><i data-lucide="clock" style="width:11px;height:11px;" class="me-1"></i>8:33 AM</small>
                <small class="text-secondary"><i data-lucide="users" style="width:11px;height:11px;" class="me-1"></i>2 students</small>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3">
            <div class="d-flex flex-column align-items-center">
              <div class="w-8px h-8px rounded-circle bg-primary mt-1 flex-shrink-0"></div>
              <div class="route-line flex-grow-1"></div>
            </div>
            <div class="pb-2 w-100 border-bottom">
              <p class="mb-0 fw-semibold fs-13px">55 Maple Dr</p>
              <div class="d-flex gap-3 mt-1">
                <small class="text-secondary"><i data-lucide="clock" style="width:11px;height:11px;" class="me-1"></i>8:40 AM</small>
                <small class="text-secondary"><i data-lucide="users" style="width:11px;height:11px;" class="me-1"></i>1 student</small>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3">
            <div>
              <div class="w-8px h-8px rounded-circle bg-success mt-1 flex-shrink-0"></div>
            </div>
            <div class="w-100">
              <p class="mb-0 fw-semibold fs-13px text-success">Central Elementary School</p>
              <div class="d-flex gap-3 mt-1">
                <small class="text-secondary"><i data-lucide="clock" style="width:11px;height:11px;" class="me-1"></i>8:45 AM</small>
                <small class="text-secondary fw-semibold">Final Destination</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editRouteModal">Edit Route</button>
      </div>
    </div>
  </div>
</div>


{{-- ========== CREATE ROUTE MODAL ========== --}}
<div class="modal fade" id="createRouteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Create New Route</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold">Route Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="e.g. Central Elementary Morning">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Shift</label>
            <select class="form-select">
              <option>Morning</option>
              <option>Afternoon</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Assign Vehicle</label>
            <select class="form-select">
              <option>Bus #45</option>
              <option>Bus #32</option>
              <option>Van #12</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Start Time</label>
            <input type="time" class="form-control" value="07:45">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">End Time</label>
            <input type="time" class="form-control" value="08:45">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Destination</label>
            <input type="text" class="form-control" placeholder="e.g. Central Elementary School">
          </div>

          {{-- Stops Section --}}
          <div class="col-12 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
              <label class="form-label fw-semibold mb-0">Route Stops</label>
              <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" onclick="addStopField('create-stops-container')">
                <i data-lucide="plus" class="icon-xs me-1"></i> Add Stop
              </button>
            </div>
            <div id="create-stops-container" class="d-flex flex-column gap-2 overflow-auto" style="max-height: 250px;">
              <div class="route-stop-row d-flex gap-2 align-items-start p-2 bg-light rounded border">
                <div class="flex-grow-1 row g-2">
                  <div class="col-md-5">
                    <label class="form-label fs-13px text-secondary mb-1">Stop Name / Location</label>
                    <input type="text" class="form-control form-control-sm" placeholder="Location">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fs-13px text-secondary mb-1">Arrival Time</label>
                    <input type="time" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fs-13px text-secondary mb-1">Assign Students</label>
                    <select class="form-select form-select-sm" multiple data-placeholder="Select students">
                      <option>John Doe</option>
                      <option>Jane Smith</option>
                      <option>Alan Wake</option>
                      <option>Sarah Connor</option>
                    </select>
                  </div>
                </div>
                <button type="button" class="btn btn-sm btn-light text-danger mt-4" onclick="this.closest('.route-stop-row').remove()">
                  <i data-lucide="trash-2" class="icon-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary px-4">Create Route</button>
      </div>
    </div>
  </div>
</div>

{{-- ========== EDIT ROUTE MODAL ========== --}}
<div class="modal fade" id="editRouteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Edit Route – #R-123</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold">Route Name</label>
            <input type="text" class="form-control" value="Central Elementary Morning">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Shift</label>
            <select class="form-select"><option selected>Morning</option><option>Afternoon</option></select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Assign Vehicle</label>
            <select class="form-select"><option selected>Bus #45</option><option>Bus #32</option></select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Start Time</label>
            <input type="time" class="form-control" value="07:45">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">End Time</label>
            <input type="time" class="form-control" value="08:45">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Destination</label>
            <input type="text" class="form-control" value="Central Elementary School">
          </div>

          {{-- Stops Section --}}
          <div class="col-12 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
              <label class="form-label fw-semibold mb-0">Route Stops</label>
              <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" onclick="addStopField('edit-stops-container')">
                <i data-lucide="plus" class="icon-xs me-1"></i> Add Stop
              </button>
            </div>
            <div id="edit-stops-container" class="d-flex flex-column gap-2 overflow-auto" style="max-height: 250px;">
              <div class="route-stop-row d-flex gap-2 align-items-start p-2 bg-light rounded border">
                <div class="flex-grow-1 row g-2">
                  <div class="col-md-5">
                    <label class="form-label fs-13px text-secondary mb-1">Stop Name / Location</label>
                    <input type="text" class="form-control form-control-sm" value="123 Main St">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fs-13px text-secondary mb-1">Arrival Time</label>
                    <input type="time" class="form-control form-control-sm" value="08:05">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fs-13px text-secondary mb-1">Assign Students</label>
                    <select class="form-select form-select-sm" multiple data-placeholder="Select students">
                      <option selected>John Doe</option>
                      <option selected>Jane Smith</option>
                      <option>Alan Wake</option>
                      <option>Sarah Connor</option>
                    </select>
                  </div>
                </div>
                <button type="button" class="btn btn-sm btn-light text-danger mt-4" onclick="this.closest('.route-stop-row').remove()">
                  <i data-lucide="trash-2" class="icon-xs"></i>
                </button>
              </div>
              <div class="route-stop-row d-flex gap-2 align-items-start p-2 bg-light rounded border">
                <div class="flex-grow-1 row g-2">
                  <div class="col-md-5">
                    <label class="form-label fs-13px text-secondary mb-1">Stop Name / Location</label>
                    <input type="text" class="form-control form-control-sm" value="456 Oak Ave">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fs-13px text-secondary mb-1">Arrival Time</label>
                    <input type="time" class="form-control form-control-sm" value="08:18">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fs-13px text-secondary mb-1">Assign Students</label>
                    <select class="form-select form-select-sm" multiple data-placeholder="Select students">
                      <option>John Doe</option>
                      <option>Jane Smith</option>
                      <option selected>Alan Wake</option>
                      <option>Sarah Connor</option>
                    </select>
                  </div>
                </div>
                <button type="button" class="btn btn-sm btn-light text-danger mt-4" onclick="this.closest('.route-stop-row').remove()">
                  <i data-lucide="trash-2" class="icon-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary px-4">Save Changes</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('custom-scripts')
<script>
  document.querySelectorAll('#routeTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelectorAll('#routeTabs .nav-link').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      const filter = this.dataset.filter;
      document.querySelectorAll('.route-item').forEach(item => {
        item.style.display = (filter === 'all' || item.dataset.shift === filter) ? '' : 'none';
      });
    });
  });

  function addStopField(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const stopHtml = `
      <div class="route-stop-row d-flex gap-2 align-items-start p-2 bg-light rounded border">
        <div class="flex-grow-1 row g-2">
          <div class="col-md-5">
            <label class="form-label fs-13px text-secondary mb-1">Stop Name / Location</label>
            <input type="text" class="form-control form-control-sm" placeholder="Location">
          </div>
          <div class="col-md-3">
            <label class="form-label fs-13px text-secondary mb-1">Arrival Time</label>
            <input type="time" class="form-control form-control-sm">
          </div>
          <div class="col-md-4">
            <label class="form-label fs-13px text-secondary mb-1">Assign Students</label>
            <select class="form-select form-select-sm" multiple data-placeholder="Select students">
              <option>John Doe</option>
              <option>Jane Smith</option>
              <option>Alan Wake</option>
              <option>Sarah Connor</option>
            </select>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-light text-danger mt-4" onclick="this.closest('.route-stop-row').remove()">
          <i data-lucide="trash-2" class="icon-xs"></i>
        </button>
      </div>`;
    container.insertAdjacentHTML('beforeend', stopHtml);
    if(typeof lucide !== 'undefined') lucide.createIcons();
  }
</script>
@endpush

@push('style')
<style>
  .w-36px { width: 36px; }
  .h-36px { height: 36px; }
  .w-8px  { width: 8px; min-width: 8px; }
  .h-8px  { height: 8px; }
  .fs-14px { font-size: 14px; }
  .fs-12px { font-size: 12px; }
  .route-line {
    width: 2px;
    background: #dee2e6;
    min-height: 20px;
    margin: 4px auto;
  }
</style>
@endpush
