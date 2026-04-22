@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">User Management</h4>
    <p class="text-secondary mb-0">Manage all system users</p>
  </div>
</div>

{{-- Filters + Add Button --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('users.index') }}" id="filterForm">
      <div class="row g-2 align-items-center">

        {{-- Search --}}
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="userSearch" placeholder="Search users..."
                   value="{{ request('search') }}">
          </div>
        </div>

        {{-- Role Filter --}}
        <div class="col-12 col-md-3">
          <select class="form-select" name="role" id="roleFilter" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="Driver"   {{ request('role') === 'Driver'   ? 'selected' : '' }}>Driver</option>
            <option value="Parent"   {{ request('role') === 'Parent'   ? 'selected' : '' }}>Parent</option>
            <option value="Student"  {{ request('role') === 'Student'  ? 'selected' : '' }}>Student</option>
          </select>
        </div>

        {{-- Search Button --}}
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          @if(request('search') || request('role'))
            <a href="{{ route('users.index') }}" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          @endif
        </div>

        {{-- Spacer --}}
        <div class="col"></div>

        {{-- Add User Button --}}
        <div class="col-auto">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add User
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Users Table --}}
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">#</th>
            <th class="py-3">Name</th>
            <th class="py-3">Email</th>
            <th class="py-3">Role</th>
            <th class="py-3">Details</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="usersTableBody">

          @forelse($users as $user)
          <tr>
            <td class="ps-4 py-3 text-muted">{{ $users->firstItem() + $loop->index }}</td>
            <td class="ps-2 py-3 fw-semibold">{{ $user->name }}</td>
            <td class="py-3 text-secondary">{{ $user->email }}</td>
            <td class="py-3">
              @php
                  $roleClass = match(strtolower($user->role)) {
                      'admin'   => 'danger',
                      'driver'  => 'info',
                      'parent'  => 'primary',
                      default   => 'secondary'
                  };
              @endphp
              <span class="badge bg-{{ $roleClass }}-subtle text-{{ $roleClass }}">{{ $user->role }}</span>
            </td>
            <td class="py-3 text-secondary">{{ is_array($user->details) ? json_encode($user->details) : ($user->details ?: '—') }}</td>
            <td class="py-3">
              @if(strtolower($user->status) === 'active')
                <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
              @else
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">{{ $user->status }}</span>
              @endif
            </td>
            <td class="py-3 text-center">
              <div class="d-flex justify-content-center align-items-center gap-2">
                <button class="btn btn-sm btn-light btn-icon" title="Edit"
                        onclick='openEditModal(event, @json($user))'>
                  <i data-lucide="edit-2" class="icon-sm"></i>
                </button>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                      class="d-inline m-0 p-0" onsubmit="confirmDelete(event, this)">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger btn-icon">
                    <i data-lucide="trash-2" class="icon-sm"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="users" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Users Found</h6>
              <p class="text-muted small mb-0">Try adjusting your search or filters.</p>
            </td>
          </tr>
          @endforelse

        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination Footer --}}
  @if($users->hasPages())
  <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-3">
    <small class="text-muted">
      Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
    </small>
    <div>
      {{ $users->links('pagination::bootstrap-5') }}
    </div>
  </div>
  @endif
</div>


{{-- ========== ADD USER MODAL ========== --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="addUserModalLabel">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <form action="{{ route('users.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
              <select name="role" class="form-select" required>
                <option value="">Select role</option>
                <option value="Driver">Driver</option>
                <option value="Parent">Parent</option>
                <option value="Student">Student</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Details</label>
              <input type="text" name="details" class="form-control" placeholder="e.g. Child: Emma / Vehicle: Bus #45">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Add User</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ========== EDIT USER MODAL ========== --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <form id="editUserForm" method="POST">
          @csrf
          @method('PUT')
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editUserName" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" id="editUserEmail" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
              <select name="role" id="editUserRole" class="form-select" required>
                <option value="Parent">Parent</option>
                <option value="Driver">Driver</option>
                <option value="Student">Student</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="editUserStatus" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Details</label>
              <input type="text" name="details" id="editUserDetails" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Password
                <small class="text-muted fw-normal">(Leave blank to keep current)</small>
              </label>
              <input type="password" name="password" class="form-control" placeholder="Update password (optional)">
            </div>
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('custom-scripts')
<script>
  @if(session('success'))
  Swal.fire({
      icon: 'success', title: 'Success', text: "{{ session('success') }}",
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 3000, timerProgressBar: true
  });
  @endif

  @if($errors->any())
  Swal.fire({
      icon: 'error', title: 'Validation Error',
      html: `{!! implode('<br>', $errors->all()) !!}`,
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 5000, timerProgressBar: true
  });
  @endif

  // Submit search on Enter key
  document.getElementById('userSearch').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      document.getElementById('filterForm').submit();
    }
  });

  // Edit Modal
  const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));

  function openEditModal(event, user) {
    event.preventDefault();
    document.getElementById('editUserName').value    = user.name    || '';
    document.getElementById('editUserEmail').value   = user.email   || '';
    document.getElementById('editUserRole').value    = user.role    || 'Student';
    document.getElementById('editUserStatus').value  = user.status  || 'Active';
    document.getElementById('editUserDetails').value = user.details || '';
    document.getElementById('editUserForm').action   = `/users/${user.id}`;
    editModal.show();
  }

  // Delete Confirmation
  function confirmDelete(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Are you sure?', text: "You won't be able to revert this!",
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then(result => { if (result.isConfirmed) form.submit(); });
  }
</script>
@endpush
