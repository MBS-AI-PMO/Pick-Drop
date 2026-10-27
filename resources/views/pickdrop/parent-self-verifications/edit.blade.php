@extends('layout.master')

@section('content')

@php
  $isSelf = strcasecmp((string) $verification->account_type, 'self') === 0;
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Edit {{ $isSelf ? 'Self' : 'Parent' }} Verification</h4>
    <p class="text-secondary mb-0">{{ $verification->full_name }} · {{ $verification->user?->email }}</p>
  </div>
  <div>
    <a href="{{ route('parent-self-verifications.show', $verification) }}" class="btn btn-outline-secondary">Cancel</a>
  </div>
</div>

<form method="POST" action="{{ route('parent-self-verifications.update', $verification) }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Personal Details</h6></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
          <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $verification->full_name) }}" required>
          @error('full_name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Father Name</label>
          <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $verification->father_name) }}">
          @error('father_name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
          <input type="date" name="date_of_birth" class="form-control"
                 value="{{ old('date_of_birth', optional($verification->date_of_birth)->format('Y-m-d')) }}" required>
          @error('date_of_birth')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Gender</label>
          <select name="gender" class="form-select">
            <option value="">Select</option>
            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
              <option value="{{ $value }}" @selected(old('gender', $verification->gender) === $value)>{{ $label }}</option>
            @endforeach
          </select>
          @error('gender')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
          <select name="city_id" class="form-select" required>
            <option value="">Select city</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}" @selected((string) old('city_id', $verification->city_id) === (string) $city->id)>
                {{ $city->name }}
              </option>
            @endforeach
          </select>
          @error('city_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Nationality</label>
          <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $verification->nationality) }}">
          @error('nationality')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Country</label>
          <input type="text" name="country" class="form-control" value="{{ old('country', $verification->country) }}">
          @error('country')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Postal Code</label>
          <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $verification->postal_code) }}">
          @error('postal_code')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
          <textarea name="address" class="form-control" rows="2" required>{{ old('address', $verification->address) }}</textarea>
          @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Complete Address</label>
          <textarea name="complete_address" class="form-control" rows="2">{{ old('complete_address', $verification->complete_address) }}</textarea>
          @error('complete_address')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Identity Documents</h6></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">CNIC Number <span class="text-danger">*</span></label>
          <input type="text" name="cnic_number" class="form-control" value="{{ old('cnic_number', $verification->cnic_number) }}" required>
          @error('cnic_number')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="row g-3">
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'cnic_front',
          'label' => 'CNIC Front',
          'previewUrl' => route('parent-self-verifications.document', [$verification, 'cnic_front']),
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'cnic_back',
          'label' => 'CNIC Back',
          'previewUrl' => route('parent-self-verifications.document', [$verification, 'cnic_back']),
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'selfie_photo',
          'label' => 'Selfie / Face Photo',
          'previewUrl' => route('parent-self-verifications.document', [$verification, 'selfie_photo']),
        ])
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('parent-self-verifications.show', $verification) }}" class="btn btn-light">Cancel</a>
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
