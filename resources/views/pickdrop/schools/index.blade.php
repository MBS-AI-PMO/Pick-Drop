@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Institutions</h4>
    <p class="text-secondary mb-0">Admin-managed list of schools, colleges, universities, offices and other pickup destinations</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Add institution</h6>
        <p class="text-secondary small mb-0 mt-1">Admin only. Parents and drivers can select from this list, not add to it.</p>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('schools.store') }}">
          @csrf
          <label class="form-label">Name</label>
          <input class="form-control mb-3" name="name" value="{{ old('name') }}" required>
          <label class="form-label">Category</label>
          <select class="form-select mb-3" name="category" required>
            @foreach($categories as $value => $label)
              <option value="{{ $value }}" {{ old('category', 'school') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <label class="form-label">City</label>
          <select class="form-select mb-3" name="city_id">
            <option value="">—</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}" {{ (string) old('city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
            @endforeach
          </select>
          <label class="form-label">Address</label>
          <input class="form-control mb-3" name="address" value="{{ old('address') }}">
          <label class="form-label">Phone</label>
          <input class="form-control mb-3" name="phone" value="{{ old('phone') }}">
          <label class="form-label">Email</label>
          <input class="form-control mb-3" name="email" value="{{ old('email') }}">
          <input type="hidden" name="status" value="Active">
          <button class="btn btn-dark w-100" type="submit">Save</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body border-bottom py-3">
        <form method="GET" action="{{ route('schools.index') }}" class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label mb-1">Search</label>
            <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="Search by name">
          </div>
          <div class="col-md-4">
            <label class="form-label mb-1">Category</label>
            <select class="form-select" name="category" onchange="this.form.submit()">
              <option value="">All categories</option>
              @foreach($categories as $value => $label)
                <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-outline-secondary w-100" type="submit">Filter</button>
          </div>
        </form>
      </div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Name</th>
              <th>Category</th>
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
                <td>{{ $school->categoryLabel() }}</td>
                <td>{{ $school->city?->name ?: '—' }}</td>
                <td>{{ $school->students_count }}</td>
                <td>{{ $school->status }}</td>
                <td><a href="{{ route('schools.show', $school) }}">View</a></td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center py-4 text-muted">No institutions yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <x-app-pagination :paginator="$schools" label="institutions" />
    </div>
  </div>
</div>
@endsection
