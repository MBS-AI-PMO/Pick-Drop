@extends('layout.master')
@section('page-content-class', 'container-fluid')

@php
  $statusBadge = match ($invoice->status) {
      'paid' => ['Paid', 'background:#f3f4f6;color:#111111;'],
      'unpaid' => ['Unpaid', 'background:#fef9c3;color:#92400e;'],
      'overdue' => ['Overdue', 'background:#fee2e2;color:#991b1b;'],
      'cancelled' => ['Cancelled', 'background:#e5e7eb;color:#374151;'],
      default => ['Draft', 'background:#e0e7ff;color:#3730a3;'],
  };
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">{{ $invoice->invoice_number }}</h4>
    <p class="text-secondary mb-0">{{ $invoice->customer?->name }} · {{ $invoice->customer?->email }}</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route('payments.index') }}" class="btn btn-outline-dark">Back</a>
    <a href="{{ route('payments.print', $invoice) }}" target="_blank" class="btn btn-outline-dark">
      <i data-lucide="printer" class="icon-xs me-1"></i> Print
    </a>
    <form method="POST" action="{{ route('payments.destroy', $invoice) }}" onsubmit="confirmDeleteInvoice(event, this)">
      @csrf
      @method('DELETE')
      <button class="btn btn-outline-dark" type="submit">
        <i data-lucide="trash-2" class="icon-xs me-1"></i> Delete
      </button>
    </form>
    @if($invoice->status !== 'cancelled' && !$invoice->isPaid())
      <form method="POST" action="{{ route('payments.send', $invoice) }}">
        @csrf
        <button class="btn btn-dark" type="submit">
          <i data-lucide="send" class="icon-xs me-1"></i> Email invoice
        </button>
      </form>
    @endif
  </div>
</div>

<div class="row g-3">
  <div class="col-12">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h6 class="mb-1">{{ $settings->company_name }}</h6>
            <small class="text-muted">
              {{ $settings->company_email }}
              @if($settings->company_phone) · {{ $settings->company_phone }}@endif
            </small>
            @if($settings->company_address)
              <div class="text-muted small">{{ $settings->company_address }}</div>
            @endif
          </div>
          <span class="badge rounded-pill px-3 py-1" style="{{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="text-muted small">Bill to</label>
            <div class="fw-semibold">{{ $invoice->customer?->name }}</div>
            <small class="text-muted">{{ $invoice->customer?->email }}</small>
          </div>
          <div class="col-md-4">
            <label class="text-muted small">Issue / Due</label>
            <div class="fw-semibold">{{ $invoice->issue_date?->format('d M Y') }} → {{ $invoice->due_date?->format('d M Y') }}</div>
          </div>
          <div class="col-md-4">
            <label class="text-muted small">Student</label>
            <div class="fw-semibold">{{ $invoice->student?->name ?: '—' }}</div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Description</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($invoice->items as $item)
                <tr>
                  <td>{{ $item->description }}</td>
                  <td class="text-end">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                  <td class="text-end">{{ $invoice->formatMoney((float) $item->unit_price) }}</td>
                  <td class="text-end">{{ $invoice->formatMoney((float) $item->total) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="row">
          <div class="col-md-5 ms-auto">
            <table class="table mb-0">
              <tr><td>Subtotal</td><td class="text-end">{{ $invoice->formatMoney((float) $invoice->subtotal) }}</td></tr>
              <tr><td>Tax ({{ rtrim(rtrim(number_format($invoice->tax_percent, 2), '0'), '.') }}%)</td><td class="text-end">{{ $invoice->formatMoney((float) $invoice->tax_amount) }}</td></tr>
              <tr><td class="fw-bold">Total</td><td class="text-end fw-bold">{{ $invoice->formattedTotal() }}</td></tr>
              <tr><td>Paid</td><td class="text-end fw-semibold">{{ $invoice->formatMoney((float) $invoice->amount_paid) }}</td></tr>
              <tr><td class="fw-bold">Balance</td><td class="text-end fw-bold">{{ $invoice->formatMoney($invoice->balance()) }}</td></tr>
            </table>
          </div>
        </div>
        @if($invoice->notes)
          <p class="text-muted small mt-3 mb-0">{{ $invoice->notes }}</p>
        @endif
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h6 class="mb-0">Payments</h6></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table align-middle mb-0 w-100">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Amount</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($invoice->payments as $payment)
                <tr>
                  <td class="ps-4">{{ $payment->created_at?->format('d M Y, h:i A') }}</td>
                  <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                  <td>{{ $payment->reference ?: '—' }}</td>
                  <td>{{ $invoice->formatMoney((float) $payment->amount) }}</td>
                  <td>{{ ucfirst($payment->status) }}</td>
                  <td class="text-center">
                    <div class="action-btns">
                      @if($payment->proof_path)
                        <a href="{{ $payment->proofUrl() }}" target="_blank" class="action-btn action-btn-view" title="View">
                          <i data-lucide="eye"></i>
                        </a>
                      @endif
                      @if($payment->method === 'bank_transfer' && $payment->status === 'pending')
                        <form method="POST" action="{{ route('payments.confirm-bank', $payment) }}">
                          @csrf
                          <button type="submit" class="action-btn action-btn-add" title="Confirm"
                                  onclick="return confirm('Confirm this bank transfer? A receipt will be emailed.')">
                            <i data-lucide="check"></i>
                          </button>
                        </form>
                        <form method="POST" action="{{ route('payments.reject-bank', $payment) }}" class="d-inline"
                              onsubmit="return rejectPayment(event, this)">
                          @csrf
                          <input type="hidden" name="reason" value="">
                          <button type="submit" class="action-btn action-btn-view" title="Reject">
                            <i data-lucide="x"></i>
                          </button>
                        </form>
                      @endif
                      @if($payment->status === 'completed')
                        <form method="POST" action="{{ route('payments.refund', $payment) }}" class="d-inline"
                              onsubmit="return refundPayment(event, this)">
                          @csrf
                          <input type="hidden" name="reason" value="">
                          <button type="submit" class="action-btn action-btn-view" title="Refund">
                            <i data-lucide="undo-2"></i>
                          </button>
                        </form>
                      @endif
                      <form action="{{ route('payments.payments.destroy', $payment) }}" method="POST"
                            onsubmit="confirmDeletePayment(event, this)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn action-btn-view" title="Delete">
                          <i data-lucide="trash-2"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  @if($invoice->isPayable())
  <div class="col-lg-6">
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Record bank payment</h6></div>
      <div class="card-body">
        <p class="text-secondary small">When payment is received in the bank account, the invoice PDF is emailed to the customer automatically.</p>
        <form method="POST" action="{{ route('payments.record', $invoice) }}">
          @csrf
          <label class="form-label">Bank</label>
          @php
            $selectedPayBank = old('bank_name', $settings->bank_name);
            $payBanks = $banks ?? [];
            if (filled($selectedPayBank) && !in_array($selectedPayBank, $payBanks, true)) {
                array_unshift($payBanks, $selectedPayBank);
            }
          @endphp
          <select name="bank_name" class="form-select mb-3" required>
            <option value="">Select bank</option>
            @foreach($payBanks as $bank)
              <option value="{{ $bank }}" {{ $selectedPayBank === $bank ? 'selected' : '' }}>{{ $bank }}</option>
            @endforeach
          </select>
          <label class="form-label">Amount received</label>
          <input type="number" step="0.01" min="0.01" name="amount" class="form-control mb-3"
                 value="{{ old('amount', $invoice->balance()) }}" required>
          <label class="form-label">Transaction reference</label>
          <input type="text" name="reference" class="form-control mb-3" placeholder="Bank reference / slip number" value="{{ old('reference') }}">
          <label class="form-label">Note</label>
          <textarea name="notes" class="form-control mb-3" rows="2" placeholder="Optional">{{ old('notes') }}</textarea>
          <button class="btn btn-dark w-100" type="submit"
                  onclick="return confirm('Mark this bank payment as received and email the invoice?')">
            Mark received &amp; email invoice
          </button>
        </form>
      </div>
    </div>
    <form method="POST" action="{{ route('payments.cancel', $invoice) }}">
      @csrf
      <button class="btn btn-outline-dark w-100" onclick="return confirm('Cancel this invoice?')">Cancel invoice</button>
    </form>
  </div>
  @endif

  @if($invoice->isPayable() && $settings->hasBankDetails())
  <div class="col-lg-6">
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Bank account</h6></div>
      <div class="card-body">
        <div class="mb-2"><small class="text-muted">Bank</small><div class="fw-semibold">{{ $settings->bank_name }}</div></div>
        <div class="mb-2"><small class="text-muted">Account title</small><div class="fw-semibold">{{ $settings->bank_account_title }}</div></div>
        <div class="mb-2"><small class="text-muted">Account number</small><div class="fw-semibold">{{ $settings->bank_account_number }}</div></div>
        @if($settings->bank_iban)<div class="mb-2"><small class="text-muted">IBAN</small><div class="fw-semibold">{{ $settings->bank_iban }}</div></div>@endif
        <p class="small text-muted mb-0">Ask the customer to use <strong>{{ $invoice->invoice_number }}</strong> as the transfer reference.</p>
      </div>
    </div>
  </div>
  @endif
</div>

@endsection

@push('custom-scripts')
<script>
  function confirmDeleteInvoice(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Delete this invoice?',
      text: 'This will also remove its payments and cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (result) {
      if (result.isConfirmed) form.submit();
    });
  }
  function confirmDeletePayment(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Delete this payment?',
      text: 'The invoice totals will be recalculated.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then(function (result) {
      if (result.isConfirmed) form.submit();
    });
  }
  function rejectPayment(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Reject this transfer?',
      input: 'text',
      inputPlaceholder: 'Reason for the customer',
      inputValidator: function (value) { if (!value) return 'Reason is required'; },
      showCancelButton: true,
      confirmButtonText: 'Reject'
    }).then(function (result) {
      if (result.isConfirmed) {
        form.querySelector('[name="reason"]').value = result.value;
        form.submit();
      }
    });
  }
  function refundPayment(event, form) {
    event.preventDefault();
    Swal.fire({
      title: 'Refund this payment?',
      input: 'text',
      inputPlaceholder: 'Refund reason',
      inputValidator: function (value) { if (!value) return 'Reason is required'; },
      showCancelButton: true,
      confirmButtonText: 'Refund'
    }).then(function (result) {
      if (result.isConfirmed) {
        form.querySelector('[name="reason"]').value = result.value;
        form.submit();
      }
    });
  }
</script>
@endpush
