@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Issue #{{ $issue->id }}</h4>
    <p class="text-secondary mb-0">{{ $issue->subject }}</p>
  </div>
  <a href="{{ route('issues.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Details</h6></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">Reported by</label>
            <div class="fw-semibold">{{ $issue->user?->name ?? '—' }}</div>
            <small class="text-muted">{{ $issue->user?->email }}</small>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Type</label>
            <div class="fw-semibold">{{ ucfirst($issue->type ?: 'general') }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Request</label>
            <div class="fw-semibold">
              @if($issue->pickup_request_id)
                <a href="{{ route('pickup-requests.show', $issue->pickup_request_id) }}">#{{ $issue->pickup_request_id }}</a>
              @else
                —
              @endif
            </div>
          </div>
          @if($issue->eta_minutes)
            <div class="col-md-6">
              <label class="text-muted small">ETA change</label>
              <div class="fw-semibold">{{ $issue->eta_minutes }} minutes</div>
            </div>
          @endif
          <div class="col-12">
            <label class="text-muted small">Description</label>
            <div>{{ $issue->description ?: '—' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Update status</h6></div>
      <div class="card-body">
        <form method="POST" action="{{ route('issues.status', $issue) }}">
          @csrf
          <label class="form-label">Status</label>
          <select name="status" class="form-select mb-3" required>
            @foreach(['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
              <option value="{{ $value }}" {{ $issue->status === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <label class="form-label">Admin notes</label>
          <textarea name="admin_notes" class="form-control mb-3" rows="4">{{ old('admin_notes', $issue->admin_notes) }}</textarea>
          <button class="btn btn-dark w-100" type="submit">Save</button>
        </form>
        @if($issue->resolved_at)
          <small class="text-muted d-block mt-2">Resolved {{ $issue->resolved_at->format('d M Y, h:i A') }} by {{ $issue->resolver?->name }}</small>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
