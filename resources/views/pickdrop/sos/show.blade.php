@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">SOS #{{ $alert->id }}</h4>
    <p class="text-secondary mb-0">{{ $alert->user?->name ?? 'Unknown' }} · {{ $alert->created_at?->format('d M Y, h:i A') }}</p>
  </div>
  <a href="{{ route('sos.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Alert</h6></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">From</label>
            <div class="fw-semibold">{{ $alert->user?->name }}</div>
            <small class="text-muted">{{ $alert->user?->phone ?: $alert->user?->email }}</small>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Status</label>
            <div class="fw-semibold">{{ ucfirst($alert->status) }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Request</label>
            <div>
              @if($alert->pickup_request_id)
                <a href="{{ route('pickup-requests.show', $alert->pickup_request_id) }}">#{{ $alert->pickup_request_id }}</a>
              @else
                —
              @endif
            </div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Location</label>
            <div class="fw-semibold">
              @if($alert->lat && $alert->lng)
                {{ $alert->lat }}, {{ $alert->lng }}
                <a class="ms-1" target="_blank" href="https://maps.google.com/?q={{ $alert->lat }},{{ $alert->lng }}">Map</a>
              @else
                —
              @endif
            </div>
          </div>
          <div class="col-12">
            <label class="text-muted small">Message</label>
            <div>{{ $alert->message ?: '—' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Actions</h6></div>
      <div class="card-body">
        @if($alert->status === 'open')
          <form method="POST" action="{{ route('sos.acknowledge', $alert) }}" class="mb-2">
            @csrf
            <button class="btn btn-outline-dark w-100" type="submit">Acknowledge</button>
          </form>
        @endif
        @if($alert->status !== 'resolved')
          <form method="POST" action="{{ route('sos.resolve', $alert) }}">
            @csrf
            <button class="btn btn-dark w-100" type="submit">Mark resolved</button>
          </form>
        @endif
        @if($alert->handler)
          <small class="text-muted d-block mt-2">Handled by {{ $alert->handler->name }}</small>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
