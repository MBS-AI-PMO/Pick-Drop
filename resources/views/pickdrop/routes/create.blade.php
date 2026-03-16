@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-secondary">Route Management</a></li>
        <li class="breadcrumb-item active">Create Route</li>
      </ol>
    </nav>
    <h4 class="mb-1">Create New Route</h4>
    <p class="text-secondary mb-0">Define a new transportation route with stops and assignments</p>
  </div>
  <a href="#" class="btn btn-light">
    <i data-lucide="arrow-left" class="icon-sm me-1"></i> Back to Routes
  </a>
</div>

<form>
  <form method="POST" action="{{ route('routes.store') }}">
    @csrf
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
              <input type="text" name="name" class="form-control" placeholder="e.g. Central Elementary Morning Pickup" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
              <select class="form-select" name="shift" required>
                <option value="">Select shift</option>
                <option value="morning" {{ old('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                <option value="afternoon" {{ old('shift') === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Assign Vehicle <span class="text-danger">*</span></label>
              <select class="form-select" name="vehicle_id">
                <option value="">Select vehicle</option>
                @foreach($vehicles as $vehicle)
                  <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->name }} ({{ $vehicle->license_plate }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
              <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
              <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Final Destination <span class="text-danger">*</span></label>
              <input type="text" name="destination" class="form-control" placeholder="e.g. Central Elementary School" value="{{ old('destination') }}" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description <span class="text-secondary fw-normal">(optional)</span></label>
              <textarea class="form-control" name="description" rows="2" placeholder="Add any notes about this route...">{{ old('description') }}</textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Right Column: Summary --}}
    <div class="col-lg-4">

      {{-- Route Preview Card --}}
      <div class="card mb-4">
        <div class="card-header border-bottom py-3">
          <h6 class="mb-0 fw-bold">Route Preview</h6>
        </div>
        <div class="card-body">
          {{-- Mini Map Placeholder --}}
          <div class="rounded-3 d-flex flex-column align-items-center justify-content-center text-center mb-3"
            style="height:180px; background:linear-gradient(135deg,#e8f0fe 0%,#f0f4ff 100%); position:relative;">
            <svg width="100%" height="100%" style="position:absolute;top:0;left:0;opacity:0.2;">
              <defs>
                <pattern id="previewGrid" width="30" height="30" patternUnits="userSpaceOnUse">
                  <path d="M 30 0 L 0 0 0 30" fill="none" stroke="#94a3b8" stroke-width="0.5"/>
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill="url(#previewGrid)"/>
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

          {{-- Summary Info --}}
          <div class="d-flex flex-column gap-2">
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Total Stops</span>
              <span class="fw-semibold fs-13px" id="previewStopCount">1</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Shift</span>
              <span class="fw-semibold fs-13px">—</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <span class="text-secondary fs-13px">Route Time</span>
              <span class="fw-semibold fs-13px">—</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2">
              <span class="text-secondary fs-13px">Vehicle</span>
              <span class="fw-semibold fs-13px">—</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Actions Card --}}
      <div class="card">
        <div class="card-body d-flex flex-column gap-2">
          <button type="submit" class="btn btn-primary w-100">
            <i data-lucide="check" class="icon-sm me-1"></i> Create Route
          </button>
          <button type="reset" class="btn btn-light w-100">
            <i data-lucide="rotate-ccw" class="icon-sm me-1"></i> Reset Form
          </button>
          <a href="#" class="btn btn-light w-100 text-danger">
            <i data-lucide="x" class="icon-sm me-1"></i> Cancel
          </a>
        </div>
      </div>

    </div>
  </div>
  </form>

@endsection

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
