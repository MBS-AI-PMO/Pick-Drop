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
    <div class="row g-2 align-items-center">
      {{-- Search --}}
      <div class="col-12 col-md-4">
        <div class="input-group">
          <div class="input-group-text bg-transparent border-end-0">
            <i data-lucide="search" style="width:16px;height:16px;"></i>
          </div>
          <input type="text" class="form-control border-start-0 ps-0" id="userSearch" placeholder="Search users...">
        </div>
      </div>

      {{-- Role Filter --}}
      <div class="col-12 col-md-3">
        <select class="form-select" id="roleFilter">
          <option value="">All Roles</option>
          <option value="Admin">Admin</option>
          <option value="Driver">Driver</option>
          <option value="Parent">Parent</option>
          <option value="Student">Student</option>
        </select>
      </div>

      {{-- Spacer --}}
      <div class="col"></div>

      {{-- Add User Button --}}
      <div class="col-auto">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i data-lucide="plus" class="icon-sm me-1"></i> Add User
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Users Table --}}
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Name</th>
            <th class="py-3">Email</th>
            <th class="py-3">Role</th>
            <th class="py-3">Details</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="usersTableBody">

          <tr>
            <td class="ps-4 py-3 fw-semibold">Sarah Johnson</td>
            <td class="py-3 text-secondary">sarah.johnson@example.com</td>
            <td class="py-3"><span class="badge bg-primary-subtle text-primary">Parent</span></td>
            <td class="py-3 text-secondary">Child: Emma Johnson</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
            </td>
            <td class="py-3 text-center">
              <a href="#" class="text-primary me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#editUserModal"><i data-lucide="edit-2" style="width:16px;height:16px;"></i></a>
              <a href="#" class="text-danger" title="Delete"><i data-lucide="trash-2" style="width:16px;height:16px;"></i></a>
            </td>
          </tr>

          <tr>
            <td class="ps-4 py-3 fw-semibold">John Smith</td>
            <td class="py-3 text-secondary">john.smith@example.com</td>
            <td class="py-3"><span class="badge bg-info-subtle text-info">Driver</span></td>
            <td class="py-3 text-secondary">Vehicle: Bus #45</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
            </td>
            <td class="py-3 text-center">
              <a href="#" class="text-primary me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#editUserModal"><i data-lucide="edit-2" style="width:16px;height:16px;"></i></a>
              <a href="#" class="text-danger" title="Delete"><i data-lucide="trash-2" style="width:16px;height:16px;"></i></a>
            </td>
          </tr>

          <tr>
            <td class="ps-4 py-3 fw-semibold">Michael Brown</td>
            <td class="py-3 text-secondary">michael.brown@example.com</td>
            <td class="py-3"><span class="badge bg-primary-subtle text-primary">Parent</span></td>
            <td class="py-3 text-secondary">Child: James Brown</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">Inactive</span>
            </td>
            <td class="py-3 text-center">
              <a href="#" class="text-primary me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#editUserModal"><i data-lucide="edit-2" style="width:16px;height:16px;"></i></a>
              <a href="#" class="text-danger" title="Delete"><i data-lucide="trash-2" style="width:16px;height:16px;"></i></a>
            </td>
          </tr>

          <tr>
            <td class="ps-4 py-3 fw-semibold">Lisa Davis</td>
            <td class="py-3 text-secondary">lisa.davis@example.com</td>
            <td class="py-3"><span class="badge bg-danger-subtle text-danger">Admin</span></td>
            <td class="py-3 text-secondary">—</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
            </td>
            <td class="py-3 text-center">
              <a href="#" class="text-primary me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#editUserModal"><i data-lucide="edit-2" style="width:16px;height:16px;"></i></a>
              <a href="#" class="text-danger" title="Delete"><i data-lucide="trash-2" style="width:16px;height:16px;"></i></a>
            </td>
          </tr>

          <tr>
            <td class="ps-4 py-3 fw-semibold">Robert Wilson</td>
            <td class="py-3 text-secondary">robert.wilson@example.com</td>
            <td class="py-3"><span class="badge bg-info-subtle text-info">Driver</span></td>
            <td class="py-3 text-secondary">Vehicle: Bus #32</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
            </td>
            <td class="py-3 text-center">
              <a href="#" class="text-primary me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#editUserModal"><i data-lucide="edit-2" style="width:16px;height:16px;"></i></a>
              <a href="#" class="text-danger" title="Delete"><i data-lucide="trash-2" style="width:16px;height:16px;"></i></a>
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination Footer --}}
  <div class="card-footer d-flex justify-content-between align-items-center py-3">
    <small class="text-secondary">Showing 1–5 of 24 users</small>
    <nav>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
      </ul>
    </nav>
  </div>
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
        <form>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="Enter full name">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" placeholder="Enter email address">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
              <select class="form-select" id="addUserRole">
                <option value="">Select role</option>
                <option value="Admin">Admin</option>
                <option value="Driver">Driver</option>
                <option value="Parent">Parent</option>
                <option value="Student">Student</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12" id="addDetailsField">
              <label class="form-label fw-semibold">Details</label>
              <input type="text" class="form-control" id="addDetailsInput" placeholder="e.g. Child: Emma Johnson / Vehicle: Bus #45">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" placeholder="Enter password">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary px-4">Add User</button>
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
        <form>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Full Name</label>
              <input type="text" class="form-control" value="Sarah Johnson">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Email Address</label>
              <input type="email" class="form-control" value="sarah.johnson@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Role</label>
              <select class="form-select">
                <option>Admin</option>
                <option selected>Parent</option>
                <option>Driver</option>
                <option>Student</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select">
                <option selected>Active</option>
                <option>Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Details</label>
              <input type="text" class="form-control" value="Child: Emma Johnson">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary px-4">Save Changes</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('custom-scripts')
<script>
  // Live search filter
  document.getElementById('userSearch').addEventListener('keyup', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#usersTableBody tr').forEach(row => {
      row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
  });

  // Role filter
  document.getElementById('roleFilter').addEventListener('change', function () {
    const role = this.value.toLowerCase();
    document.querySelectorAll('#usersTableBody tr').forEach(row => {
      row.style.display = (!role || row.innerText.toLowerCase().includes(role)) ? '' : 'none';
    });
  });
</script>
@endpush
