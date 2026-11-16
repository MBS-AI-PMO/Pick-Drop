@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Review Driver Verification</h4>
    <p class="text-secondary mb-0">{{ $verification->full_name }} · {{ $verification->user?->email }}</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ $listUrl }}" class="btn btn-outline-secondary">Back</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Personal Details</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">Full Name <span class="badge bg-light text-muted">from registration</span></label>
            <div class="fw-semibold">{{ $verification->user?->name ?? $verification->full_name }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Father Name</label>
            <div class="fw-semibold">{{ $verification->father_name ?: '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Phone <span class="badge bg-light text-muted">from registration</span></label>
            <div class="fw-semibold">{{ $verification->user?->phone ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Email <span class="badge bg-light text-muted">from registration</span></label>
            <div class="fw-semibold">{{ $verification->user?->email ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Date of Birth</label>
            <div class="fw-semibold">{{ $verification->date_of_birth?->format('d M Y') }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">City</label>
            <div class="fw-semibold">{{ $verification->city?->name ?? '—' }}</div>
          </div>
          <div class="col-12">
            <label class="text-muted small">Address</label>
            <div class="fw-semibold">{{ $verification->address }}</div>
          </div>
          <div class="col-12">
            <label class="text-muted small">Service Areas</label>
            <div class="fw-semibold">
              @php
                $areaIds = $verification->user?->service_areas ?? [];
                $areas = $areaIds ? \App\Models\Area::whereIn('id', $areaIds)->pluck('name') : collect();
              @endphp
              @if($areas->isNotEmpty())
                {{ $areas->implode(', ') }}
              @else
                —
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Identity Verification (CNIC)</h6>
      </div>
      <div class="card-body">
        <p class="mb-3"><strong>CNIC:</strong> <code>{{ $verification->cnic_number }}</code></p>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">CNIC Front</label>
            <a href="{{ route('driver-verifications.document', [$verification, 'cnic_front']) }}" target="_blank">
              <img src="{{ route('driver-verifications.document', [$verification, 'cnic_front']) }}"
                   alt="CNIC Front" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">CNIC Back</label>
            <a href="{{ route('driver-verifications.document', [$verification, 'cnic_back']) }}" target="_blank">
              <img src="{{ route('driver-verifications.document', [$verification, 'cnic_back']) }}"
                   alt="CNIC Back" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">Selfie / Face Photo</label>
            <a href="{{ route('driver-verifications.document', [$verification, 'selfie_photo']) }}" target="_blank">
              <img src="{{ route('driver-verifications.document', [$verification, 'selfie_photo']) }}"
                   alt="Selfie" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Driving License</h6>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="text-muted small">License Number</label>
            <div class="fw-semibold">{{ $verification->license_number }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Expiry Date</label>
            <div class="fw-semibold">{{ $verification->license_expiry?->format('d M Y') }}</div>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small d-block mb-2">License Front</label>
            <a href="{{ route('driver-verifications.document', [$verification, 'license_front']) }}" target="_blank">
              <img src="{{ route('driver-verifications.document', [$verification, 'license_front']) }}"
                   alt="License Front" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-6">
            <label class="text-muted small d-block mb-2">License Back</label>
            <a href="{{ route('driver-verifications.document', [$verification, 'license_back']) }}" target="_blank">
              <img src="{{ route('driver-verifications.document', [$verification, 'license_back']) }}"
                   alt="License Back" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Account & Status</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="text-muted small">Account Status</label>
          <div class="fw-semibold">
            {{ $verification->user?->status ?? '—' }}
            @if($verification->status === 'approved' && strcasecmp((string) $verification->user?->status, 'Active') !== 0)
              <small class="text-muted d-block">KYC approved · awaiting vehicle verification to activate</small>
            @endif
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">KYC Status</label>
          <div>
            @if($verification->status === 'pending')
              <span class="badge bg-warning text-dark">Pending Verification</span>
            @elseif($verification->status === 'approved')
              <span class="badge rounded-pill px-3 py-1" style="background:#e8eef4;color:#1f4e79;">Approved</span>
            @else
              <span class="badge bg-danger">Declined</span>
            @endif
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Phone</label>
          <div class="fw-semibold">{{ $verification->user?->phone ?? '—' }}</div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Terms Accepted</label>
          <div class="fw-semibold">
            {{ $verification->terms_accepted ? 'Yes' : 'No' }}
            @if($verification->terms_accepted_at)
              <small class="text-muted d-block">{{ $verification->terms_accepted_at->format('d M Y, h:i A') }}</small>
            @endif
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Submitted At</label>
          <div class="fw-semibold">{{ $verification->created_at?->format('d M Y, h:i A') }}</div>
        </div>
        @if($verification->reviewed_at)
          <div class="mb-3">
            <label class="text-muted small">Reviewed At</label>
            <div class="fw-semibold">{{ $verification->reviewed_at->format('d M Y, h:i A') }}</div>
            <small class="text-muted">By {{ $verification->reviewer?->name ?? 'Admin' }}</small>
          </div>
        @endif
        @if($verification->rejection_reason)
          <div class="alert alert-warning mb-0">
            <strong>Decline reason:</strong><br>
            {{ $verification->rejection_reason }}
          </div>
        @endif
      </div>
    </div>

  </div>
</div>

@endsection

@push('custom-scripts')
<script>
  @if(session('success'))
  Swal.fire({
    icon: 'success',
    title: 'Updated',
    text: @json(session('success')),
    confirmButtonText: 'OK',
    confirmButtonColor: '#1f4e79',
    customClass: { popup: 'rounded-4' }
  });
  @endif

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
