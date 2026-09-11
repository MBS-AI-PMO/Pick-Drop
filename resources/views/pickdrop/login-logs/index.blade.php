@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Login logs</h4>
    <p class="text-secondary mb-0">Record of every successful or failed login across admin and apps.</p>
  </div>
  @if(($logs->total() ?? 0) > 0)
    <form action="{{ route('login-logs.clear') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-outline-danger">
        <i data-lucide="trash-2" class="icon-xs"></i>
        Clear all
      </button>
    </form>
  @endif
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('login-logs.index') }}" class="row g-2 align-items-center">
      <div class="col-12 col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search name, email, role, IP..."
               value="{{ request('search') }}">
      </div>
      <div class="col-12 col-md-2">
        <select class="form-select" name="channel" onchange="this.form.submit()">
          <option value="">All channels</option>
          <option value="web" {{ request('channel') === 'web' ? 'selected' : '' }}>Admin panel</option>
          <option value="driver-api" {{ request('channel') === 'driver-api' ? 'selected' : '' }}>Driver app</option>
          <option value="parent-api" {{ request('channel') === 'parent-api' ? 'selected' : '' }}>Parent / Self app</option>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <select class="form-select" name="status" onchange="this.form.submit()">
          <option value="">All statuses</option>
          <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
          <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
          <option value="denied" {{ request('status') === 'denied' ? 'selected' : '' }}>Denied</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
        @if(request()->hasAny(['search', 'channel', 'status']))
          <a href="{{ route('login-logs.index') }}" class="btn btn-outline-danger">Clear</a>
        @endif
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
            <th class="ps-4 py-3">Time</th>
            <th class="py-3">User</th>
            <th class="py-3">Role</th>
            <th class="py-3">Channel</th>
            <th class="py-3">IP</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <td class="ps-4">{{ $log->created_at?->format('d M Y, h:i A') }}</td>
              <td>
                <div class="fw-semibold">{{ $log->name ?: '—' }}</div>
                <div class="text-secondary fs-12px">{{ $log->email ?: '—' }}</div>
              </td>
              <td>{{ $log->role ?: '—' }}</td>
              <td>{{ $log->channelLabel() }}</td>
              <td>{{ $log->ip_address ?: '—' }}</td>
              <td>
                @if($log->status === 'success')
                  <span class="badge bg-success">{{ $log->statusLabel() }}</span>
                @elseif($log->status === 'denied')
                  <span class="badge bg-warning text-dark">{{ $log->statusLabel() }}</span>
                @else
                  <span class="badge bg-danger">{{ $log->statusLabel() }}</span>
                @endif
              </td>
              <td class="text-center">
                <div class="action-btns">
                  <form action="{{ route('login-logs.destroy', $log) }}" method="POST"
                        class="d-inline" onsubmit="confirmDelete(event, this)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn action-btn-delete" title="Delete">
                      <i data-lucide="trash-2"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No login logs yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$logs" label="logs" />
</div>

@endsection
