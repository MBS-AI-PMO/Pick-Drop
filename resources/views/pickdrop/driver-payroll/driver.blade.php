@extends('layout.master')
@section('page-content-class', 'container-fluid')

@php
  $payrollService = app(\App\Services\DriverPayrollService::class);
  $monthValue = $month ?? now()->format('Y-m');
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-start flex-wrap grid-margin gap-3">
  <div>
    <a href="{{ route('driver-payroll.index') }}" class="text-secondary fs-13px text-decoration-none d-inline-flex align-items-center gap-1 mb-2">
      <i data-lucide="arrow-left" class="icon-xs"></i> Back to Driver Payments
    </a>
    <h4 class="mb-1">{{ $driver->name }}</h4>
    <p class="text-secondary mb-0">{{ $driver->phone ?: $driver->email }} · {{ count($overview['shifts']) }} active shift{{ count($overview['shifts']) === 1 ? '' : 's' }}</p>
  </div>
  <div class="text-end">
    <p class="text-secondary fs-12px mb-1">Estimated for {{ $overview['period']['label'] }}</p>
    <h4 class="fw-bold mb-0">PKR {{ number_format((float) $overview['total_estimated'], 2) }}</h4>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label fs-12px">Billing period view</label>
        <select name="view" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="monthly" {{ $view === 'monthly' ? 'selected' : '' }}>Monthly</option>
          <option value="weekly" {{ $view === 'weekly' ? 'selected' : '' }}>Weekly</option>
          <option value="daily" {{ $view === 'daily' ? 'selected' : '' }}>Daily</option>
        </select>
      </div>
      @if($view === 'monthly' || $view === 'weekly')
      <div class="col-md-3">
        <label class="form-label fs-12px">Month</label>
        <input type="month" name="month" class="form-control form-control-sm" value="{{ $monthValue }}" onchange="this.form.submit()">
      </div>
      @endif
      @if($view === 'weekly')
      <div class="col-md-2">
        <label class="form-label fs-12px">Week</label>
        <select name="week" class="form-select form-select-sm" onchange="this.form.submit()">
          @for($w = 1; $w <= 5; $w++)
            <option value="{{ $w }}" {{ (int) $week === $w ? 'selected' : '' }}>Week {{ $w }}</option>
          @endfor
        </select>
      </div>
      @endif
      @if($view === 'daily')
      <div class="col-md-3">
        <label class="form-label fs-12px">Date</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
      </div>
      @endif
      <div class="col-md-auto">
        <span class="badge rounded-pill px-3 py-2" style="background:#eef4ff;color:#3f6fd9;">
          {{ $overview['period']['label'] }}
        </span>
      </div>
    </form>
  </div>
</div>

<div class="d-flex flex-wrap gap-3 mb-3">
  @foreach(['present' => 'Present', 'absent' => 'Absent', 'skipped' => 'Leave', 'holiday' => 'Holiday', 'half_day' => 'Half day', 'off' => 'Off day'] as $key => $label)
    <div class="d-flex align-items-center gap-2 fs-12px text-secondary">
      <span class="payroll-calendar-legend" style="{{ $payrollService->statusCalendarStyle($key) }}">{{ $payrollService->statusShortLabel($key) }}</span>
      {{ $label }}
    </div>
  @endforeach
</div>

@if(empty($overview['shifts']))
<div class="card">
  <div class="card-body text-center py-5 text-secondary">
    <p class="mb-0">No shifts found for this billing period.</p>
  </div>
</div>
@endif

@foreach($overview['shifts'] as $shift)
<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h6 class="mb-0">{{ $shift['student_name'] ?: 'Shift #' . $shift['pickup_request_id'] }}</h6>
      <small class="text-secondary">
        Shift #{{ $shift['pickup_request_id'] }} · {{ $shift['shift_start'] }} – {{ $shift['shift_end'] }}
      </small>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="fs-13px text-secondary">Rate: PKR {{ number_format((float) ($shift['monthly_rate'] ?? 0), 2) }}/mo</span>
      <span class="fw-bold">PKR {{ number_format((float) $shift['estimated_amount'], 2) }}</span>
      @if($shift['can_generate'])
        <form method="POST" action="{{ route('driver-payroll.generate') }}" class="d-inline">
          @csrf
          <input type="hidden" name="pickup_request_id" value="{{ $shift['pickup_request_id'] }}">
          <input type="hidden" name="period_start" value="{{ $shift['period_start'] }}">
          <input type="hidden" name="period_end" value="{{ $shift['period_end'] }}">
          <button type="submit" class="btn btn-sm btn-primary">Generate bill</button>
        </form>
      @elseif($shift['existing_bill'])
        <a href="{{ route('driver-payroll.show', $shift['existing_bill']) }}" class="btn btn-sm btn-outline-secondary">Open bill</a>
      @endif
    </div>
  </div>
  <div class="card-body">
    <div class="row g-3 mb-3">
      <div class="col-md-2 col-4"><small class="text-secondary d-block">Scheduled</small><strong>{{ $shift['stats']['scheduled_days'] }}</strong></div>
      <div class="col-md-2 col-4"><small class="text-secondary d-block">Present</small><strong class="text-success">{{ $shift['stats']['present_days'] }}</strong></div>
      <div class="col-md-2 col-4"><small class="text-secondary d-block">Absent</small><strong class="text-danger">{{ $shift['stats']['absent_days'] }}</strong></div>
      <div class="col-md-2 col-4"><small class="text-secondary d-block">Leave</small><strong>{{ $shift['stats']['skipped_days'] }}</strong></div>
      <div class="col-md-2 col-4"><small class="text-secondary d-block">Holiday</small><strong>{{ $shift['stats']['holiday_days'] }}</strong></div>
    </div>

    @if($view !== 'daily')
    <div class="payroll-calendar">
      <div class="payroll-calendar__weekdays">
        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $wd)
          <span>{{ $wd }}</span>
        @endforeach
      </div>
      @foreach($shift['calendar'] as $week)
        <div class="payroll-calendar__week">
          @foreach($week['days'] as $day)
            <div class="payroll-calendar__day {{ $day['in_period'] ? '' : 'is-outside' }} {{ $day['status'] === 'outside' ? 'is-outside' : '' }}"
                 style="{{ $day['in_period'] && $day['status'] !== 'outside' ? $payrollService->statusCalendarStyle($day['status']) : '' }}"
                 title="{{ $day['in_period'] ? $payrollService->statusDisplayLabel($day['status']) . ' · ' . $day['date'] : '' }}">
              <span class="payroll-calendar__num">{{ $day['day'] }}</span>
              @if($day['in_period'] && $day['status'] !== 'off' && $day['status'] !== 'outside')
                <span class="payroll-calendar__mark">{{ $day['label'] }}</span>
              @endif
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
    @else
    @php $day = collect($shift['calendar'])->flatMap(fn($w) => $w['days'])->firstWhere('date', $date); @endphp
    <div class="d-flex align-items-center gap-3">
      @if($day)
        <span class="payroll-calendar-legend payroll-calendar-legend--lg" style="{{ $payrollService->statusCalendarStyle($day['status']) }}">{{ $day['label'] }}</span>
        <div>
          <strong>{{ $payrollService->statusDisplayLabel($day['status']) }}</strong>
          <div class="text-secondary fs-13px">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('l, d M Y') }}</div>
        </div>
      @else
        <span class="text-secondary">No scheduled activity for this date.</span>
      @endif
    </div>
    @endif
  </div>
</div>
@endforeach

@if($view === 'monthly' && !empty($overview['shifts']))
  @php
    $canGenerateAny = collect($overview['shifts'])->contains(fn ($s) => $s['can_generate']);
  @endphp
  @if($canGenerateAny)
  <div class="d-flex justify-content-end">
    <form method="POST" action="{{ route('driver-payroll.generate-all', $driver) }}">
      @csrf
      <input type="hidden" name="month" value="{{ $monthValue }}">
      <button type="submit" class="btn btn-primary">Generate all shift bills for this month</button>
    </form>
  </div>
  @endif
@endif

@endsection

@push('style')
<style>
  .payroll-calendar__weekdays,
  .payroll-calendar__week {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 6px;
  }
  .payroll-calendar__weekdays {
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
  }
  .payroll-calendar__weekdays span,
  .payroll-calendar__day {
    text-align: center;
  }
  .payroll-calendar__week + .payroll-calendar__week {
    margin-top: 6px;
  }
  .payroll-calendar__day {
    min-height: 54px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 6px 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
  }
  .payroll-calendar__day.is-outside {
    opacity: 0.35;
    background: #fafafa !important;
    color: #cbd5e1 !important;
    border-color: #f1f5f9 !important;
  }
  .payroll-calendar__num {
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
  }
  .payroll-calendar__mark {
    font-size: 10px;
    font-weight: 800;
    line-height: 1;
  }
  .payroll-calendar-legend {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    font-size: 10px;
    font-weight: 800;
  }
  .payroll-calendar-legend--lg {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    font-size: 13px;
  }
</style>
@endpush
