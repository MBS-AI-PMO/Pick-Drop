@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-secondary">Route Management</a></li>
        <li class="breadcrumb-item active">Edit Route</li>
      </ol>
    </nav>
    <h4 class="mb-1">Edit Route <span class="text-secondary fw-normal">#R-123</span></h4>
    <p class="text-secondary mb-0">Update route details, stops and student assignments</p>
  </div>
  <a href="{{ route('routes.index') }}" class="btn btn-light">
    <i data-lucide="arrow-left" class="icon-sm me-1"></i> Back to Routes
  </a>
</div>

<form method="POST" action="{{ route('routes.update', $route) }}">
  @csrf
  @method('PUT')
  <div class="row g-4">

    {{-- Left Column: Route Info --}}
    <div class="col-lg-8">

      {{-- Basic Info Card --}}
      <div class="card mb-4">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2">
          <div class="w-32px h-32px rounded-2 d-flex align-items-center justify-content-center" style="background:rgba(var(--bs-primary-rgb),0.1);">
            <i data-lucide="map" class="text-primary" style="width:16px;height:16px;"></i>
          </div>
          <h6 class="mb-0 fw-bold">Route Information</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Route Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="{{ old('name', $route->name) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
              <select class="form-select" name="shift" required>
                @php $shiftVal = old('shift', $route->shift); @endphp
                <option value="morning" {{ $shiftVal === 'morning' ? 'selected' : '' }}>Morning</option>
                <option value="afternoon" {{ $shiftVal === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Assign Vehicle <span class="text-danger">*</span></label>
              <select class="form-select" name="vehicle_id">
                <option value="">Select vehicle</option>
                @foreach($vehicles as $vehicle)
                  <option value="{{ $vehicle->id }}"
                          {{ (int) old('vehicle_id', $route->vehicle_id) === $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->name }} ({{ $vehicle->license_plate }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
              <input type="time" name="start_time" class="form-control"
                     value="{{ old('start_time', $route->start_time ? \Carbon\Carbon::parse($route->start_time)->format('H:i') : '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
              <input type="time" name="end_time" class="form-control"
                     value="{{ old('end_time', $route->end_time ? \Carbon\Carbon::parse($route->end_time)->format('H:i') : '') }}">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Final Destination <span class="text-danger">*</span></label>
              <input type="text" name="destination" class="form-control"
                     value="{{ old('destination', $route->destination) }}" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description <span class="text-secondary fw-normal">(optional)</span></label>
              <textarea class="form-control" name="description" rows="2" placeholder="Add any notes about this route...">{{ old('description', $route->description) }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- Route Stops Card --}}
      <div class="card">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="w-32px h-32px rounded-2 d-flex align-items-center justify-content-center" style="background:rgba(var(--bs-success-rgb),0.1);">
              <i data-lucide="map-pin" class="text-success" style="width:16px;height:16px;"></i>
            </div>
            <h6 class="mb-0 fw-bold">Route Stops</h6>
          </div>
          <span class="badge bg-light text-secondary fs-12px">
            Managed by parents/students via mobile app (read-only here)
          </span>
        </div>
        <div class="card-body">
          <div id="stopsContainer" class="d-flex flex-column gap-3">
            @forelse($route->stops as $index => $stop)
              <div class="stop-row border rounded-3 p-3 bg-light">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <span class="stop-number badge bg-primary rounded-pill px-2 py-1 fs-12px">
                    Stop {{ $index + 1 }}
                  </span>
                </div>
                <div class="row g-2">
                  <div class="col-md-6">
                    <p class="form-label fs-13px text-secondary mb-1">Stop Name / Location</p>
                    <p class="mb-0 fw-semibold">{{ $stop->name }}</p>
                  </div>
                  <div class="col-md-3">
                    <p class="form-label fs-13px text-secondary mb-1">Arrival Time</p>
                    <p class="mb-0">
                      {{ $stop->arrival_time ? \Carbon\Carbon::parse($stop->arrival_time)->format('g:i A') : '—' }}
                    </p>
                  </div>
                  <div class="col-md-3">
                    <p class="form-label fs-13px text-secondary mb-1">Order</p>
                    <p class="mb-0">#{{ $stop->order }}</p>
                  </div>
                </div>
              </div>
            @empty
              <p class="text-secondary fs-13px mb-0">
                No stops have been added yet. Parents and students will add stops from the mobile app.
              </p>
            @endforelse
          </div>
        </div>
      </div>

    </div>

    {{-- Right Column: Summary & Actions --}}
    <div class="col-lg-4">

      {{-- Route Summary Card --}}
      <div class="card mb-4">
        <div class="card-header border-bottom py-3">
          <h6 class="mb-0 fw-bold">Route Summary</h6>
        </div>
        <div class="card-body">
          {{-- Mini Map --}}
          <div class="rounded-3 d-flex flex-column align-items-center justify-content-center text-center mb-3"
            style="height:180px; background:linear-gradient(135deg,#e8f0fe 0%,#f0f4ff 100%); position:relative;">
            <svg width="100%" height="100%" style="position:absolute;top:0;left:0;opacity:0.2;">
              <defs>
                <pattern id="editGrid" width="30" height="30" patternUnits="userSpaceOnUse">
                  <path d="M 30 0 L 0 0 0 30" fill="none" stroke="#94a3b8" stroke-width="0.5"/>
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill="url(#editGrid)"/>
            </svg>
            <svg width="100%" height="100%" style="position:absolute;top:0;left:0;">
              <polyline points="40,140 130,110 240,70 330,45"
                fill="none" stroke="#3b5bdb" stroke-width="2" stroke-dasharray="5,3"/>
              <circle cx="40" cy="140" r="5" fill="#3b5bdb"/>
              <circle cx="130" cy="110" r="5" fill="#3b5bdb"/>
              <circle cx="240" cy="70" r="5" fill="#3b5bdb"/>
              <circle cx="330" cy="45" r="7" fill="#22c55e" stroke="white" stroke-width="2"/>
            </svg>
            <i data-lucide="map" style="width:28px;height:28px;color:#3b5bdb;position:relative;z-index:1;opacity:0.4;"></i>
            <p class="text-secondary fs-12px mt-2 mb-0" style="position:relative;z-index:1;">Route Map Preview</p>
          </div>

          {{-- Current Values --}}
          <div class="d-flex flex-column gap-2">
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Route ID</span>
              <span class="fw-semibold fs-13px">{{ $route->code ?? ('#R-'.$route->id) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Total Stops</span>
              <span class="fw-semibold fs-13px" id="previewStopCount">{{ $route->stops->count() }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Shift</span>
              @php $shift = strtolower($route->shift); @endphp
              @if($shift === 'morning')
                <span class="badge rounded-pill px-3 py-1 fs-12px" style="background:#dbeafe;color:#1d4ed8;">Morning</span>
              @elseif($shift === 'afternoon')
                <span class="badge rounded-pill px-3 py-1 fs-12px" style="background:#fef3c7;color:#92400e;">Afternoon</span>
              @else
                <span class="badge rounded-pill px-3 py-1 fs-12px" style="background:#f3f4f6;color:#6b7280;">{{ $route->shift }}</span>
              @endif
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Status</span>
              <span class="fw-semibold fs-13px">{{ $route->status ?? 'Active' }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2">
              <span class="text-secondary fs-13px">Vehicle</span>
              <span class="fw-semibold fs-13px">
                @if($route->vehicle)
                  {{ $route->vehicle->name }} ({{ $route->vehicle->license_plate }})
                @else
                  —
                @endif
              </span>
            </div>
          </div>
        </div>
      </div>

      {{-- Actions Card --}}
      <div class="card mb-4">
        <div class="card-body d-flex flex-column gap-2">
          <button type="submit" class="btn btn-primary w-100">
            <i data-lucide="save" class="icon-sm me-1"></i> Save Changes
          </button>
          <a href="#" class="btn btn-light w-100">
            <i data-lucide="x" class="icon-sm me-1"></i> Cancel
          </a>
        </div>
      </div>

      {{-- Danger Zone --}}
      <div class="card border-danger-subtle">
        <div class="card-header border-bottom py-3">
          <h6 class="mb-0 fw-bold text-danger">Danger Zone</h6>
        </div>
        <div class="card-body">
          <p class="text-secondary fs-13px mb-3">Deleting this route will remove all associated stop and student assignments permanently.</p>
          <button type="button" class="btn btn-outline-danger w-100" disabled>
            <i data-lucide="trash-2" class="icon-sm me-1"></i> Delete Route
          </button>
        </div>
      </div>

    </div>
  </div>
</form>

@endsection

@push('custom-scripts')
<script>
  // Keep preview stop count in sync on page load (read-only)
  const previewStopCount = document.getElementById('previewStopCount');
  if (previewStopCount) {
    previewStopCount.textContent = document.querySelectorAll('#stopsContainer .stop-row').length;
  }
</script>
@endpush

@push('style')
<style>
  .w-32px { width: 32px; }
  .h-32px { height: 32px; }
  .fs-12px { font-size: 12px; }
  .fs-13px { font-size: 13px; }
  .stop-row { transition: box-shadow 0.15s; }
  .stop-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
</style>
@endpush
