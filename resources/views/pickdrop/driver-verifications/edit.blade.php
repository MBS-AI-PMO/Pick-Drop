@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Edit Driver Verification</h4>
    <p class="text-secondary mb-0">{{ $verification->full_name }} · {{ $verification->user?->email }}</p>
  </div>
  <a href="{{ $listUrl }}" class="btn btn-outline-secondary">
    <i data-lucide="arrow-left" class="icon-xs"></i>
    Back to List
  </a>
</div>

<form method="POST" action="{{ route('driver-verifications.update', $verification) }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')
  <input type="hidden" name="return" value="{{ request('return') }}">

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
        <div class="col-md-6">
          <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
          <input type="date" name="date_of_birth" class="form-control"
                 value="{{ old('date_of_birth', optional($verification->date_of_birth)->format('Y-m-d')) }}" required>
          @error('date_of_birth')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
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
        <div class="col-12">
          <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
          <textarea name="address" class="form-control" rows="2" required>{{ old('address', $verification->address) }}</textarea>
          @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
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
      <div class="kyc-doc-grid">
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'cnic_front',
          'label' => 'CNIC Front',
          'previewUrl' => route('driver-verifications.document', [$verification, 'cnic_front']),
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'cnic_back',
          'label' => 'CNIC Back',
          'previewUrl' => route('driver-verifications.document', [$verification, 'cnic_back']),
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'selfie_photo',
          'label' => 'Selfie / Face Photo',
          'previewUrl' => route('driver-verifications.document', [$verification, 'selfie_photo']),
        ])
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Driving License</h6></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">License Number <span class="text-danger">*</span></label>
          <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $verification->license_number) }}" required>
          @error('license_number')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">License Expiry <span class="text-danger">*</span></label>
          <input type="date" name="license_expiry" class="form-control"
                 value="{{ old('license_expiry', optional($verification->license_expiry)->format('Y-m-d')) }}" required>
          @error('license_expiry')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="kyc-doc-grid kyc-doc-grid--pair">
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'license_front',
          'label' => 'License Front',
          'previewUrl' => route('driver-verifications.document', [$verification, 'license_front']),
        ])
        @include('pickdrop.partials.verification-doc-input', [
          'name' => 'license_back',
          'label' => 'License Back',
          'previewUrl' => route('driver-verifications.document', [$verification, 'license_back']),
        ])
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end mb-4">
    <button type="submit" class="btn btn-primary px-4">
      <i data-lucide="save" class="icon-xs"></i>
      Save Changes
    </button>
  </div>
</form>

<div class="card mb-4">
  <div class="card-header"><h6 class="mb-0">Verification Status</h6></div>
  <div class="card-body">
    <div class="d-flex flex-wrap gap-2 mb-3">
      <form method="POST" action="{{ route('driver-verifications.status', $verification) }}">
        @csrf
        <input type="hidden" name="return" value="{{ request('return') }}">
        <input type="hidden" name="status" value="approved">
        <button type="submit" class="btn btn-primary"
                {{ $verification->status === 'approved' ? 'disabled' : '' }}
                onclick="return confirm('Accept this driver verification?')">
          <i data-lucide="check" class="icon-xs"></i>
          Accept
        </button>
      </form>

      <form method="POST" action="{{ route('driver-verifications.status', $verification) }}">
        @csrf
        <input type="hidden" name="return" value="{{ request('return') }}">
        <input type="hidden" name="status" value="pending">
        <button type="submit" class="btn btn-outline-secondary"
                {{ $verification->status === 'pending' ? 'disabled' : '' }}
                onclick="return confirm('Move this verification back to pending?')">
          <i data-lucide="clock" class="icon-xs"></i>
          Pending
        </button>
      </form>
    </div>

    <form method="POST" action="{{ route('driver-verifications.status', $verification) }}">
      @csrf
      <input type="hidden" name="return" value="{{ request('return') }}">
      <input type="hidden" name="status" value="rejected">
      <label class="form-label">Decline Reason</label>
      <textarea name="rejection_reason" class="form-control mb-3" rows="3"
                {{ $verification->status === 'rejected' ? 'disabled' : 'required' }}
                placeholder="Explain why documents were declined...">{{ old('rejection_reason') }}</textarea>
      @error('rejection_reason')
        <div class="text-danger small mb-2">{{ $message }}</div>
      @enderror
      <button type="submit" class="btn btn-outline-danger"
              {{ $verification->status === 'rejected' ? 'disabled' : '' }}
              onclick="return confirm('Decline this verification?')">
        <i data-lucide="x" class="icon-xs"></i>
        Decline
      </button>
    </form>
  </div>
</div>

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
