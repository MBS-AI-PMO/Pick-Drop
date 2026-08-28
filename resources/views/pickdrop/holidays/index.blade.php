@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Holiday calendar</h4>
    <p class="text-secondary mb-0">School and public holidays skip pickups for matching cities</p>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Add holiday</h6></div>
      <div class="card-body">
        <form method="POST" action="{{ route('holidays.store') }}">
          @csrf
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control mb-3" value="{{ old('date') }}" required>
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control mb-3" value="{{ old('name') }}" required>
          <label class="form-label">Type</label>
          <select name="type" class="form-select mb-3" required>
            <option value="public">Public</option>
            <option value="school">School</option>
            <option value="custom">Custom</option>
          </select>
          <label class="form-label">City</label>
          <select name="city_id" class="form-select mb-3">
            <option value="">All cities</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}" {{ (string) old('city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
            @endforeach
          </select>
          <button class="btn btn-dark w-100" type="submit">Save holiday</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4 py-3">Date</th>
                <th class="py-3">Name</th>
                <th class="py-3">Type</th>
                <th class="py-3">Applies to</th>
                <th class="py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($holidays as $holiday)
                <tr>
                  <td class="ps-4">{{ $holiday->date?->format('d M Y') }}</td>
                  <td>{{ $holiday->name }}</td>
                  <td>{{ ucfirst($holiday->type) }}</td>
                  <td>{{ $holiday->city?->name ?? 'All cities' }}</td>
                  <td class="text-center">
                    <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" onsubmit="return confirm('Remove this holiday?')">
                      @csrf
                      @method('DELETE')
                      <button class="action-btn action-btn-view" type="submit" title="Delete">
                        <i data-lucide="trash-2"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center py-5 text-muted">No holidays added yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <x-app-pagination :paginator="$holidays" label="holidays" />
    </div>
  </div>
</div>

@endsection
