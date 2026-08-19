@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Vehicle Verifications</h4>
    <p class="text-secondary mb-0">Review driver vehicle documents and approval requests</p>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('vehicle-verifications.index') }}">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <input type="text" name="search" class="form-control"
                 placeholder="Search driver, vehicle, plate..."
                 value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-3">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Declined</option>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
          @if(request('search') || request('status'))
            <a href="{{ route('vehicle-verifications.index') }}" class="btn btn-outline-danger ms-1">Clear</a>
          @endif
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">#</th>
            <th class="py-3">Driver</th>
            <th class="py-3">Vehicle</th>
            <th class="py-3">Plate</th>
            <th class="py-3">Owner</th>
            <th class="py-3">Submitted</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($verifications as $item)
            <tr>
              <td class="ps-4">{{ $verifications->firstItem() + $loop->index }}</td>
              <td>
                <div class="fw-semibold">{{ $item->user?->name ?? '—' }}</div>
                <small class="text-muted">{{ $item->user?->email }}</small>
              </td>
              <td>
                <div class="fw-semibold">{{ $item->vehicle_name }}</div>
                <small class="text-muted">{{ $item->category?->vehicle_name ?? 'No category' }}</small>
              </td>
              <td><code>{{ $item->license_plate }}</code></td>
              <td>{{ $item->owner_name }}</td>
              <td>{{ $item->created_at?->format('d M Y, h:i A') }}</td>
              <td>
                @if($item->status === 'pending')
                  <span class="badge bg-warning text-dark">Pending</span>
                @elseif($item->status === 'approved')
                  <span class="badge bg-success">Approved</span>
                @else
                  <span class="badge bg-danger">Declined</span>
                @endif
              </td>
              <td class="text-center">
                <a href="{{ route('vehicle-verifications.show', $item) }}" class="btn btn-sm btn-outline-primary verification-detail-btn">
                  <i data-lucide="eye" class="icon-xs"></i>
                  Details
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">No vehicle verifications found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($verifications->hasPages())
    <div class="card-footer bg-transparent app-pagination-footer">
      <small class="app-pagination-summary">
        Showing {{ $verifications->firstItem() }} to {{ $verifications->lastItem() }} of {{ $verifications->total() }} vehicle verifications
      </small>
      <div class="app-pagination-controls">
        {{ $verifications->links('pagination::bootstrap-5') }}
      </div>
    </div>
  @endif
</div>

@endsection
