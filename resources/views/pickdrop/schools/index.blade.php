@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Schools</h4>
    <p class="text-secondary mb-0">School list used on parent student profiles</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Add school</h6></div>
      <div class="card-body">
        <form method="POST" action="{{ route('schools.store') }}">
          @csrf
          <label class="form-label">Name</label>
          <input class="form-control mb-3" name="name" required>
          <label class="form-label">City</label>
          <select class="form-select mb-3" name="city_id">
            <option value="">—</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
          </select>
          <label class="form-label">Address</label>
          <input class="form-control mb-3" name="address">
          <label class="form-label">Phone</label>
          <input class="form-control mb-3" name="phone">
          <label class="form-label">Email</label>
          <input class="form-control mb-3" name="email">
          <input type="hidden" name="status" value="Active">
          <button class="btn btn-dark w-100" type="submit">Save</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body p-0">
        <table class="table mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Name</th>
              <th>City</th>
              <th>Students</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($schools as $school)
              <tr>
                <td class="ps-4">{{ $school->name }}</td>
                <td>{{ $school->city?->name ?: '—' }}</td>
                <td>{{ $school->students_count }}</td>
                <td>{{ $school->status }}</td>
                <td><a href="{{ route('schools.show', $school) }}">View</a></td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center py-4 text-muted">No schools yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <x-app-pagination :paginator="$schools" label="schools" />
    </div>
  </div>
</div>
@endsection
