@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Pick-Drop Charges</h4>
    <p class="text-secondary mb-0">Manage fare settings by distance (per KM).</p>
  </div>
</div>

<div class="row">
  <div class="col-lg-7 col-xl-6">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title mb-3">Per KM Pricing</h6>

        <form action="{{ route('charges.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="form-label">Per KM Rate <span class="text-danger">*</span></label>
            <input
              type="number"
              step="0.01"
              min="0"
              name="per_km_rate"
              class="form-control @error('per_km_rate') is-invalid @enderror"
              value="{{ old('per_km_rate', $charge->per_km_rate) }}"
              placeholder="e.g. 55.00"
              required>
            @error('per_km_rate')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Currency <span class="text-danger">*</span></label>
            <input
              type="text"
              name="currency"
              maxlength="10"
              class="form-control @error('currency') is-invalid @enderror"
              value="{{ old('currency', $charge->currency) }}"
              placeholder="PKR"
              required>
            @error('currency')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-check form-switch mb-4">
            <input
              class="form-check-input"
              type="checkbox"
              role="switch"
              id="isActiveSwitch"
              name="is_active"
              value="1"
              {{ old('is_active', $charge->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="isActiveSwitch">Enable distance-based pricing</label>
          </div>

          <button type="submit" class="btn btn-primary">Save Charges</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

