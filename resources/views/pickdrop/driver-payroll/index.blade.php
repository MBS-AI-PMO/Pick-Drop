@extends('layout.master')
@section('page-content-class', 'container-fluid')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Driver Payments</h4>
    <p class="text-secondary mb-0">Review driver payment requests, generate shift-wise bills, and record payouts.</p>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Pending review</p>
        <h4 class="fw-bold text-warning mb-0">{{ $summary['pending_count'] }}</h4>
        <small class="text-muted">PKR {{ number_format((float) $summary['pending'], 2) }}</small>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Approved</p>
        <h4 class="fw-bold mb-0" style="color:#3f6fd9;">PKR {{ number_format((float) $summary['approved'], 2) }}</h4>
        <small class="text-muted">Awaiting payout</small>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Total paid</p>
        <h4 class="fw-bold text-success mb-0">PKR {{ number_format((float) $summary['paid'], 2) }}</h4>
        <small class="text-muted">All time</small>
      </div>
    </div>
  </div>
</div>

<div class="payroll-ready mb-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
    <div>
      <h5 class="mb-1">Ready for billing</h5>
      <p class="text-secondary mb-0 fs-13px">Completed months with unpaid shifts — generate bills from attendance.</p>
    </div>
    @php
      $pickerMonth = filled($readyMonth ?? null)
        ? \Illuminate\Support\Carbon::parse($readyMonth . '-01')
        : now()->startOfMonth();
      $pickerYear = (int) $pickerMonth->year;
      $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      $queryBase = request()->except(['ready_month', 'page', 'ready_page']);
    @endphp
    <div class="payroll-month-picker" data-payroll-month-picker>
      <button type="button"
              class="payroll-month-picker__trigger"
              data-picker-toggle
              aria-expanded="false"
              aria-haspopup="true">
        <i data-lucide="calendar" class="payroll-month-picker__icon"></i>
        <span data-picker-label>
          {{ filled($readyMonth ?? null) ? $pickerMonth->format('F Y') : 'Select month' }}
        </span>
        <i data-lucide="chevron-down" class="payroll-month-picker__caret"></i>
      </button>
      @if(filled($readyMonth ?? null))
        <a href="{{ route('driver-payroll.index', $queryBase) }}"
           class="payroll-month-picker__clear"
           title="Clear month">
          <i data-lucide="x"></i>
        </a>
      @endif

      <div class="payroll-month-picker__panel" data-picker-panel hidden>
        <div class="payroll-month-picker__header">
          <button type="button" class="payroll-month-picker__nav" data-picker-prev aria-label="Previous year">
            <i data-lucide="chevron-left"></i>
          </button>
          <strong data-picker-year>{{ $pickerYear }}</strong>
          <button type="button" class="payroll-month-picker__nav" data-picker-next aria-label="Next year">
            <i data-lucide="chevron-right"></i>
          </button>
        </div>
        <div class="payroll-month-picker__grid" data-picker-grid>
          @for($m = 1; $m <= 12; $m++)
            @php $value = sprintf('%04d-%02d', $pickerYear, $m); @endphp
            <a href="{{ route('driver-payroll.index', array_merge($queryBase, ['ready_month' => $value])) }}"
               class="payroll-month-picker__month {{ ($readyMonth ?? '') === $value ? 'is-active' : '' }}"
               data-month="{{ $m }}">
              {{ $monthNames[$m - 1] }}
            </a>
          @endfor
        </div>
      </div>
    </div>
  </div>

  @if(($groupedReady ?? null) && $groupedReady->isNotEmpty())
    <div class="row g-3">
      @foreach($groupedReady as $group)
        <div class="col-12">
          <div class="payroll-ready-card">
            <div class="payroll-ready-card__main">
              <div class="payroll-ready-card__avatar">
                {{ strtoupper(substr($group['driver_name'], 0, 1)) }}
              </div>
              <div class="payroll-ready-card__info">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                  <h6 class="mb-0">{{ $group['driver_name'] }}</h6>
                  <span class="payroll-ready-badge">{{ $group['period_label'] }}</span>
                </div>
                <p class="text-secondary fs-13px mb-0">
                  {{ $group['driver_phone'] ?: 'No phone' }}
                  · <strong>{{ $group['shift_count'] }}</strong> total shift{{ $group['shift_count'] === 1 ? '' : 's' }} this month
                </p>
              </div>
            </div>

            <div class="payroll-ready-card__amount">
              <span class="text-secondary fs-12px d-block mb-1">Estimated total</span>
              <strong class="fs-5">PKR {{ number_format((float) $group['total_estimated'], 2) }}</strong>
            </div>

            <div class="payroll-ready-card__actions">
              <a href="{{ route('driver-payroll.driver', ['user' => $group['driver_id'], 'month' => $group['month']]) }}"
                 class="btn btn-outline-secondary">
                View details
              </a>
              <form method="POST" action="{{ route('driver-payroll.generate-all', $group['driver_id']) }}">
                @csrf
                <input type="hidden" name="month" value="{{ $group['month'] }}">
                <button type="submit" class="btn btn-primary">Generate bills</button>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <div class="mt-3">
      <x-app-pagination :paginator="$groupedReady" label="drivers" class="border-0 px-0" />
    </div>
  @else
    <div class="payroll-ready-empty">
      <i data-lucide="wallet" class="icon-lg mb-2"></i>
      <p class="fw-semibold mb-1">Nothing ready for billing</p>
      <p class="text-secondary fs-13px mb-0">
        @if(filled($readyMonth ?? null))
          No completed shifts for this month yet.
        @else
          Completed months will appear here when drivers have unpaid shifts.
        @endif
      </p>
    </div>
  @endif
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('driver-payroll.index') }}">
      @if(filled($readyMonth ?? null))
        <input type="hidden" name="ready_month" value="{{ $readyMonth }}">
      @endif
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search bill, driver, student..."
                 value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected'] as $val => $label)
              <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="bill_month" onchange="this.form.submit()">
            <option value="">All months</option>
            @foreach($billMonths ?? [] as $monthOption)
              <option value="{{ $monthOption['value'] }}" {{ request('bill_month') === $monthOption['value'] ? 'selected' : '' }}>
                {{ $monthOption['label'] }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
          @if(request()->hasAny(['search', 'status', 'bill_month']))
            <a href="{{ route('driver-payroll.index', array_filter(['ready_month' => $readyMonth ?? null])) }}" class="btn btn-outline-danger">Clear</a>
          @endif
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h6 class="mb-0">Payment bills</h6>
  </div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="py-3 ps-3">Bill #</th>
          <th class="py-3">Driver</th>
          <th class="py-3">Shift / Student</th>
          <th class="py-3">Period</th>
          <th class="py-3">Amount</th>
          <th class="py-3">Status</th>
          <th class="py-3 text-end pe-3">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($bills as $bill)
          <tr>
            <td class="fw-semibold ps-3">{{ $bill->bill_number }}</td>
            <td>
              <a href="{{ route('driver-payroll.driver', $bill->driver_id) }}" class="fw-semibold text-decoration-none">
                {{ $bill->driver?->name }}
              </a>
            </td>
            <td>
              <div>{{ $bill->pickupRequest?->student?->name ?: '—' }}</div>
              <small class="text-muted">Shift #{{ $bill->pickup_request_id }}</small>
            </td>
            <td>{{ $bill->period_start->format('d M') }} – {{ $bill->period_end->format('d M Y') }}</td>
            <td class="fw-bold">{{ $bill->formattedAmount() }}</td>
            <td>
              <span class="badge rounded-pill px-3 py-1" style="{{ $bill->statusBadgeStyle() }}">{{ $bill->statusLabel() }}</span>
            </td>
            <td class="text-end pe-3">
              <a href="{{ route('driver-payroll.show', $bill) }}" class="btn btn-sm btn-outline-secondary">Open bill</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-5 text-secondary">
              <i data-lucide="receipt" class="icon-lg mb-2 d-block mx-auto"></i>
              <p class="mb-1 fw-semibold">No payment bills yet</p>
              <span class="fs-13px">Bills appear here after generation from completed shift periods.</span>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <x-app-pagination :paginator="$bills" label="bills" />
</div>

@endsection

@push('style')
<style>
  .payroll-month-picker {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .payroll-month-picker__trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 14px;
    border: 1px solid #e5ecf5;
    border-radius: 10px;
    background: #ffffff;
    color: #1d3557;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(29, 53, 87, 0.04);
    cursor: pointer;
  }
  .payroll-month-picker__trigger:hover {
    border-color: #d0dae8;
  }
  .payroll-month-picker__icon,
  .payroll-month-picker__caret {
    width: 16px;
    height: 16px;
    color: #64748b;
  }
  .payroll-month-picker__clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e5ecf5;
    background: #fff;
    color: #64748b;
    text-decoration: none;
  }
  .payroll-month-picker__clear:hover {
    color: #1d3557;
    border-color: #d0dae8;
  }
  .payroll-month-picker__clear i {
    width: 14px;
    height: 14px;
  }
  .payroll-month-picker__panel {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    z-index: 30;
    width: 268px;
    padding: 14px;
    border: 1px solid #edf1f7;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 16px 40px rgba(29, 53, 87, 0.12);
  }
  .payroll-month-picker__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
  }
  .payroll-month-picker__header strong {
    font-size: 14px;
    color: #1d3557;
  }
  .payroll-month-picker__nav {
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 8px;
    background: #f4f7fb;
    color: #1d3557;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .payroll-month-picker__nav:hover {
    background: #e8eef8;
  }
  .payroll-month-picker__nav i {
    width: 16px;
    height: 16px;
  }
  .payroll-month-picker__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
  }
  .payroll-month-picker__month {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
  }
  .payroll-month-picker__month:hover {
    background: #eef4ff;
    color: #3f6fd9;
  }
  .payroll-month-picker__month.is-active {
    background: #1d3557;
    color: #ffffff;
  }

  .payroll-ready-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    padding: 18px 20px;
    background: #ffffff;
    border: 1px solid #edf1f7;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(29, 53, 87, 0.04);
  }
  .payroll-ready-card__main {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 260px;
    flex: 1 1 280px;
  }
  .payroll-ready-card__avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #eef4ff;
    color: #3f6fd9;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    flex: 0 0 44px;
  }
  .payroll-ready-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: #fff7e6;
    color: #b7791f;
    font-size: 11px;
    font-weight: 700;
  }
  .payroll-ready-card__amount {
    min-width: 140px;
    text-align: right;
  }
  .payroll-ready-card__actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }
  .payroll-ready-card__actions form {
    margin: 0;
  }
  .payroll-ready-empty {
    text-align: center;
    padding: 42px 20px;
    background: #ffffff;
    border: 1px dashed #e5ecf5;
    border-radius: 12px;
    color: #64748b;
  }

  @media (max-width: 767.98px) {
    .payroll-ready-card__amount {
      text-align: left;
      width: 100%;
    }
    .payroll-ready-card__actions {
      width: 100%;
    }
    .payroll-ready-card__actions .btn {
      flex: 1 1 auto;
    }
    .payroll-month-picker__panel {
      right: auto;
      left: 0;
    }
  }

  [data-bs-theme="dark"] .payroll-month-picker__trigger,
  [data-bs-theme="dark"] .payroll-month-picker__clear,
  [data-bs-theme="dark"] .payroll-month-picker__panel,
  [data-bs-theme="dark"] .payroll-ready-card,
  [data-bs-theme="dark"] .payroll-ready-empty {
    background: #1e2129;
    border-color: rgba(255,255,255,0.08);
    color: #e2e8f0;
  }
  [data-bs-theme="dark"] .payroll-month-picker__nav,
  [data-bs-theme="dark"] .payroll-month-picker__month {
    background: rgba(255,255,255,0.04);
    color: #cbd5e1;
  }
  [data-bs-theme="dark"] .payroll-month-picker__month.is-active {
    background: #3f6fd9;
    color: #ffffff;
  }
  [data-bs-theme="dark"] .payroll-ready-card__avatar {
    background: rgba(63, 111, 217, 0.18);
    color: #93b4ff;
  }
</style>
@endpush

@push('custom-scripts')
<script>
  (function () {
    const root = document.querySelector('[data-payroll-month-picker]');
    if (!root) return;

    const toggle = root.querySelector('[data-picker-toggle]');
    const panel = root.querySelector('[data-picker-panel]');
    const yearEl = root.querySelector('[data-picker-year]');
    const grid = root.querySelector('[data-picker-grid]');
    const prev = root.querySelector('[data-picker-prev]');
    const next = root.querySelector('[data-picker-next]');
    const selected = @json($readyMonth ?? null);
    const baseParams = @json($queryBase);
    const indexUrl = @json(route('driver-payroll.index'));

    let year = Number(yearEl.textContent);

    function buildUrl(monthValue) {
      const params = new URLSearchParams();
      Object.keys(baseParams || {}).forEach((key) => {
        const value = baseParams[key];
        if (value === null || value === undefined || value === '') return;
        params.set(key, value);
      });
      params.set('ready_month', monthValue);
      const query = params.toString();
      return query ? `${indexUrl}?${query}` : `${indexUrl}?ready_month=${monthValue}`;
    }

    function renderMonths() {
      yearEl.textContent = String(year);
      const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      grid.innerHTML = labels.map((label, index) => {
        const month = String(index + 1).padStart(2, '0');
        const value = `${year}-${month}`;
        const active = selected === value ? ' is-active' : '';
        return `<a href="${buildUrl(value)}" class="payroll-month-picker__month${active}" data-month="${index + 1}">${label}</a>`;
      }).join('');
    }

    function setOpen(open) {
      panel.hidden = !open;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open && window.lucide) {
        window.lucide.createIcons();
      }
    }

    toggle.addEventListener('click', (e) => {
      e.stopPropagation();
      setOpen(panel.hidden);
    });

    prev.addEventListener('click', (e) => {
      e.stopPropagation();
      year -= 1;
      renderMonths();
    });

    next.addEventListener('click', (e) => {
      e.stopPropagation();
      year += 1;
      renderMonths();
    });

    document.addEventListener('click', (e) => {
      if (!root.contains(e.target)) setOpen(false);
    });
  })();
</script>
@endpush
