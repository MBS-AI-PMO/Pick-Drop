@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">SOS alerts</h4>
    <p class="text-secondary mb-0">Emergency reports from parents, self users, and drivers</p>
  </div>
</div>

<div class="row g-3 mb-3">
  @php $all = max(1, (int) $counts['all']); @endphp
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('sos.index', ['status' => 'open']) }}" class="dashboard-stat-link">
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
    <a href="{{ route('sos.index') }}" class="dashboard-stat-link">
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
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Time</th>
            <th class="py-3">From</th>
            <th class="py-3">Request</th>
            <th class="py-3">Message</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($alerts as $item)
            <tr>
              <td class="ps-4">{{ $item->created_at?->format('d M Y, h:i A') }}</td>
              <td>{{ $item->user?->name ?? '—' }}</td>
              <td>
                @if($item->pickup_request_id)
                  <a href="{{ route('pickup-requests.show', $item->pickup_request_id) }}">#{{ $item->pickup_request_id }}</a>
                @else
                  —
                @endif
              </td>
              <td>{{ $item->message ?: '—' }}</td>
              <td>{{ ucfirst($item->status) }}</td>
              <td class="text-center">
                <a href="{{ route('sos.show', $item) }}" class="action-btn action-btn-view" title="View">
                  <i data-lucide="eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center py-5 text-muted">No SOS alerts.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$alerts" label="alerts" />
</div>

@endsection
