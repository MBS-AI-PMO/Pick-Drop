@extends('layout.master')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Calendar</h4>
    <p class="text-secondary mb-0">Announce holidays and off days. Matching pickups will be skipped.</p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announceOffDayModal">
    <i data-lucide="plus" class="icon-sm me-1"></i> Announce off day
  </button>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
          <div class="d-flex align-items-center gap-2">
            <a href="{{ $prevMonthUrl }}" class="btn btn-outline-secondary btn-sm" title="Previous month">
              <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
            </a>
            <h5 class="mb-0 fw-bold">{{ $cursor->format('F Y') }}</h5>
            <a href="{{ $nextMonthUrl }}" class="btn btn-outline-secondary btn-sm" title="Next month">
              <i data-lucide="chevron-right" style="width:16px;height:16px;"></i>
            </a>
          </div>
          <a href="{{ $todayUrl }}" class="btn btn-outline-secondary btn-sm">Today</a>
        </div>

        <div class="pd-cal-weekdays">
          <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>
        <div class="pd-cal-grid">
          @foreach($days as $day)
            <button type="button"
                    class="pd-cal-day {{ $day['in_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['holidays']->isNotEmpty() ? 'has-off' : '' }}"
                    data-date="{{ $day['date'] }}"
                    title="Announce off day on {{ \Illuminate\Support\Carbon::parse($day['date'])->format('d M Y') }}">
              <span class="pd-cal-num">{{ $day['label'] }}</span>
              @foreach($day['holidays'] as $holiday)
                <span class="pd-cal-chip pd-cal-chip-{{ $holiday->type }}">{{ $holiday->name }}</span>
              @endforeach
            </button>
          @endforeach
        </div>

        <div class="d-flex flex-wrap gap-3 mt-3 small text-secondary">
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-public"></span> Public holiday</span>
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-school"></span> Institution holiday</span>
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-custom"></span> Announced off day</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Announced this month</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <tbody>
              @forelse($announcements as $holiday)
                <tr>
                  <td class="ps-3">
                    <div class="fw-semibold">{{ $holiday->name }}</div>
                    <div class="text-secondary small">{{ $holiday->date?->format('d M Y') }} · {{ $holiday->typeLabel() }} · {{ $holiday->city?->name ?? 'All cities' }}</div>
                  </td>
                  <td class="text-end pe-3">
                    <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" onsubmit="return confirm('Remove this announcement?')">
                      @csrf
                      @method('DELETE')
                      <input type="hidden" name="year" value="{{ $cursor->year }}">
                      <input type="hidden" name="month" value="{{ $cursor->month }}">
                      <button class="action-btn action-btn-delete" type="submit" title="Remove">
                        <i data-lucide="trash-2"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="text-center py-5 text-muted">No holidays or off days announced for {{ $cursor->format('F') }}.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="announceOffDayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Announce off day</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('holidays.store') }}">
        @csrf
        <div class="modal-body pt-2">
          <p class="text-secondary small">Pickups in the selected city will be skipped on this date.</p>
          <label class="form-label">Date</label>
          <input type="date" name="date" id="holiday_date" class="form-control mb-3" value="{{ old('date') }}" required>
          <label class="form-label">Title</label>
          <input type="text" name="name" class="form-control mb-3" value="{{ old('name') }}" placeholder="e.g. Eid holiday, Summer break" required>
          <label class="form-label">Type</label>
          <select name="type" class="form-select mb-3" required>
            @foreach($types as $value => $label)
              <option value="{{ $value }}" {{ old('type', 'custom') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <label class="form-label">City</label>
          <select name="city_id" class="form-select">
            <option value="">All cities</option>
            @foreach($cities as $city)
              <option value="{{ $city->id }}" {{ (string) old('city_id') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">Announce</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('style')
<style>
  .pd-cal-weekdays,
  .pd-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
  }
  .pd-cal-weekdays {
    margin-bottom: 6px;
  }
  .pd-cal-weekdays span {
    font-size: 12px;
    font-weight: 600;
    color: var(--pd-muted, #697586);
    text-align: center;
    padding: 4px 0;
  }
  .pd-cal-grid {
    gap: 6px;
  }
  .pd-cal-day {
    min-height: 92px;
    border: 1px solid var(--pd-border, #e6ebf2);
    border-radius: 10px;
    background: var(--pd-card-bg, #fff);
    padding: 8px;
    text-align: left;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    cursor: pointer;
    transition: border-color .12s ease, box-shadow .12s ease, transform .12s ease;
  }
  .pd-cal-day:hover {
    border-color: #3f6fd9;
    box-shadow: 0 6px 16px rgba(63, 111, 217, .08);
    transform: translateY(-1px);
  }
  .pd-cal-day.is-muted {
    opacity: .38;
  }
  .pd-cal-day.is-today {
    border-color: #3f6fd9;
    box-shadow: inset 0 0 0 1px #3f6fd9;
  }
  .pd-cal-day.has-off {
    background: #fbf8f2;
  }
  .pd-cal-num {
    font-size: 13px;
    font-weight: 700;
    color: var(--pd-text, #1f2937);
  }
  .pd-cal-chip {
    display: block;
    width: 100%;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.25;
    border-radius: 6px;
    padding: 3px 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .pd-cal-chip-public { background: #fde8ee; color: #9f1239; }
  .pd-cal-chip-school { background: #eef4ff; color: #1d4ed8; }
  .pd-cal-chip-custom { background: #fff4db; color: #92400e; }
  .pd-cal-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
  }
  [data-bs-theme="dark"] .pd-cal-day {
    background: var(--pd-theme-card, #1e2129);
    border-color: var(--pd-theme-border, rgba(255,255,255,.08));
  }
  [data-bs-theme="dark"] .pd-cal-day.has-off {
    background: rgba(217, 119, 6, .12);
  }
  [data-bs-theme="dark"] .pd-cal-num {
    color: #f4f7fb;
  }
  @media (max-width: 767.98px) {
    .pd-cal-day { min-height: 72px; padding: 6px; }
    .pd-cal-chip { font-size: 10px; }
  }
</style>
@endpush

@push('custom-scripts')
<script>
  document.querySelectorAll('.pd-cal-day').forEach(function (day) {
    day.addEventListener('click', function () {
      var dateInput = document.getElementById('holiday_date');
      if (dateInput) dateInput.value = this.getAttribute('data-date');
      var modal = new bootstrap.Modal(document.getElementById('announceOffDayModal'));
      modal.show();
    });
  });

  @if($errors->any())
  document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('announceOffDayModal')).show();
  });
  @endif
</script>
@endpush
