@extends('layout.master')
@section('page-content-class', 'container-fluid')

@php
  $account = $bill->driver?->paymentAccountDetails() ?? [];
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-start flex-wrap grid-margin gap-3">
  <div>
    <a href="{{ route('driver-payroll.index') }}" class="text-secondary fs-13px text-decoration-none d-inline-flex align-items-center gap-1 mb-2">
      <i data-lucide="arrow-left" class="icon-xs"></i> Back to Driver Payments
    </a>
    <h4 class="mb-1">{{ $bill->bill_number }}</h4>
    <p class="text-secondary mb-0">{{ $bill->driver?->name }} · {{ $bill->periodLabel() }}</p>
  </div>
  <span class="badge rounded-pill px-3 py-2 fs-13px" style="{{ $bill->statusBadgeStyle() }}">{{ $bill->statusLabel() }}</span>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Bill summary</h6></div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-sm-6">
            <p class="text-secondary fs-12px mb-1 text-uppercase fw-semibold">Driver</p>
            <p class="fw-semibold mb-0">
              <a href="{{ route('driver-payroll.driver', $bill->driver_id) }}" class="text-decoration-none">{{ $bill->driver?->name }}</a>
            </p>
            <small class="text-muted">{{ $bill->driver?->phone ?: $bill->driver?->email }}</small>
          </div>
          <div class="col-sm-6">
            <p class="text-secondary fs-12px mb-1 text-uppercase fw-semibold">Shift / student</p>
            <p class="fw-semibold mb-0">{{ $bill->pickupRequest?->student?->name ?: '—' }}</p>
            <a href="{{ route('pickup-requests.show', $bill->pickup_request_id) }}" class="fs-12px">Shift #{{ $bill->pickup_request_id }}</a>
          </div>
          <div class="col-sm-6">
            <p class="text-secondary fs-12px mb-1 text-uppercase fw-semibold">Billing period</p>
            <p class="fw-semibold mb-0">{{ $bill->periodLabel() }}</p>
          </div>
          <div class="col-sm-6">
            <p class="text-secondary fs-12px mb-1 text-uppercase fw-semibold">Monthly rate</p>
            <p class="fw-semibold mb-0">PKR {{ number_format((float) $bill->monthly_rate, 2) }}</p>
          </div>
          <div class="col-12">
            <p class="text-secondary fs-12px mb-1 text-uppercase fw-semibold">Payable amount</p>
            <h3 class="fw-bold mb-2">{{ $bill->formattedAmount() }}</h3>
            <div class="d-flex flex-wrap gap-3 fs-13px">
              <span><strong>{{ $bill->present_days }}</strong> present</span>
              <span><strong>{{ $bill->absent_days }}</strong> absent</span>
              <span><strong>{{ $bill->skipped_days }}</strong> leave</span>
              <span><strong>{{ $bill->holiday_days }}</strong> holiday</span>
              <span class="text-secondary">of {{ $bill->scheduled_days }} scheduled days</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h6 class="mb-0">Driver account details</h6></div>
      <div class="card-body">
        @if($bill->driver?->hasPaymentAccount())
          <div class="row g-3">
            <div class="col-sm-6">
              <p class="text-secondary fs-12px mb-1">Bank name</p>
              <p class="fw-semibold mb-0">{{ $account['bank_name'] ?: '—' }}</p>
            </div>
            <div class="col-sm-6">
              <p class="text-secondary fs-12px mb-1">Account title</p>
              <p class="fw-semibold mb-0">{{ $account['account_title'] ?: '—' }}</p>
            </div>
            <div class="col-sm-6">
              <p class="text-secondary fs-12px mb-1">Account number</p>
              <p class="fw-semibold mb-0">{{ $account['account_number'] ?: '—' }}</p>
            </div>
            <div class="col-sm-6">
              <p class="text-secondary fs-12px mb-1">IBAN</p>
              <p class="fw-semibold mb-0">{{ $account['iban'] ?: '—' }}</p>
            </div>
          </div>
        @else
          <p class="text-secondary mb-0">No bank account details on file for this driver.</p>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    @if($bill->status === \App\Models\DriverPayrollBill::STATUS_PENDING)
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Actions</h6></div>
      <div class="card-body d-grid gap-2">
        <form method="POST" action="{{ route('driver-payroll.approve', $bill) }}">
          @csrf
          <button type="submit" class="btn btn-outline-primary w-100">Approve bill</button>
        </form>
        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#payDriverModal">Approve &amp; record payment</button>
        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectBillModal">Reject request</button>
      </div>
    </div>
    @elseif($bill->status === \App\Models\DriverPayrollBill::STATUS_APPROVED)
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Record payment</h6></div>
      <div class="card-body">
        <p class="text-secondary fs-13px mb-3">Approved on {{ $bill->approved_at?->format('d M Y') }} by {{ $bill->approver?->name }}.</p>
        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#payDriverModal">Record payment</button>
      </div>
    </div>
    @elseif($bill->status === \App\Models\DriverPayrollBill::STATUS_PAID)
    <div class="card mb-3 border-success">
      <div class="card-header bg-success bg-opacity-10"><h6 class="mb-0 text-success">Payment completed</h6></div>
      <div class="card-body">
        <p class="mb-2"><span class="text-secondary">Paid on:</span> <strong>{{ $bill->paid_at?->format('d M Y, h:i A') }}</strong></p>
        <p class="mb-2"><span class="text-secondary">Recorded by:</span> <strong>{{ $bill->payer?->name ?: '—' }}</strong></p>
        @if($bill->notes)<p class="mb-0 text-secondary fs-13px">{{ $bill->notes }}</p>@endif
      </div>
    </div>
    @elseif($bill->status === \App\Models\DriverPayrollBill::STATUS_REJECTED)
    <div class="card mb-3 border-danger">
      <div class="card-header bg-danger bg-opacity-10"><h6 class="mb-0 text-danger">Request rejected</h6></div>
      <div class="card-body">
        <p class="mb-0 text-secondary">{{ $bill->notes ?: 'This payment request was rejected by an administrator.' }}</p>
      </div>
    </div>
    @endif

    <div class="card">
      <div class="card-header"><h6 class="mb-0">Need attendance detail?</h6></div>
      <div class="card-body">
        <p class="text-secondary fs-13px mb-3">Daily attendance calendars are available on the driver detail page before billing.</p>
        <a href="{{ route('driver-payroll.driver', ['user' => $bill->driver_id, 'month' => $bill->period_start->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary w-100">View driver details</a>
      </div>
    </div>
  </div>
</div>

@if(in_array($bill->status, [\App\Models\DriverPayrollBill::STATUS_PENDING, \App\Models\DriverPayrollBill::STATUS_APPROVED]))
<div class="modal fade" id="payDriverModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('driver-payroll.pay', $bill) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Record driver payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Confirm payment of <strong>{{ $bill->formattedAmount() }}</strong> to <strong>{{ $bill->driver?->name }}</strong>.</p>
          @if($bill->driver?->hasPaymentAccount())
            <div class="border rounded p-3 mb-3 fs-13px">
              <div><span class="text-secondary">Bank:</span> {{ $account['bank_name'] ?: '—' }}</div>
              <div><span class="text-secondary">Account:</span> {{ $account['account_number'] ?: '—' }}</div>
              @if($account['iban'])<div><span class="text-secondary">IBAN:</span> {{ $account['iban'] }}</div>@endif
            </div>
          @endif
          <label class="form-label">Payment notes <span class="text-secondary">(optional)</span></label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Bank reference, transaction ID, etc."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm payment</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@if($bill->status === \App\Models\DriverPayrollBill::STATUS_PENDING)
<div class="modal fade" id="rejectBillModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('driver-payroll.reject', $bill) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Reject payment request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Reason <span class="text-secondary">(optional)</span></label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Provide a reason for rejection..."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject request</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@endsection
