@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Payments</h4>
    <p class="text-secondary mb-0">Manage all payment transactions</p>
  </div>
</div>

{{-- Search + Filter + Export --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-2 align-items-center">
      <div class="col-12 col-md-4">
        <div class="input-group">
          <div class="input-group-text bg-transparent border-end-0">
            <i data-lucide="search" style="width:16px;height:16px;"></i>
          </div>
          <input type="text" class="form-control border-start-0 ps-0" id="invoiceSearch" placeholder="Search invoices...">
        </div>
      </div>
      <div class="col"></div>
      <div class="col-auto d-flex gap-2">
        <button class="btn btn-success d-flex align-items-center gap-1">
          <i data-lucide="filter" style="width:15px;height:15px;"></i> Filter
        </button>
        <button class="btn btn-success d-flex align-items-center gap-1">
          <i data-lucide="download" style="width:15px;height:15px;"></i> Export
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Invoice Table --}}
<div class="card mb-3">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Invoice</th>
            <th class="py-3">Parent</th>
            <th class="py-3">Student</th>
            <th class="py-3">Date</th>
            <th class="py-3">Amount</th>
            <th class="py-3">Status</th>
            <th class="py-3">Actions</th>
          </tr>
        </thead>
        <tbody id="invoiceTableBody">
          <tr>
            <td class="ps-4 py-3 fw-semibold">INV-001</td>
            <td class="py-3">Sarah Johnson</td>
            <td class="py-3">Emma Johnson</td>
            <td class="py-3 text-secondary">May 1, 2023</td>
            <td class="py-3 fw-semibold">$75.00</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Paid</span>
            </td>
            <td class="py-3">
              <a href="#" class="text-primary fw-semibold fs-13px" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal">View</a>
            </td>
          </tr>
          <tr>
            <td class="ps-4 py-3 fw-semibold">INV-002</td>
            <td class="py-3">Michael Brown</td>
            <td class="py-3">James Brown</td>
            <td class="py-3 text-secondary">May 1, 2023</td>
            <td class="py-3 fw-semibold">$75.00</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Paid</span>
            </td>
            <td class="py-3">
              <a href="#" class="text-primary fw-semibold fs-13px" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal">View</a>
            </td>
          </tr>
          <tr>
            <td class="ps-4 py-3 fw-semibold">INV-003</td>
            <td class="py-3">Robert Davis</td>
            <td class="py-3">Olivia Davis</td>
            <td class="py-3 text-secondary">May 1, 2023</td>
            <td class="py-3 fw-semibold">$75.00</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#fef9c3;color:#92400e;">Pending</span>
            </td>
            <td class="py-3">
              <a href="#" class="text-primary fw-semibold fs-13px" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal">View</a>
            </td>
          </tr>
          <tr>
            <td class="ps-4 py-3 fw-semibold">INV-004</td>
            <td class="py-3">Jennifer Wilson</td>
            <td class="py-3">Liam Wilson</td>
            <td class="py-3 text-secondary">April 1, 2023</td>
            <td class="py-3 fw-semibold">$75.00</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Paid</span>
            </td>
            <td class="py-3">
              <a href="#" class="text-primary fw-semibold fs-13px" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal">View</a>
            </td>
          </tr>
          <tr>
            <td class="ps-4 py-3 fw-semibold">INV-005</td>
            <td class="py-3">Thomas Moore</td>
            <td class="py-3">Sophia Moore</td>
            <td class="py-3 text-secondary">April 1, 2023</td>
            <td class="py-3 fw-semibold">$75.00</td>
            <td class="py-3">
              <span class="badge rounded-pill px-3 py-1" style="background:#fee2e2;color:#991b1b;">Overdue</span>
            </td>
            <td class="py-3">
              <a href="#" class="text-primary fw-semibold fs-13px" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal">View</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  {{-- Pagination --}}
  <div class="card-footer d-flex justify-content-between align-items-center py-3">
    <small class="text-secondary">Showing 1–5 of 24 invoices</small>
    <nav>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
      </ul>
    </nav>
  </div>
</div>

{{-- Payment Summary --}}
<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Payment Summary</h6>
        <div class="mb-3">
          <p class="text-secondary fs-13px mb-1">Total Collected (May)</p>
          <h3 class="fw-bold mb-0">$2,250.00</h3>
        </div>
        <div class="mb-3">
          <p class="text-secondary fs-13px mb-1">Pending</p>
          <h4 class="fw-bold text-warning mb-0">$375.00</h4>
        </div>
        <div>
          <p class="text-secondary fs-13px mb-1">Overdue</p>
          <h4 class="fw-bold text-danger mb-0">$150.00</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Collection Overview</h6>
        <div class="d-flex flex-column gap-3">
          <div>
            <div class="d-flex justify-content-between mb-1">
              <span class="fs-13px">Collected</span>
              <span class="fs-13px fw-semibold text-success">$2,250 / $2,775</span>
            </div>
            <div class="progress" style="height:8px;">
              <div class="progress-bar bg-success" style="width:81%;"></div>
            </div>
          </div>
          <div>
            <div class="d-flex justify-content-between mb-1">
              <span class="fs-13px">Pending</span>
              <span class="fs-13px fw-semibold text-warning">$375 / $2,775</span>
            </div>
            <div class="progress" style="height:8px;">
              <div class="progress-bar bg-warning" style="width:14%;"></div>
            </div>
          </div>
          <div>
            <div class="d-flex justify-content-between mb-1">
              <span class="fs-13px">Overdue</span>
              <span class="fs-13px fw-semibold text-danger">$150 / $2,775</span>
            </div>
            <div class="progress" style="height:8px;">
              <div class="progress-bar bg-danger" style="width:5%;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


{{-- ========== VIEW INVOICE MODAL ========== --}}
<div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Invoice – INV-001</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Invoice No.</p>
            <p class="fw-semibold mb-0">INV-001</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Date</p>
            <p class="fw-semibold mb-0">May 1, 2023</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Parent</p>
            <p class="fw-semibold mb-0">Sarah Johnson</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Student</p>
            <p class="fw-semibold mb-0">Emma Johnson</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Amount</p>
            <p class="fw-semibold mb-0">$75.00</p>
          </div>
          <div class="col-6">
            <p class="text-secondary fs-12px mb-0">Status</p>
            <span class="badge rounded-pill px-3 py-1" style="background:#d1fae5;color:#065f46;">Paid</span>
          </div>
          <div class="col-12">
            <p class="text-secondary fs-12px mb-0">Route</p>
            <p class="fw-semibold mb-0">Route #R-123 – Central Elementary Morning</p>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success px-4">
          <i data-lucide="download" class="icon-xs me-1"></i> Download PDF
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('custom-scripts')
<script>
  // Live search
  document.getElementById('invoiceSearch').addEventListener('keyup', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#invoiceTableBody tr').forEach(row => {
      row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
  });
</script>
@endpush
