@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">{{ $school->name }}</h4>
    <p class="text-secondary mb-0">{{ $school->city?->name }} · {{ $school->students->count() }} students</p>
  </div>
  <a href="{{ route('schools.index') }}" class="btn btn-outline-secondary">Back</a>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Edit</h6></div>
      <div class="card-body">
        <form method="POST" action="{{ route('schools.update', $school) }}">
          @csrf
          @method('PUT')
          <label class="form-label">Name</label>
          <input class="form-control mb-3" name="name" value="{{ $school->name }}" required>
          <label class="form-label">Phone</label>
          <input class="form-control mb-3" name="phone" value="{{ $school->phone }}">
          <label class="form-label">Address</label>
          <input class="form-control mb-3" name="address" value="{{ $school->address }}">
          <label class="form-label">Status</label>
          <select class="form-select mb-3" name="status">
            <option value="Active" {{ $school->status === 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ $school->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
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
