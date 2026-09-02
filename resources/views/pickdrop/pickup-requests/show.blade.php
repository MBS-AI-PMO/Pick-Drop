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
          <div class="col-md-6">
            <label class="text-muted small">Round trip</label>
            <div class="fw-semibold">{{ $requestItem->round_trip !== false ? 'Yes — drop back at the same pickup point' : 'One way' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Pickup &amp; drop points</h6>
      </div>
      <div class="card-body">
        @php $journey = $requestItem->journeyApiArray(); @endphp
        <p class="text-secondary small mb-3">{{ $journey['rule'] ?? 'Passenger is dropped back at the same place they were picked up.' }}</p>
        @forelse(($journey['stops'] ?? []) as $stop)
          <div class="d-flex justify-content-between align-items-start mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div>
              <div class="fw-semibold">{{ $stop['sequence'] ?? $loop->iteration }}. {{ $stop['action'] ?? $stop['point'] }}</div>
              <small class="text-muted">{{ ucfirst($stop['leg'] ?? 'outbound') }} · {{ $stop['point'] ?? '—' }} · {{ $stop['time'] ?? '—' }}</small>
            </div>
            <span class="badge text-bg-light">{{ ($stop['virtual'] ?? false) ? 'Return' : ucfirst($stop['type'] ?? 'stop') }}</span>
          </div>
        @empty
          <p class="text-muted mb-0">No stops recorded yet.</p>
        @endforelse
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
        @if(($liveDriver ?? null) && $requestItem->driver && (int) $liveDriver->id !== (int) $requestItem->driver_id)
          <div class="mt-3 pt-3 border-top">
            <label class="text-muted small">Today’s cover driver</label>
            <div class="fw-semibold">{{ $liveDriver->name }}</div>
            <small class="text-muted">{{ $liveDriver->phone ?: $liveDriver->email }}</small>
          </div>
        @endif
      </div>
    </div>

    @if($canAssignCover ?? false)
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Alternative driver</h6></div>
      <div class="card-body">
        <p class="text-secondary small">If the assigned driver cannot come or the vehicle breaks down, send a nearby driver for that day. If nobody covers, that day’s amount is refunded to the parent wallet after 8pm.</p>
        @if(($coverDrivers ?? collect())->isNotEmpty())
          <form method="POST" action="{{ route('pickup-requests.cover', $requestItem) }}">
            @csrf
            <label class="text-muted small">Date</label>
            <input type="date" name="date" class="form-control mb-2" value="{{ now()->toDateString() }}" required>
            <label class="text-muted small">Reason</label>
            <select name="reason" class="form-select mb-2">
              <option value="unavailable">Driver unavailable</option>
              <option value="breakdown">Vehicle breakdown</option>
              <option value="absent">Absent</option>
            </select>
            <label class="text-muted small">Replacement driver</label>
            <select name="driver_id" class="form-select mb-2" required>
              <option value="">Select driver</option>
              @foreach($coverDrivers as $driver)
                <option value="{{ $driver->id }}">{{ $driver->name }} · {{ $driver->phone ?: $driver->email }}</option>
              @endforeach
            </select>
            <button class="btn btn-sm btn-dark" type="submit">Assign for this day</button>
          </form>
        @else
          <p class="text-muted mb-0">No other eligible driver in this city/area yet.</p>
        @endif
        @if($requestItem->replacements->isNotEmpty())
          <div class="mt-3 pt-3 border-top">
            <label class="text-muted small d-block mb-2">Cover history</label>
            @foreach($requestItem->replacements->sortByDesc('date') as $replacement)
              <div class="small mb-2">
                <div class="fw-semibold">{{ $replacement->date?->format('d M Y') }} · {{ ucfirst($replacement->reason) }}</div>
                <div class="text-secondary">
                  {{ ucfirst($replacement->status) }}
                  @if($replacement->replacementDriver)
                    · {{ $replacement->replacementDriver->name }}
                  @else
                    · waiting for a driver
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
    @endif

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

    @if($requestItem->status === 'pending' && empty($requestItem->driver_id))
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Assign driver</h6></div>
      <div class="card-body">
        <p class="text-secondary small">If no driver accepts within 20 minutes, the system auto-assigns an eligible driver. You can also assign now.</p>
        @if(($eligibleDrivers ?? collect())->isNotEmpty())
          <form method="POST" action="{{ route('pickup-requests.assign', $requestItem) }}">
            @csrf
            <select name="driver_id" class="form-select mb-2" required>
              <option value="">Select driver</option>
              @foreach($eligibleDrivers as $driver)
                <option value="{{ $driver->id }}">{{ $driver->name }} · {{ $driver->phone ?: $driver->email }}</option>
              @endforeach
            </select>
            <button class="btn btn-sm btn-dark" type="submit">Assign &amp; create invoice</button>
          </form>
        @else
          <p class="text-muted mb-0">No eligible active driver in this city/area yet.</p>
        @endif
        @if($requestItem->match_expires_at)
          <small class="text-muted d-block mt-2">Auto-assign after {{ $requestItem->match_expires_at->format('d M Y, h:i A') }}</small>
        @endif
      </div>
    </div>
    @endif

    @if($liveDriver ?? $requestItem->driver)
    @php $mapDriver = $liveDriver ?? $requestItem->driver; @endphp
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Live tracking</h6></div>
      <div class="card-body">
        @if($mapDriver->last_lat && $mapDriver->last_lng)
          <div class="fw-semibold mb-1">{{ $mapDriver->last_lat }}, {{ $mapDriver->last_lng }}</div>
          <small class="text-muted d-block mb-2">Updated {{ $mapDriver->last_location_at?->diffForHumans() ?: '—' }} · {{ $mapDriver->last_ride_status ?: 'no status' }}</small>
          <a class="btn btn-sm btn-outline-dark" target="_blank"
             href="https://maps.google.com/?q={{ $mapDriver->last_lat }},{{ $mapDriver->last_lng }}">Open map</a>
        @else
          <p class="text-muted mb-0">Driver has not shared a live location yet.</p>
        @endif
        @if($mapDriver->last_lat)
          <div id="live-map" class="mt-3 rounded" style="height:220px;"></div>
        @endif
      </div>
    </div>
    @endif

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

@php $mapDriver = $liveDriver ?? $requestItem->driver; @endphp
@if($mapDriver?->last_lat)
@push('custom-scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  (function () {
    var lat = {{ $mapDriver->last_lat }};
    var lng = {{ $mapDriver->last_lng }};
    var map = L.map('live-map').setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(@json($mapDriver->name ?: 'Driver'));
    @if($requestItem->pickup_lat)
      L.marker([{{ $requestItem->pickup_lat }}, {{ $requestItem->pickup_lng }}]).addTo(map).bindPopup('Pickup');
    @endif
    @if($requestItem->drop_lat)
      L.marker([{{ $requestItem->drop_lat }}, {{ $requestItem->drop_lng }}]).addTo(map).bindPopup('Drop');
    @endif
  })();
</script>
@endpush
@endif
