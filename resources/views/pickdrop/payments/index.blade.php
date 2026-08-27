@extends('layout.master')
@section('page-content-class', 'container-fluid')

@php
  $statusBadge = function (?string $status) {
      return match ($status) {
          'paid' => ['Paid', 'background:#f3f4f6;color:#111111;'],
          'unpaid' => ['Unpaid', 'background:#fef9c3;color:#92400e;'],
          'overdue' => ['Overdue', 'background:#fee2e2;color:#991b1b;'],
          'cancelled' => ['Cancelled', 'background:#e5e7eb;color:#374151;'],
          default => ['Draft', 'background:#e0e7ff;color:#3730a3;'],
      };
  };
  $collected = (float) ($summary['collected'] ?? 0);
  $pending = (float) ($summary['pending'] ?? 0);
  $overdue = (float) ($summary['overdue'] ?? 0);
  $outstanding = $pending + $overdue;
  $all = $collected + $outstanding;
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Invoices &amp; Payments</h4>
    <p class="text-secondary mb-0">Issue invoices and record bank payments. Invoice PDFs are emailed automatically when a payment is made.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('payments.settings') }}" class="btn btn-outline-dark">
      <i data-lucide="settings" class="icon-xs me-1"></i> Bank account
    </a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
      <i data-lucide="plus" class="icon-xs me-1"></i> New Invoice
    </button>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Collected</p>
        <h4 class="fw-bold mb-0">PKR {{ number_format($collected, 2) }}</h4>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Unpaid</p>
        <h4 class="fw-bold text-warning mb-0">PKR {{ number_format($pending, 2) }}</h4>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Overdue</p>
        <h4 class="fw-bold text-danger mb-0">PKR {{ number_format($overdue, 2) }}</h4>
      </div>
    </div>
  </div>
</div>

@if($all > 0)
<div class="card mb-3">
  <div class="card-body">
    <h6 class="fw-bold mb-3">Collection overview</h6>
    <div class="d-flex justify-content-between mb-1">
      <span class="fs-13px">Collected vs outstanding</span>
      <span class="fs-13px fw-semibold">PKR {{ number_format($collected, 2) }} / {{ number_format($all, 2) }}</span>
    </div>
    <div class="progress" style="height:8px;">
      <div class="progress-bar bg-dark" style="width: {{ $all ? min(100, round($collected / $all * 100)) : 0 }}%;"></div>
    </div>
  </div>
</div>
@endif

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('payments.index') }}">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search invoice, customer, student..."
                 value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['draft','unpaid','paid','overdue','cancelled'] as $st)
              <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="method" onchange="this.form.submit()">
            <option value="">All methods</option>
            <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank transfer</option>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
          @if(request()->hasAny(['search','status','method']))
            <a href="{{ route('payments.index') }}" class="btn btn-outline-danger">Clear</a>
          @endif
        </div>
        <div class="col"></div>
        <div class="col-auto">
          <a href="{{ route('payments.export', request()->query()) }}" class="btn btn-outline-primary">
            <i data-lucide="download" class="icon-xs me-1"></i> Export CSV
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 w-100">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Invoice</th>
            <th class="py-3">Customer</th>
            <th class="py-3">Student</th>
            <th class="py-3">Due</th>
            <th class="py-3">Amount</th>
            <th class="py-3">Method</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoices as $invoice)
            @php [$label, $style] = $statusBadge($invoice->status); @endphp
            <tr>
              <td class="ps-4 fw-semibold">{{ $invoice->invoice_number }}</td>
              <td>
                <div>{{ $invoice->customer?->name }}</div>
                <small class="text-muted">{{ $invoice->customer?->email }}</small>
              </td>
              <td>{{ $invoice->student?->name ?? '—' }}</td>
              <td>{{ $invoice->due_date?->format('d M Y') }}</td>
              <td class="fw-semibold">{{ $invoice->formattedTotal() }}</td>
              <td>{{ $invoice->payment_method ? str_replace('_', ' ', $invoice->payment_method) : '—' }}</td>
              <td><span class="badge rounded-pill px-3 py-1" style="{{ $style }}">{{ $label }}</span></td>
              <td class="text-center">
                <div class="action-btns">
                  <a href="{{ route('payments.show', $invoice) }}" class="action-btn action-btn-view" title="View">
                    <i data-lucide="eye"></i>
                  </a>
                  <form action="{{ route('payments.destroy', $invoice) }}" method="POST"
                        onsubmit="confirmDelete(event, this)">
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
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">No invoices yet. Create one to bill a parent or self customer.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-app-pagination :paginator="$invoices" label="invoices" />
</div>

<div class="modal fade" id="createInvoiceModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('payments.store') }}" id="createInvoiceForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">New invoice</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Customer</label>
              <select name="user_id" id="invoiceCustomer" class="form-select" required>
                <option value="">Select parent / self</option>
                @foreach($customers as $customer)
                  <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }}) · {{ ucfirst($customer->role) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Student (optional)</label>
              <select name="student_id" id="invoiceStudent" class="form-select">
                <option value="">None</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Issue date</label>
              <input type="date" name="issue_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Due date</label>
              <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(7)->toDateString() }}" required>
            </div>
            <div class="col-12">
              <label class="form-label">Line items</label>
              <div id="invoiceItems"></div>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addInvoiceItem">Add item</button>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Shown on the invoice"></textarea>
            </div>
            <div class="col-12">
              <div class="alert alert-light border mb-0 py-2">
                Invoice email customer ko automatically chali jayegi. Payment ke baad receipt bhi khud send hogi.
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create invoice</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('custom-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const students = @json($studentsByParent);
  const customerSelect = document.getElementById('invoiceCustomer');
  const studentSelect = document.getElementById('invoiceStudent');
  const itemsWrap = document.getElementById('invoiceItems');
  let itemIndex = 0;

  function addItem(description, qty, price) {
    const i = itemIndex++;
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 invoice-item-row';
    row.innerHTML = `
      <div class="col-md-6"><input name="items[${i}][description]" class="form-control" placeholder="Description" value="${description || ''}" required></div>
      <div class="col-md-2"><input type="number" step="0.01" min="0.01" name="items[${i}][quantity]" class="form-control" value="${qty || 1}" required></div>
      <div class="col-md-3"><input type="number" step="0.01" min="0" name="items[${i}][unit_price]" class="form-control" placeholder="Unit price" value="${price || ''}" required></div>
      <div class="col-md-1 d-flex"><button type="button" class="btn btn-outline-danger w-100 remove-item">&times;</button></div>
    `;
    itemsWrap.appendChild(row);
  }

  document.getElementById('addInvoiceItem').addEventListener('click', function () { addItem('', 1, ''); });
  itemsWrap.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-item')) {
      const rows = itemsWrap.querySelectorAll('.invoice-item-row');
      if (rows.length > 1) e.target.closest('.invoice-item-row').remove();
    }
  });
  addItem('Monthly pick & drop service', 1, '');

  customerSelect.addEventListener('change', function () {
    const list = students[this.value] || students[String(this.value)] || [];
    studentSelect.innerHTML = '<option value="">None</option>';
    list.forEach(function (s) {
      const opt = document.createElement('option');
      opt.value = s.id;
      opt.textContent = s.name;
      studentSelect.appendChild(opt);
    });
  });
});
</script>
<script>
  function confirmDelete(event, form) {
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
</script>
@endpush
