@extends('layout.master')

@php
  $locked = $payroll->isLocked();
  $cutDays = $payroll->leave_days + $payroll->absent_days;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
  <div>
    <a href="{{ route('payrolls.index', ['month' => $payroll->month]) }}" class="text-secondary small">← All drivers</a>
    <h4 class="mb-1">{{ $payroll->driver?->name }} · {{ $monthLabel }}</h4>
    <p class="text-secondary mb-0">
      Pay = present days × daily rate.
      @if($cutDays > 0)
        {{ $payroll->deduction_note ?: $cutDays . ' days deducted.' }}
      @else
        No cuts yet.
      @endif
    </p>
  </div>
  <div class="d-flex flex-wrap gap-2 align-items-center">
    @unless($locked)
      <form method="POST" action="{{ route('payrolls.recalculate') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $payroll->month }}">
        <button class="btn btn-outline-secondary" type="submit">Refresh</button>
      </form>
      @if($payroll->status === 'draft')
        <form method="POST" action="{{ route('payrolls.approve', $payroll) }}">
          @csrf
          <button class="btn btn-outline-dark" type="submit">Looks correct</button>
        </form>
      @endif
      <form method="POST" action="{{ route('payrolls.pay', $payroll) }}" class="d-flex flex-wrap gap-2 align-items-center">
        @csrf
        @unless($payroll->monthEnded())
          <label class="small text-secondary mb-0">
            <input type="checkbox" name="pay_now" value="1" class="form-check-input me-1">
            Pay earned amount now
          </label>
        @endunless
        <button class="btn btn-primary" type="submit">
          {{ $payroll->monthEnded() ? 'Pay PKR ' . number_format($payroll->net, 0) : 'Pay earned so far' }}
        </button>
      </form>
    @else
      <span class="badge rounded-pill px-3 py-2" style="background:#d1fae5;color:#065f46;">Paid {{ $payroll->paid_at?->format('d M Y') }}</span>
    @endunless
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-2">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Present</div>
      <div class="fw-bold fs-4">{{ $payroll->worked_days }}</div>
    </div></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Still left</div>
      <div class="fw-bold fs-4">{{ $payroll->upcoming_days }}</div>
    </div></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Leave</div>
      <div class="fw-bold fs-4">{{ $payroll->leave_days }}</div>
    </div></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">No-show</div>
      <div class="fw-bold fs-4">{{ $payroll->absent_days }}</div>
    </div></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Earned so far</div>
      <div class="fw-bold">PKR {{ number_format($payroll->net, 0) }}</div>
    </div></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">If remaining days are present</div>
      <div class="fw-bold">PKR {{ number_format($payroll->expected_net, 0) }}</div>
    </div></div>
  </div>
</div>

<p class="text-secondary small mb-3">
  Click a day to mark it. <strong>Present</strong> is paid.
  <strong>Leave</strong> and <strong>No-show</strong> are deducted.
  Grey days are still coming — they are not paid until they happen.
</p>
@if((float) $payroll->daily_rate <= 0)
  <div class="alert alert-warning">Driver monthly rate is PKR 0 on these shifts. Set it in Pick-Drop Charges, then Refresh. Until then pay will stay 0.</div>
@endif

@foreach($shifts as $shift)
  @php
    $item = $shift['item'];
    $requestItem = $shift['request'];
  @endphp
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <h6 class="mb-0">{{ $requestItem?->requesterName() ?? 'Shift' }} · #{{ $item->pickup_request_id }}</h6>
        <div class="text-secondary small">
          PKR {{ number_format($item->daily_rate, 0) }} / day
          · {{ $item->worked_days }} present
          · {{ $item->upcoming_days }} left
          @if($item->deduction_note)
            · {{ $item->deduction_note }}
          @endif
        </div>
      </div>
      <div class="fw-semibold">PKR {{ number_format($item->net, 0) }} earned</div>
    </div>
    <div class="card-body">
      @forelse($shift['days'] as $day)
        @php
          $chip = match ($day['kind']) {
            'present' => 'background:#d1fae5;color:#065f46;',
            'leave' => 'background:#fef3c7;color:#92400e;',
            'absent' => 'background:#fee2e2;color:#991b1b;',
            'holiday', 'parent_skip' => 'background:#e5e7eb;color:#374151;',
            default => 'background:#f3f4f6;color:#6b7280;',
          };
        @endphp
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
          <div>
            <div class="fw-semibold">
              {{ $day['label'] }}
              @if($day['is_today'])
                <span class="badge text-bg-dark">Today</span>
              @endif
            </div>
            <div class="small" style="{{ $chip }}display:inline-block;border-radius:999px;padding:2px 10px;">{{ $day['kind_label'] }}</div>
          </div>
          @unless($locked || in_array($day['kind'], ['holiday', 'parent_skip'], true))
            <div class="d-flex flex-wrap gap-1">
              <form method="POST" action="{{ route('payrolls.days', $payroll) }}">
                @csrf
                <input type="hidden" name="pickup_request_id" value="{{ $item->pickup_request_id }}">
                <input type="hidden" name="date" value="{{ $day['date'] }}">
                <input type="hidden" name="status" value="present">
                <button class="btn btn-sm {{ $day['kind'] === 'present' ? 'btn-dark' : 'btn-outline-success' }}" type="submit">Present</button>
              </form>
              <form method="POST" action="{{ route('payrolls.days', $payroll) }}">
                @csrf
                <input type="hidden" name="pickup_request_id" value="{{ $item->pickup_request_id }}">
                <input type="hidden" name="date" value="{{ $day['date'] }}">
                <input type="hidden" name="status" value="leave">
                <button class="btn btn-sm {{ $day['kind'] === 'leave' ? 'btn-dark' : 'btn-outline-warning' }}" type="submit">Leave</button>
              </form>
              <form method="POST" action="{{ route('payrolls.days', $payroll) }}">
                @csrf
                <input type="hidden" name="pickup_request_id" value="{{ $item->pickup_request_id }}">
                <input type="hidden" name="date" value="{{ $day['date'] }}">
                <input type="hidden" name="status" value="absent">
                <button class="btn btn-sm {{ $day['kind'] === 'absent' ? 'btn-dark' : 'btn-outline-danger' }}" type="submit">No-show</button>
              </form>
            </div>
          @endunless
        </div>
      @empty
        <p class="text-secondary mb-0">No duty days in this month for this student.</p>
      @endforelse
    </div>
  </div>
@endforeach
@endsection
