@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Vehicle Categories</h4>
    <p class="text-secondary mb-0">Manage vehicle categories and capacities</p>
  </div>
</div>

{{-- Filters + Add Button --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('vehicle-categories.index') }}" id="filterForm">
      <div class="row g-2 align-items-center">

        {{-- Search --}}
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="categorySearch" placeholder="Search categories..."
                   value="{{ request('search') }}">
          </div>
        </div>

        {{-- Status Filter --}}
        <div class="col-12 col-md-3">
          <select class="form-select" name="status" id="statusFilter" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
          </select>
        </div>

        {{-- Search / Clear Buttons --}}
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          @if(request('search') || request('status') !== null && request('status') !== '')
            <a href="{{ route('vehicle-categories.index') }}" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          @endif
        </div>

        {{-- Spacer --}}
        <div class="col"></div>

        {{-- Add Button --}}
        <div class="col-auto">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add Category
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Categories Table --}}
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">#</th>
            <th class="py-3">Category Name</th>
            <th class="py-3">Passenger Capacity</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="categoriesTableBody">
          @forelse($categories as $category)
            <tr>
              <td class="ps-4 py-3 text-muted">{{ $categories->firstItem() + $loop->index }}</td>
              <td class="py-3 fw-semibold">{{ $category->vehicle_name }}</td>
              <td class="py-3 text-secondary">{{ $category->passenger_capacity }} Seats</td>
              <td class="py-3">
                @if($category->status == 1)
                  <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Active</span>
                @else
                  <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">Inactive</span>
                @endif
              </td>
              <td class="py-3 text-center">
                <div class="d-flex justify-content-center align-items-center gap-2">
                  <button class="btn btn-sm btn-light btn-icon" onclick='openEditModal(@json($category))'>
                    <i data-lucide="edit-2" class="icon-sm"></i>
                  </button>
                  <form action="{{ route('vehicle-categories.destroy', $category->id) }}" method="POST"
                        class="d-inline" onsubmit="confirmDelete(event, this)">
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
              <td colspan="5" class="text-center py-5">
                <div class="mb-3 text-muted">
                  <i data-lucide="layers" style="width:48px;height:48px;opacity:0.4;"></i>
                </div>
                <h6 class="text-secondary">No Categories Found</h6>
                <p class="text-muted small mb-0">Try adjusting your search or filters.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination Footer --}}
  @if($categories->hasPages())
  <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-3">
    <small class="text-muted">
      Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }} categories
    </small>
    <div>
      {{ $categories->links('pagination::bootstrap-5') }}
    </div>
  </div>
  @endif
</div>

{{-- ========== ADD MODAL ========== --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('vehicle-categories.store') }}" method="POST">
        @csrf
        <div class="modal-body pt-2">
          <div class="mb-3">
            <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
            <input type="text" name="vehicle_name" class="form-control" required placeholder="e.g. Minibus">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Passenger Capacity <span class="text-danger">*</span></label>
            <input type="number" min="1" name="passenger_capacity" class="form-control" required placeholder="e.g. 14">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ========== EDIT MODAL ========== --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Edit Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editCategoryForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body pt-2">
          <div class="mb-3">
            <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
            <input type="text" name="vehicle_name" id="edit_vehicle_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Passenger Capacity <span class="text-danger">*</span></label>
            <input type="number" min="1" name="passenger_capacity" id="edit_passenger_capacity" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" id="edit_status" class="form-select">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">Update</button>
        </div>
      </form>
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

  // Submit search on Enter
  document.getElementById('categorySearch').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') document.getElementById('filterForm').submit();
  });

  const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));

  function openEditModal(category) {
    document.getElementById('edit_vehicle_name').value      = category.vehicle_name;
    document.getElementById('edit_passenger_capacity').value = category.passenger_capacity;
    document.getElementById('edit_status').value            = category.status;
    document.getElementById('editCategoryForm').action      = `/vehicle-categories/${category.id}`;
    editModal.show();
  }

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
