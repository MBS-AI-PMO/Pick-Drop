@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Complaints</h4>
    <p class="text-secondary mb-0">Parent, self, and driver reports including delays</p>
  </div>
</div>

<div class="row g-3 mb-3">
  @php $all = max(1, (int) $counts['all']); @endphp
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('issues.index') }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <x-stat-ring :percent="$counts['all'] > 0 ? 100 : 0" tone="blue" />
          <div>
            <h3>{{ number_format($counts['all']) }}</h3>
            <p class="dashboard-stat-label">All</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('issues.index', ['status' => 'open']) }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <x-stat-ring :percent="($counts['open'] / $all) * 100" tone="orange" />
          <div>
            <h3>{{ number_format($counts['open']) }}</h3>
            <p class="dashboard-stat-label">Open</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('issues.index', ['status' => 'in_progress']) }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <x-stat-ring :percent="($counts['in_progress'] / $all) * 100" tone="teal" />
          <div>
            <h3>{{ number_format($counts['in_progress']) }}</h3>
            <p class="dashboard-stat-label">In progress</p>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('issues.index', ['status' => 'resolved']) }}" class="dashboard-stat-link">
      <div class="card dashboard-stat-card h-100">
        <div class="card-body">
          <x-stat-ring :percent="($counts['resolved'] / $all) * 100" tone="green" />
          <div>
            <h3>{{ number_format($counts['resolved']) }}</h3>
            <p class="dashboard-stat-label">Resolved</p>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('issues.index') }}">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search subject, user..."
                 value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
              <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="type" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach(['general', 'delay', 'breakdown'] as $type)
              <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
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
            <th class="py-3">Reported by</th>
            <th class="py-3">Type</th>
            <th class="py-3">Subject</th>
            <th class="py-3">Request</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($issues as $item)
            <tr>
              <td class="ps-4">{{ $item->id }}</td>
              <td>{{ $item->user?->name ?? '—' }}</td>
              <td>{{ ucfirst($item->type ?: 'general') }}</td>
              <td>{{ $item->subject }}</td>
              <td>
                @if($item->pickup_request_id)
                  <a href="{{ route('pickup-requests.show', $item->pickup_request_id) }}">#{{ $item->pickup_request_id }}</a>
                @else
                  —
                @endif
              </td>
              <td>{{ $item->statusLabel() }}</td>
              <td class="text-center">
                <a href="{{ route('issues.show', $item) }}" class="action-btn action-btn-view" title="View">
                  <i data-lucide="eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center py-5 text-muted">No complaints found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$issues" label="complaints" />
</div>

@endsection
