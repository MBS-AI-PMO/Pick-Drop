@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Parent / Self Verifications (KYC)</h4>
    <p class="text-secondary mb-0">Review identity documents, then users add children or commute details</p>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('parent-self-verifications.index') }}">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <input type="text" name="search" class="form-control"
                 placeholder="Search name, email, phone, CNIC..."
                 value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="type" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="parent" {{ request('type') === 'parent' ? 'selected' : '' }}>Parent</option>
            <option value="self" {{ request('type') === 'self' ? 'selected' : '' }}>Self</option>
          </select>
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
          @if(request('search') || request('status') || request('type'))
            <a href="{{ route('parent-self-verifications.index') }}" class="btn btn-outline-danger ms-1">Clear</a>
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
            <th class="py-3">User</th>
            <th class="py-3">Type</th>
            <th class="py-3">Contact</th>
            <th class="py-3">CNIC</th>
            <th class="py-3">City</th>
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
                <div class="fw-semibold">{{ $item->full_name }}</div>
                <small class="text-muted">{{ $item->user?->email }}</small>
              </td>
              <td>
                @if($item->account_type === 'self')
                  <span class="badge bg-info text-dark">Self</span>
                @else
                  <span class="badge bg-primary">Parent</span>
                @endif
              </td>
              <td>{{ $item->contactPhone() ?? '—' }}</td>
              <td><code>{{ $item->cnic_number }}</code></td>
              <td>{{ $item->city?->name ?? '—' }}</td>
              <td>{{ $item->created_at?->format('d M Y, h:i A') }}</td>
              <td>
                @if($item->status === 'pending')
                  <span class="badge bg-warning text-dark">Pending</span>
                @elseif($item->status === 'approved')
                  <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Approved</span>
                @else
                  <span class="badge bg-danger">Declined</span>
                @endif
              </td>
              <td class="text-center">
                <div class="action-btns">
                  <a href="{{ route('parent-self-verifications.show', $item) }}" class="action-btn action-btn-view" title="View">
                    <i data-lucide="eye"></i>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">No parent / self verifications found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$verifications" label="parent / self verifications" />
</div>

@endsection
