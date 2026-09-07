@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
  <div>
    <h4 class="mb-1">Driver payroll</h4>
    <p class="text-secondary mb-0">Pay at month end for days the driver actually worked. Leave and no-shows are cut.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary" href="{{ route('payrolls.index', ['month' => $prev]) }}">← {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $prev)->format('M Y') }}</a>
    <a class="btn btn-outline-secondary" href="{{ route('payrolls.index', ['month' => $next]) }}">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $next)->format('M Y') }} →</a>
    <form method="POST" action="{{ route('payrolls.recalculate') }}">
      @csrf
      <input type="hidden" name="month" value="{{ $month }}">
      <button class="btn btn-outline-dark" type="submit">Refresh numbers</button>
    </form>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-3 align-items-center">
      <div class="col-lg-4">
        <div class="fw-semibold">{{ $cursor->format('F Y') }}</div>
        <div class="text-secondary small">
          @if($monthEnded)
            Month has ended. Open each driver, check days, then pay.
          @else
            Month is still running. Days left are not paid yet. Pay after {{ $cursor->copy()->endOfMonth()->format('d M') }}.
          @endif
        </div>
      </div>
      <div class="col-lg-8">
        <div class="d-flex flex-wrap gap-4 small">
          <div>
            <div class="text-secondary">1. Days happen</div>
            <div class="fw-semibold">Present / leave / no-show</div>
          </div>
          <div>
            <div class="text-secondary">2. You check</div>
            <div class="fw-semibold">Open a driver and fix any day</div>
          </div>
          <div>
            <div class="text-secondary">3. You pay</div>
            <div class="fw-semibold">Once, at month end</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Present so far</div>
      <div class="fw-bold fs-4">{{ $totals['present'] }}</div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Days still left</div>
      <div class="fw-bold fs-4">{{ $totals['upcoming'] }}</div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Cut (leave + no-show)</div>
      <div class="fw-bold fs-4">{{ $totals['cut'] }}</div>
      <div class="text-secondary small">PKR {{ number_format($totals['deduction'], 0) }}</div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">{{ $monthEnded ? 'To pay now' : 'Earned so far' }}</div>
      <div class="fw-bold fs-4">PKR {{ number_format($totals['earned'], 0) }}</div>
      @unless($monthEnded)
        <div class="text-secondary small">If they finish remaining: PKR {{ number_format($totals['expected'], 0) }}</div>
      @endunless
    </div></div>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th class="ps-3">Driver</th>
            <th>Present</th>
            <th>Left</th>
            <th>Cut</th>
            <th>Earned so far</th>
            <th>If they finish remaining</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($payrolls as $payroll)
            <tr>
              <td class="ps-3">
                <div class="fw-semibold">{{ $payroll->driver?->name ?? 'Driver' }}</div>
                <div class="text-secondary small">
                  {{ $payroll->items_count }} student{{ $payroll->items_count === 1 ? '' : 's' }}
                  @if((float) $payroll->daily_rate > 0)
                    · daily ~ PKR {{ number_format($payroll->daily_rate, 0) }}
                  @else
                    · no monthly rate set, so pay is PKR 0
                  @endif
                </div>
              </td>
              <td>{{ $payroll->worked_days }}</td>
              <td>{{ $payroll->upcoming_days }}</td>
              <td>
                <div>{{ $payroll->leave_days + $payroll->absent_days }} days</div>
                @if($payroll->deduction_note)
                  <div class="text-secondary small">{{ $payroll->deduction_note }}</div>
                @elseif($payroll->leave_days + $payroll->absent_days > 0)
                  <div class="text-secondary small">PKR {{ number_format($payroll->deduction, 0) }} cut</div>
                @endif
              </td>
              <td class="fw-semibold">PKR {{ number_format($payroll->net, 0) }}</td>
              <td>PKR {{ number_format($payroll->expected_net, 0) }}</td>
              <td>
                @php
                  $phase = $payroll->phase();
                  $style = match ($phase) {
                    'paid' => 'background:#d1fae5;color:#065f46;',
                    'approved', 'ready' => 'background:#dbeafe;color:#1e40af;',
                    default => 'background:#fef9c3;color:#92400e;',
                  };
                @endphp
                <span class="badge rounded-pill px-3 py-2" style="{{ $style }}">{{ $payroll->phaseLabel() }}</span>
              </td>
              <td class="text-end pe-3">
                <a href="{{ route('payrolls.show', $payroll) }}" class="btn btn-sm btn-dark">Manage days</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-secondary py-4">
                No paid shifts for this month yet. When a parent pays and a driver is assigned, they appear here after Refresh.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
