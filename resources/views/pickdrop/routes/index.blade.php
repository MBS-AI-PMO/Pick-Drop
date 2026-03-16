@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Route Management</h4>
    <p class="text-secondary mb-0">Create and manage transportation routes</p>
  </div>
</div>

{{-- Filters + Create Button (match users index style) --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('routes.index') }}" id="routeFilterForm">
      <div class="row g-2 align-items-center">

        {{-- Search --}}
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="routeSearch" placeholder="Search routes..."
                   value="{{ request('search') }}">
          </div>
        </div>

        {{-- Shift Filter --}}
        <div class="col-12 col-md-3">
          <select class="form-select" name="shift" id="shiftFilter" onchange="this.form.submit()">
            <option value="all" {{ request('shift', 'all') === 'all' ? 'selected' : '' }}>All Shifts</option>
            <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
            <option value="afternoon" {{ request('shift') === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
          </select>
        </div>

        {{-- Filter / Clear --}}
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          @if(request('search') || (request('shift') && request('shift') !== 'all'))
            <a href="{{ route('routes.index') }}" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          @endif
        </div>

        {{-- Spacer --}}
        <div class="col"></div>

        {{-- Create Button --}}
        <div class="col-auto">
          <a href="{{ route('routes.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="icon-sm me-1"></i> Create Route
          </a>
        </div>
      </div>
    </form>
  </div>
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

          @forelse($routes as $route)
          <tr>
            <td class="ps-4 py-3 text-muted">{{ $routes->firstItem() + $loop->index }}</td>
            <td class="ps-2">
              <div class="d-flex align-items-center gap-2">
                <div class="w-36px h-36px rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="background:rgba(var(--bs-primary-rgb),0.1);">
                  <i data-lucide="map" class="text-primary" style="width:16px;height:16px;"></i>
                </div>
                <div>
                  <p class="mb-0 fw-semibold fs-14px">{{ $route->code ?? ('#R-'.$route->id) }}</p>
                  <small class="text-secondary">{{ $route->name }}</small>
                </div>
              </div>
            </td>
            <td>
              <span class="fs-13px">{{ $route->destination ?? '—' }}</span>
            </td>
            <td>
              @if($route->vehicle)
                <div class="d-flex align-items-center gap-1">
                  <i data-lucide="bus" class="text-info" style="width:14px;height:14px;"></i>
                  <span class="fs-13px">{{ $route->vehicle->name }} ({{ $route->vehicle->license_plate }})</span>
                </div>
              @else
                <span class="text-secondary fs-13px">—</span>
              @endif
            </td>
            <td>
              @if($route->start_time && $route->end_time)
                <span class="fs-13px">
                  {{ \Carbon\Carbon::parse($route->start_time)->format('g:i A') }}
                  –
                  {{ \Carbon\Carbon::parse($route->end_time)->format('g:i A') }}
                </span>
              @else
                <span class="fs-13px text-secondary">—</span>
              @endif
            </td>
            <td>
              <button class="btn btn-sm btn-light px-2 py-1 fs-12px"
                      data-bs-toggle="modal"
                      data-bs-target="#viewStopsModal"
                      data-route-id="{{ $route->id }}">
                <i data-lucide="map-pin" style="width:12px;height:12px;" class="me-1"></i>
                {{ $route->stops_count }} Stops
              </button>
            </td>
            <td>
              @php $shift = strtolower($route->shift); @endphp
              @if($shift === 'morning')
                <span class="badge rounded-pill px-3 py-1" style="background:#dbeafe;color:#1d4ed8;">Morning</span>
              @elseif($shift === 'afternoon')
                <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7;color:#92400e;">Afternoon</span>
              @else
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">{{ $route->shift }}</span>
              @endif
            </td>
            <td class="text-end pe-4">
              <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="{{ route('routes.edit', $route) }}" class="btn btn-sm btn-success px-3">
                  <i data-lucide="edit-2" class="icon-xs me-1"></i> Edit
                </a>
                <form action="{{ route('routes.destroy', $route) }}" method="POST" class="d-inline m-0 p-0"
                      onsubmit="confirmRouteDelete(event, this)">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="map" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Routes Found</h6>
              <p class="text-muted small mb-0">Try adjusting your search or filters.</p>
            </td>
          </tr>
          @endforelse

        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Pagination Footer --}}
@if($routes->hasPages())
  <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-3">
    <small class="text-muted">
      Showing {{ $routes->firstItem() }}–{{ $routes->lastItem() }} of {{ $routes->total() }} routes
    </small>
    <div>
      {{ $routes->links('pagination::bootstrap-5') }}
    </div>
  </div>
@endif


{{-- ========== VIEW STOPS MODAL (READ-ONLY, STOPS MANAGED VIA MOBILE APP) ========== --}}
<div class="modal fade" id="viewStopsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="stopsModalTitle">Route Stops</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-secondary fs-13px mb-3" id="stopsModalSubtitle">
          Stops on this route (added via mobile app by parents/students).
        </p>
        <div class="d-flex flex-column gap-3" id="stopsModalBody">
          {{-- Stops will be injected via JS / backend in future --}}
          <p class="text-muted fs-13px mb-0">No stop details loaded.</p>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
  const routeSearch = document.getElementById('routeSearch');
  if (routeSearch) {
    routeSearch.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') document.getElementById('routeFilterForm').submit();
    });
  }

  function confirmRouteDelete(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Are you sure?',
      text: "This will permanently delete the route.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then(result => { if (result.isConfirmed) form.submit(); });
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
