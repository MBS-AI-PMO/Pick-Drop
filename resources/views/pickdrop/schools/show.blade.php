@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">{{ $school->name }}</h4>
    <p class="text-secondary mb-0">{{ $school->categoryLabel() }} · {{ $school->city?->name ?: 'No city' }} · {{ $school->students->count() }} students</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('schools.index') }}" class="btn btn-outline-secondary">Back</a>
    <form action="{{ route('schools.destroy', $school) }}" method="POST" onsubmit="confirmDelete(event, this)">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-outline-danger">Delete</button>
    </form>
  </div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Edit</h6></div>
      <div class="card-body">
        <form method="POST" action="{{ route('schools.update', $school) }}" id="schoolEditForm" novalidate>
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input class="form-control @error('name', 'editSchool') is-invalid @enderror" name="name" value="{{ old('name', $school->name) }}" maxlength="255" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select class="form-select @error('category', 'editSchool') is-invalid @enderror" name="category" required>
              @foreach($categories as $value => $label)
                <option value="{{ $value }}" {{ old('category', $school->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input class="form-control @error('phone', 'editSchool') is-invalid @enderror" type="tel" inputmode="tel" name="phone" value="{{ old('phone', $school->phone) }}" maxlength="50" placeholder="e.g. +92 300 1234567" autocomplete="off">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control @error('email', 'editSchool') is-invalid @enderror" type="email" inputmode="email" name="email" value="{{ old('email', $school->email) }}" maxlength="255" placeholder="name@example.com" autocomplete="off">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <input class="form-control @error('address', 'editSchool') is-invalid @enderror" name="address" value="{{ old('address', $school->address) }}" maxlength="255">
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select @error('status', 'editSchool') is-invalid @enderror" name="status">
              <option value="Active" {{ old('status', $school->status) === 'Active' ? 'selected' : '' }}>Active</option>
              <option value="Inactive" {{ old('status', $school->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
          </div>
          <button class="btn btn-dark w-100" type="submit">Update</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Students</h6></div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <thead class="table-light"><tr><th class="ps-4">Child</th><th>Parent</th><th>Grade</th></tr></thead>
          <tbody>
            @forelse($school->students as $student)
              <tr>
                <td class="ps-4">{{ $student->name }}</td>
                <td>{{ $student->parent?->name }}</td>
                <td>{{ $student->grade ?: '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center py-4 text-muted">No students linked.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

