@extends('layout.master')

@section('content')

@php
  $user = $verification->user;
  $isSelf = $verification->account_type === 'self';
  $commute = $user?->commuteProfile;
  $children = $user?->students ?? collect();
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Review {{ $isSelf ? 'Self' : 'Parent' }} Verification</h4>
    <p class="text-secondary mb-0">{{ $verification->full_name }} · {{ $user?->email }}</p>
  </div>
  <div>
    <a href="{{ route('parent-self-verifications.index') }}" class="btn btn-outline-secondary">Back to list</a>
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
            <div class="fw-semibold">{{ $user?->name ?? $verification->full_name }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Father Name</label>
            <div class="fw-semibold">{{ $verification->father_name ?: '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Phone <span class="badge bg-light text-muted">from registration</span></label>
            <div class="fw-semibold">{{ $verification->contactPhone() ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Email <span class="badge bg-light text-muted">from registration</span></label>
            <div class="fw-semibold">{{ $user?->email ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Date of Birth</label>
            <div class="fw-semibold">{{ $verification->date_of_birth?->format('d M Y') }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Gender</label>
            <div class="fw-semibold">{{ $verification->gender ? ucfirst($verification->gender) : '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Nationality</label>
            <div class="fw-semibold">{{ $verification->nationality ?: '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Country</label>
            <div class="fw-semibold">{{ $verification->country ?: '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">City</label>
            <div class="fw-semibold">{{ $verification->city?->name ?? $verification->city_name ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Postal Code</label>
            <div class="fw-semibold">{{ $verification->postal_code ?: '—' }}</div>
          </div>
          <div class="col-12">
            <label class="text-muted small">Address</label>
            <div class="fw-semibold">{{ $verification->address ?: '—' }}</div>
          </div>
          <div class="col-12">
            <label class="text-muted small">Complete Address</label>
            <div class="fw-semibold">{{ $verification->complete_address ?: '—' }}</div>
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
            <a href="{{ route('parent-self-verifications.document', [$verification, 'cnic_front']) }}" target="_blank">
              <img src="{{ route('parent-self-verifications.document', [$verification, 'cnic_front']) }}"
                   alt="CNIC Front" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">CNIC Back</label>
            <a href="{{ route('parent-self-verifications.document', [$verification, 'cnic_back']) }}" target="_blank">
              <img src="{{ route('parent-self-verifications.document', [$verification, 'cnic_back']) }}"
                   alt="CNIC Back" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">Selfie / Face Photo</label>
            <a href="{{ route('parent-self-verifications.document', [$verification, 'selfie_photo']) }}" target="_blank">
              <img src="{{ route('parent-self-verifications.document', [$verification, 'selfie_photo']) }}"
                   alt="Selfie" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
        </div>
      </div>
    </div>

    @if($isSelf)
      <div class="card mb-3">
        <div class="card-header">
          <h6 class="mb-0">Pickup, Drop &amp; Office Timing</h6>
        </div>
        <div class="card-body">
          @if($commute)
            <div class="row g-3">
              <div class="col-md-6">
                <label class="text-muted small">City</label>
                <div class="fw-semibold">{{ $commute->city?->name ?? '—' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Office Name</label>
                <div class="fw-semibold">{{ $commute->office_name ?: '—' }}</div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Pickup</label>
                <div class="fw-semibold">{{ $commute->pickup_point }}</div>
                <small class="text-muted">{{ $commute->pickupArea?->name }} · {{ $commute->pickup_time ? substr((string) $commute->pickup_time, 0, 5) : '—' }}</small>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Drop / Office</label>
                <div class="fw-semibold">{{ $commute->drop_point }}</div>
                <small class="text-muted">{{ $commute->dropArea?->name }} · {{ $commute->drop_time ? substr((string) $commute->drop_time, 0, 5) : '—' }}</small>
              </div>
              <div class="col-12">
                <label class="text-muted small">Days</label>
                <div class="fw-semibold">{{ collect($commute->days ?? [])->map(fn ($d) => ucfirst($d))->implode(', ') ?: '—' }}</div>
              </div>
            </div>
          @else
            <p class="text-muted mb-0">Not submitted yet. After approval, the self user will add pickup, drop location and office timing in the app.</p>
          @endif
        </div>
      </div>
    @else
      <div class="card mb-3">
        <div class="card-header">
          <h6 class="mb-0">Children</h6>
        </div>
        <div class="card-body p-0">
          @if($children->isNotEmpty())
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4">Name</th>
                    <th>School</th>
                    <th>Grade</th>
                    <th>Pickup</th>
                    <th>Timing</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($children as $child)
                    <tr>
                      <td class="ps-4 fw-semibold">{{ $child->name }}</td>
                      <td>{{ $child->school_name ?: '—' }}</td>
                      <td>{{ $child->grade ?: '—' }}</td>
                      <td>
                        {{ $child->pickup_location ?: '—' }}
                        <small class="text-muted d-block">{{ $child->pickupArea?->name }}{{ $child->city ? ' · '.$child->city->name : '' }}</small>
                      </td>
                      <td>
                        {{ $child->pickup_time ? substr((string) $child->pickup_time, 0, 5) : '—' }}
                        –
                        {{ $child->dropoff_time ? substr((string) $child->dropoff_time, 0, 5) : '—' }}
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted mb-0 p-3">No children added yet. After approval, the parent will add children details in the app.</p>
          @endif
        </div>
      </div>
    @endif
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Account &amp; Status</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="text-muted small">Account Type</label>
          <div>
            @if($isSelf)
              <span class="badge bg-info text-dark">Self</span>
            @else
              <span class="badge bg-primary">Parent</span>
            @endif
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Account Status</label>
          <div class="fw-semibold">{{ $user?->status ?? '—' }}</div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">KYC Status</label>
          <div>
            @if($verification->status === 'pending')
              <span class="badge bg-warning text-dark">Pending Verification</span>
            @elseif($verification->status === 'approved')
              <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Approved</span>
            @else
              <span class="badge bg-danger">Declined</span>
            @endif
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Email Verified</label>
          <div class="fw-semibold">{{ $user?->email_verified_at ? 'Yes' : 'No' }}</div>
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

    <div class="card verification-action-card">
      <div class="card-header">
        <h6 class="mb-0">Admin Actions</h6>
      </div>
      <div class="card-body">
        <div class="verification-action-grid mb-3">
          <form method="POST" action="{{ route('parent-self-verifications.status', $verification) }}">
            @csrf
            <input type="hidden" name="status" value="approved">
            <button type="submit" class="btn btn-primary w-100"
                    {{ $verification->status === 'approved' ? 'disabled' : '' }}
                    onclick="return confirm('Accept this verification? A verified email will be sent.')">
              <i data-lucide="check" class="icon-xs"></i>
              Accept
            </button>
          </form>

          <form method="POST" action="{{ route('parent-self-verifications.status', $verification) }}">
            @csrf
            <input type="hidden" name="status" value="pending">
            <button type="submit" class="btn btn-outline-secondary w-100"
                    {{ $verification->status === 'pending' ? 'disabled' : '' }}
                    onclick="return confirm('Move this verification back to pending?')">
              <i data-lucide="clock" class="icon-xs"></i>
              Pending
            </button>
          </form>
        </div>

        <form method="POST" action="{{ route('parent-self-verifications.status', $verification) }}">
          @csrf
          <input type="hidden" name="status" value="rejected">
          <label class="form-label">Decline Reason</label>
          <textarea name="rejection_reason" class="form-control mb-2" rows="3"
                    {{ $verification->status === 'rejected' ? 'disabled' : 'required' }}
                    placeholder="Explain why documents were declined...">{{ old('rejection_reason') }}</textarea>
          @error('rejection_reason')
            <div class="text-danger small mb-2">{{ $message }}</div>
          @enderror
          <button type="submit" class="btn btn-outline-danger w-100"
                  {{ $verification->status === 'rejected' ? 'disabled' : '' }}
                  onclick="return confirm('Decline this verification?')">
            <i data-lucide="x" class="icon-xs"></i>
            Decline
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
