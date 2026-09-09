@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Pickup / Drop points</h4>
    <p class="text-secondary mb-0">Admin master points apps can pick when booking (optional presets)</p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPointModal">
    <i data-lucide="plus" class="icon-sm me-1"></i> Add point
  </button>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('locations.points.index') }}" class="row g-2 align-items-center">
      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="Search point..." value="{{ request('search') }}">
      </div>
      <div class="col-md-3">
        <select name="city_id" class="form-select" onchange="this.form.submit()">
          <option value="">All cities</option>
          @foreach($cities as $city)
            <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>{{ $city->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <select name="type" class="form-select" onchange="this.form.submit()">
          <option value="">All types</option>
          @foreach($types as $value => $label)
            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-outline-primary">Filter</button>
      </div>
      @if(request()->hasAny(['search','city_id','type']))
        <div class="col-auto">
          <a href="{{ route('locations.points.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
      @endif
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
            <th class="py-3">Name</th>
            <th class="py-3">City / Area</th>
            <th class="py-3">Type</th>
            <th class="py-3">Coords</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($points as $point)
            <tr>
              <td class="ps-4 py-3 text-muted">{{ $points->firstItem() + $loop->index }}</td>
              <td class="py-3">
                <div class="fw-semibold">{{ $point->name }}</div>
                @if($point->address)
                  <small class="text-secondary">{{ $point->address }}</small>
                @endif
              </td>
              <td class="py-3">
                {{ $point->city?->name ?? '—' }}
                <div class="text-secondary small">{{ $point->area?->name ?? 'Any area' }}</div>
              </td>
              <td class="py-3">{{ $point->typeLabel() }}</td>
              <td class="py-3 text-secondary">
                @if($point->latitude && $point->longitude)
                  {{ $point->latitude }}, {{ $point->longitude }}
                @else
                  —
                @endif
              </td>
              <td class="py-3">
                <span class="badge {{ strcasecmp($point->status, 'Active') === 0 ? 'bg-success' : 'bg-secondary' }}">{{ $point->status }}</span>
              </td>
              <td class="py-3 text-center">
                <button type="button" class="action-btn" title="Edit"
                        data-bs-toggle="modal" data-bs-target="#editPointModal{{ $point->id }}">
                  <i data-lucide="pencil"></i>
                </button>
                <form method="POST" action="{{ route('locations.points.destroy', $point) }}" class="d-inline"
                      onsubmit="return confirm('Remove this point?')">
                  @csrf
                  @method('DELETE')
                  <button class="action-btn action-btn-delete" type="submit" title="Delete">
                    <i data-lucide="trash-2"></i>
                  </button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="editPointModal{{ $point->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST" action="{{ route('locations.points.update', $point) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h5 class="modal-title">Edit point</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      @include('pickdrop.locations.partials.point-form', ['point' => $point, 'cities' => $cities, 'areas' => $areas, 'types' => $types])
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No pickup/drop points yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$points" label="points" />
</div>

<div class="modal fade" id="addPointModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('locations.points.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add pickup/drop point</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @include('pickdrop.locations.partials.point-form', ['point' => null, 'cities' => $cities, 'areas' => $areas, 'types' => $types])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add point</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
