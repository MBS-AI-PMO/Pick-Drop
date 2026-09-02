@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Pickup Requests</h4>
    <p class="text-secondary mb-0">See who submitted a request, current status, and assigned driver</p>
  </div>
</div>

<div class="row g-3 mb-3">
  @php $all = max(1, (int) $counts['all']); @endphp
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('pickup-requests.index') }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <x-stat-ring :percent="$counts['all'] > 0 ? 100 : 0" tone="blue" />
          <div>
            <h3>{{ number_format($counts['all']) }}</h3>
            <p class="dashboard-stat-label">All requests</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('pickup-requests.index', ['status' => 'pending']) }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <x-stat-ring :percent="($counts['pending'] / $all) * 100" tone="orange" />
          <div>
            <h3>{{ number_format($counts['pending']) }}</h3>
            <p class="dashboard-stat-label">Waiting for driver</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('pickup-requests.index', ['status' => 'in_progress']) }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <x-stat-ring :percent="($counts['accepted'] / $all) * 100" tone="teal" />
          <div>
            <h3>{{ number_format($counts['accepted']) }}</h3>
            <p class="dashboard-stat-label">In progress</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('pickup-requests.index', ['status' => 'completed']) }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <x-stat-ring :percent="($counts['completed'] / $all) * 100" tone="green" />
          <div>
            <h3>{{ number_format($counts['completed']) }}</h3>
            <p class="dashboard-stat-label">Completed</p>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('pickup-requests.index') }}">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-3">
          <input type="text" name="search" class="form-control"
                 placeholder="Search name, email, pickup, drop..."
                 value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="type" onchange="this.form.submit()">
            <option value="">All types</option>
            <option value="parent" {{ request('type') === 'parent' ? 'selected' : '' }}>Parent</option>
            <option value="self" {{ request('type') === 'self' ? 'selected' : '' }}>Self</option>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In progress</option>
            @foreach(\App\Models\PickupRequest::statusOptions() as $value => $label)
              <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="city_id" onchange="this.form.submit()">
            <option value="">All cities</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}" {{ (string) request('city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
          @if(request('search') || request('status') || request('type') || request('city_id'))
            <a href="{{ route('pickup-requests.index') }}" class="btn btn-outline-danger ms-1">Clear</a>
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
            <th class="ps-4 py-3">Request</th>
            <th class="py-3">Requested by</th>
            <th class="py-3">Type</th>
            <th class="py-3">Pickup → Drop</th>
            <th class="py-3">Driver</th>
            <th class="py-3">Submitted</th>
            <th class="py-3">Payment</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($requests as $item)
            <tr>
              <td class="ps-4">
                <div class="fw-semibold">#{{ $item->id }}</div>
                <small class="text-muted">{{ $item->city?->name ?? '—' }}</small>
              </td>
              <td>
                <div class="fw-semibold">{{ $item->requesterName() }}</div>
                <small class="text-muted">{{ $item->parent?->email ?? '—' }}</small>
                @if($item->student)
                  <div><small class="text-muted">Child: {{ $item->student->name }}</small></div>
                @endif
              </td>
              <td>
                @if($item->type === 'self')
                  <span class="badge bg-info text-dark">Self</span>
                @else
                  <span class="badge bg-primary">Parent</span>
                @endif
              </td>
              <td>
                <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($item->pickup_point, 28) }}</div>
                <small class="text-muted">{{ \Illuminate\Support\Str::limit($item->drop_point, 28) }}</small>
              </td>
              <td>
                @if($item->driver)
                  <div>{{ $item->driver->name }}</div>
                  <small class="text-muted">{{ $item->vehicle?->name ?? $item->vehicle?->license_plate ?? '—' }}</small>
                @else
                  <span class="text-muted">Unassigned</span>
                @endif
              </td>
              <td>{{ $item->created_at?->format('d M Y, h:i A') }}</td>
              <td>
                <span class="badge rounded-pill px-3 py-1" style="{{ $item->paymentStatusBadgeStyle() }}">
                  {{ $item->paymentStatusLabel() }}
                </span>
                @if($item->estimated_amount)
                  <div><small class="text-muted">{{ number_format((float) $item->estimated_amount, 0) }}</small></div>
                @endif
              </td>
              <td>
                <span class="badge rounded-pill px-3 py-1" style="{{ $item->statusBadgeStyle() }}">
                  {{ $item->statusLabel() }}
                </span>
              </td>
              <td class="text-center">
                <div class="action-btns">
                  <a href="{{ route('pickup-requests.show', $item) }}" class="action-btn action-btn-view" title="View">
                    <i data-lucide="eye"></i>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">No pickup requests found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$requests" label="pickup requests" />
</div>

@endsection
