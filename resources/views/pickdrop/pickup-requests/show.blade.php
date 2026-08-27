@extends('layout.master')

@php
  $requester = $requestItem->parent;
  $isSelf = $requestItem->type === 'self';
  $days = collect($requestItem->days ?? [])->map(fn ($day) => ucfirst($day))->implode(', ');
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Request #{{ $requestItem->id }}</h4>
    <p class="text-secondary mb-0">
      {{ $requestItem->typeLabel() }} pickup · {{ $requester?->name ?? 'Unknown user' }}
    </p>
  </div>
  <div class="d-flex flex-wrap gap-2 align-items-center">
    <span class="badge rounded-pill px-3 py-2" style="{{ $requestItem->statusBadgeStyle() }}">
      {{ $requestItem->statusLabel() }}
    </span>
    <a href="{{ route('pickup-requests.index') }}" class="btn btn-outline-secondary">Back to list</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Requested by</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">Name</label>
            <div class="fw-semibold">{{ $requester?->name ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Account type</label>
            <div>
              @if($isSelf)
                <span class="badge bg-info text-dark">Self</span>
              @else
                <span class="badge bg-primary">Parent</span>
              @endif
            </div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Email</label>
            <div class="fw-semibold">{{ $requester?->email ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Phone</label>
            <div class="fw-semibold">{{ $requester?->phone ?? '—' }}</div>
          </div>
          @if($requestItem->student)
            <div class="col-md-6">
              <label class="text-muted small">Child</label>
              <div class="fw-semibold">{{ $requestItem->student->name }}</div>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Trip details</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">City</label>
            <div class="fw-semibold">{{ $requestItem->city?->name ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Days</label>
            <div class="fw-semibold">{{ $days ?: '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Pickup area</label>
            <div class="fw-semibold">{{ $requestItem->area?->name ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Drop area</label>
            <div class="fw-semibold">{{ $requestItem->dropArea?->name ?? '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Pickup point</label>
            <div class="fw-semibold">{{ $requestItem->pickup_point ?: '—' }}</div>
            <small class="text-muted">{{ $requestItem->pickup_time ? substr((string) $requestItem->pickup_time, 0, 5) : '—' }}</small>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Drop point</label>
            <div class="fw-semibold">{{ $requestItem->drop_point ?: '—' }}</div>
            <small class="text-muted">{{ $requestItem->drop_time ? substr((string) $requestItem->drop_time, 0, 5) : '—' }}</small>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Scheduled date</label>
            <div class="fw-semibold">{{ $requestItem->scheduled_date?->format('d M Y') ?: '—' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Submitted</label>
            <div class="fw-semibold">{{ $requestItem->created_at?->format('d M Y, h:i A') }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Shift duration</label>
            <div class="fw-semibold">{{ (int) ($requestItem->duration_months ?: 1) }} month{{ (int) ($requestItem->duration_months ?: 1) === 1 ? '' : 's' }}</div>
            <small class="text-muted">{{ $requestItem->shift_start_date?->format('d M Y') ?: '—' }} → {{ $requestItem->shift_end_date?->format('d M Y') ?: '—' }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Driver & vehicle</h6>
      </div>
      <div class="card-body">
        @if($requestItem->driver)
          <div class="mb-3">
            <label class="text-muted small">Driver</label>
            <div class="fw-semibold">{{ $requestItem->driver->name }}</div>
            <small class="text-muted">{{ $requestItem->driver->email }}</small>
          </div>
          <div>
            <label class="text-muted small">Vehicle</label>
            <div class="fw-semibold">{{ $requestItem->vehicle?->name ?? '—' }}</div>
            <small class="text-muted">{{ $requestItem->vehicle?->license_plate ?? $requestItem->vehicle?->category?->name ?? '—' }}</small>
          </div>
        @else
          <p class="text-secondary mb-0">Waiting for a driver to accept this request.</p>
        @endif
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Customer advance</h6>
        <span class="badge rounded-pill px-3 py-1" style="{{ $requestItem->paymentStatusBadgeStyle() }}">
          {{ $requestItem->paymentStatusLabel() }}
        </span>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <label class="text-muted small">Amount</label>
          <div class="fw-semibold">
            @if($requestItem->latestInvoice)
              {{ $requestItem->latestInvoice->formattedTotal() }}
            @elseif($requestItem->estimated_amount)
              PKR {{ number_format((float) $requestItem->estimated_amount, 2) }}
            @else
              —
            @endif
          </div>
        </div>
        <div class="mb-2">
          <label class="text-muted small">Distance / trips</label>
          <div class="fw-semibold">{{ $requestItem->distance_km ? number_format((float) $requestItem->distance_km, 2) . ' km' : '—' }} · {{ $requestItem->trip_count ?: '—' }} trips</div>
        </div>
        @if($requestItem->latestInvoice)
          <a href="{{ route('payments.show', $requestItem->latestInvoice) }}" class="btn btn-sm btn-outline-secondary mt-1">Open invoice</a>
        @elseif(!$requestItem->driver)
          <p class="text-secondary fs-12px mb-0 mt-2">Invoice and payment methods appear after a driver accepts.</p>
        @endif
        @if($requestItem->needsPayment())
          <p class="text-secondary fs-12px mb-0 mt-2">Service stays locked until this advance payment is confirmed.</p>
        @endif
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Driver payout</h6>
        @php
          $driverPaid = $requestItem->driver_payout_status === \App\Models\PickupRequest::DRIVER_PAYOUT_PAID;
        @endphp
        <span class="badge rounded-pill px-3 py-1" style="{{ $driverPaid ? 'background:#d1fae5;color:#065f46;' : 'background:#fef9c3;color:#92400e;' }}">
          {{ $driverPaid ? 'Paid' : 'Month-end unpaid' }}
        </span>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <label class="text-muted small">Fixed monthly rate</label>
          <div class="fw-semibold">PKR {{ number_format((float) ($requestItem->driver_monthly_rate ?? 0), 2) }}</div>
        </div>
        <div class="mb-2">
          <label class="text-muted small">Total ({{ (int) ($requestItem->duration_months ?: 1) }} month{{ (int) ($requestItem->duration_months ?: 1) === 1 ? '' : 's' }})</label>
          <div class="fw-semibold">PKR {{ number_format((float) ($requestItem->driver_payout_amount ?? 0), 2) }}</div>
        </div>
        <div class="mb-2">
          <label class="text-muted small">Due</label>
          <div class="fw-semibold">{{ $requestItem->driver_payout_due_on?->format('d M Y') ?: 'Month end' }}</div>
        </div>
        <p class="text-secondary fs-12px mb-2">Paid by PickDrop to the driver at month end. Not taken from the customer invoice.</p>
        @if($requestItem->driver && !$driverPaid)
          <form method="POST" action="{{ route('pickup-requests.driver-payout', $requestItem) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark driver paid</button>
          </form>
        @elseif($requestItem->driver_payout_paid_at)
          <small class="text-muted">Paid {{ $requestItem->driver_payout_paid_at->format('d M Y, h:i A') }}</small>
        @endif
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Timeline</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="text-muted small">Created</label>
          <div class="fw-semibold">{{ $requestItem->created_at?->format('d M Y, h:i A') }}</div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Last updated</label>
          <div class="fw-semibold">{{ $requestItem->updated_at?->format('d M Y, h:i A') }}</div>
        </div>
        @if($requestItem->cancelled_at)
          <div class="mb-3">
            <label class="text-muted small">Cancelled</label>
            <div class="fw-semibold">{{ $requestItem->cancelled_at->format('d M Y, h:i A') }}</div>
          </div>
        @endif
        @if($requestItem->completed_at)
          <div>
            <label class="text-muted small">Completed</label>
            <div class="fw-semibold">{{ $requestItem->completed_at->format('d M Y, h:i A') }}</div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
