@php
  $point = $point ?? null;
  $pickupAddress = $point?->pickup_address;
  if (($pickupAddress === null || $pickupAddress === '') && $point && in_array($point->type, ['pickup', 'both'], true)) {
      $pickupAddress = $point->address;
  }
  $dropAddress = $point?->drop_address;
  if (($dropAddress === null || $dropAddress === '') && $point && in_array($point->type, ['drop', 'both'], true)) {
      $dropAddress = $point->address;
  }
@endphp
<label class="form-label">City</label>
<select name="city_id" class="form-select mb-3" required>
  <option value="">Select city</option>
  @foreach($cities as $city)
    <option value="{{ $city->id }}" @selected((int) old('city_id', $point?->city_id) === (int) $city->id)>{{ $city->name }}</option>
  @endforeach
</select>

<label class="form-label">Area (optional)</label>
<select name="area_id" class="form-select mb-3">
  <option value="">Any area</option>
  @foreach($areas as $area)
    <option value="{{ $area->id }}"
            data-city="{{ $area->city_id }}"
            @selected((int) old('area_id', $point?->area_id) === (int) $area->id)>
      {{ $area->name }}
    </option>
  @endforeach
</select>

<label class="form-label">Point name</label>
<input type="text" name="name" class="form-control mb-3" value="{{ old('name', $point?->name) }}" required placeholder="e.g. G-10 Markaz Gate 2">

<label class="form-label">Type</label>
<select name="type" class="form-select mb-3" required>
  @foreach($types as $value => $label)
    <option value="{{ $value }}" @selected(old('type', $point?->type ?? 'both') === $value)>{{ $label }}</option>
  @endforeach
</select>

<label class="form-label">Pickup Point</label>
<input type="text" name="pickup_address" class="form-control mb-3" value="{{ old('pickup_address', $pickupAddress) }}" required placeholder="Pickup address">

<label class="form-label">Drop-off Point</label>
<input type="text" name="drop_address" class="form-control mb-3" value="{{ old('drop_address', $dropAddress) }}" required placeholder="Drop-off address">

<div class="row g-2 mb-3">
  <div class="col-6">
    <label class="form-label">Latitude</label>
    <input type="number" step="any" name="latitude" class="form-control" value="{{ old('latitude', $point?->latitude) }}">
  </div>
  <div class="col-6">
    <label class="form-label">Longitude</label>
    <input type="number" step="any" name="longitude" class="form-control" value="{{ old('longitude', $point?->longitude) }}">
  </div>
</div>

<label class="form-label">Status</label>
<select name="status" class="form-select">
  <option value="Active" @selected(old('status', $point?->status ?? 'Active') === 'Active')>Active</option>
  <option value="Inactive" @selected(old('status', $point?->status) === 'Inactive')>Inactive</option>
</select>
