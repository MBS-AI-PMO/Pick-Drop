@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Edit Vehicle Verification</h4>
    <p class="text-secondary mb-0">{{ $verification->vehicle_name }} · {{ $verification->user?->name ?? 'Driver' }}</p>
  </div>
  <div>
    <a href="{{ route('vehicle-verifications.show', $verification) }}" class="btn btn-outline-secondary">Cancel</a>
  </div>
</div>

<form method="POST" action="{{ route('vehicle-verifications.update', $verification) }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Vehicle Details</h6></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
          <input type="text" name="vehicle_name" class="form-control" value="{{ old('vehicle_name', $verification->vehicle_name) }}" required>
          @error('vehicle_name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Vehicle Category <span class="text-danger">*</span></label>
          <select name="vehicle_category_id" class="form-select" required>
            <option value="">Select category</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" @selected((string) old('vehicle_category_id', $verification->vehicle_category_id) === (string) $category->id)>
                {{ $category->vehicle_name }}
              </option>
            @endforeach
          </select>
          @error('vehicle_category_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Vehicle Model</label>
          <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model', $verification->vehicle_model) }}">
          @error('vehicle_model')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Vehicle Color</label>
          <input type="text" name="vehicle_color" class="form-control" value="{{ old('vehicle_color', $verification->vehicle_color) }}">
          @error('vehicle_color')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">License Plate <span class="text-danger">*</span></label>
          <input type="text" name="license_plate" class="form-control" value="{{ old('license_plate', $verification->license_plate) }}" required>
          @error('license_plate')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Vehicle Photos & Registration</h6></div>
    <div class="card-body">
      <div class="row g-3">
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'registration_card_front',
          'label' => 'Registration Card Front',
          'previewUrl' => $verification->registration_card_front ? route('vehicle-verifications.document', [$verification, 'registration_card_front']) : null,
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'registration_card_back',
          'label' => 'Registration Card Back',
          'previewUrl' => $verification->registration_card_back ? route('vehicle-verifications.document', [$verification, 'registration_card_back']) : null,
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'number_plate_photo',
          'label' => 'Number Plate Photo',
          'previewUrl' => $verification->number_plate_photo ? route('vehicle-verifications.document', [$verification, 'number_plate_photo']) : null,
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'vehicle_front_photo',
          'label' => 'Vehicle Front Photo',
          'previewUrl' => $verification->vehicle_front_photo ? route('vehicle-verifications.document', [$verification, 'vehicle_front_photo']) : null,
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'vehicle_back_photo',
          'label' => 'Vehicle Back Photo',
          'previewUrl' => $verification->vehicle_back_photo ? route('vehicle-verifications.document', [$verification, 'vehicle_back_photo']) : null,
        ])
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Owner</h6></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Owner Name <span class="text-danger">*</span></label>
          <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $verification->owner_name) }}" required>
          @error('owner_name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Owner CNIC</label>
          <input type="text" name="owner_cnic_number" class="form-control" value="{{ old('owner_cnic_number', $verification->owner_cnic_number) }}">
          @error('owner_cnic_number')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="row g-3">
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'owner_document_front',
          'label' => 'Owner Document Front',
          'previewUrl' => $verification->owner_document_front ? route('vehicle-verifications.document', [$verification, 'owner_document_front']) : null,
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'owner_document_back',
          'label' => 'Owner Document Back',
          'previewUrl' => $verification->owner_document_back ? route('vehicle-verifications.document', [$verification, 'owner_document_back']) : null,
        ])
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('vehicle-verifications.show', $verification) }}" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">
      <i data-lucide="save" class="icon-xs"></i>
      Save Changes
    </button>
  </div>
</form>

@endsection

@push('custom-scripts')
<script>
  @if(session('error'))
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: @json(session('error')),
    confirmButtonText: 'OK',
    confirmButtonColor: '#e63946',
    customClass: { popup: 'rounded-4' }
  });
  @endif

</script>
@endpush
