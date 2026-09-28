@extends('layout.master')
@section('page-content-class', 'container-fluid')

@php
  $badge = function (string $status) {
      return match ($status) {
          'received' => ['Received', 'background:#d1fae5;color:#065f46;'],
          'not_received' => ['Not received', 'background:#fee2e2;color:#991b1b;'],
          default => ['Pending', 'background:#fef9c3;color:#92400e;'],
      };
  };
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Payment screenshots</h4>
    <p class="text-secondary mb-0">Screenshots customers send after paying. Mark each one received or not received.</p>
  </div>
  <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Back to invoices</a>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('payments.screenshots') }}" class="row g-2 align-items-center">
      <div class="col-12 col-md-3">
        <select class="form-select" name="receipt_status" onchange="this.form.submit()">
          <option value="">All statuses</option>
          <option value="not_received" {{ request('receipt_status') === 'not_received' ? 'selected' : '' }}>Not received</option>
          <option value="received" {{ request('receipt_status') === 'received' ? 'selected' : '' }}>Received</option>
        </select>
      </div>
      @if(request()->filled('receipt_status'))
        <div class="col-auto">
          <a href="{{ route('payments.screenshots') }}" class="btn btn-outline-danger">Clear</a>
        </div>
      @endif
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 w-100">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Screenshot</th>
            <th class="py-3">Invoice</th>
            <th class="py-3">Customer</th>
            <th class="py-3">Method</th>
            <th class="py-3">Amount</th>
            <th class="py-3">Sent</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-end pe-4">Update</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments as $payment)
            @php [$label, $style] = $badge($payment->receiptStatus()); @endphp
            <tr>
              <td class="ps-4">
                @if($payment->proofUrl())
                  <a href="{{ $payment->proofUrl() }}" target="_blank">
                    <img src="{{ $payment->proofUrl() }}" alt="Payment screenshot" style="width:72px;height:72px;object-fit:cover;border-radius:8px;">
                  </a>
                @else
                  —
                @endif
              </td>
              <td>
                @if($payment->invoice)
                  <a href="{{ route('payments.show', $payment->invoice) }}" class="fw-semibold">{{ $payment->invoice->invoice_number }}</a>
                @else
                  —
                @endif
              </td>
              <td>
                <div>{{ $payment->user?->name ?? $payment->invoice?->customer?->name ?? '—' }}</div>
                <small class="text-muted">{{ $payment->reference ?: 'No reference' }}</small>
              </td>
              <td>{{ str_replace('_', ' ', $payment->method) }}</td>
              <td class="fw-semibold">{{ $payment->invoice?->formatMoney((float) $payment->amount) ?? number_format((float) $payment->amount, 2) }}</td>
              <td>{{ $payment->created_at?->format('d M Y, h:i A') }}</td>
              <td><span class="badge rounded-pill px-3 py-1" style="{{ $style }}">{{ $label }}</span></td>
              <td class="text-end pe-4">
                <form method="POST" action="{{ route('payments.receipt', $payment) }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="receipt_status" value="received">
                  <button class="btn btn-sm btn-dark" type="submit" {{ $payment->receiptStatus() === 'received' ? 'disabled' : '' }}>Received</button>
                </form>
                <form method="POST" action="{{ route('payments.receipt', $payment) }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="receipt_status" value="not_received">
                  <button class="btn btn-sm btn-outline-danger" type="submit" {{ $payment->receiptStatus() === 'not_received' ? 'disabled' : '' }}>Not received</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">No payment screenshots yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$payments" label="screenshots" />
</div>

@endsection
