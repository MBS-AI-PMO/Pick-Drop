@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Review Vehicle Verification</h4>
    <p class="text-secondary mb-0">{{ $verification->vehicle_name }} · {{ $verification->user?->name ?? 'Driver' }}</p>
  </div>
  <div>
    <a href="{{ route('vehicle-verifications.index') }}" class="btn btn-outline-secondary">Back to list</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Vehicle Details</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">Vehicle Name</label>
            <div class="fw-semibold">{{ $verification->vehicle_name }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Vehicle Category</label>
            <div class="fw-semibold">{{ $verification->category?->vehicle_name ?? '—' }}</div>
          </div>
          <div class="col-md-4">
            <label class="text-muted small">Vehicle Model</label>
            <div class="fw-semibold">{{ $verification->vehicle_model ?: '—' }}</div>
          </div>
          <div class="col-md-4">
            <label class="text-muted small">Vehicle Color</label>
            <div class="fw-semibold">{{ $verification->vehicle_color ?: '—' }}</div>
          </div>
          <div class="col-md-4">
            <label class="text-muted small">License Plate</label>
            <div class="fw-semibold"><code>{{ $verification->license_plate }}</code></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Vehicle Photos & Registration</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">Registration Card Front</label>
            <a href="{{ route('vehicle-verifications.document', [$verification, 'registration_card_front']) }}" target="_blank">
              <img src="{{ route('vehicle-verifications.document', [$verification, 'registration_card_front']) }}" alt="Registration front" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">Registration Card Back</label>
            <a href="{{ route('vehicle-verifications.document', [$verification, 'registration_card_back']) }}" target="_blank">
              <img src="{{ route('vehicle-verifications.document', [$verification, 'registration_card_back']) }}" alt="Registration back" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">Number Plate Photo</label>
            <a href="{{ route('vehicle-verifications.document', [$verification, 'number_plate_photo']) }}" target="_blank">
              <img src="{{ route('vehicle-verifications.document', [$verification, 'number_plate_photo']) }}" alt="Number plate" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-6">
            <label class="text-muted small d-block mb-2">Vehicle Front Photo</label>
            <a href="{{ route('vehicle-verifications.document', [$verification, 'vehicle_front_photo']) }}" target="_blank">
              <img src="{{ route('vehicle-verifications.document', [$verification, 'vehicle_front_photo']) }}" alt="Vehicle front" class="img-fluid rounded border" style="max-height:220px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-6">
            <label class="text-muted small d-block mb-2">Vehicle Back Photo</label>
            <a href="{{ route('vehicle-verifications.document', [$verification, 'vehicle_back_photo']) }}" target="_blank">
              <img src="{{ route('vehicle-verifications.document', [$verification, 'vehicle_back_photo']) }}" alt="Vehicle back" class="img-fluid rounded border" style="max-height:220px;object-fit:cover;width:100%;">
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Owner Documents</h6>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="text-muted small">Owner Name</label>
            <div class="fw-semibold">{{ $verification->owner_name }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Owner CNIC</label>
            <div class="fw-semibold">{{ $verification->owner_cnic_number ?: '—' }}</div>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small d-block mb-2">Owner Document Front</label>
            <a href="{{ route('vehicle-verifications.document', [$verification, 'owner_document_front']) }}" target="_blank">
              <img src="{{ route('vehicle-verifications.document', [$verification, 'owner_document_front']) }}" alt="Owner document front" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-6">
            <label class="text-muted small d-block mb-2">Owner Document Back</label>
            <a href="{{ route('vehicle-verifications.document', [$verification, 'owner_document_back']) }}" target="_blank">
              <img src="{{ route('vehicle-verifications.document', [$verification, 'owner_document_back']) }}" alt="Owner document back" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
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
          <label class="text-muted small">Driver</label>
          <div class="fw-semibold">{{ $verification->user?->name ?? '—' }}</div>
          <small class="text-muted">{{ $verification->user?->email }}</small>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Phone</label>
          <div class="fw-semibold">{{ $verification->user?->phone ?? '—' }}</div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Vehicle Verification Status</label>
          <div>
            @if($verification->status === 'pending')
              <span class="badge bg-warning text-dark">Pending Approval</span>
            @elseif($verification->status === 'approved')
              <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Approved</span>
            @else
              <span class="badge bg-danger">Declined</span>
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

    <div class="card verification-action-card">
      <div class="card-header">
        <h6 class="mb-0">Admin Actions</h6>
      </div>
      <div class="card-body">
        <div class="verification-action-grid mb-3">
          <form method="POST" action="{{ route('vehicle-verifications.status', $verification) }}">
            @csrf
            <input type="hidden" name="status" value="approved">
            <button type="submit" class="btn btn-primary w-100"
                    {{ $verification->status === 'approved' ? 'disabled' : '' }}
                    onclick="return confirm('Accept this vehicle verification?')">
              <i data-lucide="check" class="icon-xs"></i>
              Accept
            </button>
          </form>

          <form method="POST" action="{{ route('vehicle-verifications.status', $verification) }}">
            @csrf
            <input type="hidden" name="status" value="pending">
            <button type="submit" class="btn btn-outline-secondary w-100"
                    {{ $verification->status === 'pending' ? 'disabled' : '' }}
                    onclick="return confirm('Move this vehicle verification back to pending?')">
              <i data-lucide="clock" class="icon-xs"></i>
              Pending
            </button>
          </form>
        </div>

        <form method="POST" action="{{ route('vehicle-verifications.status', $verification) }}">
          @csrf
          <input type="hidden" name="status" value="rejected">
          <label class="form-label">Decline Reason</label>
          <textarea name="rejection_reason" class="form-control mb-2" rows="3"
                    {{ $verification->status === 'rejected' ? 'disabled' : 'required' }}
                    placeholder="Explain why this vehicle submission was declined...">{{ old('rejection_reason') }}</textarea>
          @error('rejection_reason')
            <div class="text-danger small mb-2">{{ $message }}</div>
          @enderror
          <button type="submit" class="btn btn-outline-danger w-100"
                  {{ $verification->status === 'rejected' ? 'disabled' : '' }}
                  onclick="return confirm('Decline this vehicle verification?')">
            <i data-lucide="x" class="icon-xs"></i>
            Decline
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
