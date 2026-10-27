@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Institution Management</h4>
    <p class="text-secondary mb-0">Manage schools, colleges, universities, offices and other pickup destinations</p>
  </div>
</div>

{{-- Filters + Add Button (same pattern as Routes / Vehicles) --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('schools.index') }}" id="schoolFilterForm">
      <div class="row g-2 align-items-center">

        {{-- Search --}}
        <div class="col-12 col-md-4">
          <div class="input-group">
            <div class="input-group-text bg-transparent border-end-0">
              <i data-lucide="search" style="width:16px;height:16px;"></i>
            </div>
            <input type="text" name="search" class="form-control border-start-0 ps-0"
                   id="schoolSearch" placeholder="Search institutions..."
                   value="{{ request('search') }}">
          </div>
        </div>

        {{-- Category Filter --}}
        <div class="col-12 col-md-3">
          <select class="form-select" name="category" id="categoryFilter" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $value => $label)
              <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        {{-- Filter / Clear --}}
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">
            <i data-lucide="filter" style="width:15px;height:15px;" class="me-1"></i> Filter
          </button>
          @if(request('search') || request('category'))
            <a href="{{ route('schools.index') }}" class="btn btn-outline-danger ms-1">
              <i data-lucide="x" style="width:15px;height:15px;"></i>
            </a>
          @endif
        </div>

        {{-- Spacer --}}
        <div class="col"></div>

        {{-- Add Button --}}
        <div class="col-auto">
          <button type="button" class="btn btn-primary" onclick="openAddSchoolModal()">
            <i data-lucide="plus" class="icon-sm me-1"></i> Add Institution
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Institutions Table --}}
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">#</th>
            <th class="py-3">Institution</th>
            <th class="py-3">Category</th>
            <th class="py-3">City</th>
            <th class="py-3">Contact</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-end pe-4">Actions</th>
          </tr>
        </thead>
        <tbody id="schoolsTableBody">
          @forelse($schools as $school)
          @php
            $schoolData = [
              'id' => $school->id,
              'name' => $school->name,
              'category' => $school->category,
              'category_label' => $school->categoryLabel(),
              'city_id' => $school->city_id,
              'city' => $school->city?->name,
              'address' => $school->address,
              'phone' => $school->phone,
              'email' => $school->email,
              'status' => $school->status,
            ];
          @endphp
          <tr>
            <td class="ps-4 py-3 text-muted">{{ $schools->firstItem() + $loop->index }}</td>
            <td class="ps-2">
              <div class="d-flex align-items-center gap-2">
                <div class="w-36px h-36px rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="background:rgba(var(--bs-primary-rgb),0.1);">
                  <i data-lucide="school" class="text-primary" style="width:16px;height:16px;"></i>
                </div>
                <div>
                  <p class="mb-0 fw-semibold fs-14px">{{ $school->name }}</p>
                  <small class="text-secondary">{{ $school->address ?: 'No address added' }}</small>
                </div>
              </div>
            </td>
            <td>
              <span class="badge rounded-pill px-3 py-1" style="background:#eef2ff;color:#4338ca;">{{ $school->categoryLabel() }}</span>
            </td>
            <td><span class="fs-13px">{{ $school->city?->name ?: '—' }}</span></td>
            <td>
              @if($school->phone || $school->email)
                @if($school->phone)
                  <div class="d-flex align-items-center gap-1 fs-13px">
                    <i data-lucide="phone" class="text-secondary" style="width:13px;height:13px;"></i>{{ $school->phone }}
                  </div>
                @endif
                @if($school->email)
                  <div class="d-flex align-items-center gap-1 fs-13px">
                    <i data-lucide="mail" class="text-secondary" style="width:13px;height:13px;"></i>{{ $school->email }}
                  </div>
                @endif
              @else
                <span class="text-secondary fs-13px">—</span>
              @endif
            </td>
            <td>
              @if(strtolower($school->status) === 'active')
                <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Active</span>
              @else
                <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">{{ $school->status }}</span>
              @endif
            </td>
            <td class="text-end pe-4">
              <div class="action-btns">
                <button type="button" class="action-btn action-btn-view" title="View"
                        onclick="openViewSchoolModal({{ Js::from($schoolData) }})">
                  <i data-lucide="eye"></i>
                </button>
                <button type="button" class="action-btn action-btn-edit" title="Edit"
                        onclick="openEditSchoolModal({{ Js::from($schoolData) }})">
                  <i data-lucide="edit-2"></i>
                </button>
                <form action="{{ route('schools.destroy', $school) }}" method="POST"
                      onsubmit="confirmSchoolDelete(event, this, {{ Js::from($school->name) }})">
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
            <td colspan="7" class="text-center py-5">
              <div class="mb-3 text-muted">
                <i data-lucide="school" style="width:48px;height:48px;opacity:0.4;"></i>
              </div>
              <h6 class="text-secondary">No Institutions Found</h6>
              <p class="text-muted small mb-0">Try adjusting your search or filters, or add a new institution.</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$schools" label="institutions" />
</div>


{{-- ========== ADD / EDIT INSTITUTION MODALS ========== --}}
@foreach(['add' => ['id' => 'addSchoolModal', 'bag' => 'addSchool', 'title' => 'Add Institution', 'submit' => 'Add Institution'], 'edit' => ['id' => 'editSchoolModal', 'bag' => 'editSchool', 'title' => 'Edit Institution', 'submit' => 'Save Changes']] as $mode => $cfg)
@php $bag = $errors->getBag($cfg['bag']); @endphp
<div class="modal fade" id="{{ $cfg['id'] }}" tabindex="-1" aria-labelledby="{{ $cfg['id'] }}Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="{{ $cfg['id'] }}Label">{{ $cfg['title'] }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <form method="POST" id="{{ $mode }}SchoolForm"
              action="{{ $mode === 'add' ? route('schools.store') : '#' }}" novalidate>
          @csrf
          @if($mode === 'edit')
            @method('PUT')
            <input type="hidden" name="_school_id" id="editSchoolId" value="{{ old('_school_id') }}">
          @endif
          <input type="hidden" name="status" id="{{ $mode }}SchoolStatusHidden" value="Active" @if($mode === 'edit') disabled @endif>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Institution Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="{{ $mode }}SchoolName"
                     class="form-control {{ $bag->has('name') ? 'is-invalid' : '' }}"
                     value="{{ $bag->any() ? old('name') : '' }}" maxlength="255" placeholder="e.g. Beaconhouse School" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
              <select name="category" id="{{ $mode }}SchoolCategory" class="form-select {{ $bag->has('category') ? 'is-invalid' : '' }}" required>
                @foreach($categories as $value => $label)
                  <option value="{{ $value }}" {{ ($bag->any() ? old('category') : 'school') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">City</label>
              <select name="city_id" id="{{ $mode }}SchoolCity" class="form-select {{ $bag->has('city_id') ? 'is-invalid' : '' }}">
                <option value="">Select city</option>
                @foreach($cities as $city)
                  <option value="{{ $city->id }}" {{ $bag->any() && (string) old('city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Address</label>
              <input type="text" name="address" id="{{ $mode }}SchoolAddress"
                     class="form-control {{ $bag->has('address') ? 'is-invalid' : '' }}"
                     value="{{ $bag->any() ? old('address') : '' }}" maxlength="255" placeholder="Street, area">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Phone</label>
              <input type="tel" inputmode="tel" name="phone" id="{{ $mode }}SchoolPhone"
                     class="form-control {{ $bag->has('phone') ? 'is-invalid' : '' }}"
                     value="{{ $bag->any() ? old('phone') : '' }}" maxlength="50" placeholder="e.g. +92 300 1234567" autocomplete="off">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" inputmode="email" name="email" id="{{ $mode }}SchoolEmail"
                     class="form-control {{ $bag->has('email') ? 'is-invalid' : '' }}"
                     value="{{ $bag->any() ? old('email') : '' }}" maxlength="255" placeholder="name@example.com" autocomplete="off">
            </div>
            @if($mode === 'edit')
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="editSchoolStatus" class="form-select {{ $bag->has('status') ? 'is-invalid' : '' }}">
                <option value="Active" {{ $bag->any() && old('status') === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ $bag->any() && old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
            @endif
          </div>
          <div class="modal-footer border-0 pt-4 pb-0 px-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">{{ $cfg['submit'] }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endforeach


{{-- ========== VIEW INSTITUTION MODAL (read-only) ========== --}}
<div class="modal fade" id="viewSchoolModal" tabindex="-1" aria-labelledby="viewSchoolModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="viewSchoolModalLabel">Institution Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="row g-3 view-detail-grid">
          <div class="col-12">
            <label class="form-label mb-1">Institution Name</label>
            <div class="fw-semibold text-break" id="vSchoolName">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Category</label>
            <div class="fw-semibold text-break" id="vSchoolCategory">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">City</label>
            <div class="fw-semibold text-break" id="vSchoolCity">—</div>
          </div>
          <div class="col-12">
            <label class="form-label mb-1">Address</label>
            <div class="fw-semibold text-break" id="vSchoolAddress">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Phone</label>
            <div class="fw-semibold text-break" id="vSchoolPhone">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Email</label>
            <div class="fw-semibold text-break" id="vSchoolEmail">—</div>
          </div>
          <div class="col-6">
            <label class="form-label mb-1">Status</label>
            <span class="badge rounded-pill px-3 py-1" id="vSchoolStatusBadge">—</span>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('custom-scripts')
<script>
  const addSchoolModal = new bootstrap.Modal(document.getElementById('addSchoolModal'));
  const editSchoolModal = new bootstrap.Modal(document.getElementById('editSchoolModal'));
  const viewSchoolModal = new bootstrap.Modal(document.getElementById('viewSchoolModal'));
  const updateSchoolUrlTemplate = "{{ route('schools.update', ':id') }}";

  document.getElementById('schoolSearch')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') document.getElementById('schoolFilterForm').submit();
  });

  function openAddSchoolModal() {
    const form = document.getElementById('addSchoolForm');
    // reset() would restore values rendered from a previous failed attempt, so clear explicitly.
    form.querySelectorAll('input[type="text"], input[type="tel"], input[type="email"]').forEach(el => { el.value = ''; });
    document.getElementById('addSchoolCategory').value = 'school';
    document.getElementById('addSchoolCity').value = '';
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    addSchoolModal.show();
  }

  function openEditSchoolModal(school) {
    const form = document.getElementById('editSchoolForm');
    form.action = updateSchoolUrlTemplate.replace(':id', school.id);
    document.getElementById('editSchoolId').value = school.id;
    document.getElementById('editSchoolName').value = school.name || '';
    document.getElementById('editSchoolCategory').value = school.category || 'school';
    document.getElementById('editSchoolCity').value = school.city_id || '';
    document.getElementById('editSchoolAddress').value = school.address || '';
    document.getElementById('editSchoolPhone').value = school.phone || '';
    document.getElementById('editSchoolEmail').value = school.email || '';
    document.getElementById('editSchoolStatus').value = school.status || 'Active';
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    editSchoolModal.show();
  }

  function openViewSchoolModal(school) {
    document.getElementById('viewSchoolModalLabel').textContent = 'Institution — ' + (school.name || '');
    document.getElementById('vSchoolName').textContent = school.name || '—';
    document.getElementById('vSchoolCategory').textContent = school.category_label || '—';
    document.getElementById('vSchoolCity').textContent = school.city || '—';
    document.getElementById('vSchoolAddress').textContent = school.address || '—';
    document.getElementById('vSchoolPhone').textContent = school.phone || '—';
    document.getElementById('vSchoolEmail').textContent = school.email || '—';
    const badge = document.getElementById('vSchoolStatusBadge');
    badge.textContent = school.status || '—';
    const active = (school.status || '').toLowerCase() === 'active';
    badge.style.background = active ? '#eef4ff' : '#f3f4f6';
    badge.style.color = active ? '#3f6fd9' : '#6b7280';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    viewSchoolModal.show();
  }

  function confirmSchoolDelete(event, form, name) {
    event.preventDefault();
    Swal.fire({
      title: 'Delete institution?',
      text: (name ? name + ' ' : 'This institution ') + 'will be removed.',
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it'
    }).then(result => { if (result.isConfirmed) form.submit(); });
  }

  // Re-open the form that failed validation (values and messages are already rendered).
  @if($errors->getBag('addSchool')->any())
    addSchoolModal.show();
  @elseif($errors->getBag('editSchool')->any())
    (function () {
      const form = document.getElementById('editSchoolForm');
      const id = document.getElementById('editSchoolId').value;
      if (id) form.action = updateSchoolUrlTemplate.replace(':id', id);
      editSchoolModal.show();
    })();
  @endif
</script>
@endpush

@push('style')
<style>
  .w-36px { width: 36px; }
  .h-36px { height: 36px; }
  .fs-12px { font-size: 12px; }
  .fs-13px { font-size: 13px; }
  .fs-14px { font-size: 14px; }
</style>
@endpush
